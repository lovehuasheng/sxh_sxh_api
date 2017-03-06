<?php
// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 杰杰
// +----------------------------------------------------------------------
// | Function: UserMatchhelp 匹配 模型
// +----------------------------------------------------------------------

namespace app\common\model;
use app\common\model\Common;
use think\Model;
use think\Loader;

class UserMatchhelp extends Common 
{
    protected function initialize() {
        $this->get_month_submeter();
    }
    public function getPayImage($where , $field='*',$time) {
        return $this->partition(['quarter'=>$time]  , 'quarter' , $this->rule)
            ->where($where)->field($field)->order("create_time DESC")->find()->toArray();
    }
    
    
}
