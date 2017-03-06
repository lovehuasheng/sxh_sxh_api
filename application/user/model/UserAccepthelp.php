<?php
// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 童自扬 <2421886569@qq.com> 
// +----------------------------------------------------------------------
// | Function: 接受资助业务模型
// +----------------------------------------------------------------------


namespace app\user\model;

use think\Model;
use think\Db;

class UserAccepthelp extends Model {
    
    
    /**
     * 转换status为字符串
     * @param type $value
     * @param type $data
     * @return string
     * @Author 童自扬
     * @time  2016-10-04
     */
    public static function getSignAttr($value, $data) {
         if($data['status'] == 1 && $data['sign'] == 1 ) {
            return 3;
        }else if($data['status'] = 1 && $data['sign'] = 0) {
            return 2;
        }
        else {
            return $data['matching'];
        }
    }
    
    /**
     * 匹配状态
     * @param type $value
     * @param type $data
     * @return string
     * @Author 童自扬
     * @time  2016-10-04
     */
    public function getMatching($data) {
        $matching = [0 => '完全匹配', 1 => '部分匹配', 2 => '匹配异常', 3 => '未匹配',4=>'已完成'];
        if($data['matching'] == 1) {
            if($data['money'] == $data['used']) {
                if($data['status'] == 1 && $data['sign'] == 1) {
                    return $matching[4];
                }else {
                    return $matching[0];
                }
            }else if($data['used'] > 0 && $data['money'] > $data['used']) {
                
                return $matching[1];
            }
            else if($data['money'] < $data['used'])  {
               
                return $matching[2];
            }else {
                return $matching[3];
            }
        }else {
            return $matching[3];
        }
    }
    
    /**
     * 社区名字
     * @param type $value
     * @param type $data
     * @return string
     * @Author 童自扬
     * @time  2016-10-04
     */
    public static function getCNameAttr($value, $data) {
        
          if($data['sum'] <= 5400) {
              return '贫穷社区';
          }
          else if($data['sum'] > 5400 && $data['sum'] <= 51000) {
              return '小康社区';
          }
          else if($data['sum'] > 51000 && $data['sum'] <= 480000) {
              return '富人社区';
          }
           else if($data['sum'] > 480000 && $data['sum'] <= 15500000) {
              return '德善社区';
          }
    }

    
    /**
     * 转换type_id为字符串
     * @param type $value
     * @param type $data
     * @return string
     * @Author 童自扬
     * @time  2016-10-04
     */
    public static function getTypeIdAttr($value, $data) {
        $typeId = ['接受资助', '接单钱包'];
        return $typeId[$data['type_id']];
    }
    
   
    
    public function getAcceptData($map,$field='*') {
        return $this->where($map)->field($field)->find();
    }
    
    
    /**
     * 
     * 更新数据
     * @param type $map
     * @param type $data
     * @return type
     */
    public function setAcceptData($map,$data) {
        return $this->save($data,$map);
    }
      
