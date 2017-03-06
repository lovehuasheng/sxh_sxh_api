<?php

/*
 * @author:huanghuasheng
 * time:20161115
 * 个人中心板块
 */

namespace app\user\logic;
use think\Model;
use think\Db;
use think\Request;
/**
 * Description of UserCenter
 *
 * @author shanhubao
 * 
 */
class UserCenter extends Model{
    /**
     * @author huanghuasheng
     * @time 20161115
     * 上传用户头像
     */
    public function upload_avatar_picture($data) { 
            //验证参数ID
            if(!isset($data['user_id']) ||  intval($data['user_id']) === 0) {
                return errReturn('参数错误!', -501);
            }
            $model = \think\Loader::model('UserInfo' , 'model');
            $result = $model->updateUserInfo(array('user_id'=>$data['user_id']),array('avatar'=>$data['images']));

            if(!$result) {
                return errReturn($model->err,'-2');
            }
            
            return errReturn('头像上传成功！','0',set_aes_param(['path'=>getQiNiuPic($data['images'])]));
    }
    /**
     * 取出账户详细
     * @author huanghuasheng
     * @param array $data
     * @return array
     */
    public function accountInfo($data){         
        //验证参数ID
        if(!isset($data['user_id']) ||  intval($data['user_id']) === 0) {
            return errReturn('参数错误!', -501);
        }
        $model = \think\Loader::model('UserAccount' , 'model');
        $result = $model->accountInfo($data['user_id'],array('user_id'=>$data['user_id']),'order_taking,poor_wallet,needy_wallet,comfortably_wallet,kind_wallet,wealth_wallet,big_kind_wallet,activate_currency,guadan_currency,wallet_currency,manage_wallet,invented_currency');
        if(!$result) {
            return errReturn('账户不存在','-2');
        }
        $arr = array();
        $arr['money'][0]['money_type'] = 1;
        $arr['money'][0]['money_sum'] = $result['activate_currency'] ? $result['activate_currency'] : 0;
        $arr['money'][1]['money_type'] = 2;
        $arr['money'][1]['money_sum'] = $result['guadan_currency'] ? $result['guadan_currency'] : 0;
        $arr['money'][2]['money_type'] = 3;
        $arr['money'][2]['money_sum'] = $result['invented_currency'] ? $result['invented_currency'] : 0;
        $arr['money'][3]['money_type'] = 4;
        $arr['money'][3]['money_sum'] = $result['manage_wallet'] ? $result['manage_wallet'] : 0;
        $arr['money'][4]['money_type'] = 5;
        $arr['money'][4]['money_sum'] = $result['poor_wallet'] + $result['needy_wallet'] + $result['comfortably_wallet'] + $result['kind_wallet'] + $result['wealth_wallet'] + $result['big_kind_wallet'];
//        $arr['money'][5]['money_type'] = 6;
//        $arr['money'][5]['money_sum'] = $result['order_taking'];
        $redis = \org\RedisLib::get_instance();
        $num = $redis->hgetUserinfoByID($data['user_id'],'provide_num');
        if(empty($num) || $num<2){
            $arr['outsum'] = 0;
        }else{
//            $current_id = $redis->hgetUserinfoByID($data['user_id'],'provide_current_id');
//            $last_id = $redis->hgetUserinfoByID($data['user_id'],'provide_manage_id');
//            if($current_id != $last_id){
            $res_arr = $this->checkManage(intval($data['user_id']));
            if($res_arr['code'] == 0){
                $sum = $redis->hgetUserinfoByID($data['user_id'],'provide_current_money');
                $sum = $res_arr['provide_money'];//暂时不读redis
                $arr['outsum'] = intval(floor($sum/2/100)*100);
            }else{
                $arr['outsum'] = 0;
            }
        }
        
        $arr['outsum'] = $arr['outsum']>$result['manage_wallet'] ? intval(floor($result['manage_wallet']/100)*100) : $arr['outsum'];
        $arr['check_token'] = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return errReturn('请求成功!','0',set_aes_param($arr));
    }
    /**
     * 善种子、善心币转出操作
     * @author huanghuasheng
     * @param array $data
     * @return array
     */
    public function outputAccount($data){
        //验证参数ID
        if(!isset($data['user_id']) ||  intval($data['user_id']) === 0) {
            return errReturn('参数错误!', -501);
        }
        if(!preg_match('/^[\x{4e00}-\x{9fa5}\w-\.]{1,30}$/u', trim($data['notes']))){
            return errReturn('备注信息只能包含1-30位中文、数字、字母、“-”、“.”', -501);
        }
        $data['recipient_account'] = strtolower($data['recipient_account']);
        if(empty($data['recipient_account']) || empty($data['money_type']) || intval($data['money_sum'])<1 || empty($data['notes'])){
            return errReturn('参数不完整!', -501);
        }
        //根据post数据，获取用户信息
        $userr = array(
            'id'      => htmlspecialchars(urldecode($data['user_id'])),
        );
        $model = \think\Loader::model('User' , 'model');
        $user = $model->getUser($data['user_id'],$userr,'id,username,status,flag,is_transfer,security,secondary_password,password');
        if(!$user){
            return errReturn('用户ID不存在！', 401);
        }
        //二级密码验证
        if($user['security']){
            $pwd = set_password(md5(htmlspecialchars(urldecode($data['password']))));
        }else{
            $pwd = set_old_password(htmlspecialchars(urldecode($data['password'])));
        }
        if($user['secondary_password'] != $pwd) {
            return errReturn('二级密码错误，请重新输入！', 401);
        }
        //二级密码与登录密码对比，如果相等提示更改
        if($user['security']){
            $pwd = set_password(htmlspecialchars(urldecode($data['password'])),$user['security']);
        }else{
            $pwd = set_old_password(htmlspecialchars(urldecode($data['password'])));
        }
        if($user['password'] == $pwd) {
            return errReturn('二级密码不能与登录密码一致，请到电脑端重置！', 401);
        }
        if($user['username']==$data['recipient_account']){
            return errReturn('不能给自己转！', 401);
        }
        if($user['status'] != 1){
            return errReturn('您的账号未激活或被冻结', 401);
        }
        //特殊账号另外处理
        $spec_id = array('90','156','82');
        if(!in_array($user['id'],$spec_id)){
            if($data['money_type'] == 1){
                if($data['money_sum']>100){
                    return errReturn('转出数量超过上限', 401);
                }
            }else{
                if($data['money_sum']>300){
                    return errReturn('转出数量超过上限', 401);
                }
            }
        }else{
            if($data['money_sum']>500){
                return errReturn('转出数量超过500上限', 401);
            }
        }
        //取出接受者的ID
        $redis = \org\RedisLib::get_instance();
        $reci_id = $redis->getUserId($data['recipient_account']);
        $pid = $model->getUser($reci_id,array('id'=>$reci_id),'id,status');
        if(empty($pid) || $pid['status'] != 1){
            return errReturn('接收人账号不存在或未激活或被冻结', 401);
        }
        $m_account = \think\Loader::model('UserAccount' , 'model');
        //获取字段名称
        $field = get_ziduan($data['money_type']);
        $uinfo = $m_account->accountInfo($data['user_id'],array('user_id'=>$data['user_id']),$field);
        if($uinfo[$field]<$data['money_sum']){
            return errReturn('超额转出', 401);
        }
        $type_name = get_invented_currency_name($data['money_type']);
        //判断是否有任意转币权限，如果没有则只能在5级内转币
        if(!$user['is_transfer']){
            //除了特殊ID外
            if(!in_array($user['id'],$spec_id)){
                $m_rela = \think\Loader::model('UserRelation' , 'model');
                $res_rela = $m_rela->getUserRelationByID(array('user_id'=>$pid['id']),'full_url');
                $len = count(trim($res_rela['full_url'],','));
                if($len>6){
                    $arr = explode(',',trim($res_rela['full_url'],','));
                    $full_url = ','.$arr[$len-1].','.$arr[$len-2].','.$arr[$len-3].','.$arr[$len-4].','.$arr[$len-5].',';
                }else{
                    $full_url = $res_rela['full_url'];
                }
                if(strpos($full_url,','.$user['id'].',')===false){
                    return errReturn('操作失败，只能给5级内的下属会员转出'.$type_name, 401);
                }
            }
        }
        //token验证
        if(empty($data['check_token'])){
            return errReturn('令牌不能为空！', '400');
        }
//        $token = cache('check_token'.$data['user_id']);
//        if(!empty($token) && $token == $data['check_token']){
//            return errReturn('数据不能重复提交！', '400');
//        }else{
//            cache('check_token'.$data['user_id'],$data['check_token']);
//        }
        $check_token = $redis->incr('app_check_token_'.$data['user_id'].'_'.$pid['id']);
        if($check_token>1){
            return errReturn('数据不能重复提交！', '400');
        }
        $this->startTrans();
        try{
            //扣除操作account表
            $m_account->decUserAccountMoney($data['user_id'],array('user_id'=>$data['user_id'],$field=>array('EGT',$data['money_sum'])),$field,$data['money_sum']);
            //增加收入用户的账户
            $m_account->addUserAccountMoney($pid['id'],$field,$data['money_sum']);
            //插入outgo表
            $out = array();
            $out['id'] = $redis->incr('sxh_user_outgo:id');
            $out['type'] = $data['money_type'];
            $out['outgo'] = $data['money_sum'];
            $out['user_id'] = $data['user_id'];
            $username = $redis->getUsernameByID($data['user_id']);
            $out['pid'] = $pid['id'];
            $out['other_username'] = $data['recipient_account'];
            $out['username'] = $username;
            $out['info'] = '【App】'.$data['notes'];
            $out['create_time'] = time();
            $m_out = \think\Loader::model('UserOutgo' , 'model');
            $catid = $m_out->outgoInsert($out);
            //插入income表
            $scome = array();
            $scome['id'] = $redis->incr('sxh_user_income:id');
            $scome['type'] = $data['money_type'];
            $scome['income'] = $data['money_sum'];
            $scome['username'] = $data['recipient_account'];
            $scome['user_id'] = $pid['id'];
            $scome['pid'] = $data['user_id'];
            $scome['other_username'] = $username;
            $scome['cat_id'] = $catid;
            $scome['info'] = '【App】'.$data['notes'];
            $scome['create_time'] = time();
            $m_income = \think\Loader::model('UserIncome' , 'model');
            $res = $m_income->incomeInsert($scome);
            $this->commit();
            $flag_set = $redis->set('app_check_token_'.$data['user_id'].'_'.$pid['id'],1,8);
            if(!$flag_set){
                $redis->set('app_check_token_'.$data['user_id'].'_'.$pid['id'],1,8);
            }
        } catch (\Exception $e) {
            $this->rollback();
            $flag_del = $redis->del('app_check_token_'.$data['user_id'].'_'.$pid['id']);
            if(!$flag_del){
                $redis->del('app_check_token_'.$data['user_id'].'_'.$pid['id']);
            }
            return errReturn('系统繁忙，请重新操作！', 500);
        }
        
        $user_phone = $redis->hgetUserinfoByID($data['user_id'],'phone');
        $reci_phone = $redis->hgetUserinfoByID($pid['id'],'phone');
        //推送信息
        $sdata = array();
        $sdata['extra_data ']['user_id'] = $data['user_id'];
        $sdata['extra_data ']['phone'] = $user_phone;
        $sdata['extra_data ']['title'] = '转出'.$type_name;
        $sdata['extra_data ']['code'] = '';
        $sdata['extra_data ']['status'] = 1;
        $ipp = Request::instance()->ip();
        $sdata['extra_data ']['ip_address'] = ip2long($ipp);
        $sdata['extra_data ']['valid_time'] = '';
        $sdata['extra_data ']['create_time'] = time();
        $sdata['extra_data ']['update_time'] = time();
        $username = $user['username'];
        $time = date('Y-m-d H:i:s');
        $number = $data['money_sum'];
        $sdata['content'] = "您好，您的".$username."账户于".$time."成功扣除".$number."个".$type_name."。";
        $sdata['phone'] = $user_phone;
        
        $redis->lPush('sxh_user_sms', json_encode($sdata,JSON_UNESCAPED_UNICODE));

        $sdata['extra_data ']['user_id'] = $pid['id'];
        $sdata['extra_data ']['phone'] = $reci_phone;
        $sdata['extra_data ']['title'] = '转入'.$type_name;
        $username = $data['recipient_account'];
        $sdata['content'] = "您好，您的".$username."账户于".$time."成功充值".$number."个".$type_name."。";
        $sdata['phone'] = $reci_phone;
        $redis->lPush('sxh_user_sms', json_encode($sdata,JSON_UNESCAPED_UNICODE));

        return errReturn('转出成功！', 0);
    }
    /**
     * 提取管理奖
     * @author huanghuasheng
     * @param type $data
     * @return array
     */
    public function outManageAccount($data){
        //验证参数ID
        
        if(!isset($data['user_id']) ||  intval($data['user_id']) === 0) {
            return errReturn('参数错误!', -501);
        }
        if($data['money_sum']<500 || $data['money_sum']%100 != 0){
            return errReturn('提取管理金额必须大于500且是100的倍数', -1);
        }
        //根据post数据，获取用户信息
        $user = array(
            'id'      => htmlspecialchars(urldecode($data['user_id'])),
        );

        $model = \think\Loader::model('User' , 'model');
        $reuser = $model->getUser($data['user_id'],$user,'id,username,status,flag,verify,security,secondary_password,password');
        if(!$reuser){
            return errReturn('用户ID有误！', 401);
        }
        //二级密码判断
        if($reuser['security']){
            $pwd = set_password(md5(htmlspecialchars(urldecode($data['password']))));
        }else{
            $pwd = set_old_password(htmlspecialchars(urldecode($data['password'])));
        }
        if($reuser['secondary_password'] != $pwd) {
            return errReturn('二级密码错误，请重新输入！', 401);
        }
        //二级密码与登录密码对比
        if($reuser['security']){
            $pwd = set_password(htmlspecialchars(urldecode($data['password'])),$reuser['security']);
        }else{
            $pwd = set_old_password(htmlspecialchars(urldecode($data['password'])));
        }
        //判断二级密码与登录密码是否相等，如果相等则要修改，暂时到pc端修改
        if($reuser['password'] == $pwd) {
            return errReturn('二级密码不能与登录密码一致，请到电脑端设置！', 401);
        }
        //是否激活判断
        if($reuser['status']!=1 || $reuser['verify']!=2){
            return errReturn('您的账号未激活或未通过或被禁止使用！', 401);
        }
        $m_account = \think\Loader::model('UserAccount' , 'model');
        $res = $m_account->accountInfo($data['user_id'],array('user_id'=>$data['user_id']),"manage_wallet");
        if($res['manage_wallet']<500){
            return errReturn('管理钱包要大于500方可提取！', -1);
        }
        if($res['manage_wallet']<$data['money_sum']){
            return errReturn('提取管理奖不能大于管理钱包的金额！', -1);
        }
        //获取缓存对比
        $redis = \org\RedisLib::get_instance();
        $num = $redis->hgetUserinfoByID($data['user_id'],'provide_num');
        if($num<2){
            return errReturn('提取管理钱包必须要提供资助两次或以上', -1);
        }
        $current_id = $redis->hgetUserinfoByID($data['user_id'],'provide_current_id');
        $last_id = $redis->hgetUserinfoByID($data['user_id'],'provide_manage_id');
//        if($current_id != $last_id){
        $res_arr = $this->checkManage(intval($data['user_id']));
        if($res_arr['code'] == 0){
            $sum = $redis->hgetUserinfoByID($data['user_id'],'provide_current_money');
            $sum = $res_arr['provide_money'];//暂时不读redis
            $amount = intval(floor($sum/2/100)*100);
            if($amount<$data['money_sum']){
                return errReturn('您提取的管理奖已超出可提金额', -1);
            }
        }else{
            return errReturn('本次挂单期内只能提取一次管理奖', -1);
        }
        //设置最近订单为数据库查出的最近订单
        $current_id = $res_arr['provide_id'];
        //判断上次挂单的社区ID
        $arr = array('poor_wallet','needy_wallet','comfortably_wallet','kind_wallet','wealth_wallet','big_kind_wallet');
        $community_id = $redis->hgetUserinfoByID($data['user_id'],'provide_last_community_id');
        if(!$community_id){
            return errReturn('上次挂单的社区ID不明确！', '400');
        }
        $m_out = \think\Loader::model('UserOutgo' , 'model');
        //token验证
        if(empty($data['check_token'])){
            return errReturn('令牌不能为空！', '400');
        }
//        $token = cache('check_token'.$data['user_id']);
//        if(!empty($token) && $token == $data['check_token']){
//            return errReturn('数据不能重复提交！', '400');
//        }else{
//            cache('check_token'.$data['user_id'],$data['check_token']);
//        }
        $check_token = $redis->incr('check_token'.$data['user_id']);
        if($check_token>1){
            return errReturn('数据不能重复提交！', '400');
        }
        
        Db::startTrans();
        try{
            //扣除操作account表  Wallet_Currency,Manage_Wallet,
            $m_account = \think\Loader::model('UserAccount' , 'model');
            $field = $arr[$community_id-1];
            $s_data = array();
            $s_data['manage_wallet'] = array('exp','manage_wallet-'.$data['money_sum']);
            $s_data[$field] = array('exp',$field.'+'.$data['money_sum']);
            $res_account = $m_account->updateAccount($data['user_id'],array('user_id'=>$data['user_id'],'manage_wallet'=>array('EGT',$data['money_sum'])),$s_data);
            //插入outgo表
            $out = array();
            $out['id'] = $redis->incr('sxh_user_outgo:id');
            $out['type'] = 5;
            $out['outgo'] = $data['money_sum'];
            $out['user_id'] = $data['user_id'];
            $username = $redis->getUsernameByID($data['user_id']);
            $out['username'] = $username;
            //记录用户的最近一次提供资助ID
            $out['pid'] = $current_id;
            $out['info'] = '提取管理奖';
            $out['create_time'] = time();
            $catid = $m_out->outgoInsert($out);
            //插入income表
            $come = array();
            $come['id'] = $redis->incr('sxh_user_income:id');
            $come['type'] = 5;
            $come['income'] = $data['money_sum'];
            $come['user_id'] = $data['user_id'];
            $come['username'] = $username;
            $come['pid'] = $data['user_id'];
            $come['other_username'] = $username;
            $come['cat_id'] = $catid;
            $come['info'] = '【App】提取管理奖';
            $come['create_time'] = time();
            $m_income = \think\Loader::model('UserIncome' , 'model');
            $res = $m_income->incomeInsert($come);
            if($res_account && $catid && $res){
                Db::commit();
            }else{
                return errReturn('系统繁忙，请重新操作！', 500);
            }
            
        } catch (\Exception $e) {
            Db::rollback();
            return errReturn('系统繁忙，请重新操作！', 500);
        }
        $flag_del = $redis->del('check_token'.$data['user_id']);
        if(!$flag_del){
            $redis->set('check_token'.$data['user_id'],1,1);
        }
        //更新缓存里面的可提取金额
        $redis->hsetUserinfoByID($data['user_id'],'provide_current_id',$current_id);
        $redis->hsetUserinfoByID($data['user_id'],'provide_manage_id',$current_id);
        $redis->hsetUserinfoByID($data['user_id'],get_redis_field($community_id),time());
        //获取手机号
        $m_info = \think\Loader::model('UserInfo' , 'model');
        $uinfo = $m_info->findUserInfo($data['user_id'],array('user_id'=>$data['user_id']),'phone');
        //推送信息
        $sdata = array();
        $sdata['extra_data ']['user_id'] = $data['user_id'];
        $sdata['extra_data ']['phone'] = $uinfo['phone'];
        $sdata['extra_data ']['title'] = '提取管理奖';
        $sdata['extra_data ']['code'] = '';
        $sdata['extra_data ']['status'] = 1;
        $ipp = Request::instance()->ip();
        $sdata['extra_data ']['ip_address'] = ip2long($ipp);
        $sdata['extra_data ']['valid_time'] = '';
        $sdata['extra_data ']['create_time'] = time();
        $sdata['extra_data ']['update_time'] = time();
        $username = $reuser['username'];
        $time = date('Y-m-d H:i:s');
        $number = $data['money_sum'];
        $sdata['content'] = "您好，您的".$username."账户于".$time."提取了".$number."元到出局钱包，如有疑问，请联系服务中心。";
        $sdata['phone'] = $uinfo['phone'];
        //return errReturn('提取成功！', 0);exit;
        $redis->lPush('sxh_user_sms', json_encode($sdata,JSON_UNESCAPED_UNICODE));
        return errReturn('提取成功！', 0);
    }
    /**
     * 获取手机验证码
     * @author huanghuasheng
     */
    public function getPhoneCode($data){
        if(empty($data['type'])){
            return errReturn('验证短信类型不能为空', -501);
        }
        $redis = \org\RedisLib::get_instance();
        if(intval($data['type'])==3333){
            if(empty($data['username'])){
                return errReturn('输入的用户名为空', -501);
            }
            $data['username'] = strtolower($data['username']);
            $user_id = $redis->getUserId($data['username']);
            if(!$user_id){
                return errReturn('用户名不存在', -501);
            }
            $data['phone'] = $redis->hgetUserinfoByID($user_id,'phone');
        }else{
            //查询手机号是否已被注册（手机，微信号，支付宝号）
            $phone_result           = $redis->sismemberFieldValue('sxh_user_info:phone' , $data['phone']);
            $alipay_account_result  = $redis->sismemberFieldValue('sxh_user_info:alipay_account' , $data['phone']);
            $weixin_account_result  = $redis->sismemberFieldValue('sxh_user_info:weixin_account' , $data['phone']);
            if($phone_result || $alipay_account_result || $weixin_account_result){
                return errReturn('手机号已经被注册！' , 101);
            }
        }
        if(!preg_match('/^1[34578]\d{9}$/', $data['phone'])){
            return errReturn('手机号码格式有误!', -501);
        }
        if(cache('code_time'.$data['phone'])){
            return errReturn('您的验证码刚刚发送，请稍后再试!', -501);
        }
        $code = get_rand_num(5);
        $sdata = array();
        $sdata['extra_data ']['user_id'] = 1;
        $sdata['extra_data ']['phone'] = $data['phone'];
        if(intval($data['type'])==1111){
            $sdata['extra_data ']['title'] = '注册短信';
        }else if(intval($data['type'])==3333){
            $sdata['extra_data ']['title'] = '修改密码短信';
        }
        $sdata['extra_data ']['code'] = $code;
        $sdata['extra_data ']['status'] = 1;
        $ip = Request::instance()->ip();
        $sdata['extra_data ']['ip_address'] = ip2long($ip);
        $sdata['extra_data ']['valid_time'] = 300;
        $sdata['extra_data ']['create_time'] = time();
        $sdata['extra_data ']['update_time'] = time();
        if(intval($data['type'])==1111){
            $sdata['content'] = "您的验证码是".$code."，正在进行会员注册验证。";
        }else if(intval($data['type'])==3333){
            $sdata['content'] = "您的验证码是".$code."，您正在尝试修改登录密码，请妥善保管账户信息";
        }
        $sdata['phone'] = $data['phone'];
        $redis->lPush('sxh_user_sms', json_encode($sdata,JSON_UNESCAPED_UNICODE));//LPUSH
        cache('code'.$data['phone'],$code,600);
        cache('code_time'.$data['phone'],120,120);
        return errReturn('验证码发送成功！', 0);

    }
    /**
     * 完善资料
     * @param type $data
     * @return array
     */
    public function perfectUserInfo($data,$img){
        //验证参数ID
        if(!isset($data['user_id']) ||  intval($data['user_id']) === 0) {
            return errReturn('参数错误!', -501);
        }
        if(empty($data['city']) || empty($data['address']) || empty($data['province']) || empty($data['town'])){
            return errReturn('参数不完整!', -501);
        }
        if(!preg_match('/^[\x{4e00}-\x{9fa5}\w-\.\s]{1,250}$/u',$data['city'].$data['address'].$data['province'].$data['town'].$data['area'])){
            return errReturn('地址信息只能包含1-250位中文、数字、字母、“-”、“.”', -501);
        }
        if(!preg_match('/^(\d{10,30})$/',$data['bank_account'])){
            return errReturn('银行账号格式不正确!', -501);
        }
        if(!preg_match('/^[\x{4e00}-\x{9fa5}•·a-zA-Z]{1,20}$/u',$data['name'])){
            return errReturn('姓名长度需在1-20个中文或字母字符之间', -501);
        }
        if(!preg_match('/^[\x{4e00}-\x{9fa5}\w-]{1,50}$/u',$data['bank_name'].$data['bank_address'])){
            return errReturn('开户银行与所在支行的字符总长度为1-49位中文、数字或字母', -501);
        }
        if(!preg_match('/^(\w{10,18})$/',$data['card_id'])){
            return errReturn('身份证格式不正确!', -501);
        }
        if(!preg_match('/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i',$data['email'])){
            return errReturn('邮箱格式不正确!', -501);
        }
        
        if(!preg_match('/^1[34578]\d{9}$/', $data['phone'])){
            return errReturn('手机号码格式有误!', -501);
        }
        $redis = \org\RedisLib::get_instance();
        //微信号如果不为空则验证是否含有特殊字符和其唯一性
        $m_info = \think\Loader::model('UserInfo' , 'model');
        $user_id = intval($data['user_id']);
        $res_info = $m_info->getInfo(array('user_id'=>$user_id),$user_id,'weixin_account,alipay_account,card_id,bank_account');
        if(!empty($data['weixin_account'])){
            if(preg_match('/\!|\%|\&|\(|\)|\<|\>|\;|\'|\"/',$data['weixin_account'])){
                return errReturn('微信号不能含有特殊字符', -501);
            }
            $data['weixin_account'] =preg_replace("/\s/","",$data['weixin_account']);
            if($data['weixin_account'] != $res_info['weixin_account']){
                $weixin_account_result  = $redis->sismemberFieldValue('sxh_user_info:weixin_account' , $data['weixin_account']);
                if($weixin_account_result){
                    return errReturn('微信账号已被其他人使用!', -501);
                }
                if(!empty($res_info['weixin_account'])){
                    $redis->sremUserInfoField('weixin_account',$res_info['weixin_account']);
                }
            }
        }else{
            //第一次不为空，而第二次为空的情况
            if(!empty($res_info['weixin_account'])){
                $redis->sremUserInfoField('weixin_account',$res_info['weixin_account']);
            }
        }
        //支付宝号如果不为空则验证是否含有特殊字符和其唯一性
        if(!empty($data['alipay_account'])){
            if(preg_match('/\!|\%|\&|\(|\)|\<|\>|\;|\'|\"/',$data['alipay_account'])){
                return errReturn('支付宝号不能含有特殊字符', -501);
            }
            $data['alipay_account'] =preg_replace("/\s/","",$data['alipay_account']);
            if($data['alipay_account'] != $res_info['alipay_account']){
                $alipay_account_result  = $redis->sismemberFieldValue('sxh_user_info:alipay_account' , $data['alipay_account']);
                if($alipay_account_result){
                    return errReturn('支付宝账号已被其他人使用!', -501);
                }
                if(!empty($res_info['alipay_account'])){
                    $redis->sremUserInfoField('alipay_account',$res_info['alipay_account']);
                }
            }
        }else{
            //第一次不为空，而第二次为空的情况
            if(!empty($res_info['alipay_account'])){
                $redis->sremUserInfoField('alipay_account',$res_info['alipay_account']);
            }
        }
        $data['phone'] =preg_replace("/\s/","",$data['phone']);
        $data['card_id'] =preg_replace("/\s/","",$data['card_id']);
        $data['bank_account'] =preg_replace("/\s/","",$data['bank_account']);
        //验证手机的
//        $rep = $m_info->userinfoCount(array('UserID'=>$data['user_id'],'Phone'=>$data['phone']));
//        if(count($rep)!=1){
//            return errReturn('手机号与注册时不一致!', -501);
//        }
        //防止用户资料未审核通过期间修改资料，如果存在则要相应的修改redis
        if($data['card_id'] != $res_info['card_id']){
            $rec = $redis->sismemberFieldValue('sxh_user_info:card_id' , $data['card_id']);
            if($rec){
                return errReturn('身份证号已被其他人使用!', -501);
            }
            if(!empty($res_info['card_id'])){
                $redis->sremUserInfoField('card_id',$res_info['card_id']);
            }
        }
        if($data['bank_account'] != $res_info['bank_account']){
            $reb = $redis->sismemberFieldValue('sxh_user_info:bank_account' , $data['bank_account']);
            if($reb){
                return errReturn('银行账号已被其他人使用!', -501);
            }
            if(!empty($res_info['bank_account'])){
                $redis->sremUserInfoField('bank_account',$res_info['bank_account']);
            }
        }
            
        
        $sdata = array();
        $sdata['phone'] = $data['phone'];
        $sdata['weixin_account'] = $data['weixin_account'] ? $data['weixin_account'] : '';
        $sdata['email'] = $data['email'];
        $sdata['city'] = $data['city'];
        $sdata['name'] = $data['name'];
        $sdata['card_id'] = $data['card_id'];
        $sdata['address'] = $data['address'];
        $sdata['alipay_account'] = $data['alipay_account'] ? $data['alipay_account'] : '';
        $sdata['bank_name'] = $data['bank_name'];
        $sdata['bank_address'] = $data['bank_address'];
        $sdata['bank_account'] = $data['bank_account'];
        $sdata['province'] = $data['province'];
        $sdata['town'] = $data['town'];
        $sdata['area'] = $data['area'];
        if(isset($img['image_a']) && !empty($img['image_a'])){
            $sdata['image_a'] = $img['image_a'];
        }
        if(isset($img['image_b']) && !empty($img['image_b'])){
            $sdata['image_b'] = $img['image_b'];
        }
        if(isset($img['image_c']) && !empty($img['image_c'])){
            $sdata['image_c'] = $img['image_c'];
        }
        $res = $m_info->updateUserInfo(array('user_id'=>$data['user_id']),$sdata);
        if($res){
            if($sdata['weixin_account']){
                $redis->saddField(  'sxh_user_info:weixin_account' , $sdata['weixin_account'] );
            }
            if($sdata['alipay_account']){
                $redis->saddField(  'sxh_user_info:alipay_account' , $sdata['alipay_account'] );
            }
            $redis->saddField(  'sxh_user_info:bank_account' , $sdata['bank_account'] );
            $redis->saddField(  'sxh_user_info:card_id' , $sdata['card_id'] );
            return errReturn('保存成功！', 0);
        }else{
            return errReturn('保存成功！', 0);
        }
    }
    /**
     * 忘记密码
     * @param type $data
     * @return array
     */
    public function modUserPassword($data){
        //根据用户名获取手机号，然后再sms表获取验证码
        if(empty($data['username'])){
            return errReturn('用户名不能为空', -501);
        }
        $redis = \org\RedisLib::get_instance();
        $data['username'] = strtolower($data['username']);
        $user_id = $redis->getUserId($data['username']);
        if(!$user_id){
            return errReturn('用户名不存在', -501);
        }
        $data['phone'] = $redis->hgetUserinfoByID($user_id,'phone');
        //验证码
        $code = cache('code'.$data['phone']);
        if(empty($code) || $code != $data['verify']){
            return errReturn('验证码不正确或已过期！' , 100);
        }
        if($data['password'] == $data['username']){
            return errReturn('密码不能与用户名一致！' , 100);
        }
        if(!preg_match('/^(?!^\d+$)(?!^[a-zA-Z]+$)[0-9a-zA-Z]{6,16}$/', $data['password'])){
            return errReturn('密码格式不正确' , 100);
        }
        if($data['password']!=$data['rePassword']){
            return errReturn('二次输入的密码不一致！' , 100);
        }
        $m_user = \think\Loader::model('User' , 'model');
        $info = $m_user->getUser($user_id,array('id'=>$user_id),'password,security,secondary_password');
        if($info['security']){
            $pwd = set_password(trim($data['password']),$info['security']);
        }else{
            $pwd = set_old_password(trim($data['password']));
        }
        if($info['password'] == $pwd){
            return errReturn('新密码不能与原来密码一样！' , 100);
        }
        if($info['security']){
            $pwd = set_password(md5(trim($data['password'])));
        }else{
            $pwd = set_old_password(trim($data['password']));
        }
        if($info['secondary_password'] == $pwd){
            return errReturn('新密码不能与二级密码一样！' , 100);
        }
        $security = get_rand_num(6);
        $sdata = array();
        $sdata['password'] = $pwd;
        //$sdata['secondary_password'] = set_password(md5($data['password']));
        $sdata['security'] = $security;
        $res = $m_user->modUser($user_id,array('id'=>$user_id),$sdata);
        if($res){
            cache('code'.$data['phone'],null);
            return errReturn('修改成功！', 0);
        }else{
            return errReturn('修改失败', -1);
        }
    }
    /**
     * 查看完善资料
     * @param type $data
     * @return array
     */
    public function getUserInfo($data){
        //验证参数ID
        if(!isset($data['user_id']) ||  intval($data['user_id']) === 0) {
            return errReturn('参数错误!', -501);
        }
        $model = \think\Loader::model('UserInfo' , 'model');
        $field = 'phone,weixin_account,email,city,name,card_id,address,province,town,area,alipay_account,bank_name,bank_account,'
                . 'image_a,image_b,image_c,bank_address';
        $result = $model->getInfo(array('user_id'=>$data['user_id']),$data['user_id'],$field);

        if(empty($result['image_a']) || empty($result['image_b']) || empty($result['image_c'])) {
            return errReturn('您的资料尚未完善','1');
        }
        if(strpos($result['bank_name'], '-')){
            $temp = explode('-', $result['bank_name']);
            $result['bank_name'] = $temp[0];
            $result['bank_address'] = $temp[1];
        }
        $result['province'] = $result['province'] ? $result['province'] : '';
        $result['town'] = $result['town'] ? $result['town'] : '';
        $result['area'] = $result['area'] ? $result['area'] : '';
        $result['address'] = str_replace($result['province'].$result['town'].$result['area'],'',$result['address']);
        $result['image_a'] = $result['image_a'] ? getQiNiuPic($result['image_a']) : '';
        $result['image_b'] = $result['image_b'] ? getQiNiuPic($result['image_b']) : '';
        $result['image_c'] = $result['image_c'] ? getQiNiuPic($result['image_c']) : '';
        $result['flag'] = true;
        $m_user = \think\Loader::model('User' , 'model');
        $ver = $m_user->getUser($data['user_id'],array('id'=>$data['user_id']),'verify');
        if($ver['verify'] != 2){
//            $redis = \org\RedisLib::get_instance();
//            if($result['weixin_account']){
//                $redis->sremUserInfoField('weixin_account',$result['weixin_account']);
//            }
//            if($result['alipay_account']){
//                $redis->sremUserInfoField('alipay_account',$result['alipay_account']);
//            }
//            $redis->sremUserInfoField('bank_account',$result['bank_account']);
//            $redis->sremUserInfoField('card_id',$result['card_id']);
            $result['flag'] = false;
        }
        return errReturn('取出数据成功',0,set_aes_param($result));
    }
    
