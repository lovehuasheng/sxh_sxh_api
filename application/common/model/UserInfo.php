<?php
namespace app\common\model;
use app\common\model\Common;
use think\Model;
use think\Loader;

class UserInfo extends Common
{
    //初始化分表
    protected function initialize() {
        $this->get_id_submemter();
    }
    public function ter(){
        echo 'l';
    }
    public function getUserInfo($where , $field='*' , $user_id , $order="create_time DESC") {
        $list =  $this->partition(['id'=>$user_id] , 'id' , $this->rule)
                ->where($where)->field($field)->order($order)->find()->toArray();
        if(count($list)>0) {
            return $list;
        }
        return array();
    }
}
