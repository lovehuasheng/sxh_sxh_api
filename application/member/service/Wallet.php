<?php
// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 童自扬 <2421886569@qq.com> 
// +----------------------------------------------------------------------
// | Function: 接受资助服务层
// +----------------------------------------------------------------------


namespace app\member\service;

use think\Model;
use \think\Request;

class Wallet extends Model {
    
    /**
     * 查看钱包
     * @param type $data
     * @param type $sig
     * @return type
     */
    public static function get_user_wallet($data,$sig) {
        $validator_instance = \think\Loader::validate('Member'); 
        if($validator_instance->scene('get_user_wallet')->check($data) !== true)
        {
            //验证不通过则获取错误信息,并赋值到自身的error_code与error_msg属性
            $validate_result = $validator_instance->getError();
            list($error_msg,$error_code)  = $validate_result;
            return errReturn($error_msg, $error_code);
        }
        //签名比对
        $result = validate_response($data, $sig);
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        //调用业务逻辑
        $logic = \think\Loader::model('Wallet', 'logic');
        return $logic->get_user_wallet($data);
    }
    /*合并社区钱包*/
    public static function together_wallet($data,$sig){
        //签名比对
        $result = validate_response($data, $sig);
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        if(empty($data['check_token'])){
            return errReturn('令牌不能为空！', '400');
        }
        $token = cache('check_token_together_wallet'.$data['user_id']);
        if(!empty($token) && $token == $data['check_token']){
            cache('check_token_together_wallet'.$data['user_id'],null);
            return errReturn('数据不能重复提交！', '400');
        }else{
            cache('check_token_together_wallet'.$data['user_id'],$data['check_token']);
        }
        $Wallet = \think\Loader::model('Wallet', 'model');
        $info = $Wallet->getUserDetail(intval($data['user_id']));
        if($info['mw']>500){
            $gap = $Wallet->getUserGap(intval($data['user_id']));
            if($gap == 1){
                cache('check_token_together_wallet'.$data['user_id'],null);
                return errReturn('请先提取管理奖',1 );
            }
        }
        
        $return = $Wallet->together_wallet($data);
        cache('check_token_together_wallet'.$data['user_id'],null);
        if($return['code'] == 1){
            return errReturn('合并钱包成功',0 );
        }else{
            return errReturn('合并钱包失败',1 );
        }
    }
    
