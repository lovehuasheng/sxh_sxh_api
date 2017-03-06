<?php
// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 童自扬 <2421886569@qq.com> 
// +----------------------------------------------------------------------
// | Function: 融云接口
// +----------------------------------------------------------------------


namespace app\user\logic;
use think\Model;


class Cloud extends Model
{
    
    /**
     * 生成签名
     * @param type $secret
     * @param type $rand
     * @param type $now
     * @return type
     */
    public static function signature($secret,$rand,$now) {
       
        $appSecret = $secret; // 开发者平台分配的 App Secret。
        $nonce = $rand; // 获取随机数。
        $timestamp = $now; // 获取时间戳。
        $signature = sha1($appSecret.$nonce.$timestamp);
        return $signature;
    }
    
    
    /**
     * 获取token
     * @param type $user_id
     * @param type $name
     * @param type $header_img
     * @return boolean
     */
    public function get_user_token($user_id,$name,$header_img='') {
       
       $url = 'http://api.cn.ronghub.com/user/getToken.json';
       $arr['userId'] = $user_id;
       $arr['name'] = $name;
       $arr['portraitUri'] = $header_img;
       
       $rand = get_rand_num(10);
       $timestamp = $_SERVER['REQUEST_TIME'];
       $sign = $this->signature(config('cloud_app_secret'),$rand,$timestamp);
       $header = array();
       $header[] = 'charset: utf-8';
       $header[] = 'App-Key:'.  config('cloud_app_key');
       $header[] = 'Nonce:'.$rand;
       $header[] = 'Timestamp:'.$timestamp;
       $header[] = 'Signature:'.$sign;
       
       $result = api_http_request($url,$arr,$header);
       if($result) {
           $data = json_decode($result);
           if($data->code == '200') {
               return $data->token;
           }
           return false;
       }
       
       return false;
       
    }
    
    
}