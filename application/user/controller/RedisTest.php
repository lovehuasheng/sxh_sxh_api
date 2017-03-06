<?php

//
//    app 2.0 测试
//
namespace app\user\controller;

use app\common\controller\Base;
use think\Db;
class RedisTest{
    private static $page_num = 1000;
    private static $total = 1020273;
    
    //设置用户的redis缓存信息
    public function hset_redis(){
        $redis = \org\RedisLib::get_instance();
        $redis->hsetUserinfoByID(input('post.user_id'),input('post.field'),input('post.val'));
    }
    public function test(){
        cache('huanghuasheng','onedaynotsleep',3600);
        echo cache('huanghuasheng');exit;
    }
    //查看用户的redis信息
    public function hget_redis(){
        $redis = \org\RedisLib::get_instance();
        $res = $redis->hGetAll('sxh_userinfo:id:'.input('post.user_id'));
        dump($res);exit;
    }
    //根据用户名获取用户ID
    public function get_redis_username(){
        $redis = \org\RedisLib::get_instance();
        echo $redis->getUserId(input('post.username'));
    }
    /*
     * 根据公司业务结算时间，生成week_time表
     * @Auth：huanghuasheng
     */
    public function pullWeekTime(){exit;
        $data = array();
        $start_time = 1488124799+1;
        $end_time = $start_time+60*60*24*7-1;
        //结束时间标志
        $flag = 1549421356;
        //这个是一个月的第几周标识，即一个月的第几周
        $week_num = 1;
        $ym_flag = date('Y/m',$end_time);
        while($end_time<$flag){
            $ym = date('Y/m',$end_time);
            if($ym!=$ym_flag){
                $ym_flag = $ym;
                $week_num = 1;
            }
            $temp = array();
            $temp['start_time'] = $start_time;
            $temp['end_time'] = $end_time;
            $temp['time_info'] = $ym;
            $temp['week_num'] = $week_num;
            $data[] = $temp;
            $start_time = $end_time+1;
            $end_time = $start_time+60*60*24*7-1;
            $week_num++;
        }
        $res = db('week_time')->insertAll($data);
        if($res){
            errReturn(true,'数据插入成功');
        }else{
            errReturn(false,'数据插入失败');
        }  
    }
    public function findWeek(){
        $res = db('week_time')->where(array('end_time'=>1488729599))->select();
        dump($res);
        $res = db('week_time')->order('id desc')->limit(1)->find();
        dump($res);exit;
    }
    public function delectWeek(){
        $res = db('week_time')->where('id','>=',input('post.id'))->delete();
    }
    /**
     * 初始化自增ID
     */
    public function icreID(){
        $arr = array('sxh_user','sxh_user_accepthelp','sxh_user_provide','sxh_user_matchhelp','sxh_user_outgo','sxh_user_income');
        $redis = \org\RedisLib::get_instance();
        foreach($arr as $v){
            $sql = "select id from $v order by id desc limit 1";
            $id = Db::query($sql);
            $redis->set($v.':id',$id[0]['id']);
        }
    }

