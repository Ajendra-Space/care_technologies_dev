<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'profile_image',
        'status',
        'merged_into_contact_id',
        'merge_history',
    ];

    protected $casts = [
        'merge_history' => 'array',
    ];

    public function customFieldValues(): HasMany
    {
        return $this->hasMany(ContactCustomField::class);
    }

    public function additionalEmails(): HasMany
    {
        return $this->hasMany(ContactAdditionalEmail::class);
    }

    public function additionalPhones(): HasMany
    {
        return $this->hasMany(ContactAdditionalPhone::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ContactFile::class);
    }

    public function mergedInto(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'merged_into_contact_id');
    }

    public function mergedContacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'merged_into_contact_id');
    }
}