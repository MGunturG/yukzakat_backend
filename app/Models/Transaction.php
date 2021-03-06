<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'nominal',
        'jenis'
    ];

    /**
     * Relation to user
     */
    public function user(){
        return $this->belongsTo(User::class);
    }
}
