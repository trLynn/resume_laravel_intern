<?php

namespace app\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Employee\EmployeeRepository;
use App\Repositories\Applicant\ApplicantRepository;
use App\Interfaces\Employee\EmployeeRepositoryInterface;
use App\Repositories\Template\TemplateRepository;
use App\Interfaces\Template\TemplateRepositoryInterface;
use App\Interfaces\Applicant\ApplicantRepositoryInterface;


class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(TemplateRepositoryInterface::class, TemplateRepository::class);
        $this->app->bind(ApplicantRepositoryInterface::class, ApplicantRepository::class);    
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
