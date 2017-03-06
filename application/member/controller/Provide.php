<?php
/*
 * 提供资助
 */

namespace app\member\controller;
use think\Request;
use app\common\controller\Base;

class Provide extends Base {
    /*
     * 提供资助功能
     */
    public function provide_data(){
        //trace('【app】Provide中provide_data方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));
        //$service = \think\Loader::model('Provide', 'service');
        $ip = Request::instance()->ip();
        //return $service->save_provide_action($this->data,$this->sig,$ip);
        return \app\member\service\Provide::save_provide_action($this->data,$this->sig,$ip);
        
    }
    /*
     * 提供资助消耗预览
     */
    public function provide_sel(){
        //trace('【app】Provide中provide_sel方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));
        //$service = \think\Loader::model('Provide', 'service');
        //return $service->sel_provide_action($this->data,$this->sig);
        return \app\member\service\Provide::sel_provide_action($this->data,$this->sig);
    }
}

?>