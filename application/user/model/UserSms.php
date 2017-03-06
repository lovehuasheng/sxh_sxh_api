<?php
/* 
 * 关系模型 表user_relation
 */
namespace app\user\model;
use think\Model;

class UserSms extends Model {
    
    /** 保存短信记录
     * @param   $data   array 保存的数据
     * @return  bool    成功返回true
     * @author  江雄杰
     * @time    2016-10-06
     */
    public function saveSmsInfo($data) {
        $this->data($data);
        $result = $this->save();
        if($result !== false) {
            return true;
        }
        return false;
    }
    /**
     * 注册码信息保存
     * @author huanghuasheng
     */
    public function insertSmsinfo($data){
        return $this->insert($data);
    }
    /*
     * 验证码更新，即失效处理
     * @author huanghuasheng
     */
    public function updateSms($where,$data){
        return $this->where($where)->update($data);
    }
    /**
     * 查找数据
     * @author huanghuasheng
     */
    public function findSms($where,$field='*',$order="ID desc"){
        $res = $this->where($where)->field($field)->order($order)->find();
        if($res){
            return $res->toArray();
        }
        return $res;
    }
}

