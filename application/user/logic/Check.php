<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\user\logic;
use think\Model;
use think\Db;
use think\Request;
/**
 * Description of Check
 *
 * @author shanhubao
 */
class Check extends Model{
    /**
     * 获取查看手机收款人验证码
     * @author huanghuasheng
     */
    public function getPhoneCode($data){
        
        //验证参数ID
        if(cache('code_time'.$data['user_id'].$data['phone'])){
            return errReturn('您的验证码刚刚发送，请稍后再试!', -501);
        }
        if(!isset($data['user_id']) ||  intval($data['user_id']) === 0) {
            return errReturn('参数错误!', -501);
        }
        if(empty($data['phone']) || !preg_match('/^1[34578]\d{9}$/', $data['phone'])){
            return errReturn('手机号码为空或格式有误!', -501);
        }
        $redis = \org\RedisLib::get_instance();
        $phone = $redis->hgetUserinfoByID($data['user_id'],'phone');
        if($phone){
            $data['phone'] = $phone;
        }else{
            return errReturn('用户的手机号码为空!', -501);
        }
        
        $code = get_rand_num(5);
        $sdata = array();
        $sdata['extra_data']['user_id'] = $data['user_id'];
        $sdata['extra_data']['phone'] = $data['phone'];
        if(isset($data['type']) && $data['type']==1){
            $sdata['extra_data']['title'] = '验证码短信';
        }else{
            $sdata['extra_data']['title'] = '查看收款人验证码';
        }
        $sdata['extra_data']['code'] = $code;
        $sdata['extra_data']['status'] = 1;
        $ip = Request::instance()->ip();
        $sdata['extra_data']['ip_address'] = ip2long($ip);
        $sdata['extra_data']['valid_time'] = 300;
        $sdata['extra_data']['create_time'] = time();
        $sdata['extra_data']['update_time'] = time();
        $sdata['phone'] = $data['phone'];
        if(isset($data['type']) && $data['type']==1){
            $sdata['content'] = "您好，你的账号正在撤销订单操作，请确认。如有疑问，请登录账号查看或联系客服。您的验证码是".$code;
        }else{
            $sdata['content'] = "您好，你正在查看收款人信息,为了账户信息安全,切勿泄露。您的验证码是".$code;
        }
        $redis->lPush('sxh_user_sms', json_encode($sdata));//LPUSH
        cache('code'.$data['user_id'].$data['phone'],$code,600);
        cache('code_time'.$data['user_id'].$data['phone'],120,120);
        return errReturn('验证码发送成功！', 0);

    }
    /**
     * 验证二级密码
     * @param type $data
     * @return array
     */
    public function checkUserPassword($data){
        //根据post数据，获取用户信息
        if(empty($data['user_id']) || empty($data['password'])){
            return errReturn('提交信息不全',-1);
        }
        $userr = array(
            'id'      => htmlspecialchars(urldecode($data['user_id'])),
        );
        $model = \think\Loader::model('User' , 'model');
        $user = $model->getUser($data['user_id'],$userr,'id,security,secondary_password');
        if(!$user){
            return errReturn('用户ID不存在',-1);
        }
        if($user['security']){
            $pwd = set_password(md5(htmlspecialchars(urldecode($data['password']))));
        }else{
            $pwd = set_old_password(htmlspecialchars(urldecode($data['password'])));
        }
        if($user['secondary_password'] != $pwd) {
            return errReturn('二级密码错误',-1);
        }else{
            return errReturn('验证成功',0);
        }
    }
    /**
     * 手机验证码验证
     * @param type $data
     * @return array
     */
    public function checkPhoneCode($data){
        if(!preg_match('/^1[34578]\d{9}$/', $data['phone'])){
            return errReturn('手机号码格式有误!', -501);
        }
        $redis = \org\RedisLib::get_instance();
        $data['phone'] = $redis->hgetUserinfoByID($data['user_id'],'phone');
        $code = cache('code'.$data['user_id'].$data['phone']);
        if(empty($code) || $code != $data['verify']){
            return errReturn('验证码不正确或已过期！' , 100);
        }else{
            cache('code'.$data['user_id'].$data['phone'],null);
            return errReturn('验证成功！' , 0);
        }
    }
    /**
     * 验证用户名的唯一
     * @param type $data
     * @return array
     */
    public function checkUserName($data){
        if(!preg_match('/^[0-9a-zA-Z]{6,16}$/', $data['username'])){
            return errReturn('用户名须为6-16位字母或数字!', -501);
        }
        //查帐户是否已被注册
        $redis = \org\RedisLib::get_instance();
        $data['username'] = strtolower($data['username']);
        if($redis->sismemberFieldValue('sxh_user:username' , $data['username'])) {
            return errReturn('帐户已经被注册！' , 100);
        }else{
            $user_relation_model = \think\Loader::model("UserRelation");
            $relation_result = $user_relation_model->getUserRelationByID(['username'=>$data['username']] , 'user_id');
            if(!empty($relation_result)){
                return errReturn('帐户已经被注册！' , 100);
            }
            return errReturn('用户名可用', 0);
        }
    }
    
