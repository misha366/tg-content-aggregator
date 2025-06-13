<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phash extends Model
{
    public $timestamps = false;
    protected $fillable = ['hash'];
}
