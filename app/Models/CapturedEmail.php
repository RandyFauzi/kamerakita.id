<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapturedEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sender_address',
        'subject',
        'message_content',
        'imap_uid',
        'imap_uidvalidity',
        'message_id',
        'received_at',
        'is_read',
        'is_starred',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
    ];

    public function getSanitizedContentAttribute()
    {
        if (!$this->message_content) {
            return '';
        }
        
        try {
            // Pastikan string adalah UTF-8 yang valid
            $content = mb_convert_encoding($this->message_content, 'UTF-8', 'UTF-8');

            // Hapus tag script sebagai lapisan pertahanan ekstra (defense-in-depth)
            // Walaupun iframe sandbox sudah memblokir eksekusi JS.
            $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);

            // Cek apakah email hanya berisi teks murni tanpa tag HTML sama sekali
            if (strip_tags($content) === $content) {
                $content = '<div style="font-family: sans-serif; font-size: 14px; white-space: pre-wrap; word-wrap: break-word; padding: 16px; color: #333;">' . htmlspecialchars($content) . '</div>';
            }

            // Injeksi tag <base target="_blank"> agar link terbuka di tab baru, bukan terjebak di dalam iframe
            // (CSP pemblokir gambar telah dihapus sesuai permintaan agar desain email tetap utuh dan rapi)
            $headTags = '<base target="_blank">';
            
            if (stripos($content, '<head>') !== false) {
                $content = preg_replace('/<head>/i', '<head>' . $headTags, $content, 1);
            } else if (stripos($content, '<html>') !== false) {
                $content = preg_replace('/<html>/i', '<html><head>' . $headTags . '</head>', $content, 1);
            } else {
                $content = '<head>' . $headTags . '</head>' . $content;
            }

            return $content;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Email content formatting failed: " . $e->getMessage());
            $safeContent = mb_convert_encoding($this->message_content, 'UTF-8', 'UTF-8');
            return '<div style="padding: 16px;">' . nl2br(htmlentities(strip_tags($safeContent))) . '</div>';
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
