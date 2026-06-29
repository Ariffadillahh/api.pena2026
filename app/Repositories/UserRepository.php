<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function create(array $data)
    {
        return User::create($data);
    }

    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function updatePassword($user, string $newPassword)
    {
        return $user->update([
            'password' => $newPassword
        ]);
    }
}
