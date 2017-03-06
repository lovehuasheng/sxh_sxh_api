<?php

// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 童自扬 <2421886569@qq.com> 
// +----------------------------------------------------------------------
// | Function: 匹配业务模型
// +----------------------------------------------------------------------

namespace app\user\model;
use app\common\model\Common;
use think\Model;
use \think\Db;

class UserMatchhelp extends Common {
    public $err = '';

    /**
     * 别名为审核状态
     * @param type $value
     * @param type $data
     * @return type
     */
    public static function getStatusAttr($value, $data) {
        if (isset($data['audit_status'])) {
            return intval($data['audit_status']) + intval($data['status']) + intval($data['sign']);
        } else {
            return intval($data['status']) + intval($data['sign']);
        }
    }

    /**
     * 转换status为字符串
     * @param type $value
     * @param type $data
     * @return string
     * @Author 童自扬
     * @time  2016-10-04 
     */
    public static function getStatusTextAttr($value, $data) {

        if ($data['audit_status'] == 1) {
            if ($data['status'] == 0) {
                return '未布施';
            } else {
                return '已布施';
            }

        } else {
            return '未审核';
        }
    }

    public static function getSignTextAttr($value, $data) {

        if ($data['audit_status'] == 1) {

            switch ($data['sign']) {
                case 1:
                    $sign = '已确认';
                    break;
                default: $sign = '未确认';
                    break;
            }

            return $sign;
        } else {
            return '未审核';
        }
    }

    /**
     * 转换资助类型
     * @param type $value
     * @param type $data
     * @return string
     * @Author 童自扬
     * @time  2016-10-04 
     */
    public static function getMatchingTextAttr($value, $data) {
        if ($data['type_id'] > 0) {
            return '接单资助';
        } else {
            return '提供资助';
        }
    }

    public static function getMatchingAcceptTextAttr($value, $data) {
        if ($data['type_id'] > 0) {
            return '接单钱包';
        } else {
            return '接受资助';
        }
    }

    /**
     * 转换打款方剩余时间状态
     * @param type $value
     * @param type $data
     * @return string
     * @Author 童自扬
     * @time  2016-10-04
     */
    public static function getCreateTimeTextAttr($value, $data) {


        $out_time = $_SERVER['REQUEST_TIME'] - strtotime($data['create_time']);
        if ($data['audit_status'] == 1 && $data['status'] == 0 && $data['sign'] == 0) {
            if ($out_time > config('matchhelp_out_time')) {
                return '已超时';
            } else {
                return ((strtotime($data['create_time']) + config('matchhelp_out_time')) - $_SERVER['REQUEST_TIME']); //'剩余时间'.date('H时i分s秒',($data['create_time']+config('matchhelp_out_time')));
            }
        } else {
            return null;
        }
    }

