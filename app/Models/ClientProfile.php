<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientProfile extends Model
{
    protected $fillable = [
        'user_id',
        'passport_number',
        'passport_expiry',
        'date_of_birth',
        'nationality',
        'marital_status',
        'family_details',
        'address',
    ];

    protected $casts = [
        'family_details' => 'array',
        'date_of_birth' => 'date',
        'passport_expiry' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
