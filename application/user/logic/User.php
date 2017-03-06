<?php

namespace app\user\logic;
use think\Model;
use think\Request;

class User extends Model
{
    /** 注册逻辑
     * @param   $data  service层接收的post数据
     * @return  json
     * @author 江雄杰
     * @time    2016-10-06
     */
    public function register($data) {
        //验证码
        $data['username'] = strtolower($data['username']);
        $data['referee_name'] = strtolower($data['referee_name']);
        $code = cache('code'.$data['phone']);
        if(empty($code) || $code != $data['verify']){
            return errReturn('验证码不正确或已过期！' , 100);
        }
        $validate = \think\Loader::validate('User');//验证类
        if (!$validate->check($data)) {
            $error = $validate->getError();
            //var_dump($error);
            list($errorMsg, $errorCode) = $error;
            return errReturn($errorMsg, $errorCode);
        } else {
            $redis = \org\RedisLib::get_instance();
            
            //查帐户是否已被注册
            if($redis->sismemberFieldValue('sxh_user:username' , $data['username'])) {
                return errReturn('帐户已经被注册！' , 100);
            }
            //查询手机号是否已被注册（手机，微信号，支付宝号）
            $phone_result           = $redis->sismemberFieldValue('sxh_user_info:phone' , $data['phone']);
            $alipay_account_result  = $redis->sismemberFieldValue('sxh_user_info:alipay_account' , $data['phone']);
            $weixin_account_result  = $redis->sismemberFieldValue('sxh_user_info:weixin_account' , $data['phone']);
            if($phone_result || $alipay_account_result || $weixin_account_result){
                return errReturn('手机号已经被注册！' , 101);
            }
            //查询推荐人是否存在
            if(!$redis->sismemberFieldValue('sxh_user:username' , $data['referee_name'])) {
                return errReturn('推荐人信息有误！' , 400);
            }
            //查询推荐的人的信息
            $model = \think\Loader::model('UserInfo' , 'model');
            $m_user = \think\Loader::model('User' , 'model');
            $referee_id = $redis->getUserId($data['referee_name']);
            $referee_result = $model->getInfo(array('user_id'=>$referee_id),$referee_id,'name');
            $rename = $referee_result['name'];
            /** 管理员不能作为推荐人 **/
//            if(in_array($referee_id , array(1))) {
//                return errReturn('推荐人信息有误！' , 401);
//            }
            //新增数据（事务）
            $salt = get_rand_num(6);
            //获取 redis 自增id
            $redis_id = $redis->incr('sxh_user:id');
             //调用业务逻辑,生成token
            $logic = \think\Loader::model('Cloud', 'logic');
            $user_token = $logic->get_user_token($redis_id,$data['name'],'');
            
            $this->startTrans();
            try{
                $time   = time();
                $ipp = Request::instance()->ip();
                $ip     = ip2long($ipp);
                //新增用户信息
                $user_data = [
                    'id'                    => $redis_id,
                    'username'          => $data['username'],
                    'password'          => set_password($data['password'],$salt),
                    'secondary_password' => set_password(md5($data['password'])),
                    'last_login_time'   => $time,
                    'last_login_ip'     => $ip,
                    'status'            => 0,
                    'verify'            => 0,
                    'create_time'       => $time,
                    'update_time'       => $time,
                    'security'       => $salt,
                    'user_token'    => $user_token
                ];
                $user_id = $m_user->insertUserGetId($redis_id,$user_data);
                //更新belong表
                //$belong = $m_user->insert_user_belong($redis_id,$referee_id);
                $tel_number = '189'.str_pad($redis_id, 9,'8');
                //初始化用户详细信息
                $userinfo_data = [
                    'user_id'            => $redis_id,
                    'username'           => $data['username'],
                    'name'              => $data['name'],
                    'phone'             => $data['phone'],
                    'referee'           => $data['referee_name'],
                    'referee_id'         => $referee_id,//推荐人ID
                    'referee_name'      =>$rename,
                    'tel_number'         => $tel_number,
                    'create_time'        => $time,
                    'update_time'        => $time,
                ];
                $userinfo = $model->insertUserinfoGetId($redis_id,$userinfo_data);
                
                //初始化帐户表
                $user_account_model = \think\Loader::model('UserAccount' , 'model');
                $account_data = [
                    'user_id'                => $redis_id,
                    'create_time'            => $time,
                    'update_time'            => $time,
                ];
                $user_account = $user_account_model->insertAccountGetId($redis_id,$account_data);
                
                //初始化relation关系（查找所有父级）
                $relation_model = \think\Loader::model('UserRelation' , 'model');
                $re_info = $relation_model->getUserRelationByID(array('user_id'=>$referee_id),'full_url,a,b,c');
                //合并URL
//                if($re_info['url']){
//                    $url_array = explode(',', trim($re_info['url'],','));
//                    $len = count($url_array);
//                    if($len>5){
//                        $p_url = ','.$url_array[$len-4].','.$url_array[$len-3].','.$url_array[$len-2].','.$url_array[$len-1].','.$redis_id.',';
//                    }else{
//                        $p_url = $re_info['url'].$redis_id.',';
//                    }
//                }else{
//                    $p_url = ','.$referee_id.','.$redis_id.',';
//                }
                if($re_info['full_url']){
                    $full_url = $re_info['full_url'].$redis_id.',';
                    $p_len = count(explode(',',trim($re_info['full_url'])));
                }else{
                    $full_url = ','.$referee_id.','.$redis_id.',';
                    $p_len = 2;
                }
                
                //添加关系表user_relation
                $relation_data = [
                    'user_id'        => $redis_id,
//                    'url'           => $p_url,
                    'full_url'     => $full_url,
                    'edi'          => $referee_id,
                    'plevel'      => $p_len,
                    'username'   => $data['username'],
                    'name'       => $data['name'],
                    'create_time'    => $time,
                    'update_time'    => $time,
                    'a'            => $re_info['a'],
                    'b'            => $re_info['b'],
                    'c'            => $re_info['c'],
                ];
                $relation = $relation_model->insertAccount($relation_data);
                //设置验证码为已经使用
                if($user_id && $userinfo && $user_account && $relation) {
                    $this->commit();
                }else{
                    $this->rollback();
                    return errReturn('注册失败！', 500);
                }
                
            } catch (\Exception $e) {
                $this->rollback();
                return errReturn('系统繁忙，请重新操作！', 500);
            }
            //验证码失效
            cache('code'.$data['phone'],null);
            //缓存用户信息
            $redis->multi();
            $redis->setUsernameByID($redis_id,$data['username']);
            $redis->setUserPhoneId( $data['phone'] ,  $redis_id);
            $redis->setUserId( $data['username'] ,  $redis_id);
            $redis->saddField( 'sxh_user:username' ,  $data['username']);
            $redis->saddField(  'sxh_user_info:phone' , $data['phone'] );
            $redis->hsetUserinfoByID($redis_id,'phone',$data['phone']);
            $redis->hsetUserinfoByID($redis_id,'provide_num',0);
            $redis->exec();
            //注册成功，发送短信
            $smsinfo['extra_data'] = [
                'user_id'        => $redis_id,
                'phone'         => $data['phone'],
                'title'         => '注册短信',//短信动作id（注册动作）
                'status'        => 1,//短信发送的状态
                'ip_address'     => $ip,
                'create_time'    => $time,
                'update_time'    => $time,
            ];
            $password = $data['password'];
            $account = $data['username'];
            $smsinfo['content'] = '欢迎成为我们的注册会员，您的登录账号：'.$account.'，密码：'.$password.'。推荐人：'.$rename.'，请妥善保管个人信息。';
            $smsinfo['phone'] = $data['phone'];
            $redis->lPush('sxh_user_sms', json_encode($smsinfo));
            //注册成功，返回数据
            return errReturn('注册成功,激活后即可登录', 0 );
        }
    }
    
    
    /** 登录逻辑
     * @param   $data  service层接收的post数据
     * @return  json
     * @author 江雄杰
     * @time    2016-10-06
     */
    public function login($data) {
        //验证类
        $data['username'] = strtolower($data['username']);
        $validate = \think\Loader::validate('Login');
        if (!$validate->check($data)) {
            $error = $validate->getError();
            list($errorMsg, $errorCode) = $error;
            return errReturn($errorMsg, $errorCode);
        } else {
            $time   = time();
            $ipp = Request::instance()->ip();
            $ip     = ip2long($ipp); 
            //根据post数据，获取用户信息
            $redis = \org\RedisLib::get_instance();
            $user_id = $redis->getUserId($data['username']);
            if(!$user_id){
                return errReturn('用户名不存在！', 401);
            }
            //异常登录需要验证码验证方可登录
            if(!isset($data['phone_version']) || empty($data['phone_version'])){
                return errReturn('参数错误！', 401);
            }
            $verify_flag = $redis->hgetUserinfoByID($user_id,'user_need_verify_flag');
            $data['phone'] = $redis->hgetUserinfoByID($user_id,'phone');
            if(!empty($verify_flag) && $verify_flag>0){
                $phone_code = cache('code'.$user_id.$data['phone']);
                if(!isset($data['verify']) || empty($data['verify'])){
                    if($verify_flag==2){
                        return errReturn('密码的错误次数已达上限，需验证码登录' , -998844 );
                    }else if($verify_flag==3){
                        return errReturn('手机标识与以往不一致，需验证码登录' , -998844 );
                    }
                }
                if(!isset($data['verify']) || $phone_code != $data['verify']){
                    return errReturn('验证码错误或已经过期', 401);
                }else{
                    $redis->hsetUserinfoByID($user_id,'user_need_verify_flag',0);
                    if($verify_flag==2){
                        $redis->hsetUserinfoByID($user_id,'user_failed_login_num',0);
                    }else if($verify_flag==3){
                        $redis->hsetUserinfoByID($user_id,'user_login_phone_version',$data['phone_version']);
                    }
                }
            }
            $model = \think\Loader::model('User' , 'model');
            $user = $model->getUser($user_id,array('id'=>$user_id),'id,username,status,verify,last_login_ip,last_login_time,create_time,user_token,security,password');
            if(!$user) {
                return errReturn('用户名信息错误！', 401);
            }
            if($user['security']){
                $pwd = set_password($data['password'],$user['security']);
            }else{
                $pwd = set_old_password($data['password']);
            }
            if($user['password'] != $pwd) {
                $redis->hIncrByUserinfoByID($user_id,'user_failed_login_num',1);
                $failed_num = $redis->hgetUserinfoByID($user_id,'user_failed_login_num');
                if($failed_num>4){
                    $redis->hsetUserinfoByID($user_id,'user_need_verify_flag',2);
                    return errReturn('密码的错误次数已达上限，需验证码登录' , -998844 );
                }
                return errReturn('用户名与密码不匹配！', 401);
            }else{
                $redis->hsetUserinfoByID($user_id,'user_failed_login_num',0);
            }
            if($data['password'] == $data['username'] || $data['password'] == '123456'){
                return errReturn('密码过于简单，请点击"忘记密码"进行修改！' , 401 );
            }
            //手机型号验证，如果手机型号与历史的不一样，则弹出手机验证登录,第一次不进行验证
            $phone_version = $redis->hgetUserinfoByID($user_id,'user_login_phone_version');
            if($phone_version && !empty($phone_version)){
                if($data['phone_version'] != $phone_version){
                    $redis->hsetUserinfoByID($user_id,'user_need_verify_flag',3);
                    return errReturn('手机标识与以往不一致，需验证码登录' , -998844 );
                }
            }else{
                $redis->hsetUserinfoByID($user_id,'user_login_phone_version',$data['phone_version']);
            }
           
            //查激活状态
            if($user['status'] != 1) {
                return errReturn('此帐户尚未激活或已冻结，请先激活或解冻！' , 402 );
            }
            //查看用户是否存在审核了但未通过的情况
            $arr = array('未审核','未通过','已通过');
            $user['verify'] = $arr[$user['verify']];
            
            //登录日志
            $m_info = \think\Loader::model('UserInfo' , 'model');
            $userinfo = $m_info->getInfo(array('user_id'=>$user_id),$user_id,'name,avatar,grade,referee,referee_id,referee_name,tel_number,phone');
            if(!$userinfo){
                return errReturn('用户信息错误！' , 402 );
            }
            $userinfo['avatar'] = $userinfo['avatar'] ? getQiNiuPic($userinfo['avatar']) : '';
            $userinfo['phone'] = $userinfo['phone'] ? $userinfo['phone'] : '';
            //权限
            $member = array_merge($user,$userinfo);
            $member['user_id'] = $member['id'];
            if($userinfo['referee_id']){
                $phone = $redis->hgetUserinfoByID($userinfo['referee_id'],'phone');
                $member['referee_phone'] = $phone ? $phone : '';
            }else{
                $member['referee_phone'] = '';
            }
            $member['enroll_url'] = config('ENROLL_CODE').'/User/Enroll/outenrolllink/UserName/'.$member['username'].'.html';
            
            //保存此次登录时间
            $data = array(
                'last_login_ip'     => $ip,
                'last_login_time'   => $time,
            );
            
            if(empty($member['user_token'])) {
                 //调用业务逻辑
                $logic = \think\Loader::model('Cloud', 'logic');
                $user_token = $logic->get_user_token($member['user_id'],$member['name'],$member['avatar']);
                $member['user_token'] = $user_token;
                $data['user_token'] = $user_token;
            }
            if(empty($member['tel_number'])){
                $member['tel_number'] = '189'.str_pad($member['user_id'], 9,'8');
                $infodata = array();
                $infodata['tel_number'] = $member['tel_number'];
                $m_info->updateUserInfo(array('user_id'=>$member['user_id']),$infodata);
            }
            $str = date('YmdHis').$member['user_id'] . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $member['pass_token'] = md5($str);
            $redis->set('app_user_sign_pass_token'.$user_id,$member['pass_token'],1200);
            $result = $model->modUser($member['user_id'],array('id'=>$member['user_id']),$data);
            if($result) {
                return errReturn('登录成功！', 0 , set_aes_param($member));
            } else {
                return errReturn('系统繁忙，请重新登录！' , 501);
            }
        }
    }
    
   
}