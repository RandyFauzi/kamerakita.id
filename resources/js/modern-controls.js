const MONTHS_ID = [
    'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
    'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
];

const MONTHS_FULL_ID = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

const DAYS_ID = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

let activePopover = null;
let activeCleanup = null;

function closeActivePopover() {
    if (activePopover) {
        activePopover.remove();
        activePopover = null;
    }
    if (activeCleanup) {
        activeCleanup();
        activeCleanup = null;
    }
}

function positionPopover(popover, trigger, minWidth = 240, maxWidth = 340) {
    const rect = trigger.getBoundingClientRect();
    const vw = window.innerWidth;
    const vh = window.innerHeight;
    const width = Math.min(Math.max(rect.width, minWidth), Math.min(maxWidth, vw - 16));

    popover.style.width = `${width}px`;
    popover.style.visibility = 'hidden';
    document.body.appendChild(popover);

    const popRect = popover.getBoundingClientRect();
    const spaceBelow = vh - rect.bottom;
    const spaceAbove = rect.top;

    let top = rect.bottom + 6;
    if (spaceBelow < popRect.height + 14 && spaceAbove > spaceBelow) {
        top = Math.max(8, rect.top - popRect.height - 6);
    } else if (top + popRect.height > vh - 8) {
        top = Math.max(8, vh - popRect.height - 8);
    }

    let left = rect.left;
    if (left + width > vw - 8) {
        left = Math.max(8, vw - width - 8);
    }
    if (left < 8) {
        left = 8;
    }

    popover.style.top = `${top}px`;
    popover.style.left = `${left}px`;
    popover.style.visibility = 'visible';

    requestAnimationFrame(() => {
        popover.classList.add('is-open');
    });
}

function attachGlobalDismiss(trigger, popover, onClose) {
    const handleOutside = (event) => {
        if (!popover.contains(event.target) && !trigger.contains(event.target)) {
            closeActivePopover();
        }
    };
    const handleKey = (event) => {
        if (event.key === 'Escape') {
            closeActivePopover();
            trigger.focus();
        }
    };
    const handleViewportChange = () => {
        closeActivePopover();
    };

    document.addEventListener('pointerdown', handleOutside, true);
    document.addEventListener('keydown', handleKey, true);
    window.addEventListener('resize', handleViewportChange, { passive: true });
    window.addEventListener('scroll', handleViewportChange, { passive: true, capture: true });

    activePopover = popover;
    activeCleanup = () => {
        document.removeEventListener('pointerdown', handleOutside, true);
        document.removeEventListener('keydown', handleKey, true);
        window.removeEventListener('resize', handleViewportChange);
        window.removeEventListener('scroll', handleViewportChange, true);
        if (onClose) onClose();
    };
}

function copyLayoutClasses(source, target) {
    const keepPrefixes = ['w-', 'sm:w-', 'md:w-', 'lg:w-', 'xl:w-', 'min-w-', 'max-w-', 'flex-1', 'flex-none', 'col-span-', 'sm:col-span-', 'md:col-span-'];
    source.classList.forEach((cls) => {
        if (keepPrefixes.some((p) => cls === p || cls.startsWith(p))) {
            target.classList.add(cls);
        }
    });
}

function shouldSkipControl(el) {
    if (!el || el.dataset.kkUpgraded === '1') return true;
    if (el.hasAttribute('data-kk-native') || el.classList.contains('kk-native')) return true;
    if (el.closest('.finance-grid')) return true;
    if (el.tagName === 'SELECT' && (el.multiple || (el.size && el.size > 1))) return true;
    return false;
}

function formatMonthLabel(val) {
    if (!val || !/^\d{4}-\d{2}$/.test(val)) return 'Pilih bulan...';
    const [y, m] = val.split('-').map(Number);
    return `${MONTHS_FULL_ID[(m - 1)] || ''} ${y}`;
}

function formatDateLabel(val) {
    if (!val || !/^\d{4}-\d{2}-\d{2}$/.test(val)) return 'Pilih tanggal...';
    const [y, m, d] = val.split('-').map(Number);
    return `${d} ${MONTHS_ID[(m - 1)] || ''} ${y}`;
}

