<?php
namespace app\user\model;
use think\Model;
use think\Db;
use app\common\model\Common;
/**====================================================
 * 用户模型：表user
 * ====================================================
 */
class User extends Common
{
    //初始化分表
    protected function initialize() {
        $this->get_id_submemter();
    }
    
    protected static function getCreateTimeAttr($value,$data) {
        return date('Y-m-d H:i:s',$data['create_time']);
    }
    
    protected static function getLastLoginTimeAttr($value,$data) {
        return date('Y-m-d H:i:s',$data['last_login_time']);
    }

    /**根据条件查询一条数据 表user
     * @param   $where  array条件
     * @return  存在返回array 否则false
     * @author 江雄杰
     * @time 2016-10-06
     */
    public function getUser($user_id,$where,$field) {
        $result = $this->partition(['id'=>$user_id] , 'id' , $this->rule)
                ->where($where)->field($field)->find();
        if($result) {
            return $result->toArray();
        }
        return false;
    }
    
    /** 根据条件获取用户简易信息
     * @param $where    条件
     * @return  array
     * @author 江雄杰
     * @time 2016-10-06
     */
    public function getUserSample($where) {
        $result = $this->where($where)->field('id,username,status,flag')->find();
        if($result != false) {
            return $result;
        }
        return false;
    }
    
  
    

    public function getInfoByUserID($user_id,$field='*') {
        $result = $this->where(['id'=>$user_id])->field($field)->find();
        if($result) {
            return $result;
        }
        return false;
    }
    
    /*
     * 修改密码
     * @author huanghuasheng
     */
    public function modUser($user_id,$where,$data){
        return $this->partition(['id'=>$user_id] , 'id' , $this->rule)->where($where)->update($data);
    }
    
    /** 插入一条数据，返回自增id
     * @param   $redis_id   redis自增id
     * @param   $data       修改的数据
     * @author  江雄杰  
     * @time    2016-10-31
     */
    public function insertUserGetId($redis_id , $data) {
        return $this->partition(['id'=>$redis_id] , 'id' , $this->rule)->insert($data);
    }

    /*注册后向user_belong表中插入一条记录,$userid用户ID，$rid 推荐人ID*/
    public function insert_user_belong($userid,$rid){
        $rdata = Db::query("select * from sxh_user_belong where userid = ".intval($rid)." limit 1");
        if(empty($rdata)){
            $rdata[0]['full_url'] = ",".$userid.",";
        }else{
            $rdata[0]['full_url'] .= $userid.",";
        }
        $rdata[0]['userid']      = $userid;
        $rdata[0]['create_time'] = time();
        $rdata[0]['nickname']    = '普通用户';
        $rdata[0]['f_year']      = date("Y");
        $rdata[0]['f_month']     = date("m");
        $rdata[0]['f_day']       = date("d");
        $rdata[0]['t']           = date("Ym");
        $rdata[0]['self_num']    = 0;
        $rdata[0]['group_num']   = 0;
        return Db::name("user_belong")->insert($rdata[0]);
    }
}