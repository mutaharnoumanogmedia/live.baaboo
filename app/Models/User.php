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
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'user_name',
        'email',
        'password',
        'referred_by',
        'referral_link',
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

    // append custom fields
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
            ->withPivot(['live_show_id'])
            ->withTimestamps()
            ->orderBy('live_show_block_users.created_at', 'desc');
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

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }
    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function magicLink()
    {
        return url('live-show-magic-link/'.$this->getUserName());
    }

    public function referralLink()
    {
        return route('register-user-via-form', ['name' => $this->getUserName()]);
    }

    public function getUserName()
    {
        //take user_name if exists, otherwise generate a new one
        if ($this->user_name) {
            return $this->user_name;
        }
        // logic is take fist part of email, if not unique, then append a 2 digit number to it
        
        $userName = explode('@', $this->email)[0];
        do {
            $checkUserName = $userName;
            if (User::where('user_name', $checkUserName)->exists()) {
                $userName = $userName.rand(10, 99);
            } else {
                break;
            }
        } while (true);

        return $userName;
    }
}
