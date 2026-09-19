<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        'Code', 'Title', 'Gender', 'Name', 'First_name', 'Middle_name',
        'Last_name', 'Address_1', 'City_1', 'Address_2', 'City_2',
        'Contact_1', 'Contact_2', 'Email', 'NIC', 'Driving_license',
        'Passport', 'Other_identifications', 'Status', 'BC', 'OC','Limit_Amount','Limit_Pawn_Count'
    ];
}