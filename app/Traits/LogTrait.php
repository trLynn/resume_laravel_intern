<?php

namespace App\Traits;

use Throwable;
use App\Models\CrudLog;
use App\Traits\DeviceInfoTrait;
use Illuminate\Support\Facades\Log;
use App\Classes\MessageHelperUtil;

/**
 * LogTrait
 * 
 * @author  Htet KO Hmue
 * @create_date 2021-01-26
 */
trait LogTrait {
 use DeviceInfoTrait;
  /**
     * Write user action into crud_logs table
     * to insert log data 
     * @author Htet Ko Hmue
     * @create_date 2021-01-26
     * @param int $employee_id
     * @param string $description
     * @param string $form
     * @param int $op_flag
     *
   */
  public function writeCRUDLog($employee_id,$description,$form,$op_flag) {
    try{
        $device = $this->getDeviceInfo();
        $CrudLog['ip_address']     = $device['ip'];//current use pc ip
        $CrudLog['browser']        = $device['browsers'];//current use browser
        $CrudLog['employee_id']    = $employee_id;
        $CrudLog['description']    = $description;
        $CrudLog['form']           = $form;
        $CrudLog['op_flag']        = $op_flag;
        $CrudLog['device_flag']    = $device['device_flag'];//current use device (web , android, ios)
        CrudLog::insert($CrudLog);
    }catch(Throwable $e){
      Log::debug($e);//error log   
      $msgHelpUtil = new MessageHelperUtil;
      return $msgHelpUtil->errorMessage('errorMessage.SE005',config('HTTP_CODE_500'));
    }  
  }  
}
