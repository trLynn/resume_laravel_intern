<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplateHeadingLevel extends Model
{
    use SoftDeletes;
    
    protected $table = 'template_heading_level';

    protected $guarded = [];
}
