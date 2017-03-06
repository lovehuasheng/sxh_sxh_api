<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace app\user\model;
use think\Model;
use think\Db;
class UserOperation extends Model{
    protected $autoWriteTimestamp = 'datetime';
    // 定义时间戳字段名
    protected $createTime = 'CreateTime';
    protected $updateTime = 'UpdateTime';
    
    public function setLogByDestroy($usserid,$id) {
        
        $arr['Table'] = 'provide';
        $arr['TableID'] = $id;
        $arr['UserID'] = $usserid;
        $arr['Handlers'] = $usserid;
        $arr['Sign'] = 6;
        $arr['UpdateTime'] = $arr['CreateTime'] = date('Y-m-d H:i:s',time());
        
        
        return $this->save($arr);
    }
}