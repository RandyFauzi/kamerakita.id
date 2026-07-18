<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ManagePaymentsController extends Controller
{
    private const DEFAULT_HOURLY_RATE_IDR = 54000;

    /**
     * Display list of workers with approved, unpaid work reports.
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

        return view('payments.manage', compact('workers'));
    }

    /**
     * Process payout for a specific worker: save transfer proof and mark reports as paid.
     */
    public function processPayment(Request $request, Partner $partner)
    {
        $request->validate([
            'evidence_payment_proof' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
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

        // Save transfer proof image with compression
        $proofPath = $this->compressAndStoreImage($request->file('evidence_payment_proof'), 'evidences/payments');

        // Update all reports
        VideoWorkReport::whereIn('id', $reports->pluck('id'))->update([
            'payment_status' => 'paid',
            'payment_reference_proof_path' => $proofPath,
            'paid_at' => now(),
        ]);

        return redirect()->route('payments.manage')->with('success', "Pembayaran untuk Mitra {$partner->full_name} berhasil diproses!");
    }

    /**
     * Compress and save uploaded image as JPEG with 75% quality to save space.
     */
    private function compressAndStoreImage($file, string $folder): string
    {
        if (! function_exists('imagejpeg')) {
            return $file->store($folder, 'local');
        }

        $mime = $file->getClientMimeType();
        $originalPath = $file->getPathname();
        
        $filename = Str::uuid() . '.jpg';
        $relativePath = trim($folder, '/') . '/' . $filename;

        $image = null;
        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            $image = @imagecreatefromjpeg($originalPath);
        } elseif ($mime === 'image/png') {
            $image = @imagecreatefrompng($originalPath);
        } elseif ($mime === 'image/gif') {
            $image = @imagecreatefromgif($originalPath);
        }

        if ($image) {
            ob_start();
            imagejpeg($image, null, 75);
            $compressedImage = ob_get_clean();
            imagedestroy($image);

            if ($compressedImage === false) {
                return $file->store($folder, 'local');
            }

            Storage::disk('local')->put($relativePath, $compressedImage);

            return $relativePath;
        }

        return $file->store($folder, 'local');
    }
}
