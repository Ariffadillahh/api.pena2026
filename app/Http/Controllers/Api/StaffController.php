<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\StaffAccountCreated;
use Exception;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $search = $request->query('search', '');
            $division = $request->query('division', 'all');

            $query = User::with([
                'staffProfile.handledCompetition',
                'staffProfile.handledCompetition2'
            ])->whereIn('role_id', ['rol_4d5e6f', 'rol_7g8h9i', 'rol_kobh21j']);

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($division !== 'all' && !empty($division)) {
                $query->whereHas('staffProfile', function ($q) use ($division) {
                    $q->where('division', $division);
                });
            }

            $staffMembers = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $staffMembers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data staff: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string',
            'role_id' => 'required|string',
            'division' => 'required|string',
            'pj_competition_id' => 'nullable|uuid|exists:competitions,id',
            'pj_category' => 'nullable|string',
            'pj_competition_id_2' => 'nullable|uuid|exists:competitions,id',
            'pj_category_2' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $rawPassword = Str::password(10, true, true, false, false);

            $user = User::create([
                'email' => $request->email,
                'name' => $request->name,
                'email_verified_at' => now(),
                'password' => Hash::make($rawPassword),
                'role_id' => $request->role_id,
            ]);

            $user->staffProfile()->create([
                'division' => $request->division,
                'pj_competition_id' => $request->pj_competition_id,
                'pj_category' => $request->pj_category,
                'pj_competition_id_2' => $request->pj_competition_id_2,
                'pj_category_2' => $request->pj_category_2,
            ]);

            $roleNames = [
                'rol_4d5e6f' => 'KADIV',
                'rol_kobh21j' => 'KOORDINATOR',
                'rol_7g8h9i' => 'STAFF'
            ];
            $roleName = $roleNames[$request->role_id] ?? 'Panitia';

            Mail::to($user->email)->send(new StaffAccountCreated($user->email, $rawPassword, $roleName));

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Staff berhasil ditambahkan dan email telah dikirim.'
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan staff: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'role_id' => 'required|string',
            'division' => 'required|string',
            'pj_competition_id' => 'nullable|uuid|exists:competitions,id',
            'pj_category' => 'nullable|string',
            'pj_competition_id_2' => 'nullable|uuid|exists:competitions,id',
            'pj_category_2' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            $user->update([
                'name' => $request->name,
                'role_id' => $request->role_id,
            ]);

            $user->staffProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'division' => $request->division,
                    'pj_competition_id' => $request->pj_competition_id,
                    'pj_category' => $request->pj_category,
                    'pj_competition_id_2' => $request->pj_competition_id_2,
                    'pj_category_2' => $request->pj_category_2,
                ]
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data staff berhasil diperbarui!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui staff: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            if ($user->staffProfile) {
                $user->staffProfile()->delete();
            }

            $user->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data staff berhasil dihapus permanen.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus staff: ' . $e->getMessage()
            ], 500);
        }
    }
}
