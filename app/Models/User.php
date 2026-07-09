<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Supports\ApiHC;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class User extends Authenticatable implements AuditableContract
{
    use Auditable, HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

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
        'password' => 'hashed',
        'meta' => 'array',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function roles()
    {
        return $this->belongsToMany(\Spatie\Permission\Models\Role::class, 'model_has_roles', 'model_id', 'role_id');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'parent_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'user_projects', 'user_id', 'project_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function hasProject($project) {
        if ($project instanceof ProjectPeriodeList) {
            $project = $project->project;
        }

        if (!($project instanceof Project)) {
            return false;
        }

        return $this->projects->contains($project);
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function levels()
    {
        return $this->jabatan ? $this->jabatan->levels() : collect([]);
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    // Override method dari HasRoles trait untuk menggabungkan role dari jabatan
    public function getRoleNamesAttribute()
    {
        $directRoles = $this->roles()->pluck('name');

        // Jika user memiliki jabatan, ambil role dari level yang terkait dengan jabatan
        if ($this->jabatan) {
            $levelRoles = $this->jabatan->levels()
                ->with('roles')
                ->get()
                ->pluck('roles')
                ->flatten()
                ->pluck('name');

            return $directRoles->merge($levelRoles)->unique();
        }

        return $directRoles;
    }
}
