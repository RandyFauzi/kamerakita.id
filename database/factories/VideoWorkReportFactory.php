<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoWorkReportFactory extends Factory
{
    protected $model = VideoWorkReport::class;

    public function definition(): array
    {
        $durasiKirim = $this->faker->numberBetween(10, 300); // 10 menit sampai 5 jam
        $status = $this->faker->randomElement(['pending', 'approved', 'approved', 'approved', 'rejected']);
        
        $durasiSetuju = 0;
        if ($status === 'approved') {
            $durasiSetuju = $this->faker->randomElement([$durasiKirim, $durasiKirim - $this->faker->numberBetween(1, 10)]);
            if ($durasiSetuju < 0) $durasiSetuju = 0;
        }

        $verifikator = User::where('role', 'verifikator')->first();
        $verifierId = ($status !== 'pending') ? ($verifikator ? $verifikator->id : 1) : null;
        $verifiedAt = ($status !== 'pending') ? $this->faker->dateTimeBetween('-1 month', 'now') : null;
        $paymentStatus = ($status === 'approved') ? $this->faker->randomElement(['unpaid', 'unpaid', 'paid']) : 'unpaid';

        return [
            'partner_id' => Partner::factory(),
            'submission_date' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'evidence_email_image_path' => 'evidence/sample_email.jpg',
            'evidence_app_quality_image_path' => 'evidence/sample_quality.jpg',
            'submitted_duration_minutes' => $durasiKirim,
            'approved_duration_minutes' => $durasiSetuju,
            'qc_status' => $status,
            'payment_status' => $paymentStatus,
            'verifier_notes' => ($status === 'rejected') ? $this->faker->sentence() : null,
            'verified_by' => $verifierId,
            'verified_at' => $verifiedAt,
        ];
    }
}
