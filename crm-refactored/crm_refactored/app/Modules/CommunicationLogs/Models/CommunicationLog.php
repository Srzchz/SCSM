<?php

namespace App\Modules\CommunicationLogs\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunicationLog extends Model
{
    use HasFactory;

    protected $table = 'crm_communication_logs';

    protected $fillable = [
        'customer_id',
        'ticket_id',
        'issue',
        'details',
        'log_date',
        'mode',
        'status',
    ];

    protected $casts = [
        'log_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }
}