    /**
     * 获取分页数据
     * @param type $map
     * @param type $field
     * @return type
     * 2016-10-17改
     */
    public function getAcceptListByStatus($map, $page = 1, $r = 20, $field = '*', $order = 'id desc') {
        $table = getTable(time());
        if (isset($map['status']) && $map['status'] == 1) {
            //已匹配
//            $sql = "select $field from (select $field from sxh_user_accepthelp_".$table[0]." where user_id=".$map['user_id']." and flag=0 and status in (1,2) union select $field from sxh_user_accepthelp_"
//                    .$table[1]." where user_id=".$map['user_id']." and flag=0 and status in (1,2)) as tt order by $order";
//            $rlist = Db::query($sql);
//            $list = array();
//            $temp = array();
//            if(!empty($rlist)){
//                $ids = '';
//                foreach ($rlist as $v){
//                    $ids .= ','.$v['id'];
//                    $temp[$v['id']] = $v;
//                }
//                $ids = trim($ids,',');
//                $sql = "select id,pid from (select id,pid from sxh_user_matchhelp_".$table[0]." where pid in (".$ids.") and flag=0 and status in (1,2,3) union select id,pid from sxh_user_matchhelp_"
//                        .$table[1]." where pid in (".$ids.") and flag=0 and status in (1,2,3)) as tt order by $order";
//                $mlist = Db::query($sql);
//                if(!empty($mlist)){
//                    foreach($mlist as $v){
//                        if(isset($temp[$v['pid']])){
//                            $list[] = $temp[$v['pid']];
//                            unset($temp[$v['pid']]);
//                        }
//                    }
//                }
//            }
            $sql = "select $field from sxh_user_accepthelp_".$table[0]." where user_id=".$map['user_id']." and flag=0 and status in (1,2) order by $order";
            $list1 = Db::query($sql);
            if(!$list1 || empty($list1)){
                $list1 = array();
            }
            $sql = "select $field from sxh_user_accepthelp_".$table[1]." where user_id=".$map['user_id']." and flag=0 and status in (1,2) order by $order";
            $list2 = Db::query($sql);
            if(!$list2 || empty($list2)){
                $list2 = array();
            }
            $list_res = array_merge($list1,$list2);
            $list = $this->checkMoneyEq($list_res);
        } else if(isset($map['status']) && $map['status'] == 3) {
            $flag_again = 0;
            if($page==1){
//                $sql = "select $field from (select $field from sxh_user_accepthelp_".$table[0]." where user_id=".$map['user_id']." and flag=0 and status=".$map['status']." union select $field from sxh_user_accepthelp_"
//                        .$table[1]." where user_id=".$map['user_id']." and flag=0 and status=".$map['status'].") as tt order by $order";
//                $list = Db::query($sql);
                $sql = "select $field from sxh_user_accepthelp_".$table[0]." where user_id=".$map['user_id']." and flag=0 and status=".$map['status']." order by $order";
                $list1 = Db::query($sql);
                if(!$list1 || empty($list1)){
                    $list1 = array();
                }
                $sql = "select $field from sxh_user_accepthelp_".$table[1]." where user_id=".$map['user_id']." and flag=0 and status=".$map['status']." order by $order";
                $list2 = Db::query($sql);
                if(!$list2 || empty($list2)){
                    $list2 = array();
                }
                $list = array_merge($list1,$list2);
                $now_table = $table[1];
            }else{
                $now_table = cache('sxh_current_table'.$map['user_id']);
                $sql = "select $field from sxh_user_accepthelp_".$now_table." where user_id=".$map['user_id']." and flag=0 and status=".$map['status']." order by $order";
                $list = Db::query($sql);
                if(!$list){
                    $now_table = getQueterTabel($now_table);
                    if($now_table){
                        $sql = "select $field from sxh_user_accepthelp_".$now_table." where user_id=".$map['user_id']." and flag=0 and status=".$map['status']." order by $order";
                        $list = Db::query($sql);
                        $flag_again = 1;
                    }
                }
            }
                
//            $sql = "select count(*) as total from (select id from sxh_user_accepthelp_".$table[0]." where user_id=".$map['user_id']." and flag=0 and status=".$map['status']." union select id from sxh_user_accepthelp_"
//                    .$table[1]." where user_id=".$map['user_id']." and flag=0 and status=".$map['status'].") as tt";
//            $count = Db::query($sql);
        }else{
//            $sql = "select $field from (select $field from sxh_user_accepthelp_".$table[0]." where user_id=".$map['user_id']." and flag=0 and status=".$map['status']." union select $field from sxh_user_accepthelp_"
//                    .$table[1]." where user_id=".$map['user_id']." and flag=0 and status=".$map['status'].") as tt order by $order";
//            $list = Db::query($sql);
            $sql = "select $field from sxh_user_accepthelp_".$table[0]." where user_id=".$map['user_id']." and flag=0 and status=".$map['status']." order by $order";
            $list1 = Db::query($sql);
            if(!$list1 || empty($list1)){
                $list1 = array();
            }
            $sql = "select $field from sxh_user_accepthelp_".$table[1]." where user_id=".$map['user_id']." and flag=0 and status=".$map['status']." order by $order";
            $list2 = Db::query($sql);
            if(!$list2 || empty($list2)){
                $list2 = array();
            }
            $list = array_merge($list1,$list2);
        }
        if (!empty($list)) {
            $b_list = array();
            $b_list['list']['data'] = $list;
            $b_list['current_page'] = $page;
            if($map['status'] == 3){
                $res = getQueterTabel($now_table);
                if($res){
                    cache('sxh_current_table'.$map['user_id'],$res);
                    $b_list['current_page'] += 1;
                    if($flag_again){
                        $b_list['current_page'] += 1;
                    }
                }else {
                    $b_list['current_page'] = 0;
                }
                $b_list['total'] = 10;
                $b_list['per_page'] = $r;
            }else{
                $b_list['total'] = count($list);
                $b_list['current_page']  = 0;
                $b_list['per_page'] = $b_list['total']>$r ? $b_list['total'] : $r;
            }
            return $b_list;
        }
        return null;
    }
     /*
     * 查询匹配表，查看已经审核的匹配金额是否等于提供资助金额，如果不等，提示部分匹配
     */
    public function checkMoneyEq($list){
        if(count($list)>0){
            $table = getTable(time(),1);
            foreach($list as $k => $v){
                $sum1 = 0;
                $sum2 = 0;
                if($v['money']==$v['used']){
                    if($table[1]){
                        $sql = 'select sum(other_money) as total_money from sxh_user_matchhelp_'.$table[1].' where flag=0 and status!=0 pid='.$v['id'];
                        $list2 = Db::query($sql);
                        $sum2 = $list2[0]['total_money'];
                    }
                    if($sum2 < $v['money']){
                        $sql = 'select sum(other_money) as total_money from sxh_user_matchhelp_'.$table[0].' where flag=0 and status!=0 pid='.$v['id'];
                        $list1 = Db::query($sql);
                        $sum1 = $list1[0]['total_money'];
                    }
                    $list[$k]['used'] = $sum1+$sum2;
                }
            }
        }
        return $list;
    }
    
    /**
     * 物理删除数据
     * @param type $map
     * @return type
     * @Author 童自扬
     * @time  2016-10-06
     */
    public function delAccepthelpData($map) {
       return $this->where($map)->delete();
    }
    
    

}
