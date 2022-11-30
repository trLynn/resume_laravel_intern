<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplateApplicant extends Model
{
    use SoftDeletes;

    protected $table = 'template_applicant';

    protected $guarded = [];
}
