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

namespace app\user\controller;

use app\common\controller\Base;

class Accept extends Base {

    /**
     * 提供资助列表
     * @return type
     * @Author 童自扬
     * @time  2016-10-08
     */
    public function accept_list() {

        //写入日志
        trace('controller中accept_list方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        $service = \think\Loader::model('Accept', 'service');

        return $service->accept_list($this->data,$this->sig);
    }

    /**
     * 取消提供资助
     * @return type
     *  @Author 童自扬
     * @time  2016-10-03 
     */
    public function accept_destroy() {
        //写入日志
        trace('controller中accept_destroy方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        $service = \think\Loader::model('Accept', 'service');

        return $service->accept_destroy($this->data,$this->sig);
    }

    /**
     * 提供资助的匹配详情页
     * @return type
     * @Author 童自扬
     * @time  2016-10-04
     */
    public function get_accept_detail() {

        //写入日志
        trace('controller中get_provide_detail方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        $service = \think\Loader::model('Accept', 'service');

        return $service->accept_detail($this->data,$this->sig);
    }

    /**
     * 进入打款人页面
     * @Author 童自扬
     * @time  2016-10-04
     * @return type
     */
    public function get_accept_person_msg() {

        //写入日志
        trace('controller中get_pay_person_msg方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        $service = \think\Loader::model('Accept', 'service');

        return $service->accept_person_msg($this->data,$this->sig);
    }

    /**
     * 延时打款
     * @return type
     * @Author 童自扬
     * @time  2016-10-09
     */
    public function accept_delayed() {
        //写入日志
        trace('controller中get_pay_person_msg方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        $service = \think\Loader::model('Accept', 'service');

        return $service->accept_delayed($this->data,$this->sig);
    }

    /**
     * 收款
     * @return type
     * @Author 童自扬
     * @time  2016-10-09
     */
    public function accept_collections() {
        return \app\member\service\Accept::accept_collections($this->data,$this->sig);
    }

   
}
