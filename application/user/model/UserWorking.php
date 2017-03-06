<?php
namespace app\user\model;
use think\Model;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of UserWorking
 *
 * @author shanhubao
 */
class UserWorking extends Model{
    public function findUserWorking($where,$field,$order='ID desc'){
        return db('user_working')->where($where)->field($field)->order($order)->find();
    }
}
