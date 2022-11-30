<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplateHeadingSubheading extends Model
{
    use SoftDeletes;
    
    protected $table = 'template_heading_subheading';

    protected $guarded = [];
}
