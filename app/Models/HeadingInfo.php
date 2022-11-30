<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HeadingInfo extends Model
{
    use SoftDeletes;
    protected $table = "heading_info";
    protected $guarded = [];
}
