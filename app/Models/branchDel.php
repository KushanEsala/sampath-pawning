<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class branchDel extends Model
{
    use HasFactory;

    protected $fillable = [
        'bccode',
        'name',
        'address',
        'contact1',
        'contact2',
        'date',

    ];
}
