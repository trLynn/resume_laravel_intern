<?php

namespace App\Http\Controllers\API\Employee;

use Illuminate\Http\Request;
use App\Http\Requests\loginRequest;
use App\Http\Controllers\Controller;
use App\Interfaces\Employee\EmployeeRepositoryInterface;

class EmployeeController extends Controller
{

     /**
     * Create constructor.
     * @create [20.6.2022]
     * @author Thu Ta
     */
    public function __construct(EmployeeRepositoryInterface $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * Admin login with employeeid and password.
     * @create [21.6.2022]
     * @author Thu Ta
     * @param  loginRequest 
     * @return \Illuminate\Http\Response
     */
    public function login(loginRequest $request)
    {
        $userData = $request;
        $result = $this->employeeRepository->adminLogin($userData);
        return $result;
    }
}
