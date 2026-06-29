<?php

namespace App\Services;

use App\Mail\ResetPasswordMail;
use App\Mail\UserRegistered;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(array $data)
    {
        if ($this->userRepository->findByEmail($data['email'])) {
            throw new Exception('Email sudah terdaftar.');
        }

        $otp = rand(1000, 9999);

        Cache::put('reg_data_' . $data['email'], $data, now()->addMinutes(5));

        Cache::put('otp_' . $data['email'], $otp, now()->addMinutes(5));

        Mail::to($data['email'])->send(
            new UserRegistered((object) $data, $otp)
        );

        return true;
    }

    public function verifyOtp(array $data)
    {
        $cachedOtp = Cache::get('otp_' . $data['email']);
        $cachedRegData = Cache::get('reg_data_' . $data['email']);

        if (!$cachedOtp || !$cachedRegData) {
            throw new Exception('OTP expired atau data pendaftaran tidak ditemukan. Silakan daftar ulang.');
        }

        if ($cachedOtp != $data['otp']) {
            throw new Exception('OTP salah.');
        }

        $cachedRegData['password'] = Hash::make($cachedRegData['password']);
        $cachedRegData['email_verified_at'] = now();
        $cachedRegData['role_id'] = $cachedRegData['role_id'] ?? 3;

        $user = $this->userRepository->create($cachedRegData);

        Cache::forget('otp_' . $data['email']);
        Cache::forget('reg_data_' . $data['email']);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function resendOtp(array $data)
    {
        $email = $data['email'];

        $cachedRegData = Cache::get('reg_data_' . $email);

        if (!$cachedRegData) {
            throw new Exception('Sesi pendaftaran telah berakhir. Silakan isi ulang form registrasi.');
        }

        $newOtp = rand(1000, 9999);

        Cache::put('reg_data_' . $email, $cachedRegData, now()->addMinutes(5));
        Cache::put('otp_' . $email, $newOtp, now()->addMinutes(5));

        Mail::to($email)->send(
            new UserRegistered((object) $cachedRegData, $newOtp)
        );

        return true;
    }

    public function forgotPassword(array $data)
    {
        $user = $this->userRepository->findByEmail($data['email']);

        if (!$user) {
            throw new Exception('Email tidak terdaftar di sistem kami.');
        }

        $token = Str::random(60);

        Cache::put('reset_token_' . $user->email, $token, now()->addMinutes(15));

        Mail::to($user->email)->send(
            new ResetPasswordMail($user, $token)
        );

        return true;
    }

    public function resetPassword(array $data)
    {
        $email = $data['email'];
        $token = $data['token'];

        $cachedToken = Cache::get('reset_token_' . $email);

        if (!$cachedToken) {
            throw new Exception('Token reset password telah kadaluarsa. Silakan request ulang.');
        }

        if ($cachedToken !== $token) {
            throw new Exception('Token tidak valid.');
        }

        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new Exception('User tidak ditemukan.');
        }

        $this->userRepository->updatePassword($user, Hash::make($data['password']));

        Cache::forget('reset_token_' . $email);

        return true;
    }

    public function updatePassword($user, $oldPassword, $newPassword)
    {
        if (!Hash::check($oldPassword, $user->password)) {
            throw new Exception("Password lama yang Anda masukkan salah.");
        }

        return $this->userRepository->updatePassword($user, Hash::make($newPassword));
    }

    public function login(array $credentials)
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new Exception('Email atau password salah.');
        }

        if (!$user->email_verified_at) {
            throw new Exception('Email belum diverifikasi.');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function logout($user)
    {
        $user->tokens()->delete();

        return true;
    }
}
