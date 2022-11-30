<?php

namespace App\DBTransactions\Department;

use App\Models\Department;
use App\Classes\DBTransaction;

/**
 * To update department in `departments` table
 *
 * @author  PhyoNaing Htun
 * @create  2022/06/06
 */
class UpdateDepartment extends DBTransaction
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
	 * Update Department
     *
     * @author  PhyoNaing Htun
     * @create  2022/06/06
     * @return  array
	 */
    public function process()
    {
        $affected = Department::where('id', $this->request->department_id)
                            ->update([
                                'name' => $this->request->department_name,
                                'updated_emp' => 10001,
                                'updated_at' => now()
                            ]);

        # check row is affected or not
        if (!$affected) {
            return ['status' => false, 'error' => 'Update Failed!'];
        }
        return ['status' => true, 'error' => ''];
    }
}
