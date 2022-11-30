<?php

namespace App\Http\Middleware;

use Closure;
use Throwable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

//use Bamawl\Libraries\Utilities\MessageHelperUtil;

class ConstantFileMiddleware
{
    /**
     * Handle an incoming request. Htet Ko Hmue
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try{
            //select constant values
            $result = DB::table('constant_collections')->pluck(
                'constant_definition','constant_name'
            );
            if($result){
                $constant = array();
                foreach($result as $key=>$res) {
                    $constant[$key] = $res;
                    Config::set($constant);
                }
            }
            $customerKeyName = (request()->segments()) ? request()->segments()[1] : ''; //get customer name
            Config::set("CUSTOMER_KEY_NAME", $customerKeyName);//set customeName as config constant
            return $next($request);
        }catch(Throwable $e){
            Log::debug($e);
            //$msgHelpUtil = new MessageHelperUtil();
            //return $msgHelpUtil->errorMessage('errorMessage.SE005',500);
        }
    }
}