function enhanceSelect(select) {
    if (shouldSkipControl(select)) return;
    select.dataset.kkUpgraded = '1';

    const host = document.createElement('div');
    host.className = 'kk-control-host';
    copyLayoutClasses(select, host);

    const isBorderless = select.classList.contains('border-0') || select.classList.contains('bg-transparent');
    const isCompact = select.classList.contains('text-xs') || select.classList.contains('text-[11px]') || select.classList.contains('py-1');

    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = `kk-select-trigger${isCompact ? ' is-compact' : ''}${isBorderless ? ' is-borderless' : ''}`;
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');

    const labelSpan = document.createElement('span');
    labelSpan.className = 'kk-trigger-label';

    const chevron = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    chevron.setAttribute('class', 'kk-chevron');
    chevron.setAttribute('viewBox', '0 0 20 20');
    chevron.setAttribute('fill', 'currentColor');
    chevron.innerHTML = '<path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>';

    trigger.append(labelSpan, chevron);

    const syncLabel = () => {
        const selected = select.options[select.selectedIndex];
        const text = selected ? selected.textContent.trim() : 'Pilih...';
        labelSpan.textContent = text;
        const isEmpty = !select.value;
        trigger.classList.toggle('is-placeholder', isEmpty);
        trigger.disabled = select.disabled;
    };

    select.parentNode.insertBefore(host, select);
    host.appendChild(select);
    host.appendChild(trigger);
    select.classList.add('kk-native-hidden');
    select.tabIndex = -1;

    syncLabel();

    select.addEventListener('change', syncLabel);
    select.addEventListener('input', syncLabel);

    const observer = new MutationObserver(() => {
        requestAnimationFrame(syncLabel);
    });
    observer.observe(select, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled'] });

    trigger.addEventListener('click', (event) => {
        event.preventDefault();
        if (select.disabled) return;

        const alreadyOpenForThis = activePopover && activePopover.dataset.ownerId === select.id && trigger.getAttribute('aria-expanded') === 'true';
        if (alreadyOpenForThis) {
            closeActivePopover();
            return;
        }
        closeActivePopover();

        const popover = document.createElement('div');
        popover.className = 'kk-popover kk-select-popover';
        popover.setAttribute('role', 'listbox');
        if (select.id) popover.dataset.ownerId = select.id;

        const options = Array.from(select.options);
        let searchInput = null;

        if (options.length >= 7) {
            const searchWrap = document.createElement('div');
            searchWrap.className = 'kk-popover-search-wrap';
            searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = 'Cari pilihan...';
            searchInput.className = 'kk-popover-search';
            searchWrap.appendChild(searchInput);
            popover.appendChild(searchWrap);
        }

        const list = document.createElement('div');
        list.className = 'kk-select-list';
        popover.appendChild(list);

        const renderOptions = (query = '') => {
            list.innerHTML = '';
            const norm = query.trim().toLowerCase();
            let shown = 0;

            options.forEach((opt) => {
                const text = opt.textContent.trim();
                if (norm && !text.toLowerCase().includes(norm)) return;
                shown++;

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.disabled = opt.disabled;
                const isSelected = opt.value === select.value;
                btn.className = `kk-select-option${isSelected ? ' is-selected' : ''}`;
                btn.setAttribute('role', 'option');
                btn.setAttribute('aria-selected', isSelected ? 'true' : 'false');

                const textSpan = document.createElement('span');
                textSpan.className = 'kk-option-text';
                textSpan.textContent = text;
                btn.appendChild(textSpan);

                if (isSelected) {
                    const check = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                    check.setAttribute('class', 'kk-option-check');
                    check.setAttribute('viewBox', '0 0 20 20');
                    check.setAttribute('fill', 'currentColor');
                    check.innerHTML = '<path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/>';
                    btn.appendChild(check);
                }

                btn.addEventListener('click', () => {
                    select.value = opt.value;
                    select.dispatchEvent(new Event('input', { bubbles: true }));
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    syncLabel();
                    closeActivePopover();
                    trigger.focus();
                });

                list.appendChild(btn);
            });

            if (shown === 0) {
                const empty = document.createElement('div');
                empty.className = 'kk-popover-empty';
                empty.textContent = 'Tidak ada pilihan yang cocok.';
                list.appendChild(empty);
            }
        };

        renderOptions();

        if (searchInput) {
            searchInput.addEventListener('input', () => renderOptions(searchInput.value));
        }

        trigger.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');
        positionPopover(popover, trigger, 210, 360);

        const selectedEl = list.querySelector('.is-selected');
        if (selectedEl) {
            selectedEl.scrollIntoView({ block: 'nearest' });
        }
        if (searchInput) {
            setTimeout(() => searchInput.focus(), 20);
        }

        attachGlobalDismiss(trigger, popover, () => {
            trigger.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        });
    });
}

function enhancePicker(input) {
    if (shouldSkipControl(input)) return;
    const mode = input.type === 'month' ? 'month' : (input.type === 'date' ? 'date' : null);
    if (!mode) return;

    input.dataset.kkUpgraded = '1';

    const host = document.createElement('div');
    host.className = 'kk-control-host';
    copyLayoutClasses(input, host);

    const isBorderless = input.classList.contains('border-0') || input.classList.contains('bg-transparent');
    const isCompact = input.classList.contains('text-xs') || input.classList.contains('text-[11px]');

    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = `kk-picker-trigger${isCompact ? ' is-compact' : ''}${isBorderless ? ' is-borderless' : ''}`;
    trigger.setAttribute('aria-haspopup', 'dialog');
    trigger.setAttribute('aria-expanded', 'false');

    const labelSpan = document.createElement('span');
    labelSpan.className = 'kk-trigger-label';

    const calIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    calIcon.setAttribute('class', 'kk-cal-icon');
    calIcon.setAttribute('fill', 'none');
    calIcon.setAttribute('stroke', 'currentColor');
    calIcon.setAttribute('stroke-width', '2');
    calIcon.setAttribute('viewBox', '0 0 24 24');
    calIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>';

    trigger.append(labelSpan, calIcon);

    const syncLabel = () => {
        const val = input.value;
        labelSpan.textContent = mode === 'month' ? formatMonthLabel(val) : formatDateLabel(val);
        trigger.classList.toggle('is-placeholder', !val);
        trigger.disabled = input.disabled;
    };

    input.parentNode.insertBefore(host, input);
    host.appendChild(input);
    host.appendChild(trigger);
    input.classList.add('kk-native-hidden');
    input.tabIndex = -1;

    syncLabel();
    input.addEventListener('change', syncLabel);
    input.addEventListener('input', syncLabel);

    const observer = new MutationObserver(() => {
        requestAnimationFrame(syncLabel);
    });
    observer.observe(input, { attributes: true, attributeFilter: ['disabled', 'value'] });

    trigger.addEventListener('click', (event) => {
        event.preventDefault();
        if (input.disabled) return;

        const isSameOpen = trigger.getAttribute('aria-expanded') === 'true';
        if (isSameOpen) {
            closeActivePopover();
            return;
        }
        closeActivePopover();

        const popover = document.createElement('div');
        popover.className = 'kk-popover kk-picker-popover';
        popover.setAttribute('role', 'dialog');

        const now = new Date();
        const currentVal = input.value;

        let viewYear = now.getFullYear();
        let viewMonth = now.getMonth(); // 0-indexed

        if (mode === 'month' && /^\d{4}-\d{2}$/.test(currentVal)) {
            const [y, m] = currentVal.split('-').map(Number);
            viewYear = y;
            viewMonth = m - 1;
        } else if (mode === 'date' && /^\d{4}-\d{2}-\d{2}$/.test(currentVal)) {
            const [y, m] = currentVal.split('-').map(Number);
            viewYear = y;
            viewMonth = m - 1;
        }

        const commitValue = (nextVal) => {
            input.value = nextVal;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            syncLabel();
            closeActivePopover();
            trigger.focus();
        };

        const renderBody = () => {
            popover.innerHTML = '';

            // KameraKita Blue Gradient Header
            const header = document.createElement('div');
            header.className = 'kk-picker-header';

            const prevBtn = document.createElement('button');
            prevBtn.type = 'button';
            prevBtn.className = 'kk-picker-nav';
            prevBtn.innerHTML = '&lsaquo;';
            prevBtn.setAttribute('aria-label', 'Sebelumnya');

            const title = document.createElement('span');
            title.className = 'kk-picker-title';

            const nextBtn = document.createElement('button');
            nextBtn.type = 'button';
            nextBtn.className = 'kk-picker-nav';
            nextBtn.innerHTML = '&rsaquo;';
            nextBtn.setAttribute('aria-label', 'Berikutnya');

            if (mode === 'month') {
                title.textContent = `${viewYear}`;
                prevBtn.addEventListener('click', () => {
                    viewYear -= 1;
                    renderBody();
                });
                nextBtn.addEventListener('click', () => {
                    viewYear += 1;
                    renderBody();
                });
            } else {
                title.textContent = `${MONTHS_FULL_ID[viewMonth]} ${viewYear}`;
                prevBtn.addEventListener('click', () => {
                    viewMonth -= 1;
                    if (viewMonth < 0) {
                        viewMonth = 11;
                        viewYear -= 1;
                    }
                    renderBody();
                });
                nextBtn.addEventListener('click', () => {
                    viewMonth += 1;
                    if (viewMonth > 11) {
                        viewMonth = 0;
                        viewYear += 1;
                    }
                    renderBody();
                });
            }

            header.append(prevBtn, title, nextBtn);
            popover.appendChild(header);

            const body = document.createElement('div');
            body.className = 'kk-picker-body';

            if (mode === 'month') {
                const grid = document.createElement('div');
                grid.className = 'kk-month-grid';

                MONTHS_ID.forEach((mLabel, idx) => {
                    const ym = `${viewYear}-${String(idx + 1).padStart(2, '0')}`;
                    const cell = document.createElement('button');
                    cell.type = 'button';
                    const isSelected = currentVal === ym;
                    const isCurrent = now.getFullYear() === viewYear && now.getMonth() === idx;
                    cell.className = `kk-month-cell${isSelected ? ' is-selected' : ''}${isCurrent ? ' is-today' : ''}`;
                    cell.textContent = mLabel;
                    cell.addEventListener('click', () => commitValue(ym));
                    grid.appendChild(cell);
                });

                body.appendChild(grid);
            } else {
                const weekHead = document.createElement('div');
                weekHead.className = 'kk-day-head';
                DAYS_ID.forEach((d) => {
                    const s = document.createElement('span');
                    s.textContent = d;
                    weekHead.appendChild(s);
                });
                body.appendChild(weekHead);

                const grid = document.createElement('div');
                grid.className = 'kk-day-grid';

                const firstDay = new Date(viewYear, viewMonth, 1).getDay(); // 0=Sun..6=Sat
                const offset = (firstDay + 6) % 7; // Monday first
                const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

                for (let i = 0; i < offset; i++) {
                    const pad = document.createElement('span');
                    grid.appendChild(pad);
                }

                for (let day = 1; day <= daysInMonth; day++) {
                    const ymd = `${viewYear}-${String(viewMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    const cell = document.createElement('button');
                    cell.type = 'button';
                    const isSelected = currentVal === ymd;
                    const isToday = now.getFullYear() === viewYear && now.getMonth() === viewMonth && now.getDate() === day;
                    cell.className = `kk-day-cell${isSelected ? ' is-selected' : ''}${isToday ? ' is-today' : ''}`;
                    cell.textContent = `${day}`;
                    cell.addEventListener('click', () => commitValue(ymd));
                    grid.appendChild(cell);
                }

                body.appendChild(grid);
            }

            popover.appendChild(body);

            const footer = document.createElement('div');
            footer.className = 'kk-picker-footer';

            const todayBtn = document.createElement('button');
            todayBtn.type = 'button';
            todayBtn.className = 'kk-picker-action';
            todayBtn.textContent = mode === 'month' ? 'Bulan Ini' : 'Hari Ini';
            todayBtn.addEventListener('click', () => {
                const y = now.getFullYear();
                const m = String(now.getMonth() + 1).padStart(2, '0');
                const d = String(now.getDate()).padStart(2, '0');
                commitValue(mode === 'month' ? `${y}-${m}` : `${y}-${m}-${d}`);
            });

            footer.appendChild(todayBtn);

            if (!input.required) {
                const clearBtn = document.createElement('button');
                clearBtn.type = 'button';
                clearBtn.className = 'kk-picker-clear';
                clearBtn.textContent = 'Reset';
                clearBtn.addEventListener('click', () => commitValue(''));
                footer.appendChild(clearBtn);
            }

            popover.appendChild(footer);
        };

        renderBody();
        trigger.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');
        positionPopover(popover, trigger, 272, 310);

        attachGlobalDismiss(trigger, popover, () => {
            trigger.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        });
    });
}

export function scanModernControls(root = document) {
    if (!root || !root.querySelectorAll) return;
    root.querySelectorAll('select').forEach(enhanceSelect);
    root.querySelectorAll('input[type="month"], input[type="date"]').forEach(enhancePicker);
}

export function initModernControls() {
    scanModernControls(document);

    let scheduled = false;
    const bodyObserver = new MutationObserver(() => {
        if (scheduled) return;
        scheduled = true;
        requestAnimationFrame(() => {
            scheduled = false;
            scanModernControls(document);
        });
    });

    if (document.body) {
        bodyObserver.observe(document.body, { childList: true, subtree: true });
    }
}
