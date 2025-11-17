<?php
// app/Traits/HasUser.php

namespace App\Traits;

use App\Models\User;

trait HasUser
{
    /**
     * Get the user associated with the model
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(User::class, 'userable');
    }

    /**
     * Get first name from user
     */
    public function getFirstNameAttribute(): ?string
    {
        return $this->user ? $this->user->first_name : null;
    }

    /**
     * Get last name from user
     */
    public function getLastNameAttribute(): ?string
    {
        return $this->user ? $this->user->last_name : null;
    }

    /**
     * Get full name from user
     */
    public function getNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->first_name . ' ' . $this->user->last_name;
        }

        return $this->getDefaultName();
    }

    /**
     * Get email from user
     */
    public function getEmailAttribute(): ?string
    {
        return $this->user ? $this->user->email : null;
    }

    /**
     * Get phone from user
     */
    public function getPhoneAttribute(): ?string
    {
        return $this->user ? $this->user->phone : null;
    }

    /**
     * Get address from user
     */
    public function getAddressAttribute(): ?string
    {
        return $this->user ? $this->user->address : null;
    }

    /**
     * Get birth date from user
     */
    public function getBirthDateAttribute(): ?string
    {
        return $this->user ? $this->user->birth_date : null;
    }

    /**
     * Get the default name when no user is associated
     */
    protected function getDefaultName(): string
    {
        $className = class_basename($this);
        return "Unknown {$className}";
    }

    /**
     * Check if the model has a user account
     */
    public function hasUser(): bool
    {
        return !is_null($this->user);
    }

    /**
     * Create or update user account for this model
     */
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

    /**
     * Get user type based on model class
     */
    public function getUserTypeAttribute(): string
    {
        return class_basename($this);
    }
}
