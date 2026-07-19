<?php

namespace App\Modules\ASCM\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'ascm_case_status_history';

    protected $fillable = [
        'case_id',
        'from_status',
        'to_status',
        'changed_by',
        'note',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(SupportCase::class, 'case_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
