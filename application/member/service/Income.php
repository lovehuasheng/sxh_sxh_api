<?php
// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 童自扬 <2421886569@qq.com> 
// +----------------------------------------------------------------------
// | Function: 接受资助服务层
// +----------------------------------------------------------------------


namespace app\member\service;

use think\Model;
use \think\Request;

class Income extends Model {
    
    
     public function get_log_list($data,$sig) {
        
         $validator_instance = \think\Loader::validate('Income'); 
     
         if($validator_instance->scene('get_log_list')->check($data) !== true)
        {
                //验证不通过则获取错误信息,并赋值到自身的error_code与error_msg属性
                $validate_result = $validator_instance->getError();
                list($error_msg,$error_code)  = $validate_result;
                return errReturn($error_msg, $error_code);
        }
        //写入日志
        trace('Incomeservice的get_log_list方法过滤的post参数：' . json_encode($data, JSON_UNESCAPED_UNICODE));
        trace('Incomeservice的get_log_list方法过滤的sig参数：' . $sig);
        
        //签名比对
        $result = validate_response($data, $sig);

        trace('Incomeservice的get_log_list方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        
        //调用业务逻辑
        $logic = \think\Loader::model('Income', 'logic');
        return $logic->get_log_list($data);
        
        
    }
}
