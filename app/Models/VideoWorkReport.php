<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VideoWorkReport extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'partner_id',
        'submission_date',
        'evidence_email_image_path',
        'evidence_app_quality_image_path',
        'submitted_duration_minutes',
        'approved_duration_minutes',
        'qc_status',
        'payment_status',
        'verifier_notes',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'verified_at' => 'datetime',
    ];

    protected $appends = [
        'evidence_email_image_url',
        'evidence_app_quality_image_url',
        'submitted_duration_formatted',
        'approved_duration_formatted',
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
        return $this->publicStorageUrl($this->evidence_email_image_path);
    }

    public function getEvidenceAppQualityImageUrlAttribute(): ?string
    {
        return $this->publicStorageUrl($this->evidence_app_quality_image_path);
    }

    private function publicStorageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