    /**
     * 打款方超时状态
     * @param type $value
     * @param type $data
     * @return int
     */
    public static function getProvideOvertimeStatusAttr($value, $data) {


        $out_time = $_SERVER['REQUEST_TIME'] - strtotime($data['create_time']);
        if ($data['audit_status'] == 1 && $data['status'] == 0 && $data['sign'] == 0) {
            if ($out_time > config('matchhelp_out_time')) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     * 收款方超时标记
     * @param type $vaule
     * @param type $data
     * @return int
     */
    public static function getAcceptOvertimeStatusAttr($vaule, $data) {


        if (strtotime($data['pay_time']) > 0) {
            $out_time = $_SERVER['REQUEST_TIME'] - strtotime($data['pay_time']);
            if ($data['status'] == 1 && $data['sign'] == 0) {
                if ($out_time > config('matchhelp_out_time')) {
                    return 1;
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     * 转换收款方剩余时间状态
     * @param type $vaule
     * @param type $data
     * @return string
     * @Author 童自扬
     * @time  2016-10-04
     */
    public static function getPayTimeTextAttr($vaule, $data) {
        if (strtotime($data['pay_time']) > 0) {
            $out_time = $_SERVER['REQUEST_TIME'] - strtotime($data['pay_time']);
            if ($data['status'] == 1 && $data['sign'] == 0) {
                if ($out_time > config('matchhelp_out_time')) {
                    return '已超时';
                } else {
                    return ((strtotime($data['pay_time']) + config('matchhelp_out_time')) - $_SERVER['REQUEST_TIME']); //'剩余时间'.date('H时i分s秒',($data['pay_time']+config('matchhelp_out_time')));
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * 获得匹配详情列表
     * @param type $other_id
     * @param type $field
     * @return boolean
     * @Author 童自扬
     * @time  2016-10-04
     */
    public function getMatchingListByProvideID($map, $field = '*') {
        //查找表后缀，即属于第几季度的表
        $table = getTable($map['create_time'],1);
        if($table[1]){
//            $sql = "select $field from (select $field from sxh_user_matchhelp_".$table[0]." where other_id=".$map['other_id']." and flag=0 and status != 0 union select $field from sxh_user_matchhelp_"
//                    .$table[1]." where other_id=".$map['other_id']." and flag=0 and status != 0) as tt order by id desc";
//            $list = Db::query($sql);
            $sql = "select $field from sxh_user_matchhelp_".$table[0]." where other_id=".$map['other_id']." and flag=0 and status != 0 order by id desc";
            $list1 = Db::query($sql);
            if(!$list1 || empty($list1)){
                $list1 = array();
            }
            $sql = "select $field from sxh_user_matchhelp_".$table[1]." where other_id=".$map['other_id']." and flag=0 and status != 0 order by id desc";
            $list2 = Db::query($sql);
            if(!$list2 || empty($list2)){
                $list2 = array();
            }
            $list = array_merge($list1,$list2);
        }else{
            $sql = "select $field from sxh_user_matchhelp_".$table[0]." where other_id=".$map['other_id']." and flag=0 and status != 0  order by id desc";
            $list = Db::query($sql);
        }
            
        return $list;
    }

    public function getMatchingListByAcceptID($map, $field = '*') {
        //查找表后缀，即属于第几季度的表,然后再向上取一个表，如果没有则为0
        $table = getTable($map['create_time'],1);
        if($table[1]){
//            $sql = "select $field from (select $field from sxh_user_matchhelp_".$table[0]." where pid=".$map['pid']." and flag=0 and status != 0 union select $field from sxh_user_matchhelp_"
//                    .$table[1]." where pid=".$map['pid']." and flag=0 and status != 0) as tt order by id desc";
//            $list = Db::query($sql);
            $sql = "select $field from sxh_user_matchhelp_".$table[0]." where pid=".$map['pid']." and flag=0 and status != 0 order by id desc";
            $list1 = Db::query($sql);
            if(!$list1 || empty($list1)){
                $list1 = array();
            }
            $sql = "select $field from sxh_user_matchhelp_".$table[1]." where pid=".$map['pid']." and flag=0 and status != 0 order by id desc";
            $list2 = Db::query($sql);
            if(!$list2 || empty($list2)){
                $list2 = array();
            }
            $list = array_merge($list1,$list2);
        }else{
            $sql = "select $field from sxh_user_matchhelp_".$table[0]." where pid=".$map['pid']." and flag=0 and status != 0 order by id desc";
            $list = Db::query($sql);
        }

        return $list;
    }
    /**
     * 获得1：1匹配信息
     * @param type $map
     * @param type $field
     * @return type
     * @Author huanghuasheng
     * @time  2016-12-15
     */
    public function getMatchingOneDataByID($table,$map, $field = '*') {
        $info = Db::table($table)->where($map)->field($field)->find();
        return $info;
    }
    /**
     * 获得1：1匹配信息
     * @param type $map
     * @param type $field
     * @return type
     * @Author 童自扬
     * @time  2016-10-05
     */
    public function getMatchingOneDataByMap($map, $field = '*') {

        $info = $this->where($map)->field($field)->find();
        if (!empty($info)) {
            return $info->toArray();
        }

        return [];
    }

    /**
     * 确认收款商务中心与供应商的数据处理
     */
    private function setCommany($map, $match, $user_info) {
        $provide = model('UserProvide')->getProvideData(['ID' => $match['OtherID']], 'CID,PoorID,MatchingID');
        \think\Log::error('provide:查询');
        if (empty($provide)) {
            $this->err = '数据错误！';
            return false;
        }
        //更新匹配表的打款状态和付款时间
        $map['Status'] = 1;
        $map['Sign'] = 0;
        $match_save = $this->save(['Sign' => 1, 'SignTime' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])], $map);
        \think\Log::error('match_save:更新匹配表的打款状态和付款时间');
        //更改接受资助表的收款状态
        $accept_count = $this->where(['PID' => $match['PID'], 'Sign' => 0])->count();
        \think\Log::error('查看接受资助表的收款是否完成');
        if ($accept_count == 0) {
            //如果此单接受资助匹配数据的收款是最后一笔，修改接受资助单的状态为完成状态
            $user_accepthelp = model('UserAccepthelp')->setAcceptData(['ID' => $match['PID']], ['Sign' => 1]);
            \think\Log::error('接受资助表是最后一笔');
        } else {
            $user_accepthelp = true;
        }
        //更改提供资助表的收款状态
        $provide_count = $this->where(['OtherID' => $match['OtherID'], 'Sign' => 0])->count();
        \think\Log::error('提供资助表');
        if ($provide_count == 0) {
            //如果此单的匹配的数据收款完成，更改提供资助的订单为完成状态
            $user_provide = model('UserProvide')->setProvideData(['ID' => $match['OtherID']], ['Sign' => 1]);
            \think\Log::error('改提供资助的订单');
            //企业暂时没有考虑转接单的情况
            //if(intval($provide['MatchingID']) == 0) {
            if ($user_info['nickname'] != '商务中心') {
                //给挂单人返善金币
                $user_invented_currency_result = model('UserAccount')->updateProvide(array('UserID' => $match['OtherUserID']), array('Invented_Currency' => array('exp', 'Invented_Currency+300')));
                //挂单人返善金币
                $user_income_result = model('UserIncome')->setDataByMap(4, 300, $match['OtherUserID'], $match['OtherID'], $match['ID']);
            } else {
                $user_invented_currency_result = 1;
                $user_income_result = 1;
            }
            //}
        } else {
            $user_invented_currency_result = 1;
            $user_income_result = 1;
            $user_provide = 1;
        }
        //查找推荐人、招商员、商务中心的返点数
        $community = model('UserCommunity')->getInfoByCid(6, 'Rebate,RefereeRebate,MembershipRebate,BasinessCenterRebater');
        $deduct = $community['Rebate'];
        //用户的提成
        $user_rebate = $match['OtherSum'] * $deduct * 0.01 / 2;
        $user_deduct = $match['OtherSum'] + $user_rebate;
        $user_result = $this->accountUserRebate($match, $user_deduct, $user_rebate, 2);
        //查找推荐人、招商员、商务中心ID
        $res = model('Userinfo')->findUserInfo(array('UserID' => $match['OtherUserID']), 'RefereeID,MembershipID,BusinessCenterID');
        //给企业推荐人返利
        if ($res['RefereeID'] > 0) {
            //返利金额
            $refid1 = $match['OtherSum'] * $community['RefereeRebate'] * 0.01;
            //给推荐人返利
            $refid1_accounta = model('UserAccount')->updateProvide(array('UserID' => $res['RefereeID']), ['CompManageWallet' => ['exp', 'CompManageWallet+' . $refid1]]);
            //返利记录入income表
            $refid1_returna = model('UserIncome')->setDataByMap(1, $refid1, $res['RefereeID'], $match['OtherUserID'], $match['ID']);
        } else {
            $refid1_accounta = 1;
            $refid1_returna = 1;
        }
        //给企业招商员返利
        if ($res['MembershipID'] > 0) {
            //返利金额
            $refid1 = $match['OtherSum'] * $community['MembershipRebate'] * 0.01;
            //给推荐人返利
            $refid1_accountb = model('UserAccount')->updateProvide(array('UserID' => $res['MembershipID']), ['CompManageWallet' => ['exp', 'CompManageWallet+' . $refid1]]);
            //返利记录入income表
            $refid1_returnb = model('UserIncome')->setDataByMap(1, $refid1, $res['MembershipID'], $match['OtherUserID'], $match['ID']);
        } else {
            $refid1_accountb = 1;
            $refid1_returnb = 1;
        }
        //给企业商务中心返利
        if ($res['BusinessCenterID'] > 0) {
            //返利金额
            $refid1 = $match['OtherSum'] * $community['BasinessCenterRebater'] * 0.01 / 2;
            //给推荐人返利
            $refid1_accountc = model('UserAccount')->updateProvide(array('UserID' => $res['BusinessCenterID']), ['CompManageWallet' => ['exp', 'CompManageWallet+' . $refid1], 'Invented_Currency' => ['exp', 'Invented_Currency+' . $refid1]]);
            //返利记录入income表
            $refid1_returnc1 = model('UserIncome')->setDataByMap(1, $refid1, $res['BusinessCenterID'], $match['OtherUserID'], $match['ID']);
            $refid1_returnc2 = model('UserIncome')->setDataByMap(4, $refid1, $res['BusinessCenterID'], $match['OtherUserID'], $match['ID']);
        } else {
            $refid1_accountc = 1;
            $refid1_returnc1 = 1;
            $refid1_returnc2 = 1;
        }
        if ($match_save && $user_accepthelp && $user_invented_currency_result && $user_income_result && $user_provide && $user_result && $refid1_accounta && $refid1_returna && $refid1_accountb && $refid1_returnb && $refid1_accountc && $refid1_returnc1 && $refid1_returnc2) {
            return true;
        } else {
            return false;
        }
    }
    /*确认收款*/
    public function setUserPayToMatchingIsLastNo($map){
        $redis = \org\RedisLib::get_instance();
        $table_matchhelp_now  = "sxh_user_matchhelp_".date("Y",$map['create_time']).'_'.ceil(date("m",$map['create_time'])/3);
        $sql = "select * from ".$table_matchhelp_now." where id = ".$map['id']." limit 1"  ;
        $match_data = Db::query($sql);
        if(count($match_data) == 0){
            $this->err = '未找到订单';
            return false;
        }
        $arr = $match_data[0];
        if($arr['status'] != 2){
            $this->err = '订单不处于可确认接款状态';
            return false;
        }
        /*获取社区信息*/
        $community = Db::query("select * from sxh_user_community where id = ".$arr['other_cid']." limit 1");
        $com = current($community);
        
        /*更新matchhelp表中的状态*/
        $sql = "update ".$table_matchhelp_now ." set status = 3 where id = ".$map['id'];
        $match_update = Db::execute($sql);
        if(!$match_update){
            $this->err = '确认收款失败';
            return false;
        }
        $this->startTrans();
        /*更新provide，accept表中的状态*/
        $return = $this->updateMatchStatus($arr);
        if(!$return){
            $this->err = '确认收款失败';
            $this->rollback();
            $sql = "update ".$table_matchhelp_now ." set status = 2 where id = ".$map['id'];
            $match_update = Db::execute($sql);
            return false;
        }
        $arr['pro'] = $return['pro'];
        $arr['is_company'] = $return['is_company'];
        $arr['pro_money'] = $return['pro_money'];
        $arr['act'] = $return['act'];
        /*关于转接单的返利处理*/
        if($arr['other_type_id'] == 2){ 
            /*个人直接返利%5（接单钱包，还是社区钱包）不在考虑上级  企业直接返利%5企业钱包不在考虑上下级*/
            $back_data = $this->setRebate($arr);
            if($back_data){
                $sql = "update ".$table_matchhelp_now ." sign_time = ".time()." where id = ".$map['id'];
                $match_update = Db::execute($sql);
                $this->commit();
                $redis->set('sxh_user_matchhelp_repeat:id:'.intval($arr['id']),1,3);
                if($arr['act'] == 100 && $arr['type_id'] == 1){ /*最后一笔匹配打款*/
                    $num = $redis->hget('sxh_userinfo:id:'.intval($arr['user_id']),"accepthelp_finish_num");/*提出接受资助的次数*/
                    if($num == '') $num = 0;
                    $redis->hset('sxh_userinfo:id:'.intval($arr['user_id']),"accepthelp_finish_num",$num+1);/*接受资助的次数+1*/
                }
                $this->err = '确认收款成功';
                return true;
            }else{
                $this->err = '确认收款失败';
                $this->rollback();
                $sql = "update ".$table_matchhelp_now ." set status = 2 where id = ".$map['id'];
                $match_update = Db::execute($sql);
                return false;
            }
        }
        /*计算返利个人版,个人打款*/
        
        if($arr['is_company'] == 0){
            /*查询用户上五级*/
            $user_relation = Db::query("select full_url from sxh_user_relation where user_id = ".$arr['other_user_id']);
            if(count($user_relation) == 0){
                $this->err = '用户关系存在错误请联系客服。';
                $this->rollback();
                $sql = "update ".$table_matchhelp_now ." set status = 2 where id = ".$map['id'];
                $match_update = Db::execute($sql);
                return false;
            }
            $pids =  explode(',',$user_relation[0]['full_url']);
            krsort($pids);
            //$pid = array_reverse($pids);
            $pid = [];
            $l = 0;
            foreach($pids as $k=>$v){
                $l = $l + 1;
                if($v == '' || $l>=8){
                    continue;
                }
                $pid[]=$v; 
            }
            $return_status = $this->userParentLevelRebate($arr,$com,$pid);
            if($return_status){
                $sql = "update ".$table_matchhelp_now ." sign_time = ".time()." where id = ".$map['id'];
                $match_update = Db::execute($sql);
                 /*设置防重复操作*/
                $provide = $redis->get('sxh_user_matchhelp_repeat:id:'.intval($arr['id']));
                if(is_numeric($provide)){/*3秒钟时效，在此期间不处理第二次请求*/
                    $this->rollback(); 
                    $sql = "update ".$table_matchhelp_now ." set status = 2 where id = ".$map['id'];
                    $match_update = Db::execute($sql);               
                    $this->err = '请勿重复提交';
                    return false;
                }
                $this->commit();
                $redis->set('sxh_user_matchhelp_repeat:id:'.intval($arr['id']),1,3);
                if($arr['pro'] == 2 || $arr['pro'] == 102){ /*第一笔匹配打款*/
                    $num = $redis->hget('sxh_userinfo:id:'.intval($arr['other_user_id']),"provide_finish_num");/*提出挂单的次数*/
                    if($num ==''){
                        $num = 0;
                    }
                    $redis->hset('sxh_userinfo:id:'.intval($arr['other_user_id']),"provide_finish_num",($num+1));/*成功挂单，挂单的次数+1*/
                }
                if($arr['pro'] == 100 || $arr['pro'] == 102){/*最后一笔匹配打款*/
                    $redis->hset("sxh_userinfo:id:".$arr['other_user_id'],"provide_last_community_id",$arr['other_cid']);/*最近挂单的社区ID*/
                    $redis->hset("sxh_userinfo:id:".$arr['other_user_id'],"provide_current_id",$arr['other_id']);/*上一次完成的订单ID*/
                    $redis->hset("sxh_userinfo:id:".$arr['other_user_id'],"provide_current_money",$arr['pro_money']);/*上一次完成的订单金额*/
                    $provide_num = $redis->hget("sxh_userinfo:id:".$arr['other_user_id'],"provide_num");
                    $redis->hset("sxh_userinfo:id:".$arr['other_user_id'],"provide_num",($provide_num+1));/*完成的订单次数，包含所有社区*/
                    $provide_community = $redis->hget("sxh_userinfo:id:".$arr['other_user_id'],"provide_community_".$arr['other_cid']."_count")+1;
                    $redis->hset("sxh_userinfo:id:".$arr['other_user_id'],"provide_community_".$arr['other_cid']."_count",$provide_community); /*用户在特定某个社区挂单的次数*/
                    $redis->delDataList('provide',$arr['other_user_id'],2);/*黄华盛缓存*/
                    $redis->delDataList('provide',$arr['other_user_id'],3);/*黄华盛缓存*/
                }
                if($arr['act'] == 100 && $arr['type_id'] == 1 ){ /*接受资助的最后一笔匹配*/
                    $num1 = $redis->hget('sxh_userinfo:id:'.intval($arr['user_id']),"accepthelp_finish_num");/*接受资助的次数*/
                    if($num1 == '') $num1 = 0;
                    $redis->hset('sxh_userinfo:id:'.intval($arr['user_id']),"accepthelp_finish_num",$num1+1);/*接受资助，挂单的次数+1*/
                }
                $redis->hset("sxh_userinfo:id:".$arr['other_user_id'],$return_status['field']."_last_changetime",time());  /*钱包变化的最后时间，用于显示获取钱包的最后钱包变化时间*/              
                $redis->hset("sxh_userinfo:id:".$arr['other_user_id'],"provide_match_time",time());  /*上一笔完成匹配打款的时间，用于挂单扣除善心币是否翻倍*/
                if($arr['type_id'] == 1){
                        $redis->hset("sxh_userinfo:id:".$arr['user_id'],"accept_match_time",time());/*上一笔完成匹配收款的时间，用于挂单扣除善心币是否翻倍*/
                }
                $redis->delMatchDetail('provide',$arr['other_user_id'],$arr['other_id']);/*黄华盛缓存*/
                $redis->delMatchDetail('accepthelp',$arr['user_id'],$arr['pid']);        /*黄华盛缓存*/
                return true;
            }else{
                $this->rollback();
                $this->err = '收款失败。';
                $sql = "update ".$table_matchhelp_now ." set status = 2 where id = ".$map['id'];
                $match_update = Db::execute($sql); 
                return false;
            }
            
        }
        /*企业版，企业打款*/
        if($arr['is_company'] == 1){
            $return_status = $this->userParentLevelRebate($arr,$com,[]);
            if($return_status){
                $sql = "update ".$table_matchhelp_now ." sign_time = ".time()." where id = ".$map['id'];
                $match_update = Db::execute($sql);
                 /*设置防重复操作*/
                $provide = $redis->get('sxh_user_matchhelp_repeat:id:'.intval($arr['id']));
                if(is_numeric($provide)){/*3秒钟时效，在此期间不处理第二次请求*/
                    $this->rollback(); 
                    $sql = "update ".$table_matchhelp_now ." set status = 2 where id = ".$map['id'];
                    $match_update = Db::execute($sql);
                    $this->err = '请勿重复提交';
                    return false;
                }
                $this->commit();
                $redis->set('sxh_user_matchhelp_repeat:id:'.intval($arr['id']),1,3);
                if($arr['pro'] ==2 || $arr['pro'] ==102 ){ /*第一笔匹配打款可能也是最后一笔*/
                    $redis = \org\RedisLib::get_instance(); 
                    $num = $redis->hget('sxh_userinfo:id:'.intval($arr['other_user_id']),"provide_finish_num");/*提出挂单的次数*/
                    $redis->hset('sxh_userinfo:id:'.intval($arr['other_user_id']),"provide_finish_num",$num+1);/*成功挂单，挂单的次数+1*/
                }
                if($arr['pro'] == 100 || $arr['pro'] ==102){/*最后一笔匹配打款 可能也是第一笔*/
                    $redis->hset("sxh_userinfo:id:".$arr['other_user_id'],"provide_current_id",$arr['other_id']);/*上一次完成的订单ID*/
                    $redis->hset("sxh_userinfo:id:".$arr['other_user_id'],"provide_current_money",$arr['pro_money']);/*上一次完成的订单金额*/
                    $provide_num = $redis->hget("sxh_userinfo:id:".$arr['other_user_id'],"provide_num");
                    $redis->hset("sxh_userinfo:id:".$arr['other_user_id'],"provide_num",($provide_num+1));/*完成的订单次数*/
                    $provide_community = $redis->hget("sxh_userinfo:id:".$arr['other_user_id'],"provide_community_".$arr['other_cid']."_count")+1;
                    $redis->hset("sxh_userinfo:id:".$arr['other_user_id'],"provide_community_".$arr['other_cid']."_count",$provide_community);/*社区的挂单次数*/
                    $redis->delDataList('provide',$arr['other_user_id'],2);/*黄华盛缓存*/
                    $redis->delDataList('provide',$arr['other_user_id'],3);/*黄华盛缓存*/
                }
                if($arr['act'] == 100 && $arr['type_id'] == 1  ){
                    $redis->delDataList('accepthelp',$arr['user_id'],2);
                    $redis->delDataList('accepthelp',$arr['user_id'],3);
                    $num = $redis->hget('sxh_userinfo:id:'.intval($arr['user_id']),"accepthelp_finish_num");/*完成接受资助的次数*/
                    if($num == '') $num = 0;
                    $redis->hset('sxh_userinfo:id:'.intval($arr['user_id']),"accepthelp_finish_num",$num+1);/*完成接受资助的次数 +1*/
                    if($arr['cid']==8){
                        $num2 = $redis->hget('sxh_userinfo:id:'.intval($arr['user_id']),"accepthelp8_finish_num");/*货款提取接受资助完成数*/
                        if($num2 == '') $num2 = 0;
                        $redis->hset('sxh_userinfo:id:'.intval($arr['user_id']),"accepthelp8_finish_num",$num2+1);/*货款提取接受资助完成数 +1*/
                    }
                }
                $redis->hset("sxh_userinfo:id:".$arr['other_user_id'],"provide_match_time",time());/*上一笔匹配打款完成时间*/
                if($arr['type_id'] == 1){
                        $redis->hset("sxh_userinfo:id:".$arr['user_id'],"accept_match_time",time());/*上一笔匹配收款完成时间*/
                }

                $redis->delMatchDetail('provide',$arr['other_user_id'],$arr['other_id']);/*黄华盛缓存*/
                $redis->delMatchDetail('accepthelp',$arr['user_id'],$arr['pid']);        /*黄华盛缓存*/
                return true;
            }else{
                $this->rollback();
                $this->err = '收款失败。';
                $sql = "update ".$table_matchhelp_now ." set status = 2 where id = ".$map['id'];
                $match_update = Db::execute($sql);
                return false;
            }
        }
    }
    /*转接单的处理*/
    public function setRebate($arr){
        $redis = \org\RedisLib::get_instance();
        $table_user = 'sxh_user_account_'.ceil($arr['other_user_id']/1000000);
        $field = 'order_taking';
        if($arr['is_company'] == 1){
            $field = 'company_order_taking';
        }
        $income     = $arr['other_money']*5*0.01+$arr['other_money'];
        $earnings    = $arr['other_money']*5*0.01;
        $sql = "update ".$table_user." set ".$field." = ".$field." + ".$income." where user_id = ".$arr['other_user_id'];
        $update = Db::execute($sql);
        if(!$update){
            return false;
        }
        $Income = \think\Loader::model('common/UserIncome','model');
        $insertincome1['id']          = $redis->incr("sxh_user_income:id");
        $insertincome1['cid']         = $arr['other_cid'] ;
        $insertincome1['user_id']     = $arr['other_user_id'];
        $insertincome1['username']    = $redis->get("sxh_user:id:".$arr['other_user_id'].":username");
        $insertincome1['pid']         = $arr['other_id'];
        $insertincome1['create_time'] = time();
        $insertincome1['status']      = 0;    
        $insertincome1['income']      = $income;/*转接单收益5%*/
        $insertincome1['earnings']    = $earnings;
        $insertincome1['info']        = '【App】转接单收益接单钱包';
        $insertincome1['type']        =  6 ;/*接单钱包*/
        if($arr['is_company'] == 1){
            $insertincome1['type']        =  12 ; /*企业钱包*/
        }
        $insert = $Income->insertIncome($insertincome1);
        if($insert<=0){
            return false;
        }
        return true;
    }
    /*更新provide,accept表中的状态*/
    public function updateMatchStatus($arr){
        $tabal_provide  = 'sxh_user_provide_'.date("Y",$arr['provide_create_time']).'_'.ceil(date("m",$arr['provide_create_time'])/3);
        $tabal_accept   = 'sxh_user_accepthelp_'.date("Y",$arr['accepthelp_create_time']).'_'.ceil(date("m",$arr['accepthelp_create_time'])/3);
        /*更新挂单表中的信息*/
        $provide = Db::query("select money,used,match_num,finish_count,is_company from ".$tabal_provide." where id = ".$arr['other_id']." limit 1");
        if(count($provide) == 0){
            return false;
        }
        $pro = current($provide);
        $i = 0;
        if($pro['finish_count'] == 0){/*第一笔提供资助*/
                $i = $i+2;
        }
        $sql = "update ".$tabal_provide." set finish_count = finish_count + 1  where id = ".$arr['other_id'];
        if($pro['money'] == $pro['used']){ /*是否拆分完毕，这条很重要*/
            if($pro['match_num'] == ($pro['finish_count']+1)){ /*匹配收款的最后一条*/
                $sql = "update ".$tabal_provide." set finish_count = match_num,status =3 ,sign_time = ".time()." where id = ".$arr['other_id'];
                $i = $i+100;/*本笔订单的最后一笔匹配*/
            }
        }
        $update_pro = Db::execute($sql);
        if(!$update_pro){
            return false;
        }
        /*更新接受资助信息*/
        $accept = Db::query("select money,used,match_num,finish_count from ".$tabal_accept." where id = ".$arr['pid']." limit 1");
        if(count($accept) == 0){
            return false;
        }
        $acc = current($accept);
        $j = 0;
        $sql2 = "update ".$tabal_accept." set finish_count = finish_count + 1  where id = ".$arr['pid'];
        if($acc['money'] == $acc['used']){ /*是否拆分完毕，这条很重要*/
            if($acc['match_num'] == ($acc['finish_count']+1)){ /*匹配收款的最后一条*/
                $sql2 = "update ".$tabal_accept." set finish_count = finish_count + 1,status =3 ,sign_time = ".time()." where id = ".$arr['pid'];
                $j = 100;
            }
        }
        $update_acc = Db::execute($sql2);
        if(!$update_acc){
            return false;
        }
        $return['pro'] = $i;
        $return['pro_money'] = $pro['money'];
        $return['act'] = $j;
        $return['is_company'] = $pro['is_company'];
        return $return;
    }
    /*返利收益计算$arr匹配表中数据，$com挂单人的社区信息，$pids挂单人的推荐人信息*/
    public function  userParentLevelRebate($arr,$com,$pids){
        if($arr['is_company'] == 0){
            $Income = \think\Loader::model('common/UserIncome','model');
            $redis = \org\RedisLib::get_instance();
            if(isset($pids[0])){/*挂单人*/
                $user[0]['user_id'] = $pids[0]; 
                $user[0]['rebate']  = $com['rebate'];
                if($arr['other_cid'] == 3){ /*小康社区挂单五次以上，返利变成15%*/
                    $counts = $redis->hget("sxh_userinfo:id:".$pids[0],"provide_community_3_count");
                    if($counts > 5){
                        $user[0]['rebate']  = 15; 
                    }
                }
                $user[0]['level']   = 0;
                $user[0]['field']   = $com['wallet_field'];
                $user[0]['rebate_money']  = $arr['other_money']*$user[0]['rebate']*0.01 + $arr['other_money'];
            }
            if(isset($pids[1])){/*一级推荐人*/
               $user[1]['user_id'] = $pids[1]; 
               $user[1]['rebate']  = $com['level1_rebate'];
               $user[1]['level']   = 1;
               $user[1]['rebate_money']  = $arr['other_money']*$user[1]['rebate']*0.01;
            }
            if(isset($pids[3])){/*三级级推荐人*/
               $user[3]['user_id'] = $pids[3]; 
               $user[3]['rebate']  = $com['level3_rebate'];
               $user[3]['level']   = 3;
               $user[3]['rebate_money']  = $arr['other_money']*$user[3]['rebate']*0.01;
            }
            /*if(isset($pids[5])){//五极级推荐人
               $user[5]['user_id'] = $pids[5]; 
               $user[5]['rebate']  = $com['level5_rebate'];
               $user[5]['level']   = 5;
               $user[5]['rebate_money']  = $arr['other_money']*$user[5]['rebate']*0.01;
            }*/
            foreach($user as $k=>$v){
                $table_user = '';
                //$table_user = $UserAccount->getPartitionTableName(['id'=>$v['user_id']],'id',['type' => 'id','expr' => 1000000]);
                $table_user = 'sxh_user_account_'.ceil($v['user_id']/1000000);
                $insertincome1 = [];
                $insertincome1['cid']         = $arr['other_cid'] ;
                $insertincome1['user_id']     = $v['user_id'];
                $insertincome1['username']    = $redis->get("sxh_user:id:".$v['user_id'].":username");
                $insertincome1['pid']         = $arr['other_id'];
                $insertincome1['create_time'] = time();
                $insertincome1['status']      = 0;
                if($v['level'] == 0){ /*自身返利*/
                    $update_user = Db::execute("update ".$table_user." set ".$com['wallet_field']." = ".$com['wallet_field']." + ".$v['rebate_money']." where user_id = ".$v['user_id']);
                    if(!$update_user){
                        return false;
                    }
                    switch($com['wallet_status']){
                        case 7:$package = '特困钱包';break;
                        case 8:$package = '贫穷钱包';break;
                        case 9:$package = '小康钱包';break;
                        case 10:$package = '德善钱包';break;
                        case 11:$package = '富人钱包';break;
                        case 15:$package = '大德钱包';break;
                    }
                    $insertincome1['id']          = $redis->incr("sxh_user_income:id");
                    $insertincome1['type']        = $com['wallet_status'] ;
                    $insertincome1['income']      = $v['rebate_money'];
                    $insertincome1['earnings']    = $v['rebate_money'] - $arr['other_money'];
                    $insertincome1['info']        = '【App】挂单收益'.$package;
                    $insert = $Income->insertIncome($insertincome1);
                    if($insert<=0){
                        return false;
                    }
                    /*完成第一笔匹配或者只有一笔挂单返善心币转成善金币，翻倍的善心币作为也按照正常处理*/
                    if($arr['pro'] == 2 || $arr['pro'] ==102){
                        $insertincome1['id']          = $redis->incr("sxh_user_income:id");
                        $insertincome1['type']        = 3 ;
                        $insertincome1['income']      = $com['need_currency']*100;
                        $insertincome1['earnings']    = $com['need_currency']*100;
                        $insertincome1['info']        = '【App】完成挂单返善金币';
                        $update_user_acc = Db::execute("update ".$table_user." set invented_currency = invented_currency + ".$insertincome1['income']." where user_id = ".$v['user_id']);
                        if(!$update_user_acc){
                            return false;
                        }
                        $insert = $Income->insertIncome($insertincome1);
                        if($insert<=0){
                            return false;
                        }
                    }
                }else{ /*1,3级返利管理钱包和善金币*/
                    $money = $v['rebate_money']/2;
                    $update_user = Db::execute("update ".$table_user." set  manage_wallet = manage_wallet + ".$money.",invented_currency = invented_currency + ".$money."  where user_id = ".$v['user_id']);
                    if(!$update_user){
                        return false;
                    }
                    $insertincome1['id']          = $redis->incr("sxh_user_income:id");
                    $insertincome1['type']        = 5 ;
                    $insertincome1['income']      = $money;
                    $insertincome1['earnings']    = $money;
                    $insertincome1['info']        = '【App】返利管理钱包';
                    $insert1 = $Income->insertIncome($insertincome1);
                    if($insert1<=0){
                        return false;
                    }
                    $insertincome1['id']          = $redis->incr("sxh_user_income:id");
                    $insertincome1['type']        = 3;
                    $insertincome1['info']        = '【App】返利善金币';
                    $insert2 = $Income->insertIncome($insertincome1);
                    if($insert2<=0){
                        return false;
                    }
                }
            }
            return ['rebate_money'=>$user[0]['rebate_money'],'field'=>$com['wallet_field']];
        }else{/*企业*/
            $Income = \think\Loader::model('common/UserIncome','model');
            $redis = \org\RedisLib::get_instance();
            $company = Db::query("select * from sxh_company_info where company_id =  ".$arr['other_user_id']." limit 1");
            if(!$company){
                return false;
            }
            $comp=current($company);
            if($arr['other_user_id'] > 0){   /*自身返利*/
                $cp[0]['user_id'] = $arr['other_user_id'];
                $cp[0]['level']   = 0;
                $cp[0]['rebate']  = $com['rebate'];
                $cp[0]['rebate_money']  = $cp[0]['rebate']*$arr['other_money']*0.01+$arr['other_money'];
                if($comp['business_type'] == 1){
                    $cp[0]['rebate_money']  = $arr['other_money'];
                }
            }
            if($comp['referee_id'] > 0){    /*引荐人*/
                $cp[1]['user_id'] = $comp['referee_id'];
                $cp[1]['level']   = 1;
                $cp[1]['rebate']  = $com['level1_rebate'];
                $cp[1]['rebate_money']  = $cp[1]['rebate']*$arr['other_money']*0.01;
            }
            if($comp['membership_id'] > 0){ /*招商员*/
                $cp[2]['user_id'] = $comp['membership_id'];
                $cp[2]['level']   = 2;
                $cp[2]['rebate']  = $com['membership_rebate'];
                $cp[2]['rebate_money']  = $cp[2]['rebate']*$arr['other_money']*0.01;
            }
            if($comp['business_center_id'] > 0){ /*商务中心*/
                $cp[3]['user_id'] = $comp['business_center_id'];
                $cp[3]['level']   = 3;
                $cp[3]['rebate']  = $com['business_rebate'];
                $cp[3]['rebate_money']  = $cp[3]['rebate']*$arr['other_money']*0.01;
            }
            foreach($cp as $k=>$v){
                $table_user = '';
                //$table_user = $UserAccount->getPartitionTableName(['id'=>$v['user_id']],'id',['type' => 'id','expr' => 1000000]);
                $table_user = 'sxh_user_account_'.ceil($v['user_id']/1000000);
                $insertincome2 = [];
                $insertincome2['cid']         = $arr['other_cid'] ;
                $insertincome2['user_id']     = $v['user_id'];
                $username = $redis->get("sxh_user:id:".$v['user_id'].":username");
                if(!$username){
                    $username = '';
                }
                $insertincome2['username']    = $username;
                $insertincome2['pid']         = $arr['other_id'];
                $insertincome2['create_time'] = time();
                $insertincome2['status']      = 0;
                if($v['level'] == 0){ /*自身企业钱包本金+利息*/
                   $update_user = Db::execute("update ".$table_user." set  company_wallet = company_wallet + ".$v['rebate_money']." where user_id = ".$v['user_id']);
                    if(!$update_user){
                        return false;
                    }
                    $insertincome2['id']          = $redis->incr("sxh_user_income:id");
                    $insertincome2['type']        = 12;
                    $insertincome2['income']      = $v['rebate_money'];
                    $insertincome2['earnings']    = $v['rebate_money'];
                    $insertincome2['info']        = '【App】挂单收益企业钱包';
                    $insert2 = $Income->insertIncome($insertincome2);
                    if($insert2 <=0){
                        return false;
                    }
                    /*商务中心挂单善心币返回善金币*/
                    if(($arr['pro'] == 2 || $arr['pro'] == 102)&&$comp['business_type'] == 1){ /*挂单匹配收款的最后一笔，商务中心要返回挂单时扣得善心币，已善金币的形式返回*/
                       
                        $insertincome2['id']          = $redis->incr("sxh_user_income:id"); ;
                        $insertincome2['type']        = 3;
                        $insertincome2['income']      = $com['need_currency']*100;
                        $insertincome2['info']        = '【App】完成挂单返回善金币';
                        $update_user_acc = Db::execute("update ".$table_user." set invented_currency = invented_currency + ".$insertincome2['income']." where user_id = ".$v['user_id']);
                        if(!$update_user_acc){
                            return false;
                        }
                        $insert1 = $Income->insertIncome($insertincome2);
                        if($insert1<=0){
                            return false;
                        }
						
                    }
                }else if($v['level'] == 1 || $v['level'] == 2){/*企业管理钱包*/
                    $update_user = Db::execute("update ".$table_user." set  company_manage_wallet = company_manage_wallet + ".$v['rebate_money']."  where user_id = ".$v['user_id']);
                    if(!$update_user){
                        return false;
                    }
                    $insertincome2['id']          = $redis->incr("sxh_user_income:id");
                    $insertincome2['type']        = 13;
                    $insertincome2['income']      = $v['rebate_money'];
                    $insertincome2['earnings']    = $v['rebate_money'];
                    $insertincome2['info']        = '【App】返利企业管理钱包';
                    $insert2 = $Income->insertIncome($insertincome2);
                    if($insert2<=0){
                        return false;
                    }
					
                }else if($v['level'] == 3 ){/*企业管理钱包，善金币*/
                    $money = $v['rebate_money']/2;
                    $update_user = Db::execute("update ".$table_user." set  company_manage_wallet = company_manage_wallet + ".$money." ,invented_currency = invented_currency + ".$money ." where user_id = ".$v['user_id']);
                    if(!$update_user){
                        return false;
                    }
                    $insertincome2['id']          = $redis->incr("sxh_user_income:id");
                    $insertincome2['type']        = 13;
                    $insertincome2['income']      = $money;
                    $insertincome2['earnings']    = $money;
                    $insertincome2['info']        = '【App】返利企业管理钱包';
                    $insert2 = $Income->insertIncome($insertincome2);
                    if($insert2<=0){
                        return false;
                    }

                    $insertincome2['id']          = $redis->incr("sxh_user_income:id");
                    $insertincome2['type']        = 3;
                    $insertincome2['info']        = '【App】返利善金币';
                    $insert1 = $Income->insertIncome($insertincome2);
                    if($insert1<=0){
                        return false;
                    }
                }
            }
            return true;
        }
    }               

    /**
     * 接单钱包
     * @param type $match
     * @param type $user_deduct
     * @param type $type
     * @return boolean
     */
    private function setUserOrderDeduct(&$match, &$user_deduct, $type) {
        $this->startTrans();
        //用户的提成
        $user_account = model('UserAccount')->addUserAccountMoney($match['OtherUserID'], 'OrderTaking', $user_deduct);
        $user_income = model('UserIncome')->setDataByMap($type, $user_deduct, $match['OtherUserID'], $match['OtherID'], $match['ID']);

        if ($user_account && $user_income) {
            $this->commit();
            return true;
        }
        $this->rollback();
        return false;
    }

    private function setUserDeduct(&$match, &$user_deduct, $type) {
        $this->startTrans();
        //用户的提成
        $user_account = model('UserAccount')->addUserAccountMoney($match['OtherUserID'], 'Wallet_Currency', $user_deduct);
        $user_income = model('UserIncome')->setDataByMap($type, $user_deduct, $match['OtherUserID'], $match['OtherID'], $match['ID']);

        if ($user_account && $user_income) {
            $this->commit();
            return true;
        }
        $this->rollback();
        return false;
    }

    private function accountUserRebate(&$match, &$user_deduct, $user_rebate, $type) {

        //用户的提成
        //$user_account = model('UserAccount')->addUserAccountMoney($match['OtherUserID'],'CompanyWallet',$user_deduct);
        $data = array();
        $data['CompanyWallet'] = array('exp', 'CompanyWallet+' . $user_deduct);
        $data['Invented_Currency'] = array('exp', 'Invented_Currency+' . $user_rebate);
        $user_account = model('UserAccount')->updateProvide(array('UserID' => $match['OtherUserID']), $data);
        $user_income = model('UserIncome')->setDataByMap($type, $user_deduct, $match['OtherUserID'], $match['OtherID'], $match['ID']);
        $user_income1 = model('UserIncome')->setDataByMap(4, $user_rebate, $match['OtherUserID'], $match['OtherID'], $match['ID']);
        if ($user_account && $user_income && $user_income1) {
            return true;
        }
        return false;
    }

    /*private function setDeduct(&$provide, &$match) {
        $this->startTrans();
        $deduct = 0;
        //查询提供方分成比例
        if ($provide['CID'] == 1 && $provide['PoorID'] == 1) {
            $deduct = 50;
            \think\Log::error('$deduct:50');
        } else {
            $count = 0;
            if ($provide['CID'] == 2) {
                $count = model('UserProvide')->getCount(['UserID' => $provide['UserID'], 'Sign' => 1, 'Status' => 1, 'CID' => 2,'MatchingID'=>0]);
            }
            if ($count > 5) {
                $deduct = 15;
            } else {
                $community = model('UserCommunity')->getInfoByCid($provide['CID'], 'LowRebate');
                $deduct = $community['LowRebate'];
                unset($community);
            }
        }
        \think\Log::error('$deduct外:' . $deduct);
        //用户的提成
        $user_deduct = ($match['OtherSum'] + ($match['OtherSum'] * $deduct * 0.01));
        $user_result = $this->setUserDeduct($match, $user_deduct, 2);
//        $user_account = model('UserAccount')->addUserAccountMoney($match['OtherUserID'],'Wallet_Currency',$user_deduct);
//        $user_income = model('UserIncome')->setDataByMap(1,$user_deduct,$match['OtherUserID'],$match['OtherID'],$match['ID']);
        //查询提供者的上1，3，5级用户
        $relation = model('UserRelation')->getUserRelationByID(['UserID' => $match['OtherUserID']], 'PID1,PID3,PID5');

        //查询提供方上级分成比例
        $user_rank = model('UserRank')->getInfoByLevelIn('1,3,5', 'Level,Ratio');

        if (empty($user_rank)) {
            return false;
        }

        //给提供方上一级分成
        if ($relation['PID1'] > 0) {
            $refid1 = ($match['OtherSum'] * $user_rank[1]['Ratio'] * 0.01) / 2;

            $refid1_account = model('UserAccount')->setUserAccountMoney($relation['PID1'], [
                'Manage_Wallet' => ['exp', 'Manage_Wallet+' . $refid1],
                'Invented_Currency' => ['exp', 'Invented_Currency+' . $refid1],
            ]);

            $refid1_return = model('UserIncome')->setDataByMap(1, $refid1, $relation['PID1'], $match['OtherUserID'], $match['ID']);
            $refid1_return1 = model('UserIncome')->setDataByMap(4, $refid1, $relation['PID1'], $match['OtherUserID'], $match['ID']);
        } else {
            $refid1_account = true;
            $refid1_return = true;
            $refid1_return1 = true;
        }


        //给提供方上三级分成
        if ($relation['PID3'] > 0) {
            $refid3 = ($match['OtherSum'] * $user_rank[3]['Ratio'] * 0.01) / 2;
            $refid3_account = model('UserAccount')->setUserAccountMoney($relation['PID3'], [
                'Manage_Wallet' => [
                    'exp', 'Manage_Wallet+' . $refid3
                ],
                'Invented_Currency' => [
                    'exp', 'Invented_Currency+' . $refid3
                ]
            ]);
            $refid3_return = model('UserIncome')->setDataByMap(1, $refid3, $relation['PID3'], $match['OtherUserID'], $match['ID']);
            $refid3_return1 = model('UserIncome')->setDataByMap(4, $refid3, $relation['PID3'], $match['OtherUserID'], $match['ID']);
        } else {
            $refid3_account = true;
            $refid3_return = true;
            $refid3_return1 = true;
        }


        //给提供方上五级分成
        if ($relation['PID5'] > 0) {
            $refid5 = ($match['OtherSum'] * $user_rank[5]['Ratio'] * 0.01) / 2;
            $refid5_account = model('UserAccount')->setUserAccountMoney($relation['PID5'], [
                'Manage_Wallet' => [
                    'exp', 'Manage_Wallet+' . $refid5
                ],
                'Invented_Currency' => [
                    'exp', 'Invented_Currency+' . $refid5
                ]
            ]);
            $refid5_return = model('UserIncome')->setDataByMap(1, $refid5, $relation['PID5'], $match['OtherUserID'], $match['ID']);
            $refid5_return1 = model('UserIncome')->setDataByMap(4, $refid5, $relation['PID5'], $match['OtherUserID'], $match['ID']);
        } else {
            $refid5_account = true;
            $refid5_return = true;
            $refid5_return1 = true;
        }

        if ($user_result && $refid1_account && $refid1_return && $refid1_return1 && $refid3_account && $refid3_return && $refid3_return1 && $refid5_account && $refid5_return && $refid5_return1) {
            $this->commit();
            return true;
        } else {
            $this->rollback();
            return false;
        }
    }*/

    /**
     * 
     * 更新数据
     * @param type $map
     * @param type $data
     * @return type
     */
    public function setMatchhelpData($map, $data) {
        return $this->save($data, $map);
    }
    /**
     * 订单延时
     * @param array $map
     * @param type $delayed_time
     * @return boolean
     */
    public function set_delayed_time($map, $delayed_time) {
        $a = \think\Loader::model('common/UserMatchhelp','model');
        $table_matchhelp_now  = $a->getPartitionTableName(['quarter'=>$map['create_time']],'quarter',['type' => 'quarter','expr' => 3]);
        $match_data = Db::query("select * from ".$table_matchhelp_now." where id = ".$map['id']." limit 1" );
        if(count($match_data) == 0){
            $this->err = '未找到订单';
            return false;
        }
        $info = $match_data[0];
        if ($info['status'] != 1) {
            $this->err = '匹配不处于可延时打款状态';
            return false;
        }
        if(intval($info['delayed_time_status']) > 0 ){
            $this->err = '订单已延时过了，不能再次延时了！';
            return false;
        }
        if($info['audit_time'] + config('matchhelp_out_time') > time()){/*24之内提出延时*/
            $time = $info['audit_time'] + config('matchhelp_out_time') + $delayed_time * 3600;
        }else{
            $time = time() + $delayed_time * 3600;
        }
        $update_sql = "update ".$table_matchhelp_now." set expiration_create_time = " .$time.", delayed_time_status = 1 where id = " .$map['id'];           
        $update = Db::execute($update_sql);
        if(!$update){
           $this->err = '延时失败！';
            return false; 
        }
        $redis = \org\RedisLib::get_instance();
        //延时需要清除的缓存
        $redis->delMatchDetail('provide',$info['other_user_id'],$info['other_id']);
        $redis->delMatchDetail('accepthelp',$info['user_id'],$info['pid']);
        return true;
    }

}
