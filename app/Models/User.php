<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    //append custom fields
    protected $appends = ['role'];

    public function getRoleAttribute()
    {
        return $this->getRoleNames()->first();
    }

    public function scopeRole($query, $role)
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    public function liveShows()
    {
        return $this->belongsToMany(LiveShow::class, 'user_live_shows')
            ->using(UserLiveShow::class)
            ->withPivot(['score', 'status', 'created_at', 'prize_won', 'is_winner', 'is_online', 'created_at'])
            ->withTimestamps();
    }

    public function blockedLiveShows()
    {
        return $this->belongsToMany(LiveShow::class, 'live_show_block_users')
            ->using(LiveShowBlockUser::class)
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(LiveShowMessages::class, 'user_id');
    }

    public function quizzes()
    {
        return $this->hasMany(UserQuiz::class, 'user_id');
    }

    public function quizResponses()
    {
        return $this->hasMany(UserQuizResponse::class, 'user_id');
    }
}
