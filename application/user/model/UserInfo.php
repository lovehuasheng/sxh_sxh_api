<?php
namespace app\user\model;
use think\Model;
use think\Db;
use app\common\model\Common;

/**====================================================
 * 用户信息Model层
 * ====================================================
 */
class UserInfo extends Common
{
    //初始化分表
    protected function initialize() {
        $this->get_id_submemter();
    }
    /** 插入一条数据，返回自增id
     * @param   $redis_id   redis自增id
     * @param   $data       修改的数据
     * @author  江雄杰  
     * @time    2016-10-31
     */
    public function insertUserinfoGetId($redis_id , $data) {
        return $this->partition(['id'=>$redis_id] , 'id' , $this->rule)->insert($data);
    }
    /**
     * 获取用户信息
     * @param type $uid
     * @param type $field
     * @return type
     * @Author 童自扬
     * @time  2016-10-04 
     */
    public function getInfo($where,$uid,$field='*') {
        $list =  $this->partition(['id'=>$uid] , 'id' , $this->rule)
                ->where($where)->field($field)->find();
        if(!empty($list)) {
            return $list->toArray();
        }
        
        return false;
    }
    
    /**
     * 获取用户信息列表
     * @param type $map
     * @param type $field
     * @return type
     * @Author 童自扬
     * @time  2016-10-04
     */
    public function getDataListByMap($map,$field='*') {
        $list =  db('UserInfo')->where($map)->field($field)->select();
        if(!empty($list)) {
            $info = db('UserLogin')->where($map)->field('status,verify')->select();
            return array_merge_recursive($list,$info);
        }
        
        return null;
    }
    
    
    
    /**根据用户ID查询用户信息
     * @param   $userid 条件用户ID
     * @parem   return  array
     * @author  江雄杰
     */
    /*
    public function getUserInfoById($userid) {
        $result = $this->where(['user_id'=>$userid])->find();
        if($result != false) {
            return $result;
        }
        return false;
    }*/
    public function getUserInfoById($userid) {
        $result = $this->where(['UserID'=>$userid])->find();
        if($result != false) {
            return $result;
        }
        return false;
    }
    
    /**根据条件array查询用户信息 表userinfo
     * @param   $where  条件array or str
     * @return  存在返回array 否则false
     * @author  江雄杰
     */
    public function getUserInfo($where,$field='*') {
        $result = $this->where($where)->field($field)->find();
        //var_dump($result);
        if($result != false) {
            return $result;
        }
        return false;
    }
    
    /** 查询手机是否已被使用
     * @param   phone  条件array or str
     * @return  存在返回array 否则false
     * @author  江雄杰
     */
    public function checkPhoneExists($where) {
        $result = $this->where($where)->count();
        //var_dump($result);
        if($result > 0) {
            return true;
        }
        return false;
    }
    /**
     * 更新userinfo表的数据
     * @param array $where 更新条件
     * @param array $data 要更新的字段
     * @author huanghuasheng
     */
    public function updateUserInfo($where,$data){
        return $this->partition(['id'=>$where['user_id']] , 'id' , $this->rule)->where($where)->update($data);
    }
    /**
     * 查找userinfo表的数据总数
     * @param array $where 更新条件
     * @author huanghuasheng
     */
    public function userinfoCount($where) {
        return db('userinfo')->where($where)->count();
    }
    /**
     * 查找userinfo表的数据和user数据
     * @param array $where 更新条件
     * @author huanghuasheng
     */
    public function userAndUserinfo($where,$table,$jwhere,$field="*") {
        $res = db('userinfo')->join($table,$jwhere)->where($where)->field($field)->select();
        return $res;
    }
    public function findUserInfo($user_id,$where,$field='*') {
        $result = $this->partition(['id'=>$user_id] , 'id' , $this->rule)->where($where)->field($field)->find();
        if($result != false) {
            return $result->toArray();
        }
        return false;
    }
    public function selectUserInfo($where,$field='*') {
        $result = db('userinfo')->where($where)->field($field)->select();
        return $result;
    }
}
