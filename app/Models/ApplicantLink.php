<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantLink extends Model
{
    use SoftDeletes;

    protected $table = 'applicant_links';

    protected $guarded = [];
}