    /**
     * 根据用户名获取姓名
     * @param type $data
     * @return array
     */
    public function findUserName($data){
        if(!preg_match('/^[0-9a-zA-Z]{1,16}$/', $data['username'])){
            //return errReturn('用户名为6-16位字母或数字!', -501);
        }
        $redis = \org\RedisLib::get_instance();
        $data['username'] = strtolower($data['username']);
        $user_id = $redis->getUserId($data['username']);
        if($user_id){
            $model = \think\Loader::model('UserInfo' , 'model');
            $res = $model->getInfo(array('user_id'=>$user_id),$user_id,'name');
            if($res){
                return errReturn('请求成功', 0,set_aes_param(array('name'=>$res['name'])));
            }else{
                return errReturn('用户名不存在!', -501);
            }
        }else{
            return errReturn('用户名不存在!', -501);
        }
            
    }
    /**
     * 个人中心信息刷新
     */
    public function getCenterInfo($data){
        //验证参数ID
        if(!isset($data['user_id']) ||  intval($data['user_id']) === 0) {
            return errReturn('参数错误!', -501);
        }
        $model = \think\Loader::model('User' , 'model');
        $user = $model->getUser($data['user_id'],array('id'=>$data['user_id']),'id,verify');
        if(!$user){
            return errReturn('请求信息错误',-22);
        }
        $arr = array('未审核','未通过','已通过');
        $user['verify'] = $arr[$user['verify']];
        return errReturn('请求成功',0,set_aes_param($user));
    }
    /*异常登录*/
    public function sendLoginCode($data){
        if(empty($data['username'])){
            return errReturn('输入的用户名为空', -501);
        }
        $data['username'] = strtolower($data['username']);
        $redis = \org\RedisLib::get_instance();
        $user_id = $redis->getUserId($data['username']);
        if(!$user_id){
            return errReturn('用户名不存在', -501);
        }
        $data['phone'] = $redis->hgetUserinfoByID($user_id,'phone');
        if(cache('code_time'.$user_id.$data['phone'])){
            return errReturn('您的验证码刚刚发送，请稍后再试!', -501);
        }
        $code = get_rand_num(5);
        $sdata = array();
        $sdata['extra_data ']['user_id'] = 1;
        $sdata['extra_data ']['phone'] = $data['phone'];
        $sdata['extra_data ']['title'] = '异常登录验证码';
        $sdata['extra_data ']['code'] = $code;
        $sdata['extra_data ']['status'] = 1;
        $ip = Request::instance()->ip();
        $sdata['extra_data ']['ip_address'] = ip2long($ip);
        $sdata['extra_data ']['valid_time'] = 300;
        $sdata['extra_data ']['create_time'] = time();
        $sdata['extra_data ']['update_time'] = time();
        $sdata['content'] = "您的验证码是".$code."，正在进行异常登录验证。";
        $sdata['phone'] = $data['phone'];
        $redis->lPush('sxh_user_sms', json_encode($sdata,JSON_UNESCAPED_UNICODE));//LPUSH
        cache('code'.$user_id.$data['phone'],$code,600);
        cache('code_time'.$user_id.$data['phone'],120,120);
        return errReturn('验证码发送成功！', 0);
    }
    
    
}
