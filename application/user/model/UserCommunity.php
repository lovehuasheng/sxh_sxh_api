<?php
namespace app\user\model;
use think\Model;


class UserCommunity extends Model
{
    
    
    public function getInfoByCid($cid,$field) {
        $result = $this->where(['CID'=>$cid])->field($field)->find();
        if($result) {
              return $result->toArray();
        }
        return false;
    }
    
    
    
}