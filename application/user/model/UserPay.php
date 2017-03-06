<?php
namespace app\user\model;
use think\Model;
use think\Db;

// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 童自扬 <2421886569@qq.com> 
// +----------------------------------------------------------------------
// | Function: 上传打款截图模型
// +----------------------------------------------------------------------
class UserPay extends Model
{
    
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'CreateTime';
    protected $updateTime = 'UpdateTime';
    public static $err;
    /**
     * 取图片路径
     * @param type $value
     * @param type $data
     * @return type
     * @Author 童自扬
     * @time  2016-10-05
     */
    public static function getImagesAttr($value,$data) {
        if(!empty($data['images'])) {
            return getQiNiuPic($data['images']);
        }
        
        return $data['images'];
    }
    
    
    /**
     * 根据匹配表ID找打款记录
     * @param type $pid
     * @param type $field
     * @return type
     * @Author 童自扬
     * @time  2016-10-05
     */
    public function getUserPayOneDataByPid($pid,$field='*') {
        $map['PID'] = $pid;
        
        $info =  $this->where($map)->field($field)->find();
        if(!empty($info)) {
            return $info->toArray();
        }
        
        return [];
    }
    
    /**
     * 上传打款截图
     * @param type $data
     * @return type
     */
    public function setUserPayImage($data) {
        $a = \think\Loader::model('common/UserMatchhelp','model');
        $table_matchhelp_now  = $a->getPartitionTableName(['quarter'=>$data['create_time']],'quarter',['type' => 'quarter','expr' => 3]);
        $model = Db::query("select * from ".$table_matchhelp_now." where id = ".$data['id']);
        
        if(count($model) == 0){
            $this->err = '找不到打款记录';
            return false;
        }
        $m = $model[0];
        $b = \think\Loader::model('common/UserAccepthelp','model');
        $table_accepthelp_now  = $b->getPartitionTableName(['quarter'=>$m['accepthelp_create_time']],'quarter',['type' => 'quarter','expr' => 3]);
         
        $c = \think\Loader::model('common/UserProvide','model');
        $table_provide_now  = $c->getPartitionTableName(['quarter'=>$m['provide_create_time']],'quarter',['type' => 'quarter','expr' => 3]);
        
        if($model[0]['status'] == 0){
            $this->err = '订单未审核，不能打款了！';
            return false; 
        }
        if($m['delayed_time_status'] == 1 && time()>$m['expiration_create_time']){
            $this->err = '订单已打款超时，不能打款了！';
            return false;
        }
        if($m['delayed_time_status'] == 0 && time()>($m['audit_time']+config('matchhelp_out_time')) ){
            $this->err = '订单已打款超时，不能打款了！';
            return false;
        }
        $this->startTrans();
        //更新匹配表的状态和打款时间
        $sql_match = "update ".$table_matchhelp_now." set status = 2,pay_time = ".$_SERVER['REQUEST_TIME'].",pay_image = '".$data['images']."' where id = ".$data['id'];
        $update_match = Db::execute($sql_match);
        if(!$update_match){
            $this->err = '更新匹配信息失败！';
            $this->rollback();
            return false;
        }
        /*更新接受资助表中的状态*/
        $sql_accept = "update ".$table_accepthelp_now." set status = 2,pay_num = pay_num + 1  where id = ".$m['pid'];
        $update_accept = Db::execute($sql_accept);
        if(!$update_accept){
            $this->err = '更新打款信息失败！';
            $this->rollback();
            return false;
        }
        /*更新挂单表中的状态*/
        $sql_provide = "update ".$table_provide_now." set status = 2 ,pay_num = pay_num + 1 where id = ".$m['other_id'];
        $update_provide = Db::execute($sql_provide);
        if(!$update_provide){
            $this->err = '更新打款信息失败！';
            $this->rollback();
            return false;
        }
        $this->commit();
        $redis = \org\RedisLib::get_instance();
        $redis->delMatchDetail('provide',$model[0]['other_user_id'],$model[0]['other_id']);/*黄华盛上传打款截图需要清除的缓存*/
        $redis->delMatchDetail('accepthelp',$model[0]['user_id'],$model[0]['pid']);/**/
        return true;
    }
}