<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JuriAssignment;
use Illuminate\Http\Request;

class ScoreboardController extends Controller
{
    public function saveJurySignature(Request $request, $assignment_id)
    {
        $request->validate(['signature_image' => 'required']);

        $assignment = JuriAssignment::findOrFail($assignment_id);
        $assignment->signature = $request->signature_image;
        $assignment->save();

        return response()->json(['message' => 'Tanda tangan berhasil disimpan!']);
    }

    public function generateCompetitionBundle($competition_id)
    {
        $competition = \App\Models\Competition::findOrFail($competition_id);

        $teams = \App\Models\Team::with(['submission', 'scores.criteria', 'members'])
            ->where('competition_id', $competition_id)
            ->whereIn('status', ['dinilai', 'lolos_top_10', 'lolos_top_5'])
            ->get();

        if ($teams->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Belum ada tim yang memiliki nilai pada kategori ini.'], 404);
        }

        $teams = $teams->map(function ($team) {
            $total = 0;
            foreach ($team->scores as $score) {
                $bobot = $score->criteria->weight ?? 0;
                $total += ($score->score * $bobot) / 100;
            }
            $team->total_score = round($total, 2);
            return $team;
        })->sortByDesc('total_score')->values();

        $juriAssignment = \App\Models\JuriAssignment::with('user')
            ->where('competition_id', $competition_id)
            ->first();

        $juriName = $juriAssignment && $juriAssignment->user ? $juriAssignment->user->name : 'Dewan Juri';
        $juriSignature = $juriAssignment ? $juriAssignment->signature : null;

        // =========================================================
        // PERBAIKAN: SIAPKAN NAMA FILE & URL DI AWAL
        // =========================================================
        $fileNameBundle = 'Bundle_Scoreboard_' . \Illuminate\Support\Str::slug($competition->title) . '_' . time() . '.pdf';
        $pathBundle = 'competitions/' . $competition->id . '/score_board/' . $fileNameBundle;

        // Hapus file lama jika ada
        if ($competition->scoreboard_link) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($competition->scoreboard_link)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($competition->scoreboard_link);
            }
        }

        // Generate PDF dengan mengirimkan path URL ke Blade
        try {
            $pdfBundle = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.scoreboard', [
                'teams' => $teams,
                'competition' => $competition,
                'is_bundle' => true,
                'current_juri_name' => $juriName,
                'current_juri_signature' => $juriSignature,
                'bundle_qr_url' => $pathBundle // <-- TAMBAHAN: Variabel dikirim ke Blade
            ])->setPaper('a4', 'portrait');

            \Illuminate\Support\Facades\Storage::disk('public')->put($pathBundle, $pdfBundle->output());
            $competition->update(['scoreboard_link' => $pathBundle]);

            $fileUrlBundle = asset('storage/' . $pathBundle);

            return response()->json([
                'status' => 'success',
                'message' => 'Bundle PDF dan Klasemen seluruh tim berhasil diperbarui!',
                'url' => $fileUrlBundle
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}
