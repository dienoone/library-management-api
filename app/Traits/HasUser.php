<?php
// app/Traits/HasUser.php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasUser
{
    protected $with = ['user'];

    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'userable');
    }

    public function getFirstNameAttribute(): ?string
    {
        return $this->user ? $this->user->first_name : null;
    }

    public function getLastNameAttribute(): ?string
    {
        return $this->user ? $this->user->last_name : null;
    }

    public function getNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->first_name . ' ' . $this->user->last_name;
        }

        return $this->getDefaultName();
    }

    public function getEmailAttribute(): ?string
    {
        return $this->user ? $this->user->email : null;
    }

    public function getPhoneAttribute(): ?string
    {
        return $this->user ? $this->user->phone : null;
    }

    public function getAddressAttribute(): ?string
    {
        return $this->user ? $this->user->address : null;
    }

    public function getBirthDateAttribute(): ?string
    {
        return $this->user ? $this->user->birth_date : null;
    }

    protected function getDefaultName(): string
    {
        $className = class_basename($this);
        return "Unknown {$className}";
    }

    public function hasUser(): bool
    {
        return !is_null($this->user);
    }

    public function createUser(array $userData): User
    {
        if ($this->user) {
            $this->user->update($userData);
            return $this->user;
        }

        $user = User::create($userData);
        $this->user()->save($user);

        return $user;
    }

    public function getUserTypeAttribute(): string
    {
        return class_basename($this);
    }
}
