<?php

namespace App\Traits;

use Jenssegers\Agent\Agent;

/**
 * DeviceInfoTrait
 *
 * @author  Htet KO Hmue
 * @create_date 2021-06-03
 */
trait DeviceInfoTrait
{
  /**
     * Get Device Info
     * @author Htet Ko Hmue
     * @create_date 2021-06-03
     * @return  array $data
     *
   */
  public function getDeviceInfo()
  {
    $agent = new Agent();
    // get user browser name
    $browsers = $agent->browser();
    // get user browser version
    $version  = $agent->version($browsers);

    $device_flag = request()->device_flag;//get device_flag

    if(empty($device_flag) || is_null($device_flag)){
      $device_flag = config('WEB');
    }

    $data['ip']          = request()->ip();//current use pc ip
    $data['browsers']    = $browsers;//current use browser
    $data['device_flag'] = $device_flag;//current use device
    return $data;
  }
}
