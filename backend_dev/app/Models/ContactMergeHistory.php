<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMergeHistory extends Model
{
    protected $table = 'contact_merge_history';
    
    protected $fillable = [
        'master_contact_id',
        'merged_contact_id',
        'merge_details',
        'merged_at',
    ];

    protected $casts = [
        'merge_details' => 'array',
        'merged_at' => 'datetime',
    ];

    public function masterContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'master_contact_id');
    }

    public function mergedContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'merged_contact_id');
    }
}