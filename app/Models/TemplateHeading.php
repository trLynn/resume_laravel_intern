<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplateHeading extends Model
{
    use SoftDeletes;
    
    protected $table = 'template_heading';

    protected $guarded = [];
}
