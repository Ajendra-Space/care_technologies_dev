<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomField extends Model
{
    protected $fillable = [
        'field_name',
        'field_type',
        'field_options',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'field_options' => 'array',
        'is_active' => 'boolean',
    ];

    public function contactValues(): HasMany
    {
        return $this->hasMany(ContactCustomField::class);
    }
}