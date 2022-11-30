<?php

namespace App\DBTransactions\Department;

use App\Models\Department;
use App\Classes\DBTransaction;

/**
 * To delete department in `departments` table
 *
 * @author  PhyoNaing Htun
 * @create  2022/06/06
 */
class DeleteDepartment extends DBTransaction
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
	 * Delete Department
     *
     * @author  PhyoNaing Htun
     * @create  2022/06/06
     * @return  array
	 */
    public function process()
    {
        $affected = Department::where('id', $this->request->department_id)->delete();

        # check row is affected or not
        if (!$affected) {
            return ['status' => false, 'error' => 'Delete Failed!'];
        }
        return ['status' => true, 'error' => ''];
    }
}
