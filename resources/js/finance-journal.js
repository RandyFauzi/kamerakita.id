export default function financeJournal(initial) {
    const fields = ['entry_date', 'type', 'description', 'category', 'amount'];
    const amountValue = value => {
        const text = String(value).trim();
        return /^\d+$/.test(text) ? Number(text) : /^\d{1,3}(\.\d{3})+$/.test(text) ? Number(text.replaceAll('.', '')) : null;
    };
    const fingerprint = row => JSON.stringify(fields.map(key => key === 'amount' && amountValue(row[key]) !== null ? String(amountValue(row[key])) : String(row[key] ?? '').trim()));
    const ready = row => /^\d{4}-\d{2}-\d{2}$/.test(row.entry_date) && row.description.trim() && row.category.trim()
        && amountValue(row.amount) > 0 && amountValue(row.amount) <= 999999999999;
    const decorate = row => ({ ...row, key: row.client_key || `entry-${row.id}`, status: 'saved', error: '', busy: false, dirty: false, saved: fingerprint(row) });
    return {
        ...initial, rows: initial.rows.map(decorate), recipients: initial.recipients.length ? initial.recipients : [{ name: '', percent: 100 }],
        notice: '', noticeError: false, refreshing: false, settingsBusy: false, distributionBusy: false, timers: new Map(),
        init() {
            if (!this.closed) this.addRow(false);
            window.addEventListener('beforeunload', event => {
                if (this.unsaved) { event.preventDefault(); event.returnValue = ''; }
            });
        },
        get unsaved() { return this.rows.some(row => row.dirty || row.busy || row.status === 'error'); },
        money(value) { return 'Rp ' + Number(value || 0).toLocaleString('id-ID'); },
        typeLabel(type) { return { income: 'Pemasukan', expense: 'Biaya', capital: 'Modal / saldo awal', distribution: 'Bagi profit' }[type]; },
        statusLabel(row) { return { draft: 'Baris baru', incomplete: 'Lengkapi baris', dirty: 'Belum tersimpan', saving: 'Menyimpan…', saved: 'Tersimpan', error: 'Gagal disimpan' }[row.status]; },
        peak() { return Math.max(1, ...this.trend.flatMap(bar => [bar.income, bar.expense])); },
        addRow(focus = true) {
            if (this.closed) return;
            const key = crypto.randomUUID();
            this.rows.push({ id: null, client_key: key, key, revision: 1, entry_date: this.month === this.today.slice(0, 7) ? this.today : this.month + '-01',
                type: 'expense', description: '', category: '', amount: '', status: 'draft', error: '', busy: false, dirty: false, saved: '' });
            if (focus) this.$nextTick(() => this.$refs.grid.querySelector(`[data-row="${key}"] [data-cell]`)?.focus());
        },
        changed(row) {
            row.dirty = fingerprint(row) !== row.saved;
            row.error = '';
            if (!row.busy) row.status = row.dirty ? (ready(row) ? 'dirty' : 'incomplete') : 'saved';
        },
        queue(row, delay = 500) {
            clearTimeout(this.timers.get(row.key));
            if (this.closed || !row.dirty || row.type === 'distribution') return;
            if (!ready(row)) {
                row.status = 'incomplete';
                if (String(row.amount).trim() && !(amountValue(row.amount) > 0 && amountValue(row.amount) <= 999999999999)) row.error = 'Nominal harus Rupiah bulat positif, misalnya 100000 atau 100.000.';
                return;
            }
            this.timers.set(row.key, setTimeout(() => { this.timers.delete(row.key); this.save(row); }, delay));
        },
        nextCell(event, row) {
            const inputs = [...event.target.closest('tr').querySelectorAll('[data-cell]:not(:disabled)')];
            const next = inputs[inputs.indexOf(event.target) + 1];
            if (next) next.focus(); else {
                this.queue(row, 0);
                const following = event.target.closest('tbody').nextElementSibling?.querySelector('[data-cell]:not(:disabled)');
                if (following) following.focus(); else this.addRow();
            }
        },
        async request(url, method = 'GET', payload = null) {
            let response;
            try {
                response = await fetch(url, { method, credentials: 'same-origin', cache: 'no-store', headers: {
                    Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                }, ...(payload ? { body: JSON.stringify(payload) } : {}) });
            } catch (_) { throw new Error('Koneksi terputus. Isian tetap ada; tekan Coba lagi.'); }
            if (response.redirected || [401, 419].includes(response.status)) throw new Error('Sesi berakhir. Salin isian yang belum tersimpan lalu login kembali.');
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const error = new Error(response.status >= 500 ? 'Server belum dapat menyimpan. Isian tetap ada; tekan Coba lagi.' : data.errors ? Object.values(data.errors).flat().join(' ') : data.message || 'Gagal menyimpan. Isian tetap ada; coba lagi.');
                error.data = data; error.status = response.status; throw error;
            }
            return data;
        },
        async save(row) {
            if (row.busy || !row.dirty || !ready(row) || this.closed) return;
            row.busy = true; row.status = 'saving'; row.error = '';
            const sent = fingerprint(row);
            const payload = Object.fromEntries(fields.map(key => [key, row[key]]));
            payload.amount = amountValue(payload.amount); payload.client_key = row.client_key; payload.revision = row.revision;
            let success = false;
            try {
                const data = await this.request(row.id ? `${this.storeUrl}/${row.id}` : this.storeUrl, row.id ? 'PUT' : 'POST', payload);
                row.id = data.entry.id; row.revision = data.entry.revision; row.saved = fingerprint(data.entry);
                row.dirty = fingerprint(row) !== row.saved; row.status = row.dirty ? 'dirty' : 'saved'; success = true;
                if (!this.rows.some(r => !r.id && r.status === 'draft')) this.addRow(false);
            } catch (error) {
                // A create retry may find its original row after a lost response.
                if (!row.id && error.data?.entry) { row.id = error.data.entry.id; row.revision = error.data.entry.revision; }
                row.status = 'error'; row.error = error.message;
            } finally {
                row.busy = false;
                if (success) {
                    await this.refreshSummary();
                    if (row.dirty && fingerprint(row) !== sent) this.queue(row, 0);
                }
            }
        },
        async refreshSummary() {
            // Serialize summary reads so an earlier response cannot replace newer totals.
            if (this.refreshing) { this.refreshAgain = true; return; }
            this.refreshing = true;
            try {
                const url = new URL(this.indexUrl); url.searchParams.set('month', this.month);
                const data = await this.request(url);
                for (const key of ['totals', 'balance', 'allocation', 'trend', 'previousProfit', 'largest', 'closed']) this[key] = data[key];
                this.summaryError = '';
            } catch (_) { this.summaryError = 'Transaksi tetap tersimpan. Ringkasan belum diperbarui; tekan Perbarui ringkasan.'; }
            finally { this.refreshing = false; if (this.refreshAgain) { this.refreshAgain = false; this.refreshSummary(); } }
        },
        async remove(row) {
            if (row.busy || this.closed) return;
            if (!row.id) { if (!row.dirty || confirm('Buang isian baris ini?')) { clearTimeout(this.timers.get(row.key)); this.rows = this.rows.filter(r => r.key !== row.key); } return; }
            if (!confirm('Hapus transaksi ini dari jurnal?')) return;
            clearTimeout(this.timers.get(row.key));
            row.busy = true;
            try {
                await this.request(`${this.storeUrl}/${row.id}`, 'DELETE', { revision: row.revision });
                this.rows = this.rows.filter(r => r.key !== row.key); await this.refreshSummary();
            } catch (error) { row.status = 'error'; row.error = error.message; }
            finally { row.busy = false; }
        },
        async saveSettings() {
            this.settingsBusy = true; this.notice = ''; this.noticeError = false;
            try { await this.request(this.settingsUrl, 'POST', { recipients: this.recipients }); await this.refreshSummary(); this.notice = 'Komposisi profit tersimpan.'; }
            catch (error) { this.notice = error.message; this.noticeError = true; }
            finally { this.settingsBusy = false; }
        },
        async distribute() {
            if (this.unsaved || this.closed || this.distributionBusy) return;
            if (!confirm('Catat pembagian profit dan kunci bulan ini? Ini pencatatan kas, bukan transfer bank.')) return;
            this.distributionBusy = true; this.notice = ''; this.noticeError = false;
            try {
                await this.request(this.distributeUrl, 'POST', { month: this.month });
                const url = new URL(this.indexUrl); url.searchParams.set('month', this.month);
                const data = await this.request(url);
                this.rows = data.rows.map(decorate); this.closed = true; await this.refreshSummary(); this.notice = 'Pembagian profit dicatat. Jurnal bulan ini dikunci.';
            } catch (error) { this.notice = error.message; this.noticeError = true; }
            finally { this.distributionBusy = false; }
        },
        summaryError: '', refreshAgain: false,
    };
}