    /**
     * 转让善种子
     * @return type
     */
     public function attorn_activate_currency($data, $sig) {
         
        $validator_instance = \think\Loader::validate('Member'); 
        if($validator_instance->scene('get_user_wallet')->check($data) !== true){
                //验证不通过则获取错误信息,并赋值到自身的error_code与error_msg属性
                $validate_result = $validator_instance->getError();
                list($error_msg,$error_code)  = $validate_result;
                return errReturn($error_msg, $error_code);
        }
        //写入日志
        trace('Walletservice的attorn_activate_currency方法过滤的post参数：' . json_encode($data, JSON_UNESCAPED_UNICODE));
        trace('Walletservice的attorn_activate_currency方法过滤的sig参数：' . $sig);
        
        //签名比对
        $result = validate_response($data, $sig);

        trace('Walletservice的attorn_activate_currency方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        
        //调用业务逻辑
        $logic = \think\Loader::model('Wallet', 'logic');
        return $logic->attorn_activate_currency($data);
     }
     public static function save_accept_action($data, $sig,$ip){
        //签名比对
        if(!is_numeric($data['user_id'])&&$data['user_id']<1){
            return errReturn('user_id参数错误，只能为大于1数字！', '400');
        }
        if(!is_numeric($data['money'])&&$data['money']<100){
            return errReturn('money参数错误，只能为大于等于100数字！', '400');
        }
        $result = validate_response($data, $sig);
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        $c = self::check_accept_action($data);
        
        if($c['code'] == 0){
            return errReturn($c['msg'], '400');
        }
        //token验证
        if(empty($data['check_token'])){
            return errReturn('令牌不能为空！', '400');
        }
        $token = cache('check_token_accept'.$data['user_id']);
        if(!empty($token) && $token == $data['check_token']){
            cache('check_token_accept'.$data['user_id'],null);
            return errReturn('数据不能重复提交！', '400');
        }else{
            cache('check_token_accept'.$data['user_id'],$data['check_token']);
        }
        $data['ip'] = $ip;
        $m = \think\Loader::model('Wallet', 'model');
        $r = $m->doSaveAccept($data,$c['data']);
        if($r['code'] == 1){
            return errReturn('接受资助成功',0 );
        }else{
            cache('check_token_accept'.$data['user_id'],null);
            return errReturn('接受资助失败',0 );
        }
        return $return;
     }
     /*是否符合接受资助的条件*/
     private static function check_accept_action($d){
         $return = [];
         //金额大于500
        if(intval($d['money'])<500){
            $return['code'] = 0;
            $return['msg']  = '接受资助金额必须大于500';
            return $return;
        }
        if(intval($d['money'])%100){
            $return['code'] = 0;
            $return['msg']  = '接受资助金额必须必须是规定金额的倍数';
            return $return;
        }
        /*获取用户的二级密码，特困会员，激活状态，审核状态，黑名单状态*/
        $user = \think\Loader::model('Wallet', 'model');
        $info = $user->getUserDetail(intval($d['user_id']));
        
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
        if($info['id'] != ''){
            $return['code'] = 0;
            $return['msg']  = '账户处于黑名单';
            return $return;
        }
        if($info['security']){
            $pwd = set_password(md5(trim($d['password'])));
            $pwd_sed = set_password(md5(trim($d['password'])),$info['security']);/*登录密码*/
        }else{
            $pwd = set_old_password(trim($d['password']));
            $pwd_sed = set_old_password(trim($d['password']));/*登录密码*/
        }
        if($pwd !=$info['secondary_password'] ){
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
        if(intval($d['money']) > ( intval($info['pw'])+intval($info['nw'])+intval($info['cw'])+intval($info['kw'])+intval($info['ww'])+intval($info['bkw']) )){
            $return['code'] = 0;
            $return['msg']  = '钱包余额不足';
            return $return;
        }
        /*用户是否有过挂单*/
        $provide = $user->getUserProvide(intval($d['user_id']));
        if($provide == 0){
            $return['code'] = 0;
            $return['msg']  = '至少完成一笔提供资助，才能接受资助';
            return $return;
        }
        /*新需求，挂单未完成的不允许接受资助*/
        $provide_not_finish = $user->getUserProvideFinish(intval($d['user_id']));
        if($provide_not_finish == 0){
            $return['code'] = 0;
            $return['msg']  = '您有未完成的提供资助';
            return $return;
        }
        /*是否有未完成的接受资助*/
        $accept = $user->getUserAccept(intval($d['user_id']));
        if($accept == 0){
            $return['code'] = 0;
            $return['msg']  = '有未完成的接受资助';
            return $return;
        }
        /*是否有提取管理奖*//*尚未提取管理奖*/
        if($info['mw']>500){ 
            $gap = $user->getUserGap(intval($d['user_id']));
            if($gap == 1){
                $return['code'] = 0;
                $return['msg']  = '请先提取管理奖';
                return $return;
            }
        }
        $sum =  intval($info['pw'])+intval($info['nw'])+intval($info['cw'])+intval($info['kw'])+intval($info['ww'])+intval($info['bkw']) ;
        $arr[1]['k'] = intval($info['pw']);$arr[1]['sort'] = 6;$arr[1]['c'] = 1;$arr[1]['field'] = 'poor_wallet';
        $arr[2]['k'] = intval($info['nw']);$arr[2]['sort'] = 5;$arr[2]['c'] = 2;$arr[2]['field'] = 'needy_wallet';
        $arr[3]['k'] = intval($info['cw']);$arr[3]['sort'] = 4;$arr[3]['c'] = 3;$arr[3]['field'] = 'comfortably_wallet';
        $arr[4]['k'] = intval($info['ww']);$arr[4]['sort'] = 3;$arr[4]['c'] = 4;$arr[4]['field'] = 'wealth_wallet';
        $arr[5]['k'] = intval($info['kw']);$arr[5]['sort'] = 2;$arr[5]['c'] = 5;$arr[5]['field'] = 'kind_wallet';
        $arr[6]['k'] = intval($info['bkw']);$arr[6]['sort'] = 1;$arr[6]['c'] = 6;$arr[6]['field'] = 'big_kind_wallet';
        rsort($arr);
        $return ['data']    = $arr;
        $return['code']    = 1;
        if($arr[1]['k']>0){
            $return['code']    = 0;
            $return['msg']  = '请先合并钱包';
        }
        
        return $return;
     }  

}
