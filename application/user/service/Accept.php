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


namespace app\user\service;

use think\Model;
use \think\Request;

class Accept extends Model {
    
    /**
     * 接受资助列表服务层
     * @param type $user_id
     * @return type
     * @Author 童自扬
     * @time  2016-10-08
     */
    public function accept_list($data,$sig) {

        //写入日志
        trace('Acceptservice的accept_list方法过滤的post参数：' . json_encode($data, JSON_UNESCAPED_UNICODE));
        trace('Acceptservice的accept_list方法过滤的sig参数：' . $sig);
        
        //签名比对
        $result = validate_response($data, $sig);

        trace('Acceptservice的accept_list方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        
        //调用业务逻辑
        $logic = \think\Loader::model('Accept', 'logic');
        return $logic->accept_list($data,$data['page'],$data['current_page']);
        
        
    }
    
     /**
     * 取消订单服务层
     * @param type $user_id
     * @return type
     * @Author 童自扬
     * @time  2016-10-04
     */
    public function accept_destroy($data,$sig) {
        
        //写入日志
        trace('service的accept_destroy方法过滤的post参数：' . json_encode($data, JSON_UNESCAPED_UNICODE));
        trace('service的accept_destroy方法过滤的sig参数：' . $sig);
        
        //签名比对
        $result = validate_response($data, $sig);

        trace('service的accept_destroy方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        
        
        //调用业务逻辑
        $logic = \think\Loader::model('Accept', 'logic');
        return $logic->accept_destroy($data);
    }
    
    /**
     * 提供资助的匹配详情页[服务层]
     * @return type
     * @Author 童自扬
     * @time  2016-10-04
     */
    public function accept_detail($data,$sig) {
      
        //写入日志
        trace('service的accept_detail方法过滤的post参数：' . json_encode($data, JSON_UNESCAPED_UNICODE));
        trace('service的accept_detail方法过滤的sig参数：' . $sig);
        
        //签名比对
        $result = validate_response($data,$sig);

        trace('service的accept_detail方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        
        //调用业务逻辑
        $logic = \think\Loader::model('Accept', 'logic');
        return $logic->accept_detail($data);
        
    }
    
    
    /**
     * 进入打款人页面[服务层]
     * @return type
     * @Author 童自扬
     * @time  2016-10-04
     */
    public function accept_person_msg($data,$sig) {
        
        //写入日志
        trace('service的accept_person_msg方法过滤的post参数：' . json_encode($data, JSON_UNESCAPED_UNICODE));
        trace('service的accept_person_msg方法过滤的sig参数：' . $sig);
        
        //签名比对
        $result = validate_response($data,$sig);

        trace('service的accept_person_msg方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        
        //调用业务逻辑
        $logic = \think\Loader::model('Accept', 'logic');
        return $logic->accept_person_msg($data);
    }
    
    
    public function accept_delayed($data,$sig) {
        
        //写入日志
        trace('service的accept_delayed方法过滤的post参数：' . json_encode($data, JSON_UNESCAPED_UNICODE));
        trace('service的accept_delayed方法过滤的sig参数：' . $sig);
        
        //签名比对
        $result = validate_response($data,$sig);

        trace('service的accept_delayed方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        //token验证
        /*if(empty($data['check_token'])){
            return errReturn('令牌不能为空！', '400');
        }
        $token = cache('check_token_accept_delayed'.$data['user_id']);
        if(!empty($token) && $token == $data['check_token']){
            cache('check_token_accept_delayed'.$data['user_id'],null);
            return errReturn('数据不能重复提交！', '400');
        }else{
            cache('check_token_accept_delayed'.$data['user_id'],$data['check_token']);
        }*/
        //调用业务逻辑
        $logic = \think\Loader::model('Accept', 'logic');
        $return = $logic->accept_delayed($data);
        //cache('check_token_accept_delayed'.$data['user_id'],null);
        return $return;
    }
    
    
    public static function accept_collections($data,$sig) {
       if(!isset($data['id']) || !isset($data['pwd']) || !isset($data['user_id'])) {
            return errReturn('参数错误!', -501);
       }  
       //token验证
        if(empty($data['check_token'])){
            return errReturn('令牌不能为空！', '400');
        }
        $token = cache('accept_collections_form_token'.$data['user_id']);
        if(!empty($token) && $token == $data['check_token']){
            cache('accept_collections_form_token'.$data['user_id'],null);
            return errReturn('数据不能重复提交！', '400');
        }else{
            cache('accept_collections_form_token'.$data['user_id'],$data['check_token']);
        }
        $result = validate_response($data,$sig); //签名比对
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        //调用业务逻辑
        $logic = \think\Loader::model('Accept', 'logic');
        return $logic->accept_collections($data);
    }

}