    /**
     * 统计企业用户的激活数量
     * 
     */
    public function companyActiveNum(){
        $redis = \org\RedisLib::get_instance();
        $sql = "select count(*) as sum from sxh_user where is_commpany=1 and status=1";
        $sum = Db::query($sql);
        $redis->set('company_active_number',$sum[0]['sum']);
    }
    public function modPhone(){
        $redis = \org\RedisLib::get_instance();
        $user_name = input('post.username');
        $user_id = $redis->getUserId($user_name);
        $phone = input('post.phone');
        $redis->hsetUserinfoByID($user_id,'phone',$phone);
    }
    /*
zgl1798       | 1005405 |
| zrm5566       | 1006744 |
| zxh548816      | 1008067 |
| zy660214        | 1026653 |
| zz1970         | 1001284

sf888888       | 1001711 |
| sxh1741       | 1020433 |
| syelj         | 1001978

xms9620       | 1023055
    select * from sxh_user_2 username='zgl1798' and id=1005405;
    select * from sxh_user_2 username='zrm5566' and id=1006744;
    select * from sxh_user_2 username='zxh548816' and id=1008067;
    select * from sxh_user_2 username='zy660214' and id=1026653;
    select * from sxh_user_2 username='zz1970' and id=1001284;
    select * from sxh_user_2 username='sf888888' and id=1001711;
    select * from sxh_user_2 username='sxh1741' and id=1020433;
    select * from sxh_user_2 username='syelj' and id=1001978;
    select * from sxh_user_2 username='xms9620' and id=1023055;
     select * from sxh_user_1 username='lijun12' and id=646436;
     select * from sxh_user_1 username='fps3333' and id=643710;
     select * from sxh_user_1 username='csx772' and id=642798;
     select * from sxh_user_1 username='gsj6662' and id=635904;
     select * from sxh_user_1 username='yzs1209' and id=635654;
     select * from sxh_user_1 username='ysx9999' and id=593640;
     select * from sxh_user_1 username='shenli168' and id=626246;

lijun12         | 646436
fps3333       | 643710
csx772        | 642798
gsj6662       | 635904
yzs1209       | 635654
ysx9999       | 593640
shenli          | 626246 
     select * from sxh_user_1 username='lijun12' and id=646436;
     select * from sxh_user_1 username='fps3333' and id=643710;
     select * from sxh_user_1 username='csx772' and id=642798;
     select * from sxh_user_1 username='gsj6662' and id=635904;
     select * from sxh_user_1 username='yzs1209' and id=635654;
     select * from sxh_user_1 username='ysx9999' and id=593640;
     select * from sxh_user_1 username='shenli168' and id=626246;
     
     zgl1798--1005405zrm5566--1006744zxh548816--1008067zy660214--1026653zz1970--1001284sf888888--1001711
     * sxh1741--1020433syelj--1001978xms9620--1023055lijun12--646436fps3333--643710csx772--642798gsj6662--635904yzs1209--635654ysx9999--593640shenli--626246
     */

