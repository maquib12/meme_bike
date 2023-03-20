<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
     protected $fillable = [
      'id','user_id','friend_user_id','status'
    ];
}
