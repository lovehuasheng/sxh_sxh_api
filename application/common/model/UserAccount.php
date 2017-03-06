<?php
// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 杰杰
// +----------------------------------------------------------------------
// | Function: 用户 功德钱包 模型
// +----------------------------------------------------------------------

namespace app\common\model;
use think\Model;

class UserAccount extends Model {
    protected $rule = [
        'type' => 'id',     // 分表方式,按id范围分表
        'expr'  => 1000000  // 每张表的记录数
    ];
    
    
    /** 获取用户account信息
     * @param       $where      条件
     * @param       $field      需要的字段
     * @return      object      
     * @author      江雄杰
     * @time        2016-10-13
     */
    public function getUserAccount($user_id , $where , $field='user_id') {
        $result = $this->partition(['id'=>$user_id ], 'id' , $this->rule)
                ->where($where)->field($field)->find();
        if($result != false) {
            return $result;
        }
        return false;
    }
    
    /** 插入account数据
     * @param   $where  条件
     * @param   $data   插入的数据信息
     * @return  int     
     * @author  江雄杰  
     * @time    2016-10-15
     */
    public function insertUserAccount($user_id , $data) {
        return $this->partition(['id'=>$user_id ], 'id' , $this->rule)
                ->insert($data);
    }
    
    
    public function updateUserAccount($user_id , $data) {
        return $this->partition(['id'=>$user_id ], 'id' , $this->rule)
                ->where(['user_id'=>$user_id])
                ->update($data);
    }
    
    
    /** 根据用户id 扣除币
     * @param   $user_id        用户id
     * @param   $currency_type  币种
     * @param   $money          数量
     * @return  bool            
     * @author  江雄杰  
     * @time    2016-11-02
     */
    public function updateDec($user_id , $currency_type , $money) {
        return $this->partition(['id'=>$user_id] , 'id' , $this->rule)
                ->where(['user_id'=>$user_id])->setDec($currency_type , $money);
    }
    
    
    /** 根据用户id 增加币
     * @param   $user_id        用户id
     * @param   $currency_type  币种
     * @param   $money          数量
     * @return  bool            
     * @author  江雄杰  
     * @time    2016-11-02
     */
    public function updateInc($user_id , $currency_type , $money) {
        return $this->partition(['id'=>$user_id] , 'id' , $this->rule)
                ->where(['user_id'=>$user_id])->setInc($currency_type , $money);
    }
    
    
    public function getUserAccountByUserId($user_id){
    	$field="activate_currency,company_wallet,company_manage_wallet,guadan_currency,invented_currency,wallet_currency,manage_wallet";
    	return $this
                ->getUserAccount($user_id , ['user_id'=>$user_id],$field);
    }
    
    /**
     * 修改出局钱包（jwf）
     * @param int $currency 加减的值
     * @param string|array $where 更新条件
     * @param int $type 0减  1加
     */
    public function updateWalletCurrency($user_id , $currency,$where,$type=0){
    	if($type==0){
    		return $this->partition(['id'=>$user_id] , 'id' , $this->rule)
                        ->where($where)->setDec("wallet_currency",$currency);
    	}else{
    		return $this->partition(['id'=>$user_id] , 'id' , $this->rule)
                        ->where($where)->setInc("wallet_currency",$currency);
    	}
    }
    
    /**
     * 修改善心币
     * @author jwf
     * @param unknown $currency
     * @param array|string $where
     * @param number $type 0减  1加
     */
    public function updateGuadanCurrency($user_id , $currency,$where,$type=0){
    	if($type==0){
    		return $this->partition(['id'=>$user_id] , 'id' , $this->rule)
                        ->where($where)->setDec("guadan_currency",$currency);
    	}else{
    		return $this->partition(['id'=>$user_id] , 'id' , $this->rule)
                        ->where($where)->setInc("guadan_currency",$currency);
    	}
    }
    
   /**
    * 修改个人用户的管理钱包(jwf)
    * @param int $money
    * @param array|string $where
    * @param number $type 0减  1加
    */
   public function updateManageWallet($user_id , $money,$where,$type=0){
   	  if($type == 0){
   	  	return $this->partition(['id'=>$user_id] , 'id' , $this->rule)
                        ->where($where)->setDec("manage_wallet",$money);
   	  }else{
   	  	return $this->partition(['id'=>$user_id] , 'id' , $this->rule)
                        ->where($where)->setInc("manage_wallet",$money);
   	  }
   }
   
   /**
    * 修改善金币（jwf）
    * @param int $currency 加减的值
    * @param string|array $where 更新条件
    * @param int $type 0减  1加
    */
   public function updateInventedCurrency($user_id , $currency,$where,$type=0){
   	if($type==0){
   		return $this->partition(['id'=>$user_id] , 'id' , $this->rule)
                        ->where($where)->setDec("invented_currency",$currency);
   	}else{
   		return $this->partition(['id'=>$user_id] , 'id' , $this->rule)
                        ->where($where)->setInc("invented_currency",$currency);
   	}
   }
   
   public function updateCompanyWallet($user_id,$currency,$where,$type=0){
	   	if($type==0){
	   		return $this->partition(['id'=>$user_id] , 'id' , $this->rule)
	   		->where($where)->setDec("company_wallet",$currency);
	   	}else{
	   		return $this->partition(['id'=>$user_id] , 'id' , $this->rule)
	   		->where($where)->setInc("company_wallet",$currency);
	   	}
   }
    
    
    
    
    
    
    
}

