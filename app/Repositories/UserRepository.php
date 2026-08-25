<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserRepository
{
    public function create(array $data)
    {
        return User::create($data);
    }

    public function findByEmail(string $email)
    {
        $user = User::where('email', trim($email))->first();

        return $user;
    }

    public function updatePassword($user, string $newPassword)
    {
        return $user->update([
            'password' => $newPassword
        ]);
    }
}
