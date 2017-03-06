<?php
/*
 * 提供资助的数据处理
 */
namespace app\member\model;
use think\Model;
use think\Db;
class Wallet extends Model
{
    /*获取用户的二级密码，特困会员，激活状态，审核状态，黑名单状态,出局钱包*/
    public function getUserDetail($userid){
        /*获取用户所在的表*/
        $table_user    = 'sxh_user_'.ceil($userid/1000000);   /*用户所在的表*/
        $table_account = 'sxh_user_account_'.ceil($userid/1000000); /*用户金额所在的表*/       
        $sql = "select a.*,b.*,c.id  from "
                    ." (select id as user_id, status,verify,is_poor,secondary_password,security from  ".$table_user." where id = ".$userid." limit 1)a "
                ." join "
                    ." (select manage_wallet as mw,poor_wallet as pw,needy_wallet as nw,comfortably_wallet as cw,kind_wallet as kw,wealth_wallet as ww,big_kind_wallet as bkw,user_id as uid from ".$table_account." where user_id =".$userid." limit 1)b on a.user_id = b.uid "
                ." left join "
                    ." (select id ,user_id from sxh_user_blacklist where user_id = ".$userid." limit 1)c on a.user_id = c.user_id ";
        $user = Db::query($sql);
        if(count($user) == 0){
            return [];
        }else{
            return $user[0];
        }
    }
    /*用户是否有过挂单*/
    public function getUserProvide($userid){
        $redis = \org\RedisLib::get_instance();
        $provide_num = $redis->hget("sxh_userinfo:id:".$userid,"provide_num");
        if($provide_num>0){
            return 1;
        }else{
            return 0;
        }
    }
    /*是否有未完成的接受资助*/
    public function getUserAccept($userid){
        /*$redis = \org\RedisLib::get_instance();
        $accept_create_num = $redis->hget("sxh_userinfo:id:".$userid,"accepthelp_create_num");
        $accept_finish_num = $redis->hget("sxh_userinfo:id:".$userid,"accepthelp_finish_num");
        if($accept_create_num > $accept_finish_num){
                return  0;
        }else{
                return  1;
        }*/
        $table_accept_now  = 'sxh_user_accepthelp_'.date("Y").'_'.ceil(date("m")/3);
        $table_accept_last = 'sxh_user_accepthelp_'.date("Y",(time()-90*24*3600)).'_'.ceil(date("m",(time()-90*24*3600))/3);
        $sql =  " select user_id from ".$table_accept_now." where user_id = ".$userid." and status in (0,1,2) and flag = 0 and type_id = 1 limit 1";
        $user = Db::query($sql);
        if(count($user)>0){
            return  0;
        }
        $sql1= " select user_id from ".$table_accept_last." where user_id = ".$userid." and status in (0,1,2) and flag = 0 and type_id = 1 limit 1";
        $user1 = Db::query($sql1);
        if( count($user1)>0){
            return  0;
        }
        return  1;
    }
    /*接受资助时是否有未完成的提供资助*/
    public function getUserProvideFinish($userid){
        $table_provide_now      = 'sxh_user_provide_'.date("Y").'_'.ceil(date("m")/3);/*当前表*/
        $table_provide_last     = 'sxh_user_provide_'.date("Y",(time()-90*24*3600)).'_'.ceil(date("m",(time()-90*24*3600))/3); /*90天前的表，一般的排单不打款不会超过90天*/
        //一，查provide表
        $provide_where = "user_id=".$userid." AND status in (1,2) AND type_id=1";
        $pro_sql = "SELECT id FROM ".$table_provide_now." WHERE ".$provide_where." LIMIT 1";
        $pro_result = \think\Db::query($pro_sql);
        //如果空，就查上季
        if(empty($pro_result)) {
            $pro_sql2 = "SELECT id FROM ".$table_provide_last." WHERE ".$provide_where." LIMIT 1";
            $pro_result2 = \think\Db::query($pro_sql2);
            if(empty($pro_result2)){
                return 1;
            }
        }
        return 0;
    }
    /*上笔挂单，是否提取管理奖*/
    public function getUserGap($userid){
        $redis = \org\RedisLib::get_instance();
        $last_id = $redis->hgetUserinfoByID($userid,'provide_current_id');
        $provide_manage_id = $redis->hgetUserinfoByID($userid,'provide_manage_id');
        if($last_id == $provide_manage_id ){
            /*已经提取管理奖*/
            return 0;
        }else{
            return 1;
        }
    }
    /*合并社区钱包*/
    public function together_wallet($data){
        $info = $this->getUserDetail($data['user_id']);
        $arr[1]['k'] = intval($info['pw']);$arr[1]['sort'] = 6;$arr[1]['c'] = 1;$arr[1]['field'] = 'poor_wallet';$arr[1]['cname']='特困社区';
        $arr[2]['k'] = intval($info['nw']);$arr[2]['sort'] = 5;$arr[2]['c'] = 2;$arr[2]['field'] = 'needy_wallet';$arr[2]['cname']='贫穷社区';
        $arr[3]['k'] = intval($info['cw']);$arr[3]['sort'] = 4;$arr[3]['c'] = 3;$arr[3]['field'] = 'comfortably_wallet';$arr[3]['cname']='小康社区';
        $arr[4]['k'] = intval($info['ww']);$arr[4]['sort'] = 3;$arr[4]['c'] = 4;$arr[4]['field'] = 'wealth_wallet';$arr[4]['cname']='富人社区';
        $arr[5]['k'] = intval($info['kw']);$arr[5]['sort'] = 2;$arr[5]['c'] = 5;$arr[5]['field'] = 'kind_wallet';$arr[5]['cname']='德善社区';
        $arr[6]['k'] = intval($info['bkw']);$arr[6]['sort'] = 1;$arr[6]['c'] = 6;$arr[6]['field'] = 'big_kind_wallet';$arr[6]['cname']='大德社区';
        $redis = \org\RedisLib::get_instance();
        $username = $redis->get("sxh_user:id:".intval($data['user_id']).":username");  
        rsort($arr);
        Db::startTrans(); 
        $outgo  = \think\Loader::model('common/UserOutgo','model');
        $income = \think\Loader::model('common/UserIncome','model');
        $table_user = 'sxh_user_account_'.ceil($data['user_id']/1000000);
        $sum = $arr[0]['k']+$arr[1]['k']+$arr[2]['k']+$arr[3]['k']+$arr[4]['k']+$arr[5]['k'];
        $sql = "update ".$table_user." set ".$arr[1]['field']." = 0 ,".$arr[2]['field']." = 0 ,".$arr[3]['field']." = 0 ,".$arr[4]['field']." = 0 ,".$arr[5]['field']." = 0 ,".
                $arr[0]['field']." = ".$sum." where user_id = ".intval($data['user_id']);
        $update = Db::execute($sql);
        if(!$update){
            Db::rollback(); 
            $return['code'] = 0;
            return $return;
        }
        $var = 0;
        switch($arr[0]['c']){
            case 1:$type = 7;$cname = '特困社区';break;
            case 2:$type = 8;$cname = '贫穷社区';break;
            case 3:$type = 9;$cname = '小康社区';break;
            case 4:$type = 11;$cname = '富人社区';break;
            case 5:$type = 10;$cname = '德善社区';break;
            case 6:$type = 15;$cname = '大德社区';break;
            default :;
        }
        foreach($arr as $k=>$v){
            if($k == 0){
               continue; 
            }
            if($v['k'] == 0){
                continue;
            }
            $outgoinsert = [];
            switch($v['c']){
                case 1:$outgoinsert['type'] = 7;break;
                case 2:$outgoinsert['type'] = 8;break;
                case 3:$outgoinsert['type'] = 9;break;
                case 4:$outgoinsert['type'] = 11;break;
                case 5:$outgoinsert['type'] = 10;break;
                case 6:$outgoinsert['type'] = 15;break;
                default :;
            }
            $outgoinsert['id']       = $redis->incr("sxh_user_outgo:id");
            $outgoinsert['user_id']  = intval($data['user_id']);
            $outgoinsert['username'] = $username;
            $outgoinsert['outgo']    = $v['k'];
            $outgoinsert['pid']      = 0;
            $outgoinsert['info']     = '【App】合并钱包';
            $outgoinsert['create_time']     = time();
            $outgoinsert['status']     = 0;

            $pid = $outgo->insertOutgo($outgoinsert);
            if($pid){
                $incomeinset = [];
                $incomeinset['id']       = $redis->incr("sxh_user_income:id");
                $incomeinset['type'] = $type;
                $incomeinset['cid'] = $arr[0]['c'];
                $incomeinset['user_id'] = intval($data['user_id']);
                $incomeinset['username'] = $username;
                $incomeinset['income'] = $v['k'];
                $incomeinset['earnings'] = 0;
                $incomeinset['pid'] = $outgoinsert['id'] ;
                $incomeinset['cat_id'] = 0;
                $incomeinset['info'] = '【App】合并钱包';
                $incomeinset['create_time'] = time();
                $incomeinset['status'] = 0;
                $inser = $income->insertIncome($incomeinset);
            }
        }
        Db::commit();
        $redis->hset("sxh_userinfo:id:".$data['user_id'],$arr['0']['field']."_last_changetime",time());  /*钱包变化的最后时间，用于显示获取钱包的最后钱包变化时间*/              
        $return['code'] = 1;
        return $return;
       
    }
    /*接受资助业务处理，插入数据库 1特困，2贫穷，3,小康，4富人，5德善，6大德*/
    public function doSaveAccept($d,$c){
        $redis = \org\RedisLib::get_instance();
        $username = $redis->get("sxh_user:id:".intval($d['user_id']).":username");
        $m_info = \think\Loader::model('user/UserInfo', 'model');
        $info = $m_info->getInfo(array('user_id'=>$d['user_id']),$d['user_id'],'name');
        $return = [];
        Db::startTrans(); 
        $accept = \think\Loader::model('common/UserAccepthelp','model');
        $table_accept_now  = 'sxh_user_accepthelp_'.date("Y").'_'.ceil(date("m")/3);
        
        $insertid = $redis->incr('sxh_user_accepthelp:id'); /*接受资助的创建次数*/
        if($insertid == ''){
            $acc = Db::query("select max(id) as a from ".$table_accept_now." limit 1");
            if(count($acc) == 0){
                Db::rollback(); 
                $return['code'] = 0;
                return $return;
            }else{
                $insertid = $acc[0]['a']+1;
            }
        }
        $outgo  = \think\Loader::model('common/UserOutgo','model');
        $table_user = 'sxh_user_account_'.ceil($d['user_id']/1000000);
        switch($c[0]['c']){
            case 1:$type = 7;$cname = '特困社区';break;
            case 2:$type = 8;$cname = '贫穷社区';break;
            case 3:$type = 9;$cname = '小康社区';break;
            case 4:$type = 11;$cname = '富人社区';break;
            case 5:$type = 10;$cname = '德善社区';break;
            case 6:$type = 15;$cname = '大德社区';break;
            default :;
        }
        $insert_accepthelp['id']         = $insertid;
        $insert_accepthelp['type_id']    = 1;
        $insert_accepthelp['money']      = intval($d['money']);
        $insert_accepthelp['used']       = 0;
        $insert_accepthelp['cid']        = $c[0]['c'];
        $insert_accepthelp['user_id']    = intval($d['user_id']);
        $insert_accepthelp['username']   = $username;
        $insert_accepthelp['name']   = $info['name'];
        $insert_accepthelp['cname']      = $cname;
        $insert_accepthelp['status']     = 0;
        $insert_accepthelp['batch']      = strtotime(date("Y-m-d"));
        $insert_accepthelp['ipaddress']  = ip2long($d['ip']);
        $insert_accepthelp['create_time']= time();
        $insert_accepthelp['update_time']= time();
        $id = $accept->insertAccepthelp($insert_accepthelp);
        if($id){
            $s = Db::execute("update ".$table_user." set ".$c[0]['field']." = ".$c[0]['field']." - ".intval($d['money'])." where user_id = ".intval($d['user_id']));
            if($s > 0){
                $insert_outgo['id']          = $redis->incr("sxh_user_outgo:id");
                $insert_outgo['type']        = $type;
                $insert_outgo['user_id']     = intval($d['user_id']);
                $insert_outgo['username']    = $username;
                $insert_outgo['outgo']       = intval($d['money']);
                $insert_outgo['pid']         = $insertid;
                $insert_outgo['info']        = '【App】接受资助';
                $insert_outgo['create_time'] = time();
                $insert_outgo['status']       = 0;
                $insid = $outgo->insertOutgo($insert_outgo);
                if($insid){
                    $provide = $redis->get('sxh_user_accepthelp:userid:'.intval($d['user_id']));
                    if(is_numeric($provide)){/*3秒钟时效，在此期间不处理第二次请求*/
                        Db::rollback(); 
                        $return['err'] = '不能重复提交';
                        $return['code'] = 0;
                        return $return;
                    }
                    Db::commit();
                    $redis->set('sxh_user_accepthelp:userid:'.intval($d['user_id']),1,3);
                    $redis->delDataList('accepthelp',intval($d['user_id']),1);/*黄华盛缓存*/
                    $num = $redis->hget('sxh_userinfo:id:'.intval($d['user_id']),"accepthelp_create_num");
                    if($num == ''){
                        $num =0;
                    }
                    $redis->hset('sxh_userinfo:id:'.intval($d['user_id']),"accepthelp_create_num",($num+1)); /*接受资助的创建次数*/
		    $redis->hset("sxh_userinfo:id:".intval($d['user_id']),$c['0']['field']."_last_changetime",time());  /*钱包变化的最后时间，用于显示获取钱包的最后钱包变化时间*/  
                    $return['code'] = 1;
                    return $return;
                }
            }
        }
        Db::rollback(); 
        $return['code'] = 0;
        return $return;
    }
}


