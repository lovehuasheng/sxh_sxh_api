<?php


// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 童自扬 <2421886569@qq.com> 
// +----------------------------------------------------------------------
// | Function: 融云测试控制器
// +----------------------------------------------------------------------

namespace app\api\controller;
use \think\Request;


class Version { 
    
    public function version_number() {
        
         $info = Request::instance()->post();
         $sig = Request::instance()->get('sig');
         
         $params = json_decode(decrypt($info['data'],substr($sig,0,16),substr($sig,-16,16)),true);
         if(empty($params)) {
           return errReturn('数据参数错误!', '-9999');
        }
        trace('service的version_number方法过滤的post参数：' . json_encode($info, JSON_UNESCAPED_UNICODE));
        //签名比对
        $result = validate_response($params, $sig);

        trace('Acceptservice的accept_list方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        
         
         if(!isset($params['version_code']) || !isset($params['version_name']) || !isset($params['customer_service_model']) || !isset($params['phone_model']) || !isset($params['user_set_up'])) {
             return errReturn('参数解析错误!', '-10000');
         }
    
         $tmp = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/version_'.$params['user_set_up'].'.json');
         if(empty($tmp)) {
             return errReturn('版本检测失败!', '-10001');
         }
         $data = json_decode($tmp);
         if(empty($data)) {
             return errReturn('版本数据检测失败!', '-10002');
         }

         if($data->version_code > $params['version_code']) {
             return errReturn('版本更新!', '0',set_aes_param(['status'=>$data->status,'version_code'=>$data->version_code,'version_name'=>$data->version_name,'user_set_up'=>$data->user_set_up,'version_url'=>$data->version_url]));
         }
         
          return errReturn('没有检查到版本!', '1');
        
    }
    
    public function version_ios_number() {
        
         $info = Request::instance()->post();
         $sig = Request::instance()->get('sig');
         if(empty($info) || empty($sig)){
             return errReturn('数据参数错误!', '-9999');
         }
         $params = json_decode(decrypt($info['data'],substr($sig,0,16),substr($sig,-16,16)),true);
         if(empty($params)) {
           return errReturn('数据参数错误!', '-9999');
        }
        trace('service的version_number方法过滤的post参数：' . json_encode($info, JSON_UNESCAPED_UNICODE));
        //签名比对
        $result = validate_response($params, $sig);

        trace('Acceptservice的accept_list方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        
         
         if(!isset($params['version_code']) || !isset($params['version_name']) || !isset($params['customer_service_model']) || !isset($params['phone_model']) || !isset($params['user_set_up'])) {
             return errReturn('参数解析错误!', '-10000');
         }
    
         $tmp = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/version_ios_'.$params['user_set_up'].'.json');
         if(empty($tmp)) {
             return errReturn('版本检测失败!', '-10001');
         }
         $data = json_decode($tmp);
         if(empty($data)) {
             return errReturn('版本数据检测失败!', '-10002');
         }
         
         if($data->version_code > $params['version_code']) {
             return errReturn('版本更新!', '0',set_aes_param(['is_hidden'=>'0','status'=>$data->status,'version_code'=>$data->version_code,'version_name'=>$data->version_name,'user_set_up'=>$data->user_set_up,'version_url'=>$data->version_url]));
         }
         
          return errReturn('没有检查到版本!', '1',set_aes_param(array('is_hidden'=>'0','version_code'=>19)));
        
    }
}