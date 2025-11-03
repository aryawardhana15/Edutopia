<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByUsername(string $username)
    {
        return $this->model->where('username', $username)->first();
    }

    public function findByRole(string $role)
    {
        return $this->model->where('role', $role)->get();
    }

    public function search(string $query)
    {
        return $this->model->where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('username', 'like', "%{$query}%")
            ->get();
    }
}

