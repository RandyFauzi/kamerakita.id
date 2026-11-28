<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApkDownloadController extends Controller
{
    private const FILE_NAME = 'app-debug.apk';

    public function index(): View
    {
        $file = $this->availableFile();

        return view('admin.apk.index', [
            'fileName' => self::FILE_NAME,
            'size' => $file?->getSize(),
        ]);
    }

    public function download(): BinaryFileResponse
    {
        $file = $this->availableFile();
        abort_if($file === null, 404, 'APK belum tersedia.');

        return response()->download($file->getPathname(), self::FILE_NAME, [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ])->setPrivate();
    }

    private function availableFile(): ?\SplFileInfo
    {
        $file = new \SplFileInfo(Storage::disk('kamerakita_apk')->path(self::FILE_NAME));

        return $file->isFile() && $file->isReadable() && $file->getSize() > 0 ? $file : null;
    }
}
