<?php

namespace App\Modules\ASCM\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyClaimNote extends Model
{
    use HasFactory;

    protected $table = 'ascm_warranty_claim_notes';

    protected $fillable = [
        'warranty_claim_id',
        'author_id',
        'note_type',
        'body',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(WarrantyClaim::class, 'warranty_claim_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
