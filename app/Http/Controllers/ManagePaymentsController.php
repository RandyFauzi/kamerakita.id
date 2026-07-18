<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use App\Services\EvidenceFileBackupService;
use App\Services\StoreEvidenceImageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ManagePaymentsController extends Controller
{
    private const DEFAULT_HOURLY_RATE_IDR = 54000;

    /**
     * Display list of workers with approved, unpaid work reports.
     */
    /**
     * Display list of workers with approved, unpaid work reports and payout history.
     */
    public function index()
    {
        $unpaidReports = VideoWorkReport::with('partner')
            ->where('qc_status', 'approved')
            ->where('payment_status', 'unpaid')
            ->get();

        $grouped = $unpaidReports->groupBy('partner_id');

        $workers = [];
        foreach ($grouped as $partnerId => $reports) {
            $partner = $reports->first()->partner;
            if (! $partner) {
                continue;
            }

            $totalMinutes = $reports->sum('approved_duration_minutes');
            $hours = $totalMinutes / 60;
            $rate = $partner->base_hourly_rate ?: self::DEFAULT_HOURLY_RATE_IDR;
            $totalAmount = round($hours * $rate);

            $workers[] = [
                'partner' => $partner,
                'reports' => $reports->sortByDesc('submission_date'),
                'total_minutes' => $totalMinutes,
                'hours' => $hours,
                'rate' => $rate,
                'total_amount' => $totalAmount,
            ];
        }

        // Fetch payout history
        $paidReports = VideoWorkReport::with('partner')
            ->where('payment_status', 'paid')
            ->whereNotNull('paid_at')
            ->orderBy('paid_at', 'desc')
            ->get();

        $groupedPaid = $paidReports->groupBy(function ($item) {
            $paidAt = $item->paid_at instanceof Carbon ? $item->paid_at : Carbon::parse($item->paid_at);

            return $paidAt->format('Y-m-d H:i:s').'_'.$item->payment_reference_proof_path;
        });

        $payoutHistory = [];
        foreach ($groupedPaid as $key => $reports) {
            $first = $reports->first();
            $partner = $first->partner;
            if (! $partner) {
                continue;
            }

            $totalMinutes = $reports->sum('approved_duration_minutes');
            $hours = $totalMinutes / 60;
            $rate = $partner->base_hourly_rate ?: self::DEFAULT_HOURLY_RATE_IDR;
            $totalAmount = round($hours * $rate);

            $payoutHistory[] = [
                'paid_at' => $first->paid_at,
                'proof_url' => $first->payment_proof_url,
                'proof_path' => $first->payment_reference_proof_path,
                'partner' => $partner,
                'reports' => $reports->sortByDesc('submission_date'),
                'total_minutes' => $totalMinutes,
                'total_amount' => $totalAmount,
                'batch_id' => base64_encode($first->paid_at->format('Y-m-d H:i:s').'|'.$first->payment_reference_proof_path),
            ];
        }

        return view('payments.manage', compact('workers', 'payoutHistory'));
    }

    /**
     * Process payout for a specific worker: save transfer proof and mark reports as paid.
     */
    public function processPayment(Request $request, Partner $partner)
    {
        $request->validate([
            'evidence_payment_proof' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'evidence_payment_proof.required' => 'Bukti transfer wajib diunggah.',
            'evidence_payment_proof.image' => 'File bukti transfer harus berupa gambar.',
            'evidence_payment_proof.max' => 'Ukuran file bukti transfer maksimal 2MB.',
        ]);

        // Fetch all unpaid approved reports for this partner
        $reports = VideoWorkReport::where('partner_id', $partner->id)
            ->where('qc_status', 'approved')
            ->where('payment_status', 'unpaid')
            ->get();

        if ($reports->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada tagihan tertunda yang perlu dibayar untuk Mitra ini.');
        }

        $proofPath = null;

        try {
            $proofPath = app(StoreEvidenceImageService::class)
                ->store($request->file('evidence_payment_proof'), 'evidences/payments');

            DB::transaction(function () use ($reports, $proofPath): void {
                VideoWorkReport::whereIn('id', $reports->pluck('id'))->update([
                    'payment_status' => 'paid',
                    'payment_reference_proof_path' => $proofPath,
                    'paid_at' => now(),
                ]);

                app(EvidenceFileBackupService::class)->backup($proofPath);
            });
        } catch (Throwable $exception) {
            if ($proofPath && Storage::disk('evidence')->exists($proofPath)) {
                Storage::disk('evidence')->delete($proofPath);
            }

            Log::error('Failed to store payment proof evidence.', [
                'partner_id' => $partner->id,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Pembayaran gagal diproses karena bukti transfer tidak berhasil disimpan. Cek permission folder storage/app/private lalu coba lagi.');
        }

        return redirect()->route('payments.manage')->with('success', "Pembayaran untuk Mitra {$partner->full_name} berhasil diproses!");
    }

    /**
     * Cancel/delete a past payout batch: revert reports to unpaid and delete physical transfer proof.
     */
    public function cancelPayment(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|string',
        ]);

        try {
            $decoded = base64_decode($request->batch_id);
            if (! str_contains($decoded, '|')) {
                return redirect()->back()->with('error', 'Format batch ID tidak valid.');
            }

            [$paidAtStr, $proofPath] = explode('|', $decoded);

            // Fetch reports in this batch
            $reports = VideoWorkReport::where('paid_at', $paidAtStr)
                ->where('payment_reference_proof_path', $proofPath)
                ->get();

            if ($reports->isEmpty()) {
                return redirect()->back()->with('error', 'Data riwayat pembayaran tidak ditemukan atau sudah dibatalkan.');
            }

            // Delete proof file
            if ($proofPath) {
                foreach (['evidence', 'local', 'public'] as $diskName) {
                    $disk = Storage::disk($diskName);
                    if ($disk->exists($proofPath)) {
                        $disk->delete($proofPath);
                    }
                }

                app(EvidenceFileBackupService::class)->delete($proofPath);
            }

            // Revert reports to unpaid status
            VideoWorkReport::whereIn('id', $reports->pluck('id'))->update([
                'payment_status' => 'unpaid',
                'payment_reference_proof_path' => null,
                'paid_at' => null,
            ]);

            return redirect()->route('payments.manage')->with('success', 'Riwayat pembayaran berhasil dihapus. Laporan terkait telah dikembalikan ke status Unpaid.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
