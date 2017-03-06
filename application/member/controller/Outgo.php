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

use app\common\controller\Base;

class Outgo extends Base {
    
    public function get_log_list() {
        
        //写入日志
        trace('outgo中get_log_list方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        $service = \think\Loader::model('Outgo', 'service');

        return $service->get_log_list($this->data,$this->sig);
    }
}