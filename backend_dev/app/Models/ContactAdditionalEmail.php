<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactAdditionalEmail extends Model
{
    protected $fillable = ['contact_id', 'email'];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}