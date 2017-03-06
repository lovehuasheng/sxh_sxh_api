<?php
/*
 * 提供资助的数据处理
 */
namespace app\member\model;
use app\common\model\Common;
use think\Model;
use think\Db;
class Provide extends Common
{
    /*获取用户的关于此次挂单二级密码，特困会员，激活状态，审核状态，黑名单状态,以及其他基本信息*/
    public function getUserDetail($userid,$cid){
        $table_user    = 'sxh_user_'.ceil($userid/1000000);   /*用户所在的表*/
        $table_account = 'sxh_user_account_'.ceil($userid/1000000); /*用户金额所在的表*/
        $sql = "select a.*,b.gc,c.*,d.bid from "
                    ." (select id,special,status,verify,is_poor,secondary_password,password,security,".$cid." as caid from  ".$table_user." where id = ".$userid." limit 1)a "
                ." join "
                    ." (select manage_wallet,guadan_currency as gc ,user_id from ".$table_account." where user_id =".$userid." limit 1)b on a.id = b.user_id "
                ." left join "
                    ."(select id as cid,low_sum as ls,top_sum as ts,name,multiple,need_currency as nc from sxh_user_community where id = ".$cid." limit 1)c on a.caid = c.cid "
                ." left join "
                    ." (select id as bid,user_id from sxh_user_blacklist where user_id = ".$userid." limit 1)d on a.id = d.user_id ";
        $user = Db::query($sql);
        if(count($user) == 0){
            return [];
        }else{
            return $user[0];
        }
    }
    /*最后一笔提供资助*/
    public function  getLastProvide($userid){
        $table      = 'sxh_user_provide_'.date("Y").'_'.ceil(date("m")/3);/*当前表*/
        $last_table = 'sxh_user_provide_'.date("Y",(time()-90*24*3600)).'_'.ceil(date("m",(time()-90*24*3600))/3); /*90天前的表，一般的排单不打款不会超过90天*/
        $sql = "select id from ".$table." where status in (0,1) and user_id = ".$userid." and flag =0 limit 1";
        $provide = Db::query($sql);
        if(count($provide)>0){
            return  ['sign'=>0];
        }
        $sql_last = "select id from ".$last_table." where status in (0,1) and user_id = ".$userid." and flag =0 limit 1";
        $provide_last = Db::query($sql_last);
        if(count($provide_last)>0){
            return  ['sign'=>0];
        }
        return  ['sign'=>1];
        
        /*redis中读取挂单次数，和完成的挂单次数*/
        //$redis = \org\RedisLib::get_instance(); /*撤销订单时挂单次数减一*/
        //$num1 = $redis->hget('sxh_userinfo:id:'.$userid,"provide_create_num");/*提出挂单的次数，redis数据不一定准确，减少从redis读取数据*/
        //$num2 = $redis->hget('sxh_userinfo:id:'.$userid,"provide_finish_num");/*完成挂单的次数，redis数据不一定准确，减少从redis读取数据*/
        //if($num1 == $num2){
        //    return  ['sign'=>1];
        //}
        //return  ['sign'=>0];
    }    
    /*挂单扣除的善心币是否翻倍*/
    public function  getGccount($userid){
        /*有在排队的接受资助则再次挂单消耗的善心币不翻倍*/
        $table_accept_now  = 'sxh_user_accepthelp_'.date("Y").'_'.ceil(date("m")/3);
        $table_accept_last = 'sxh_user_accepthelp_'.date("Y",(time()-90*24*3600)).'_'.ceil(date("m",(time()-90*24*3600))/3);
        $sql_accept  = "select id from ".$table_accept_now." where user_id = ".$userid." and status in(0,1,2) and type_id = 1 and flag = 0 limit 1"; 
        $user_accept = Db::query($sql_accept);
        if(count($user_accept)>0){
            return 0;
        }
        $sql_accept1  = "select id from ".$table_accept_last." where user_id = ".$userid." and status in(0,1,2) and type_id = 1 and flag = 0 limit 1"; 
        $user_accept1 = Db::query($sql_accept1);
        if(count($user_accept) > 0){
            return 0;
        }
//        $time     = date("Y").'_'.ceil(date("m")/3);                                         /*当前时间*/
//        $time_ago = date("Y",(time()-90*24*3600)).'_'.ceil(date("m",(time()-3*24*3600))/3); /*72小时以前*/
      
        /*从redis中获取最后挂单或最后接受资助的时间*/
        $redis = \org\RedisLib::get_instance(); /*撤销订单时挂单次数减一*/
//        $num11 = $redis->hget('sxh_userinfo:id:'.$userid,"accepthelp_create_num");/*提出挂单的时间，redis数据不一定准确，减少从redis读取数据*/
//        $num21 = $redis->hget('sxh_userinfo:id:'.$userid,"accepthelp_finish_num");/*完成接受资助的时间，redis数据不一定准确，减少从redis读取数据*/
//        if($num11 != $num21){
//            return 1;
//        }
        $num1 = $redis->hget('sxh_userinfo:id:'.$userid,"provide_match_time");/*最后一笔完成匹配打款的时间，这个不用redis，数据库目前不好查询*/
        $num2 = $redis->hget('sxh_userinfo:id:'.$userid,"accept_match_time");/*最后一笔完成匹配接款的时间，这个不用redis，数据库目前不好查询*/
        $max1 = max($num1,$num2);
        if($max1>0 &&($max1+72*3600<time())){
            return 1;
        }
        return 0;
    }       
    /*挂单数据处理,插入数据库*/
    public function doSaveProvide($d){
        $redis = \org\RedisLib::get_instance();
        $username = $redis->get('sxh_user:id:'.intval($d['user_id']).":username");
        $m_info = \think\Loader::model('user/UserInfo', 'model');
        $info = $m_info->getInfo(array('user_id'=>$d['user_id']),$d['user_id'],'name');
        $return = [];
        $insert_provide['id']          = $redis->incr("sxh_user_provide:id");
        $insert_provide['type_id']     = 1;
        $insert_provide['money']       = intval($d['money']);
        $insert_provide['cid']         = $d['cid'];
        $insert_provide['cname']       = $d['name'];/*社区名*/
        $insert_provide['user_id']     = intval($d['user_id']);
        $insert_provide['username']    = $username; /*用户名*/
        $insert_provide['name']        = $info['name'];/*真实姓名*/
        $insert_provide['status']      = 0;
        $insert_provide['batch']       = strtotime(date("Y-m-d"));
        $insert_provide['ipaddress']   = ip2long($d['ip']);
        $insert_provide['sign_time']   = '0';
        $insert_provide['create_time'] = time();
        $insert_provide['update_time'] = time();
        $insert_provide['match_num']   = 0;
        $insert_provide['pay_num']     = 0;
        $insert_provide['flag']        = 0;
        $a = \think\Loader::model('common/UserProvide','model');
        $b = \think\Loader::model('common/UserOutgo','model');
        //$t = \think\Loader::model('common/UserAccount', 'model');
        //$table_user_account = $t->getPartitionTableName(['id'=>intval($d['user_id'])],'id',['type' => 'id','expr' => 1000000]);
        $table_user_account = 'sxh_user_account_'.ceil($d['user_id']/1000000);
        Db::startTrans();
        /*设置防重复操作*/
        $provide = $redis->get('sxh_user_provide:userid:'.intval($d['user_id']));
        if(is_numeric($provide)){/*3秒钟时效，在此期间不处理第二次请求*/
            Db::rollback(); 
            $return['err'] = '不能重复提交';
            $return['code'] = 0;
            return $return;
        }
        $id = $a->insertProvide($insert_provide);
        if($id){
            $s = Db::execute("update ".$table_user_account." set guadan_currency = guadan_currency - ".intval($d['c'])." where user_id = ".intval($d['user_id']));
            if($s){
                $insert_outgo['id']      = $redis->incr("sxh_user_outgo:id");
                $insert_outgo['type']    = 2;
                $insert_outgo['outgo']   = intval($d['c']);
                $insert_outgo['user_id'] = intval($d['user_id']);
                $insert_outgo['pid']     = $insert_provide['id'];
                $insert_outgo['info']    = '【App】提供资助扣善心币';
                $insert_outgo['create_time']    = time();
                $insid = $b->insertOutgo($insert_outgo);
                if($insid){
                    Db::commit();
                    $redis->set('sxh_user_provide:userid:'.intval($d['user_id']),1,3);
                    $num = $redis->hget('sxh_userinfo:id:'.intval($d['user_id']),"provide_create_num");/*提出挂单的次数*/
                    if($num == ''){
                        $num = 0;
                    }
                    $redis->hset('sxh_userinfo:id:'.intval($d['user_id']),"provide_create_num",$num+1);/*成功挂单，挂单的次数+1*/                
                    $redis->delDataList('provide',intval($d['user_id']),1);/*黄华盛缓存*/
                    $return['code'] = 1;
                    return $return;
                }
            }
        }
        Db::rollback(); 
        $return['err'] = '提供资助失败';
        $return['code'] = 0;
        return $return;
        
    }
    /*获取手机号码*/
    /*public function getUserPhone($userid){
        $a = \think\Loader::model('common/UserInfo','model');
        $user = $a->getUserInfo(['user_id'=>$userid] , $field='phone' , $userid);
        if(count($user)>0){
            return $user;
        }else{
            return [];
        }
    }*/
}