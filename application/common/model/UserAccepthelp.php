<?php
// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 杰杰
// +----------------------------------------------------------------------
// | Function: 接受资助业务模型
// +----------------------------------------------------------------------

namespace app\common\model;
use think\Model;
use app\common\model\Common;

class UserAccepthelp extends Common {
    
    protected function initialize() {
        $this->get_month_submeter();
    }
    public function insertAccepthelp($data) {
        return $this->partition($this->info_date , $this->info_field , $this->rule)
                ->insert($data);
    }
}
