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
        'is_active',
        'inactive_reason',
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

    /**
     * Check if user has any administrative role
     * 
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole(['Super Admin', 'Admin Divisi', 'Vice President', 'Human Resource']);
    }

    /**
     * Check if user has full administrative privileges
     * 
     * @return bool
     */
    public function isFullAdmin()
    {
        return $this->hasRole(['Super Admin']);
    }

    /**
     * Check if user has department management privileges
     * 
     * @return bool
     */
    public function isDepartmentAdmin()
    {
        return $this->hasRole(['Super Admin', 'Vice President', 'Admin Divisi']);
    }

    /**
     * Check if user can modify the given report based on ownership and status
     * 
     * @param Report $report
     * @return bool
     */
    public function canModifyReport(Report $report)
    {
        // Super Admin can modify any report
        if ($this->isFullAdmin()) {
            return true;
        }
        
        // Regular users can only modify their own reports
        if ($this->id !== $report->user_id) {
            return false;
        }
        
        // Check if report can be modified based on status
        return in_array($report->status, [
            Report::STATUS_DRAFT,
            Report::STATUS_NON_OVERTIME,
            Report::STATUS_REJECTED_BY_VERIFIER,
            Report::STATUS_REJECTED_BY_VP,
            Report::STATUS_REJECTED_BY_HR
        ]);
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

    /**
     * Check if user is a Verifikator
     * 
     * @return bool
     */
    public function isVerifikator()
    {
        return $this->hasRole('Verifikator');
    }

    /**
     * Check if user is a Vice President
     * 
     * @return bool
     */
    public function isVicePresident()
    {
        return $this->hasRole('Vice President');
    }

    /**
     * Check if user is a Human Resource
     * 
     * @return bool
     */
    public function isHumanResource()
    {
        return $this->hasRole('Human Resource');
    }

    /**
     * Check if user can verify reports
     * 
     * @param Report $report
     * @return bool
     */
    public function canVerifyReport(Report $report)
    {
        return $this->isVerifikator() && 
               $this->department_id === $report->user->department_id;
    }

    /**
     * Check if user can approve reports
     * 
     * @param Report $report
     * @return bool
     */
    public function canApproveReport(Report $report)
    {
        return $this->isVicePresident() && 
               $this->department_id === $report->user->department_id;
    }

    /**
     * Check if user can review reports
     * 
     * @return bool
     */
    public function canReviewReports()
    {
        return $this->isHumanResource();
    }

    /**
     * Check if user is an Admin Divisi
     * 
     * @return bool
     */
    public function isDivisiAdmin()
    {
        return $this->hasRole('Admin Divisi');
    }

    /**
     * Check if user is a regular employee
     * 
     * @return bool
     */
    public function isEmployee()
    {
        return $this->hasRole('Employee');
    }
}
