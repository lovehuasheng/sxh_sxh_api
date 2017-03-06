<?php
// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 童自扬 <2421886569@qq.com> 
// +----------------------------------------------------------------------
// | Function: 接受资助控制器
// +----------------------------------------------------------------------

namespace app\member\controller;
use think\Request;
use app\common\controller\Base;

class Wallet extends Base {
    /**
     * 查看钱包
     * @return type
     */
    public function get_user_wallet() {
        //写入日志
        //trace('Wallet中get_user_wallet方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));
        //$service = \think\Loader::model('Wallet', 'service');
        //return $service->get_user_wallet($this->data,$this->sig);
        return \app\member\service\Wallet::get_user_wallet($this->data,$this->sig);
    }
    
    /**
     * 转让善种子
     * @return type
     */
    public function attorn_activate_currency() {
        //写入日志
        //trace('Wallet中attorn_activate_currency方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));
        $service = \think\Loader::model('Wallet', 'service');
        return $service->attorn_activate_currency($this->data,$this->sig);
    }
    /**
     * 接受资助接口
     */
    public function save_accept(){
        $ip = Request::instance()->ip();
        return \app\member\service\Wallet::save_accept_action($this->data,$this->sig,$ip);
    }
    /*合并社区钱包钱包*/
    public function together_wallet(){
         //写入日志
        //trace('Wallet中save_accept方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));
        //$service = \think\Loader::model('Wallet', 'service');
        //$ip = Request::instance()->ip();
        //return $service->together_wallet($this->data,$this->sig);
        return \app\member\service\Wallet::together_wallet($this->data,$this->sig);
    }
}