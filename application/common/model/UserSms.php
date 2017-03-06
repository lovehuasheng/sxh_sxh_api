<?php
// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 杰杰
// +----------------------------------------------------------------------
// | Function: sms手机短信 模型
// +----------------------------------------------------------------------

namespace app\common\model;
use think\Model;

class UserSms extends Model {
    
    public function initialize() {
        //分表规则
        $this->rule = [
            'type' => 'mod',     //按照id的模数分表
            'num'  => '10',  //
        ];
        $this->info_data = [
            'user_id' => 0,
        ];
        $this->info_field = 'user_id';
    }
    
    
    /** 插入一条数据
     * @param   $data   
     * @return  
     * @author  江雄杰
     * @author  2016-11-03
     */
    public function insertSms($data , $user_id = 0) {
        return $this->partition(['user_id'=>$user_id] , $this->info_field , $this->rule)
                ->insert($data);
    }
     
}

