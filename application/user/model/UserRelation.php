<?php
/* 
 * 关系模型 表user_relation
 */
namespace app\user\model;
use think\Model;
use think\Db;
use app\common\model\Common;
class UserRelation extends Common {
    //初始化分表
    protected function initialize() {
        $this->get_id_submemter();
    }
    
    public function insertAccount($data){
        return $this->insert($data);
    }
    /**根据用户id 查询用户的上级关系信息
     * @param   $userid     用户id
     * @return  array
     * @author 江雄杰
     * @time    2016-10-07
     */
    public function getRelationById($userid) {
        $result = $this->hasOne('Userinfo')->where(array('UserID'=>$userid))->field('UserID,RefereeID,Referee')->find();
        if($result != false) {
            return $result;
        }
        return false;
    }
    
    /**根据用户id 获取用户所有父级 表relation
     * @param   $userid     用户id
     * @return  array      
     * @author 江雄杰
     * @time    2016-10-07
     */
    public function getRelationAllById($userid , $array=array()) {
        static $array;
        $result = $this->getRelationById($userid);
        if($result != false) {
            if($result->RefereeID > 0) {
                $array[] = $result->RefereeID;
                $this->getRelationAllById($result->RefereeID ,  $array);
            }
            //var_dump($arr);
            return $array;
        }
        return false;
    }
    
    
    
    public function getUserRelationByID($map,$field) {
        $result = $this->where($map)->field($field)->find();
        if($result) {
              return $result->toArray();
        }
        return false;
    }
}

