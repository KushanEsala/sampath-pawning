<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MMainAccountType extends Model
{
    use HasFactory;
    protected $fillable = ['code','category','description'];
}
