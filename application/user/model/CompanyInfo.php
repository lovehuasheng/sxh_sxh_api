<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\user\model;
use think\Model;
use app\common\model\Common;
/**
 * Description of CompanyInfo
 *
 * @author shanhubao
 */
class CompanyInfo extends Common{
   //初始化分表
    protected function initialize() {
        $this->get_id_submemter();
    }
    /**
     * 获取用户信息
     * @param type $uid
     * @param type $field
     * @return type
     * @Author 童自扬
     * @time  2016-10-04 
     */
    public function getCompanyInfo($where,$field='*') {
        $list =  $this->where($where)->field($field)->find();
        if(!empty($list)) {
            return $list->toArray();
        }
        
        return null;
    }
}
