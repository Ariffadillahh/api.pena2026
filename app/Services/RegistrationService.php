<?php

namespace App\Services;

use App\Models\Team;
use App\Repositories\TeamRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Exception;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class RegistrationService
{
    protected $teamRepository;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function saveStep1($userId, array $data)
    {
        DB::beginTransaction();
        try {
            $team = $this->teamRepository->updateOrCreateDraft(
                [
                    'user_id' => $userId,
                    'competition_id' => $data['competition_id'],
                ],
                [
                    'name' => $data['team_name'],
                    'institution' => $data['institution'],
                    'wave_id' => $data['wave_id'] ?? null,
                    'status' => 'draft',
                    'payment_status' => 'menunggu_verifikasi'
                ]
            );

            $members = [];
            foreach ($data['members'] as $member) {
                $members[] = [
                    'name' => $member['name'],
                    'email' => $member['email'],
                    'phone' => $member['phone'],
                    'role' => $member['role'],
                ];
            }
            $this->teamRepository->syncMembers($team->id, $members);

            DB::commit();
            return $team;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Gagal menyimpan data pendaftaran: " . $e->getMessage());
        }
    }

    public function uploadDocument($userId, $competitionId, UploadedFile $file, $type)
    {
        $team = Team::where('user_id', $userId)
            ->where('competition_id', $competitionId)
            ->firstOrFail();

        $existingFile = $this->teamRepository->findFileByType($team->id, $type);

        if ($existingFile) {
            Storage::disk('public')->delete($existingFile->file_path);
            $existingFile->delete();
        }

        $folderPath = 'registrations/' . $team->id . '/' . $type;
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = $file->getClientOriginalName();
        $path = '';

        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            $fileName = $type . '_' . time() . '.webp';

            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());

            $webpData = $image->toWebp(80);

            Storage::disk('public')->put($folderPath . '/' . $fileName, (string) $webpData);
            $path = $folderPath . '/' . $fileName;

            $originalName = pathinfo($originalName, PATHINFO_FILENAME) . '.webp';
        }
        elseif ($extension === 'pdf') {
            $fileName = $type . '_' . time() . '.pdf';
            $tempPath = $file->getRealPath();

            $compressedPath = storage_path('app/temp_' . time() . '.pdf');

            $gsCmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook -dNOPAUSE -dQUIET -dBATCH -sOutputFile=" . escapeshellarg($compressedPath) . " " . escapeshellarg($tempPath);

            @shell_exec($gsCmd);

            if (file_exists($compressedPath) && filesize($compressedPath) > 0) {
                $path = Storage::disk('public')->putFileAs($folderPath, new File($compressedPath), $fileName);
                unlink($compressedPath); // Hapus file temp
            } else {
                $path = $file->storeAs($folderPath, $fileName, 'public');
            }
        }
        else {
            $fileName = $type . '_' . time() . '.' . $extension;
            $path = $file->storeAs($folderPath, $fileName, 'public');
        }

        return $this->teamRepository->saveFileInfo([
            'team_id' => $team->id,
            'file_path' => $path,
            'original_name' => $originalName,
            'type' => $type
        ]);
    }
}
