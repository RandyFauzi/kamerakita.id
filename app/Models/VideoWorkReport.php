<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

class VideoWorkReport extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'partner_id',
        'project_name',
        'submission_date',
        'evidence_email_image_path',
        'evidence_app_quality_image_path',
        'evidence_submitted_image_paths',
        'submitted_duration_minutes',
        'approved_duration_minutes',
        'rate_applied',
        'qc_status',
        'payment_status',
        'payment_reference_proof_path',
        'paid_at',
        'verifier_notes',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'verified_at' => 'datetime',
        'paid_at' => 'datetime',
        'evidence_submitted_image_paths' => 'array',
    ];

    protected $appends = [
        'evidence_email_image_url',
        'evidence_app_quality_image_url',
        'payment_proof_url',
        'submitted_duration_formatted',
        'approved_duration_formatted',
        'evidence_submitted_image_urls',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get duration formatted in "Hh Mm"
     */
    public function formatMinutes(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $remainingMinutes);
        }
        
        return sprintf('%dm', $remainingMinutes);
    }

    public function getSubmittedDurationFormattedAttribute(): string
    {
        return $this->formatMinutes($this->submitted_duration_minutes);
    }

    public function getApprovedDurationFormattedAttribute(): string
    {
        return $this->formatMinutes($this->approved_duration_minutes);
    }

    public function getEvidenceEmailImageUrlAttribute(): ?string
    {
        return $this->signedEvidenceUrl('email', $this->evidence_email_image_path);
    }

    public function getEvidenceAppQualityImageUrlAttribute(): ?string
    {
        return $this->signedEvidenceUrl('app-quality', $this->evidence_app_quality_image_path);
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        return $this->signedEvidenceUrl('payment', $this->payment_reference_proof_path);
    }

    public function getEvidenceSubmittedImageUrlsAttribute(): array
    {
        $paths = $this->evidence_submitted_image_paths ?? [];
        $urls = [];
        foreach ($paths as $index => $path) {
            $urls[] = $this->signedEvidenceUrl('submitted', $path, $index);
        }
        return $urls;
    }

    private function signedEvidenceUrl(string $type, ?string $path, ?int $index = null): ?string
    {
        if (! $path) {
            return null;
        }

        $params = [
            'report' => $this->id,
            'type' => $type
        ];

        if ($index !== null) {
            $params['index'] = $index;
        }

        return URL::signedRoute('video-submissions.evidence.show', $params, null, false); // Generate relative signed URL for universal cross-host reliability
    }
}
