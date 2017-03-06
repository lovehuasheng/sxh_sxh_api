<?php
// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 杰杰
// +----------------------------------------------------------------------
// | Function: 帐户收入明细 模型
// +----------------------------------------------------------------------

namespace app\common\model;
use app\common\model\Common;
use think\Model;

class UserIncome extends Common {
    
    protected function initialize() {
        $this->get_month_submeter();
      
    }
     
    
    /** 插入收入明细数据
     * @param   $where  条件
     * @param   $data   插入的数据信息
     * @return  int     
     * @author  江雄杰  
     * @time    2016-10-15
     */
    public function insertIncome($data) {
        return $this->partition($this->info_date , $this->info_field , $this->rule)
                ->insert($data);
    }
    
    
    /***
     */
    public function saveData($data) {
        return $this->insertIncome($data);
    }
    
    
    /** 获取用户收入明细list
     * @param   $where  条件
     * @param   $field  字段
     * @param   $order  排序
     * @param   $limit  每页显示条数
     * @param   $page   第几页
     * @return  
     * @author  江雄杰  
     * @time    2016-10-21
     */
    public function getUserIncomeList($where , $field='*' , $order='create_time DESC' , $limit=20 , $page=1) {
        $result = $this->partition($this->info_date , $this->info_field , $this->rule)
                ->where($where)
                ->field($field)
                ->order($order)
                ->limit($limit)
                ->page($page)
                ->select();
        if(!empty($result)) {
            for($i=0,$c=count($result) ; $i<$c ; $i++) {
                if(!empty($result[$i]->info_field)) {
                    unset($result[$i]->info_field);
                }
                if(!empty($result[$i]->rule)) {
                    unset($result[$i]->rule);
                }
                if(!empty($result[$i]->info_date)) {
                    unset($result[$i]->info_date);
                }
                $result[$i]['type_text']    = $result[$i]->type_text;
            }
            return $result;
        }
        return array();
    }
    
    
    
    
}

