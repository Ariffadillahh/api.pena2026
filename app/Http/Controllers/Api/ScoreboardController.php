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
            return response()->json([
                'status' => 'error',
                'message' => 'Belum ada tim yang memiliki nilai pada kategori ini.'
            ], 404);
        }

        $teams = $teams->map(function ($team) {
            $groupedScores = $team->scores->groupBy('juri_id');
            $jumlahJuri = $groupedScores->count();

            $grandTotal = 0;

            foreach ($groupedScores as $juriScores) {
                $juriTotal = 0;
                foreach ($juriScores as $score) {
                    $bobot = $score->criteria->weight ?? 0;
                    $juriTotal += ($score->score * $bobot) / 100;
                }
                $grandTotal += $juriTotal;
            }

            $rataRata = $jumlahJuri > 0 ? ($grandTotal / $jumlahJuri) : 0;
            $team->total_score = round($rataRata, 2);

            return $team;
        })->sortByDesc('total_score')->values();

        $juriAssignments = \App\Models\JuriAssignment::with('user')
            ->where('competition_id', $competition_id)
            ->get();

        $juriDetails = [];
        foreach ($juriAssignments as $assignment) {
            $juriDetails[$assignment->user_id] = [
                'name'      => $assignment->user ? $assignment->user->name : 'Dewan Juri',
                'signature' => $assignment->signature
            ];
        }

        $fileNameBundle = 'Bundle_Scoreboard_' . \Illuminate\Support\Str::slug($competition->title) . '_' . time() . '.pdf';
        $pathBundle = 'competitions/' . $competition->id . '/score_board/' . $fileNameBundle;

        if ($competition->scoreboard_link) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($competition->scoreboard_link)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($competition->scoreboard_link);
            }
        }

        try {
            $pdfBundle = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.scoreboard', [
                'teams'         => $teams,
                'competition'   => $competition,
                'is_bundle'     => true,
                'juriDetails'   => $juriDetails, 
                'bundle_qr_url' => $pathBundle
            ])->setPaper('a4', 'portrait');

            \Illuminate\Support\Facades\Storage::disk('public')->put($pathBundle, $pdfBundle->output());
            $competition->update(['scoreboard_link' => $pathBundle]);

            $fileUrlBundle = asset('storage/' . $pathBundle);

            return response()->json([
                'status'  => 'success',
                'message' => 'Bundle PDF dan Klasemen seluruh tim berhasil diperbarui!',
                'url'     => $fileUrlBundle
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}
