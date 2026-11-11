<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'student_id',
        'email',
        'password',
        'profile_image',
        'bio',
        'gender',
        'show_gender',
        'birthday',
        'phone',
        'course',
        'year_level',
        'campus_location',
        'favorite_food',
        'interests',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthday' => 'date',
            'show_gender' => 'boolean',
        ];
    }

    public function getGenderLabelAttribute(): ?string
    {
        return match ($this->gender) {
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other',
            'prefer_not_to_say' => 'Prefer not to say',
            default => null,
        };
    }

    public function getInterestsListAttribute(): array
    {
        if (!$this->interests) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $this->interests))));
    }

    public function getProfileCompletionAttribute(): int
    {
        $fields = [
            'profile_image', 'bio', 'gender', 'birthday', 'phone',
            'course', 'year_level', 'campus_location', 'favorite_food', 'interests',
        ];

        $filled = collect($fields)->filter(fn ($f) => !empty($this->{$f}))->count();

        return (int) round(($filled / count($fields)) * 100);
    }
}