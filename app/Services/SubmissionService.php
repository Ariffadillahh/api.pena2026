<?php

namespace App\Services;

use App\Models\Team;
use App\Repositories\SubmissionRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class SubmissionService
{
    protected $submissionRepo;

    public function __construct()
    {
        $this->submissionRepo = new SubmissionRepository();
    }

    public function handleUpload(string $teamId, $file, ?string $gdriveLink)
    {
        $existingSubmission = $this->submissionRepo->findByTeamId($teamId);

        $data = [
            'gdrive_link' => $gdriveLink,
        ];

        if ($file) {
            if ($existingSubmission && $existingSubmission->file_path) {
                Storage::disk('public')->delete($existingSubmission->file_path);
            }

            $folderPath = "registrations/{$teamId}/karya";
            $extension = strtolower($file->getClientOriginalExtension());
            $originalName = $file->getClientOriginalName();
            $path = '';

            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $fileName = 'karya_' . time() . '.webp';

                $manager = new ImageManager(new Driver());
                $image = $manager->read($file->getRealPath());

                $webpData = $image->toWebp(80);

                Storage::disk('public')->put($folderPath . '/' . $fileName, (string) $webpData);
                $path = $folderPath . '/' . $fileName;

                $originalName = pathinfo($originalName, PATHINFO_FILENAME) . '.webp';
            }
            elseif ($extension === 'pdf') {
                $fileName = 'karya_' . time() . '.pdf';
                $tempPath = $file->getRealPath();

                $compressedPath = storage_path('app/temp_karya_' . time() . '.pdf');

                $gsCmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook -dNOPAUSE -dQUIET -dBATCH -sOutputFile=" . escapeshellarg($compressedPath) . " " . escapeshellarg($tempPath);

                @shell_exec($gsCmd);

                if (file_exists($compressedPath) && filesize($compressedPath) > 0) {
                    $path = Storage::disk('public')->putFileAs($folderPath, new File($compressedPath), $fileName);
                    unlink($compressedPath); 
                } else {
                    $path = $file->storeAs($folderPath, $fileName, 'public');
                }
            }
            else {
                $fileName = 'karya_' . time() . '.' . $extension;
                $path = $file->storeAs($folderPath, $fileName, 'public');
            }

            $data['file_path'] = $path;
            $data['original_filename'] = $originalName;
        }

        $submission = $this->submissionRepo->createOrUpdate($teamId, $data);

        Team::where('id', $teamId)->update(['karya_uploaded' => true]);

        return $submission;
    }
}
