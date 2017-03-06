<?php
/*
 * 提供资助
 */

namespace app\member\service;

use think\Model;
use \think\Request;

class Provide extends Model {
    /*提供资助的*/
    public static function save_provide_action($data, $sig, $ip){
        $cids = [1,2,3,4,5];
        if(!is_numeric($data['cid']) || !in_array($data['cid'],$cids)){
            return errReturn('cid社区参数错误，请选择正确的社区！', '400');
        }
        //签名比对
        if(!is_numeric($data['user_id'])&&$data['user_id']<1){
            return errReturn('user_id参数错误，只能为大于1数字！', '400');
        }
        if(!is_numeric($data['money'])&&$data['money']<1000){
            return errReturn('money参数错误，只能为大于等于1000数字！', '400');
        }
        $result = validate_response($data, $sig);/*参数验证*/
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        $c = self::check_provide_action($data,1);
        if($c['code'] != 1){
            return errReturn($c['msg'], '400');
        }
        //token验证
        if(empty($data['check_token'])){
            return errReturn('令牌不能为空！', '400');
        }
        $token = cache('check_token_provide'.$data['user_id']);
        if(!empty($token) && $token == $data['check_token']){
            cache('check_token_provide'.$data['user_id'],null);
            return errReturn('数据不能重复提交！', '400');
        }else{
            cache('check_token_provide'.$data['user_id'],$data['check_token']);
        }
        $data['c']      = $c['r'];    /*消耗的善心币*/
        $data['ip']     = $ip;        /*用户IP*/
        $data['name']   = $c['name']; /*社区名*/
        $m = \think\Loader::model('Provide', 'model');
        $result = $m->doSaveProvide($data);
        cache('check_token_provide'.$data['user_id'],null);
        if($result['code'] == 0){
            $return['errorCode'] = 400;
            $return['errorMsg']  = $result['err'];
            $return['result']    = [];
            return $return;
        }else{
            /*短信接口处理*/
            $redis = \org\RedisLib::get_instance();
            $phone['phone'] = $redis->hgetUserinfoByID($data['user_id'],'phone');
            if(!empty($phone)){
                $p['phone'] = $phone['phone'];
            }else{
                $return['errorCode'] = 1;
                $return['errorMsg']  = '提供资助成功，无法发送短信，请完善个人资料';
                $return['result']    = [];
                return $return;
            }
            $p['extra_data ']['user_id'] = $data['user_id'];
            $p['extra_data ']['phone'] = $phone['phone'];
            $p['extra_data ']['title'] = '挂单扣除善心币';
            $p['extra_data ']['ipaddress']    = $ip;
            $p['extra_data ']['valid_time'] = 0;
            $p['extra_data ']['create_time'] = time();
            $p['extra_data ']['update_time'] = time();
            $p['content'] = "挂单扣除".$data['c']."个善心币";
            $redis = \org\RedisLib::get_instance();
            $redis->lPush('sxh_user_sms', json_encode($p));
            $return['errorCode'] = 0;
            $return['errorCode'] = 0;
            $return['errorMsg']  = '提供资助成功';
            $return['result']    = [];
            return $return;
        }
    }
    /*提供资助函数的善心币消耗*/
    public static function sel_provide_action($data, $sig){
        $cids = [1,2,3,4,5];
        if(!is_numeric($data['cid']) || !in_array($data['cid'],$cids)){
            return errReturn('cid社区参数错误，请选择正确的社区！', '400');
        }
        //签名比对
        if(!is_numeric($data['user_id'])&&$data['user_id']<1){
            return errReturn('user_id参数错误，只能为大于1数字！', '400');
        }
        $result = validate_response($data, $sig);
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        
        $c = self::check_provide_action($data,0);
        $token = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        if($c['code'] == 0){
            return errReturn($c['msg'], '400');
        }
        if($c['code'] == 3){
            $return['errorCode'] = -100;
            $return['errorMsg']  = $c['msg'];
            $return['result']    = set_aes_param(['g_currency'=>$c['gc'],'consume'=>$c['r'],'flag'=>$c['flag'],'check_token'=>$token]);
            return $return;
        }
        
        $return['errorCode'] = 0;
        $return['errorMsg']  = '请求成功';
        $return['result']    = set_aes_param(['g_currency'=>$c['gc'],'consume'=>$c['r'],'flag'=>$c['flag'],'check_token'=>$token]);
        return $return;
    }
    /*挂单条件审核，$s=0,预览消耗善心币，$s=1 挂单检测*/
    private static function check_provide_action($d,$s = 1){
        if($s == 1){
            if(intval($d['money'])%100 ){
                $return['code'] = 0;
                $return['msg']  = '提供资助金额必须必须是规定金额的倍数';
                return $return;
            }
        }
        /*获取用户的关于此次挂单二级密码，特困会员，激活状态，审核状态，黑名单状态,以及其他基本信息*/
        $user = \think\Loader::model('Provide', 'model');
        $info = $user->getUserDetail(intval($d['user_id']),intval($d['cid']));
        if(empty($info)){
            $return['code'] = 0;
            $return['msg']  = '用户不存在';
            return $return;
        }
        if($info['status'] != 1){
            $return['code'] = 0;
            $return['msg']  = '账户未激活';
            return $return;
        }
        if($info['verify'] != 2){
            $return['code'] = 0;
            $return['msg']  = '账户未审核';
            return $return;
        }
        if($info['bid'] != ''){
            $return['code'] = 0;
            $return['msg']  = '账户处于黑名单';
            return $return;
        }
        if($s == 1 ){
            if(intval($d['cid']) == 4 && $info['manage_wallet']<100000){
                $return['code'] = 0;
                $return['msg']  = '富人区提供资助金额管理奖必须大于100000';
                return $return;
            }
            if(intval($d['cid']) == 5 && $info['manage_wallet']<250000){
                $return['code'] = 0;
                $return['msg']  = '德善区提供资助金额管理奖必须大于250000';
                return $return;
            }
            if(intval($d['cid']) == 5 && $d['money'] > 2*$info['manage_wallet']){
                $return['code'] = 0;
                $return['msg']  = '德善区提供资助金额必须为管理奖的2倍';
                return $return;
            }
            if(intval($d['money'])%$info['multiple']){
                $return['code'] = 0;
                $return['msg']  = '提供资助金额必须必须是规定金额的倍数';
                return $return;
            }
            if($info['security']){
                $pwd     = set_password(md5(trim($d['password'])));/*二级密码*/
                $pwd_sed = set_password(md5(trim($d['password'])),$info['security']);/*登录密码*/
            }else{
                $pwd     = set_old_password(trim($d['password']));/*二级密码*/
                $pwd_sed = set_old_password(trim($d['password']));/*登录密码*/
            }
            if($pwd != $info['secondary_password']){
                $return['code'] = 0;
                $return['msg']  = '二级密码错误';
                return $return;
            }
            /*登录密码与二级密码不能一致*/
            if($pwd == $pwd_sed){
                $return['code'] = 0;
                $return['msg']  = '二级密码与登陆密码不能一致!';
                return $return;
            }
        }
        if($info['special'] == 1){
            $return['code'] = 0;
            $return['msg']  = '管理员不得参与挂单';
            return $return;
        }
        /*if($info['is_poor']!=0 && $info['cid']!=1){
            $return['code'] = 0;
            $return['msg']  = '特困会员只能挂特困社区';
            return $return;
        }*/
        if($info['is_poor']==0 && $info['cid']==1){
            $return['code'] = 0;
            $return['msg']  = '您不是特困会员，不能在特困区挂单';
            return $return;
        }
        if( $s == 1){
                if((intval($d['money'])< $info['ls'] || intval($d['money']) > $info['ts'])){
                     $return['code'] = 0;
                     $return['msg']  = '挂单的金额不符合社区要求范围';
                     return $return;
                 } 
        }
        $r = $info['nc'];
        /*是否有未完成的挂单*/ 
        $p = $user->getLastProvide(intval($d['user_id']));
        if($p['sign'] == 0){
            $return['code'] = 0;
            $return['msg']  = '您还有尚未完成的挂单';
            return $return;
        }
        /*挂单扣除的善心币是否翻倍*/
        $a = $user->getGccount(intval($d['user_id']));
        $flag = 0;
        if($a == 1){  
            $r = 2*$r;
            $flag = 1;
        }
        if($r > $info['gc']){
            $return['code'] = 3;
            $return['msg']  = '善心币余额不足';
            $return['r']    = $r;
            $return['flag']    = $flag;
            $return['gc']   = intval($info['gc']);
            return $return;
        }
        $return['code'] = 1;
        $return['r']    = $r;/*善心币*/
        $return['flag']    = $flag;/*善心币翻倍*/
        $return['gc']   = intval($info['gc']);
        $return['name'] = $info['name'];/*社区名*/
        $return['is_poor'] = $info['is_poor'];
        return $return;
    }
}

