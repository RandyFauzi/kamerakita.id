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
            // Pastikan string adalah UTF-8 yang valid untuk mencegah json_encode() crash (Malformed UTF-8)
            $rawContent = $this->message_content;
            $content = mb_convert_encoding($rawContent, 'UTF-8', 'UTF-8');

            // Hapus gambar inline (base64) yang sangat besar sebelum diproses Purifier
            // Ini untuk mencegah memory exhaustion (PHP Fatal Error) saat parsing HTML yang kompleks
            $content = preg_replace('/src=["\']data:image\/[^;]+;base64[^"\']+["\']/i', 'src="#" alt="[Inline Image Removed]"', $content);
            
            // Batasi ukuran total konten untuk mencegah crash (maks 500KB)
            if (strlen($content) > 500000) {
                $content = substr($content, 0, 500000) . '<br><br><em>[Pesan terpotong karena terlalu panjang]</em>';
            }
            
            return clean($content, 'email');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("HTMLPurifier failed: " . $e->getMessage());
            // Fallback aman jika Purifier gagal (misal karena tag HTML cacat ekstrim)
            $safeContent = mb_convert_encoding($this->message_content, 'UTF-8', 'UTF-8');
            return nl2br(htmlentities(strip_tags($safeContent)));
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
