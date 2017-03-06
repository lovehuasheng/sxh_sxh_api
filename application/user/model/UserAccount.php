<?php
/* ============================================================
 * 用户模型：表user_account
 * ============================================================
 */
namespace app\user\model;
use think\Model;
use app\common\model\Common;
use think\Db;
class UserAccount extends Common {
    
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
    public function insertAccountGetId($redis_id , $data) {
        return $this->partition(['id'=>$redis_id] , 'id' , $this->rule)->insert($data);
    }
    public function getUserAccount($userid,$field='*') {
        $map['user_id'] = $userid;
        $res = $this->partition(['id'=>$userid] , 'id' , $this->rule)->where($map)->field($field)->find();
        if($res){
            return $res->toArray();
        }
        return $res;
    }
    
    public function addUserAccountMoney($user_id,$field,$money=1) {
        return $this->partition(['id'=>$user_id] , 'id' , $this->rule)
                ->where(['user_id'=>$user_id])->setInc($field,$money);
    }
    
    
    public function setUserAccountMoney($user_id,$data) {
        return $this->save($data,['UserID'=>$user_id]);
        
    }
    /*
     * 查找账户明细
     * @author：huanghuasheng
     */
    public function accountInfo($user_id,$where,$field="*"){
        $res = $this->partition(['id'=>$user_id] , 'id' , $this->rule)->where($where)->field($field)->find();
        if($res){
            return $res->toArray();
        }
        return $res;
    }
    /**
     * 减少账户金额
     */
    public function decUserAccountMoney($user_id,$where,$field,$money=1) {
        return $this->partition(['id'=>$user_id] , 'id' , $this->rule)
                ->where($where)->setDec($field,$money);
    }
    /**
     * 更新数据
     */
    public function updateAccount($user_id,$where,$data){
        return $this->partition(['id'=>$user_id] , 'id' , $this->rule)
                ->where($where)->update($data);
    }
}

