<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    /**
     * Upload profile photo
     */
    public function uploadProfilePhoto(UploadedFile $file): string
    {
        $path = $file->store('profile-photos', 'public');
        return Storage::url($path);
    }

    /**
     * Upload CV
     */
    public function uploadCV(UploadedFile $file): string
    {
        $path = $file->store('cvs', 'public');
        return Storage::url($path);
    }

    /**
     * Upload material file
     */
    public function uploadMaterialFile(UploadedFile $file): string
    {
        $path = $file->store('materials', 'public');
        return Storage::url($path);
    }

    /**
     * Upload submission file
     */
    public function uploadSubmissionFile(UploadedFile $file): string
    {
        $path = $file->store('submissions', 'public');
        return Storage::url($path);
    }

    /**
     * Delete file
     */
    public function deleteFile(string $path): bool
    {
        return Storage::disk('public')->delete($path);
    }
}
