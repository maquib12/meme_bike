<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bike extends Model
{
     protected $fillable = [
      'id','bike_name','thumbnail','last_update_date','status'
    ];
}
