<?php
// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 童自扬 <2421886569@qq.com> 
// +----------------------------------------------------------------------
// | Function: 提供资助服务层
// +----------------------------------------------------------------------


namespace app\user\service;

use think\Model;
use \think\Request;

class Provide extends Model {
    
    /**
     * 提供资助列表服务层
     * @param type $user_id
     * @return type
     * @Author 童自扬
     * @time  2016-10-03 
     */
    public function give_list($data,$sig) {

        //写入日志
        trace('service的give_list方法过滤的post参数：' . json_encode($data, JSON_UNESCAPED_UNICODE));
        trace('service的give_list方法过滤的sig参数：' . $sig);
      
        //签名比对
        $result = validate_response($data, $sig);

        trace('service的give_list方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        //调用业务逻辑
        $logic = \think\Loader::model('Provide', 'logic');
        return $logic->give_list($data);
        
        
    }
    
    
    
    /**
     * 取消订单服务层
     * @param type $user_id
     * @return type
     * @Author 童自扬
     * @time  2016-10-04
     */
    public function give_destroy($data,$sig) {
        //写入日志
        trace('service的give_destroy方法过滤的post参数：' . json_encode($data, JSON_UNESCAPED_UNICODE));
        trace('service的give_destroy方法过滤的sig参数：' . $sig);
        
        //签名比对
        $result = validate_response($data, $sig);

        trace('service的give_destroy方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        
        //调用业务逻辑
        $logic = \think\Loader::model('Provide', 'logic');
        return $logic->give_destroy($data);
    }

    
    /**
     * 提供资助的匹配详情页[服务层]
     * @return type
     * @Author 童自扬
     * @time  2016-10-04
     */
    public function give_detail($data,$sig) {
        
        //写入日志
        trace('service的give_detail方法过滤的post参数：' . json_encode($data, JSON_UNESCAPED_UNICODE));
        trace('service的give_detail方法过滤的sig参数：' . $sig);
        
        //签名比对
        $result = validate_response($data,$sig);

        trace('service的give_detail方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        
        //调用业务逻辑
        $logic = \think\Loader::model('Provide', 'logic');
        return $logic->give_detail($data);
        
    }
    
    
    /**
     * 进入打款人页面[服务层]
     * @return type
     * @Author 童自扬
     * @time  2016-10-04
     */
    public function give_person_msg($data,$sig) {
        
        //写入日志
        trace('service的give_person_msg方法过滤的post参数：' . json_encode($data, JSON_UNESCAPED_UNICODE));
        trace('service的give_person_msg方法过滤的sig参数：' . $sig);
        
        //签名比对
        $result = validate_response($data,$sig);

        trace('service的give_person_msg方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        
        //调用业务逻辑
        $logic = \think\Loader::model('Provide', 'logic');
        return $logic->give_person_msg($data);
    }
    
    
    /**
     * 上传打款截图[服务层]
     * @param type $user_id
     * @return type
     * @Author 童自扬
     * @time  2016-10-04
     */
    public function upload_pay_picture($data,$sig) {
      
        
        //写入日志
        trace('service的upload_pay_picture方法过滤的post参数：' . json_encode($data, JSON_UNESCAPED_UNICODE));
        trace('service的upload_pay_picture方法过滤的sig参数：' . $sig);
        
        //签名比对
        $result = validate_response($data,$sig);

        trace('service的upload_pay_picture方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
      
        $info = new \org\Upload(config('upload_picture'),'Qiniu',config('qiniu'));
        $tmp = $info->upload();
        if(!$tmp) {
            return errReturn($info->getError(),-1);
        }
        $data['images'] = $tmp['file']['savename'];
        unset($tmp);
        //调用业务逻辑
        $logic = \think\Loader::model('Provide', 'logic');
        return $logic->upload_pay_picture($data);
    }
}
