<?php

/**
 * 用户注册登录页面
 * @author huanghuasheng
 */


namespace app\user\controller;

use app\common\controller\Base;
use \think\Request;
class User {

    public function __construct() {
        //升级提示
        $sig    = Request::instance()->get('sig');
        $params = Request::instance()->post();
        //验证参数
        if (empty($params) || empty($sig) || !isset($params['data'])) {
            return errReturn('参数错误',-11);
        } 
        
        $data = json_decode(decrypt($params['data'],substr($sig,0,16),substr($sig,-16,16)),true);
        $req = time();
        $stop = 1484661599;
        $arr = array('童自扬','137777777777','huangjie','jjhuang');
        if($req>$stop && !in_array($data['username'], $arr)){
            echo json_encode(errReturn('系统升级调试中，请暂停登录', -1), JSON_UNESCAPED_UNICODE);
            die;
        }
        $maintain = date('H', $_SERVER['REQUEST_TIME']);
        if (intval($maintain) >= 2 && $maintain <= 5) {
            echo json_encode(errReturn('维护中……', -1), JSON_UNESCAPED_UNICODE);
            die;
        }
    }
    
    public function register() {

        //写入日志
        trace('controller接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        $service = \think\Loader::model('User', 'service');

        return $service->register();
    }

    public function login() {
        //日志

        $service = \think\Loader::model('User', 'service');
        return $service->login();
    }

}
