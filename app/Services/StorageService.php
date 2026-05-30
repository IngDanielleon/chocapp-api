<?php

namespace App\Services;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageService
{
    private string $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.default', 's3');
    }

    public function uploadFile(UploadedFile|File $file, string $path): string
    {
        $extension = $file instanceof UploadedFile
            ? $file->getClientOriginalExtension()
            : $file->getExtension();

        $filename = Str::uuid() . '.' . $extension;
        $fullPath = "{$path}/{$filename}";

        Storage::disk($this->disk)->put($fullPath, file_get_contents($file->getRealPath()));

        return Storage::disk($this->disk)->url($fullPath);
    }

    public function deleteFile(string $url): void
    {
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            Storage::disk($this->disk)->delete(ltrim($path, '/'));
        }
    }

    public function generateSignedUrl(string $path, int $minutes = 60): string
    {
        return Storage::disk($this->disk)
            ->temporaryUrl($path, now()->addMinutes($minutes));
    }
}
