<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role_id' => 'required|exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->authService->register($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'OTP telah dikirim ke email Anda. Berlaku selama 5 menit.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $this->authService->verifyOtp($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Registrasi dan verifikasi berhasil',
                'data' => $data['user'],
                'access_token' => $data['token'],
                'token_type' => 'Bearer'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->authService->resendOtp($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'OTP baru telah dikirim ke email Anda. Berlaku selama 5 menit.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->authService->forgotPassword($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Link/Token reset password telah dikirim ke email Anda.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->authService->resetPassword($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Password berhasil diubah. Silakan login dengan password baru.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $this->authService->login($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Login berhasil',
                'data' => $data['user'],
                'role_id' => $data['user']->role_id,
                'access_token' => $data['token'],
                'token_type' => 'Bearer'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    public function logout(Request $request)
    {
        try {

            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            $this->authService->logout($user);

            return response()->json([
                'status' => 'success',
                'message' => 'Logout berhasil'
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'oldPassword' => 'required|string',
            'newPassword' => 'required|string|min:8',
        ], [
            'newPassword.min' => 'Password baru minimal harus 8 karakter.'
        ]);

        try {
            $this->authService->updatePassword(
                $request->user(),
                $request->oldPassword,
                $request->newPassword
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Password berhasil diperbarui!'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getMe(Request $request)
    {
        $user = $request->user()->load('staffProfile');

        $isRegistered = Team::where('user_id', $user->id)
            ->where('status', '!=', 'draft')
            ->exists();

        $division = null;
        $roleId = (string) $user->role_id;

        if ($roleId === '4') {
            $division = 'Peserta';
        } elseif ($roleId === 'rol_jms02ks6') {
            $division = 'Juri';
        } elseif (in_array($roleId, ['rol_4d5e6f', 'rol_7g8h9i', 'rol_1a2b3c', 'rol_kobh21j']) && $user->staffProfile) {
            $division = $user->staffProfile->division;
        }

        $userData = array_merge($user->toArray(), [
            'is_registered' => $isRegistered,
            'division'      => $division,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $userData
        ], 200);
    }
}
