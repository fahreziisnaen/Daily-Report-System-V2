<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Report;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'homebase',
        'position',
        'department_id',
        'password',
        'avatar_path',
        'signature_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Tambahkan relasi ke reports
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    // Tambahkan relationship untuk reports yang diupdate
    public function updatedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'updated_by');
    }

    public function isAdmin()
    {
        return $this->hasRole(['Super Admin', 'Admin Divisi', 'Vice President', 'Human Resource']);
    }

    public function canModifyReport(Report $report)
    {
        if ($this->hasRole('Super Admin')) {
            return true;
        }
        
        // Admin Divisi or regular users can only modify their own reports
        return $report->user_id === $this->id;
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar_path) {
            return asset('storage/' . $this->avatar_path);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    public function getSignatureUrlAttribute()
    {
        if ($this->signature_path) {
            return asset('storage/' . $this->signature_path);
        }
        return null;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
