<?php

namespace App\DBTransactions\Department;

use App\Models\Department;
use App\Classes\DBTransaction;

/**
 * To save new department in `departments` table
 *
 * @author  PhyoNaing Htun
 * @create  2022/06/06
 */
class SaveDepartment extends DBTransaction
{
    private $request;

    /**
     * Constructor to assign interface to variable
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
	 * Save Department
     *
     * @author  PhyoNaing Htun
     * @create  2022/06/06
     * @return  array
	 */
    public function process()
    {
        Department::insert([
            'name' => $this->request->department_name,
            'created_emp' => 10001,
            'updated_emp' => 10001
        ]);
        return ['status' => true, 'error' => ''];
    }
}
