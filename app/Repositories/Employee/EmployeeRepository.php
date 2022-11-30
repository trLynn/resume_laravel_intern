<?php
namespace App\Repositories\Employee;

use App\Models\Employee;
use App\Classes\MessageHelperUtil;
use Illuminate\Support\Facades\Hash;
use App\Interfaces\Employee\EmployeeRepositoryInterface;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    public function __construct()
    {
        $this->msgHelpUtil = new MessageHelperUtil;
    }
    
    /**
     * Function for admin login.
     * @create [20.6.2022]
     * @author Thu Ta
     * @param  $userData
     * @return array
     */
    public function adminLogin($userData)
    {
        $userId = $userData->employee_id;
        $password =$userData->password;
        $employeeExists = Employee::where('employee_id',$userId)->exists();
        if($employeeExists){
            $employeeData = Employee::where('employee_id',$userId)->first();
            
            if(Hash::check($password,$employeeData->password)){
                return $this->msgHelpUtil->successMessage('successMessage.SS005',config('HTTP_CODE_200'));
            } else {
                return $this->msgHelpUtil->errorMessage('errorMessage.SE009',config('HTTP_CODE_200'),['attribute'=>'Password']);
            }
        } else {
            return $this->msgHelpUtil->errorMessage('errorMessage.SE009',config('HTTP_CODE_200'),['attribute'=>'Admin user']);
        }
    }
}