    public function kongge(){
        $arr = array('z g l 1798','z r m 5566','z x h548816','z y660214','z z 1970','s f 888888',
            's x h 1741','s ye l j','x m s 9620','li jun12','f p s 3333','c s x 772','g s j 6662','y z s 1209','y s x 9999','shen li');
        $redis = \org\RedisLib::get_instance();

        //$redis->multi();
        foreach($arr as $v){
            /*绑定用户名与用户ID*/
            $user_id = $redis->getUserId($v);//sxh_user:username:z g l 1798:id 3
          
            if($user_id){
                $redis->set('sxh_user:username:'.$v.':id','');
                $redis->set('sxh_user:id:'.$user_id.':username','');
                $redis->srem('sxh_user:username',$v);
                $username = preg_replace("/[^a-zA-Z0-9]/","",$v);
                echo $username,'--',$user_id;
                $redis->set('sxh_user:username:'.$username.':id',$user_id);
                $redis->set('sxh_user:id:'.$user_id.':username',$username);
                /*保存集合信息*/
                $redis->saddField('sxh_user:username',$username);
            }
                
        }
        //$redis->exec();
        usleep(10);

        echo 'success';
    }
    public function tempUserinfo(){
        set_time_limit(0);
        $redis = \org\RedisLib::get_instance();
        $page_num = self::$page_num;
        $total = self::$total;
        $page = ceil($total/$page_num);
        for($i=1;$i<=$page;$i++){
            $offset = ($i-1)*$page_num;
            $sql = "select user_id,user_name from redis limit $offset,$page_num";
            $info = Db::query($sql);
            if(!empty($info)){
                $redis->multi();
                foreach($info as $v){
                    $flag = preg_match('/[A-Z]/', $v['user_name']);
                    if($flag && !empty($v['user_name'])){
                        /*绑定用户名与用户ID*/
                        $redis->set('sxh_user:username:'.$v['user_name'].':id',null);
                        $redis->set('sxh_user:id:'.$v['user_id'].':username',null);
                        $redis->srem('sxh_user:username',$v['user_name']);
                        $v['user_name'] = strtolower($v['user_name']);
                        $redis->set('sxh_user:username:'.$v['user_name'].':id',$v['user_id']);
                        $redis->set('sxh_user:id:'.$v['user_id'].':username',$v['user_name']);
                        /*保存集合信息*/
                        $redis->saddField('sxh_user:username',$v['user_name']);
                    }
                }
                $redis->exec();
                usleep(10);
                echo "第{$i}页";
            }
        }
        echo 'success';
    }
    /**
     * 极终函数
     * 
     */
    public function saveUserinfo(){exit;
        //更新自增ID
        //$this->icreID();
        //统计企业激活数量
        //$this->companyActiveNum();
        set_time_limit(0);
        $redis = \org\RedisLib::get_instance();
        $page_num = self::$page_num;
        $total = self::$total;
        $page = ceil($total/$page_num);
        for($i=1;$i<=$page;$i++){
            $offset = ($i-1)*$page_num;
            $sql = "select * from redis limit $offset,$page_num";
            $info = Db::query($sql);
            if(!empty($info)){
                $redis->multi();
                foreach($info as $v){
                    /*绑定用户名与用户ID*/
                    $redis->set('sxh_user:username:'.$v['user_name'].':id',$v['user_id']);
                    $redis->set('sxh_user:id:'.$v['user_id'].':username',$v['user_name']);
                    
                    /*保存集合信息*/
                    if($v['user_name']){
                        $redis->saddField('sxh_user:username',$v['user_name']);
                    }
                    if($v['is_company'] != 1){
                        if($v['phone']){
                            $redis->saddField('sxh_user_info:phone',$v['phone']);
                        }
                        if($v['alipay_account']){
                            $redis->saddField('sxh_user_info:alipay_account',$v['alipay_account']);
                        }
                        if($v['weixin_account']){
                            $redis->saddField('sxh_user_info:weixin_account',$v['weixin_account']);
                        }
                        if($v['bank_account']){
                            $redis->saddField('sxh_user_info:bank_account',$v['bank_account']);
                        }
                        if($v['card_id']){
                            $redis->saddField('sxh_user_info:card_id',$v['card_id']);
                        }
                    }
                    
                    /*利用哈希保存用户信息*/
                    $user_id = $v['user_id'];
                    $data = array();
                    $data['provide_num'] = $v['provide_num'];
                    $data['provide_current_id'] = $v['provide_current_id'];
                    $data['provide_manage_id'] = $v['provide_manage_id'];
                    $data['provide_current_money'] = $v['provide_current_money'];
                    $data['phone'] = $v['phone'];
                    $data['provide_match_time'] = $v['provide_match_time'];
                    $data['accept_match_time'] = $v['accept_match_time'];
                    $data['provide_last_community_id'] = $v['provide_last_community_id'];
                    $data['provide_community_1_count'] = $v['provide_community_1_count'];
                    $data['provide_community_2_count'] = $v['provide_community_2_count'];
                    $data['provide_community_3_count'] = $v['provide_community_3_count'];
                    $data['provide_community_4_count'] = $v['provide_community_4_count'];
                    $data['provide_community_5_count'] = $v['provide_community_5_count'];
                    $data['provide_community_6_count'] = $v['provide_community_6_count'];
                    $data['provide_community_7_count'] = $v['provide_community_7_count'];
                    $data['provide_create_num'] = $v['provide_create_num'];
                    $data['provide_finish_num'] = $v['provide_finish_num'];
                    $data['accepthelp_create_num'] = $v['accepthelp_create_num'];
                    $data['accepthelp_finish_num'] = $v['accepthelp_finish_num'];
                    $data['poor_wallet_last_changetime'] = $v['poor_wallet_last_changetime'];
                    $data['needy_wallet_last_changetime'] = $v['needy_wallet_last_changetime'];
                    $data['comfortably_wallet_last_changetime'] = $v['comfortably_wallet_last_changetime'];
                    $data['wealth_wallet_last_changetime'] = $v['wealth_wallet_last_changetime'];
                    $data['kind_wallet_last_changetime'] = $v['kind_wallet_last_changetime'];
                    $data['big_kind_wallet_last_changetime'] = $v['big_kind_wallet_last_changetime'];
                    $data['accepthelp8_create_num'] = 0;
                    $data['accepthelp8_finish_num'] = 0;
                    $redis->hMset('sxh_userinfo:id:'.$user_id,$data);
                }
                $redis->exec();
                usleep(10);
                echo "第{$i}页";
            }
        }
        echo 'success';
    }

    
}
