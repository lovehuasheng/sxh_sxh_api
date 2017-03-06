<?php

/*
 * 扫码登录接口
 */

namespace app\member\controller;
use think\Request;
use app\common\controller\Base;

class Scan extends Base {
    /*
     * 扫码登录接口
     */
    public function scan_code(){
        trace('Scan中scan_code方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));
        $service = \think\Loader::model('Scan', 'service');
        return $service->scan_code_action($this->data,$this->sig);
    }
}


