<?php

// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 童自扬 <2421886569@qq.com> 
// +----------------------------------------------------------------------
// | Function: 接受资助业务逻辑层
// +----------------------------------------------------------------------

namespace app\user\logic;

use think\Model;

class Accept extends Model {

    /**
     * 接受资助列表业务逻辑层
     * @param array $data
     * @return type
     * @Author 童自扬
     * @time  2016-10-08
     */
    public function accept_list($data,$page,$total) {

        $model = \think\Loader::model('UserAccepthelp');
        $redis = \org\RedisLib::get_instance();
        $arr = array(0,0,1,3);
        $map = array();
        $type = isset($data['type']) ? $data['type'] : 1;
        $map['status'] = $arr[$type];
        $map['user_id'] = intval($data['user_id']);
        $page = isset($data['page']) ? $data['page']:1;
        //如果页面没传每页条数，就去配置的条数
        $total = isset($data['current_page'])?$data['current_page']:config('app_list_rows');
        //读取缓存
        $redis->delDataList('accepthelp',$map['user_id'],$type);
        $result_list = json_decode($redis->lindexDataList('accepthelp',$map['user_id'],$type,$page-1),true);
        if(!$result_list){
            $field = 'id,type_id,money,used,cid,cname,user_id,status,create_time';
            $result_list = $model->getAcceptListByStatus($map,intval($page),intval($total),$field);
            if(empty($result_list)) {
                $result_list['list']['data'] = [];
                $result_list['total'] = 0;
                $result_list['per_page'] = 10;
                $result_list['current_page'] = 0;
            }
            $redis->rPushDataList('accepthelp',$map['user_id'],$type,  json_encode($result_list));
        }
            
        return errReturn('请求成功','0',set_aes_param($result_list));
    }
    
    
    /**
     * 取消订单[业务逻辑层]
     * @param type $data
     * @return type
     * @Author 童自扬
     * @time  2016-10-04
     */
    public function accept_destroy(&$data) {
        //验证参数ID
        if(!isset($data['id']) || intval($data['id']) === 0 || empty($data['pwd'])) {
            return errReturn('参数错误!', -501);
        }
        
        $db = model('User');
        $user_info = $db->getInfoByUserID($data['user_id'],'secondarypassword as pay_password,salt');
        if(!$user_info){
            return errReturn('用户ID不不存!', -502);
        }
        if($user_info['security']){
            $pwd = set_password(md5(trim($data['pwd'])));
        }else{
            $pwd = set_old_password(trim($data['pwd']));
        }
        if($pwd != $user_info['pay_password']) {
            return errReturn('二级密码不正确!', -502);
        }
        
        unset($user_info);
        
        $model = \think\Loader::model('UserAccepthelp');
        $map['ID'] = $data['id'];
        $map['UserID'] = $data['user_id'];
        $map['Matching'] = 0;//附加条件，未匹配时才可取消
        unset($data);
        $result_list = $model->delAccepthelpData($map);
        if($result_list == false) {
            return errReturn('取消失败!', -2);
        }
        
        return errReturn('取消成功!', '0');
    }
    
