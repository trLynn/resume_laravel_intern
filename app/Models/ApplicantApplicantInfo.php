<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantApplicantInfo extends Model
{
    use SoftDeletes;

    protected $table = 'applicant_applicant_info';

    protected $guarded = [];
}
