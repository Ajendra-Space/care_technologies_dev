<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactFile extends Model
{
    protected $fillable = [
        'contact_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}