    /** 查询本轮管理奖是否已经提取
     */
    public function checkManage($user_id) {
        $time = time();
        $table_r = getTable($time);
        $table = "sxh_user_provide_".$table_r[0];
        //一，查provide表
        $provide_where = "user_id=".$user_id." AND status=3 AND type_id=1";
        $pro_sql = "SELECT id,money,used,cid FROM ".$table." WHERE ".$provide_where." ORDER BY id DESC LIMIT 1";
        $pro_result = \think\Db::query($pro_sql);
        //如果空，就查上季
        if(empty($pro_result)) {
            $table_2 = "sxh_user_provide_".$table_r[1];
            $pro_sql = "SELECT id,money,used,cid FROM ".$table_2." WHERE ".$provide_where." ORDER BY id DESC LIMIT 1";
            $pro_result = \think\Db::query($pro_sql);
        }
        //如果空，就查上季
        if(empty($pro_result)) {
            $table_2 = "sxh_user_provide_".getNextTable($table_r[1]);
            $pro_sql = "SELECT id,money,used,cid FROM ".$table_2." WHERE ".$provide_where." ORDER BY id DESC LIMIT 1";
            $pro_result = \think\Db::query($pro_sql);
        }
        
        //一，如果提供表有数据，就更新 Redis ，如果没有就不管
        if(!empty($pro_result)) {
            $provide_id = $pro_result[0]['id'];
            $pro_money= $pro_result[0]['money'];
            
            //二，查income表
            //$table = getTable($time);
            $out_table = "sxh_user_outgo_".$table_r[0];
            $outgo_where = "user_id=".$user_id." AND pid=".$provide_id." AND info='提取管理奖'";
            $now_sql = "SELECT id FROM ".$out_table." WHERE ".$outgo_where." LIMIT 1";
            $outgo_result = \think\Db::query($now_sql);
            
            //如果空，就查上季
            if(empty($outgo_result)) {
                $out_table_2 = "sxh_user_outgo_".$table_r[1];
                $last_sql = "SELECT id FROM ".$out_table_2." WHERE ".$outgo_where." LIMIT 1";
                $outgo_result = \think\Db::query($last_sql);
            }
            //二，如果outgo有数据
            if(!empty($outgo_result)) {
                //1，本轮已提取过管理奖
                return [
                    'code'=>1,
                    'provide_id' => 0    ,
                    'provide_money' => 0,
                    ];
            } else {
                //2，本轮没有提取过管理奖
                return [
                    'code'=>0,
                    'provide_id' => $provide_id    ,
                    'provide_money' => $pro_money,
                    ];
            }
            
        } else {
            return [
                'code'=>2,
                'provide_id' => 0    ,
                'provide_money' => 0,
                ];
        }
    }
    
   
    
}