    /**
     * 提供资助的匹配详情页[业务逻辑层]
     * @param type $data
     * @return type
     * @Author 童自扬
     * @time  2016-10-04
     */
    public function accept_detail($data) {
        //验证参数ID
        if(!isset($data['id']) || intval($data['id']) === 0 || empty($data['create_time'])) {
            return errReturn('参数错误!', -501);
        }
        $map = array();
        $map['pid'] = intval($data['id']);
        $map['create_time'] = $data['create_time'];
        $redis = \org\RedisLib::get_instance();
        $redis->delMatchDetail('accepthelp',$data['user_id'],$data['id']);
        $list = json_decode($redis->getMatchDetail('accepthelp',$data['user_id'],$data['id']));
        if(!$list){
            $model = \think\Loader::model('UserMatchhelp');
            $result_list = $model->getMatchingListByAcceptID($map,'id,pid,type_id,user_id,money,other_id,other_user_id,other_money,status,sign_time,create_time,other_name,pay_time,delayed_time_status,expiration_create_time,audit_time');
            if(empty($result_list)){
                return errReturn('数据未匹配',-12);
            }
            //整理数据返回
            $arr = array('接受资助','接受资助','接单钱包');
            $status_text = array('未审核','未布施','已布施','已布施');
            $sign_text = array('未审核','未确认','未确认','已确认');
            $list = array();
            foreach($result_list as $k=>$v){
                if($v['user_id'] != $data['user_id']){
                    return errReturn('数据错误',-12);break;
                }
                $list[$k]['id'] = $v['id'];
                $list[$k]['pid'] = $v['pid'];
                $list[$k]['check_token'] = getToken();
                $list[$k]['other_id'] = $v['other_id'];
                $list[$k]['matching_accept_text'] = $arr[$v['type_id']];
                $list[$k]['other_money'] = $v['other_money'];
                $list[$k]['status'] = $v['status'];
                $list[$k]['status_text'] = $status_text[$v['status']];
                $list[$k]['sign_text'] = $sign_text[$v['status']];
                $list[$k]['other_username'] = $v['other_name'];
                $list[$k]['create_time'] = $v['create_time'];
                $list[$k]['pay_time'] = $v['pay_time'];
                $list[$k]['delayed_time_status'] = $v['delayed_time_status'];
                $list[$k]['provide_overtime_status'] = 0;
                $list[$k]['accept_overtime_status'] = 0;
                //打款方未付款倒计时计算
                if($v['status'] == 1){
                    if($v['delayed_time_status']){
                        if(($v['expiration_create_time']-$_SERVER['REQUEST_TIME']) > 0){
                            $list[$k]['create_time_text'] = $v['expiration_create_time']-$_SERVER['REQUEST_TIME'];
                        }else{
                            $list[$k]['create_time_text'] = null;
                            $list[$k]['provide_overtime_status'] = 1;
                        }
                    }else{
                        if(($_SERVER['REQUEST_TIME']-$v['audit_time']) < config('matchhelp_out_time')){
                            $list[$k]['create_time_text'] = $v['audit_time']+config('matchhelp_out_time')-$_SERVER['REQUEST_TIME'];
                        }else{
                            $list[$k]['create_time_text'] = null;
                            $list[$k]['provide_overtime_status'] = 1;
                        }
                    }
                }else{
                    $list[$k]['create_time_text'] = null;
                }
                if($v['status'] == 2){
                    if(($_SERVER['REQUEST_TIME']-$v['pay_time']) < config('matchhelp_out_time')){
                        $list[$k]['pay_time_text'] = $v['pay_time']+config('matchhelp_out_time')-$_SERVER['REQUEST_TIME'];
                    }else{
                        $list[$k]['pay_time_text'] = null;
                        $list[$k]['accept_overtime_status'] = 1;
                    }
                }else{
                    $list[$k]['pay_time_text'] = null;
                }
            }
            $redis->setMatchDetail('accepthelp',$data['user_id'],$data['id'],json_encode($list));
        }    
        return errReturn('请求成功','0',set_aes_param(['list'=>$list]));
    }
    
    
    /**
     * 进入打款人页面[业务逻辑层]
     * @param type $data
     * @return type
     * @Author 童自扬
     * @time  2016-10-05
     */
    public function accept_person_msg($data) {
        
        //验证参数ID
        if(!isset($data['id']) ||  intval($data['id']) === 0 || empty($data['create_time'])) {
            return errReturn('参数错误!', -501);
        }
        //根据时间获取表后缀
        $arr = getTable($data['create_time']);
        $table = 'sxh_user_matchhelp_'.$arr[0];
        $map['id'] = $data['id'];
        
        //预定义 
        $user = [];
        $m_match = \think\Loader::model('UserMatchhelp');
        $m_info = \think\Loader::model('UserInfo');
        $matching = $m_match->getMatchingOneDataByID($table,$map,'id,pid,other_id,user_id,other_user_id,status,other_money,pay_image,other_cid,create_time');
        if($data['user_id'] != $matching['user_id']){
            return errReturn('数据错误!', -501);
        }
        $arr = array(7,8,10);
        if(!empty($matching)) {
            if(in_array($matching['other_cid'], $arr)){
                $c_info = \think\Loader::model('CompanyInfo');
                $user = $c_info->getCompanyInfo(array('company_id'=>$matching['other_user_id']),'business_center_id,company_name,legal_person as name,legal_alipay_account as alipay_account,mobile as phone,legal_bank_name as bank_name,legal_bank_account as bank_account');
                $user['tel_number'] = '';
                if(isset($user['business_center_id']) && $user['business_center_id']>0){
                    $reuser = $c_info->getCompanyInfo(array('company_id'=>$user['business_center_id']),'company_name,legal_person as name,mobile');
                    $user['referee_company_name'] = $reuser['company_name'];
                    $user['referee_name'] = $reuser['name'];
                    $user['referee_phone'] = $reuser['mobile'];
                    $user['referee_tel_number'] = '';
                }else{
                    $user['referee_company_name'] = '';
                    $user['referee_name'] = '';
                    $user['referee_phone'] = '';
                    $user['referee_tel_number'] = '';
                }
            }else{
                $user = $m_info->getInfo(array('user_id'=>$matching['other_user_id']),$matching['other_user_id'],'name,alipay_account,phone,weixin_account,bank_name,bank_account,tel_number,referee_id');
                $user['company_name'] = '';
                $user['referee_company_name'] = '';
                if(isset($user['referee_id']) && $user['referee_id']>0){
                    $reuser = $m_info->getInfo(array('user_id'=>$user['referee_id']),$user['referee_id'],'name,phone,tel_number');
                    $user['referee_name'] = $reuser['name'];
                    $user['referee_phone'] = $reuser['phone'];
                    $user['referee_tel_number'] = $reuser['tel_number'] ? $reuser['tel_number'] : '';
                }else{
                    $user['referee_name'] = '';
                    $user['referee_phone'] = '';
                    $user['referee_tel_number'] = '';
                }
            }
            $user['images'] = $matching['pay_image'] ? getQiNiuPic($matching['pay_image']) : '';
            $user['pid'] = $matching['pid'];
            $user['other_money'] = $matching['other_money'];
            $user['other_id'] = $matching['other_id'];
            $user['other_user_id'] = $matching['other_user_id'];
            $user['id'] = $matching['id'];
            $user['create_time'] = $matching['create_time']; 
        }else{
            return errReturn('数据错误',-23);
        }
        //合并数据数组
        return errReturn('请求成功','0',set_aes_param(['list'=>$user]));
    }
    
    
   /**
    * 打款延时
    * @param type $data
    * @return type
    */
   public function accept_delayed(&$data) {
        //验证参数ID
        if(!isset($data['id']) ||  intval($data['id']) === 0  || !isset($data['pwd']) || !isset($data['delayed_time'])) {
            return errReturn('参数错误!', -501);
        }
        //匹配表ID
        $map['id']          = $data['id'];
        $map['user_id']     = $data['user_id'];
        $map['create_time'] = $data['create_time'];
        $delayed_time       = $data['delayed_time'];
        $user = \think\Loader::model('common/User', 'model');
        $user_info = $user->getUser(["id"=>$data['user_id']],'secondary_password as pay_password,security',$data['user_id'])->toArray();
        if(!$user_info){
            return errReturn('用户ID不存在!', -502);
        }
        if($user_info['security']){
            $pwd = set_password(md5(trim($data['pwd'])));
        }else{
            $pwd = set_old_password(trim($data['pwd']));
        }
        if($pwd != $user_info['pay_password']) {
            return errReturn('二级密码不正确!', -502);
        }
        
        unset($data);
        $model = model('UserMatchhelp');
        $result = $model->set_delayed_time($map,$delayed_time);
        
        if($result == false) {
            return errReturn($model->err, -2);
        }
        
        return errReturn('延时成功!', '0');
    }
    
    
    /**
     * 确认收款
     * @param type $data
     * @return type
     */
    public function accept_collections(&$data) {
         //验证参数ID
        if(!isset($data['id']) ||  intval($data['id']) === 0 || !isset($data['pwd'])) {
            //清除缓存
            cache('accept_collections_form_token'.$data['user_id'],null);
            return errReturn('参数错误!', -501);
        }
        //匹配表ID
        $map['id'] = $data['id'];
        $map['user_id'] = $data['user_id'];  
        $user = \think\Loader::model('common/User', 'model');
        $user_info = $user->getUser(["id"=>$data['user_id']],'secondary_password as pay_password,security',$data['user_id'])->toArray();
        if(!$user_info){
            cache('accept_collections_form_token'.$data['user_id'],null);
            return errReturn('用户ID不存在!', -502);
        }
        if($user_info['security']){
            $pwd = set_password(md5(trim($data['pwd'])));
            $pwd_sed = set_password(md5(trim($data['pwd'])),$info['security']);/*登录密码*/
        }else{
            $pwd = set_old_password(trim($data['pwd']));
            $pwd_sed = set_old_password(trim($d['password']));/*登录密码*/
        }
        if($pwd != $user_info['pay_password']) {
            cache('accept_collections_form_token'.$data['user_id'],null);
            return errReturn('二级密码不正确!', -502);
        }
        /*登录密码与二级密码不能一致*/
        if($pwd == $pwd_sed){
            $return['code'] = 0;
            $return['msg']  = '二级密码与登陆密码不能一致!';
            return $return;
        }
        $map['id']          = $data['id'];
        $map['user_id']     = $data['user_id'];
        $map['create_time'] = $data['create_time'];
        $model = \think\Loader::model('user/UserMatchhelp', 'model');
        $result =$model->setUserPayToMatchingIsLastNo($map);
        //清除缓存
        cache('accept_collections_form_token'.$data['user_id'],null);
        if($result == false) {
            return errReturn($model->err, -2);
        }
        return errReturn('确认收款成功!', '0');
    }
}
