<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleAlojamientos extends Model
{
    protected $table = 'details_bookings';
    //public $timestamps = false;

    protected $casts = [
        'item_expenses' => 'json',
    ];
}
