<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
     protected $fillable = [
      'id','character_name','thumbnail','last_update_date','status'
    ];
}
