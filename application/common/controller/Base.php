<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace app\common\controller;
use \think\Request;

class Base {

    protected $user_id;
    protected $data;
    protected $sig;

    public function __construct() {
        //升级提示
//        $req = time();
//        $stop = 1484661599;
//        if($req>$stop){
//            echo json_encode(errReturn('系统升级调试中，请暂停登录', -1), JSON_UNESCAPED_UNICODE);
//            die;
//        }
        $maintain = date('H', $_SERVER['REQUEST_TIME']);
        if (intval($maintain) >= 2 && $maintain <= 5) {
            echo json_encode(errReturn('维护中……', -1), JSON_UNESCAPED_UNICODE);
            die;
        }
        //经过过滤
        //echo 123;die();
        $sig = Request::instance()->get('sig');
        $params = Request::instance()->post();
        //print_R($params);die();
        //验证参数
        if (empty($params) || empty($sig) || !isset($params['data'])) {
            echo json_encode(errReturn('参数错误', -11), JSON_UNESCAPED_UNICODE);
            die();
        }

        $this->data = json_decode(decrypt($params['data'], substr($sig, 0, 16), substr($sig, -16, 16)), true);
        $this->sig = $sig;
        //升级提示
        /*$req = time();
        $stop = 1484661599;
        $arr = array(224796,122786,1,514545,555093);
        if($req>$stop && !in_array($this->data['user_id'],$arr)){
            echo json_encode(errReturn('系统升级调试中，请暂停登录', -1), JSON_UNESCAPED_UNICODE);
            die;
        }*/
        if (empty($this->data)) {
            send_http_status(403);
            echo json_encode(errReturn('数据错误！', -1), JSON_UNESCAPED_UNICODE);
            die;
        }

        if (!isset($this->data['user_id']) || empty($this->data['user_id'])) {
            //拦截
            send_http_status(403);
            echo json_encode(errReturn('用户错误！', -1), JSON_UNESCAPED_UNICODE);
            die;
        }
    }
    
    
    

}
