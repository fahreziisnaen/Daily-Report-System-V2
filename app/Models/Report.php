<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Report extends Model
{
    // Status constants
    const STATUS_DRAFT = 'Draft';
    const STATUS_NON_OVERTIME = 'Laporan tanpa Lembur';
    const STATUS_PENDING_VERIFICATION = 'Menunggu Verifikasi';
    const STATUS_REJECTED_BY_VERIFIER = 'Ditolak Verifikator';
    const STATUS_PENDING_APPROVAL = 'Menunggu Approval VP';
    const STATUS_REJECTED_BY_VP = 'Ditolak VP';
    const STATUS_PENDING_HR = 'Menunggu Review HR';
    const STATUS_REJECTED_BY_HR = 'Ditolak HR';
    const STATUS_COMPLETED = 'Selesai';
    const STATUS_REJECTED = 'Ditolak';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'verifikator_id',
        'vp_id',
        'report_date',
        'project_code',
        'location',
        'start_time',
        'end_time',
        'is_overnight',
        'is_overtime',
        'is_shift',
        'work_day_type',
        'status',
        'rejection_notes',
        'can_revise',
        'submitted_at',
        'verified_at',
        'approved_at',
        'reviewed_at',
        'reviewed_by',
        'completed_at',
        'updated_by'
    ];

    protected $casts = [
        'report_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_overnight' => 'boolean',
        'is_overtime' => 'boolean',
        'is_shift' => 'boolean',
        'can_revise' => 'boolean',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(ReportDetail::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_id');
    }

    public function vp(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vp_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function getValidStatuses()
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PENDING_VERIFICATION,
            self::STATUS_REJECTED_BY_VERIFIER,
            self::STATUS_PENDING_APPROVAL,
            self::STATUS_REJECTED_BY_VP,
            self::STATUS_PENDING_HR,
            self::STATUS_REJECTED_BY_HR,
            self::STATUS_COMPLETED,
            self::STATUS_REJECTED,
            self::STATUS_NON_OVERTIME
        ];
    }
} 