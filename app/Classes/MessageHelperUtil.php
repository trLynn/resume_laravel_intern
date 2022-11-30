<?php
namespace App\Classes;
use Throwable;
use Brycen\Libraries\StandardLibraries\CheckUtil;
use Brycen\Libraries\StandardLibraries\StringUtil;

class MessageHelperUtil
{
    protected $number_pattern,$strUtil,$checkUtil;
    public function __construct(){
		$this->number_pattern  = "/^[\d]*$/";
		$this->strUtil   = new StringUtil;
		$this->checkUtil = new CheckUtil;
    }

    /**
     * Display success message
     * @author  ZAR NI WIN
     * @create  [11.02.2021]
     * @param  array data
     * @return \Illuminate\Http\Response
     */
    function successMessage($msgCode,$statusCode='',$attribute=''){
        try{
            $statusCode = $statusCode?$statusCode:config('HTTP_CODE_200');
            if(!$this->checkUtil->requireCheck($msgCode)){// check require field for message code
                return response()->json([
                    'status'  => 'NG',
                    'message' => __('bamawl_lib_errorMsg.SE006',['attribute' => 'Message Code'])
                ],config('HTTP_CODE_422'));
            }
            if(!$this->strUtil->match($this->number_pattern,$statusCode)){// check status code valid
                return response()->json([
                    'status'  => 'NG',
                    'message' => __('bamawl_lib_errorMsg.SE007',['attribute' => 'Status Code'])
                ],config('HTTP_CODE_422'));
            }
            if(is_array($attribute)){// for array value of attribute
                return response()->json([
                    'status'  => 'OK',
                    'message' => __($msgCode,$attribute)
                ],$statusCode);
            }else{// single value of attribute
                return response()->json([
                    'status'  => 'OK',
                    'message' => __($msgCode,['attribute' => $attribute])
                ],$statusCode);
            }
        }catch(Throwable $e){
            report($e);
            return response()->json([
                'status'  => 'NG',
                'message' => __('bamawl_lib_errorMsg.SE005')
            ],config('HTTP_CODE_500'));
        }
    }

    /**
     * Display error message
     * @author  ZAR NI WIN
     * @create  [11.02.2021]
     * @param  array data
     * @return \Illuminate\Http\Response
     */
    function errorMessage($msgCode,$statusCode='',$attribute=''){
        try{
            $statusCode = $statusCode?$statusCode:config('HTTP_CODE_500');
            if(!$this->checkUtil->requireCheck($msgCode)){// check require field for message code
                return response()->json([
                    'status'  => 'NG',
                    'message' => __('bamawl_lib_errorMsg.SE006',['attribute' => 'Message Code'])
                ],config('HTTP_CODE_422'));
            }
            if(!$this->strUtil->match($this->number_pattern,$statusCode)){// check status code valid
                return response()->json([
                    'status'  => 'NG',
                    'message' => __('bamawl_lib_errorMsg.SE007',['attribute' => 'Status Code'])
                ],config('HTTP_CODE_422'));
            }
            if(is_array($attribute)){// for array value of attribute
                $msgArr = [
                    'status'  => 'NG',
                    'message' => __($msgCode,$attribute)
                ];
            }else{// single value of attribute
                $msgArr = [
                    'status'  => 'NG',
                    'message' => __($msgCode,['attribute' => $attribute])
                ];
            }
            return response()->json($msgArr,$statusCode);
        }catch(Throwable $e){
            report($e);
            return response()->json([
                'status'  => 'NG',
                'message' => __('bamawl_lib_errorMsg.SE005')
            ],config('HTTP_CODE_500'));
        }
    }
}
