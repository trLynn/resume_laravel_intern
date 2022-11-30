<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplateApplicantApplicantInfo extends Model
{
    use SoftDeletes;

    protected $table = 'template_applicant_applicant_info';

    protected $guarded = [];
}
