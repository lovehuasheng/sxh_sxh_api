<?php
namespace app\common\model;
use app\common\model\Common;
use think\Model;

class UserProvide extends Common 
{
    
    protected function initialize() {
        $this->get_month_submeter();
      
    }
    public function insertProvide($data) {
        return $this->partition($this->info_date , $this->info_field , $this->rule)->insert($data);
    }
    
    public function getProvideData($where , $field='*') {
        return $this->partition($this->info_date , $this->info_field , $this->rule)
            ->where($where)->field($field)->order("create_time DESC")->find();
    }
    public function updateProvideById($id,$data){
    	return $this->partition($this->info_date , $this->info_field , $this->rule)
    	     ->where(["id"=>$id])->update($data);
    }
    public function updateProvideByData($where,$data,$time){
      
    	return  $this->partition(['quarter'=>$time] , $this->info_field , $this->rule)
    	   ->where($where)->update($data);
    }
}
