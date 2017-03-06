<?php
// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 杰杰
// +----------------------------------------------------------------------
// | Function: 用户模型
// +----------------------------------------------------------------------

namespace app\common\model;
use app\common\model\Common;
use think\Model;

class User extends Common
{
    //初始化 rule分表规则
    protected function initialize() {
        $this->get_id_submemter();
    }
    public function getUser($where , $field , $id) {
        $result = $this->partition(['id'=>$id] , 'id' , $this->rule)->where($where)->field($field)->find();
        if($result) {
            return $result;
        }
        return false;
    }
    
    public function getUserByID($userid){
    	return $this->getUser(["id"=>$userid], "*" , $userid);
    }
 
    public function updateUserById($id , $data=[]) {
        return $this->partition(['id'=>$id] , 'id' , $this->rule)
                ->where(['id'=>$id])->update($data);
    }
   
    public function insertUserGetId($redis_id , $data) {
        return $this->partition(['id'=>$redis_id] , 'id' , $this->rule)->insertGetId($data);
    }
    
}
