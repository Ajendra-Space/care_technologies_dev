<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactAdditionalPhone extends Model
{
    protected $fillable = ['contact_id', 'phone'];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}