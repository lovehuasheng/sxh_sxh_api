<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\user\model;
use think\Model;
use think\Db;
use app\common\model\Common;
/**
 * Description of UserOutgo
 *
 * @author shanhubao
 */
class UserOutgo extends Common{
    //初始化
    protected function initialize() {
        $this->get_month_submeter();
      
    }
    /*
     * 插入数据
     */
    public function outgoInsert($data){
        return $this->partition($this->info_date,$this->info_field,$this->rule)->insert($data);
    }
    /*
     * 查找数据
     */
    public function findOutgo($where,$field='*'){
        $res = $this->where($where)->field($field)->find();
        if($res){
            return $res->toArray();
        }
        return false;
    }
    /*
     * 查找数据
     */
    public function outgoSum($where,$field){
        $res = db('user_outgo')->where($where)->field($field)->select();
        return $res;
    }
}
