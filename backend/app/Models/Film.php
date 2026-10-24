<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
    use HasFactory;

    /**
     * Status tontonan yang dipakai backend dan frontend.
     *
     * @var list<string>
     */
    public const STATUSES = [
        'ingin_ditonton',
        'sedang_ditonton',
        'sudah_ditonton',
    ];

    protected $fillable = [
        'title',
        'director',
        'year',
        'status',
        'rating',
        'review',
    ];

    protected $casts = [
        'year' => 'integer',
        'rating' => 'integer',
    ];
}
