<?php

// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 童自扬 <2421886569@qq.com> 
// +----------------------------------------------------------------------
// | Function: 提供资助控制器
// +----------------------------------------------------------------------

namespace app\user\controller;
use \think\Request;

class Upload {
    public function __construct() {
        //升级提示
        $req = time();
        $stop = 1484661599;
        if($req>$stop){
            echo json_encode(errReturn('系统升级调试中，请暂停登录', -1), JSON_UNESCAPED_UNICODE);
            die;
        }
        $maintain = date('H', $_SERVER['REQUEST_TIME']);
        if (intval($maintain) >= 2 && $maintain <= 5) {
            echo json_encode(errReturn('维护中……', -1), JSON_UNESCAPED_UNICODE);
            die;
        }
    }
    public function picture() {
     //写入日志
        trace('service的upload_pay_picture方法过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));
        trace('service的upload_pay_picture方法过滤的file参数：' . json_encode($_FILES, JSON_UNESCAPED_UNICODE));
      
         $sig = input('sig');
         $data['ts'] = input('ts');
         $data['appkey'] = input('appkey');
         $data['id'] = input('id');
         $data['other_id'] = input('other_id');
         $data['pid'] = input('pid');
         $data['user_id'] = input('user_id');
         $data['create_time'] = input('create_time');
         if(!is_numeric($data['create_time'])){
             $data['create_time'] = strtotime($data['create_time']);
         }
       
         
         /*$data['id'] = Request::instance()->post('id');
        
         $data['other_id'] = Request::instance()->get('other_id');
         $data['pid'] = Request::instance()->get('pid');
         $data['user_id'] = Request::instance()->get('user_id');
         $data['create_time'] = input('create_time');*/
         
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
