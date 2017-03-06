<?php

// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 童自扬 <2421886569@qq.com> 
// +----------------------------------------------------------------------
// | Function: 提供资助业务逻辑层
// +----------------------------------------------------------------------

namespace app\user\logic;

use think\Model;
use \think\Db;

class Provide extends Model {

    /**
     * 提供资助列表业务逻辑层
     * @param array $data
     * @return type
     * @Author 童自扬
     * @time  2016-10-03
     */
    public function give_list($data) {

        $model = \think\Loader::model('UserProvide');
        $redis = \org\RedisLib::get_instance();
        $arr = array(0,0,1,3);
        $map = array();
        $type = isset($data['type']) ? $data['type'] : 1;
        $map['status'] = $arr[$type];
        $map['user_id'] = intval($data['user_id']);
        $page = isset($data['page']) ? $data['page']:1;
        //如果页面没传每页条数，就去配置的条数
        $total = isset($data['current_page'])?$data['current_page']:config('app_list_rows');
        $redis->delDataList('provide',$map['user_id'],$type);
        $result_list = json_decode($redis->lindexDataList('provide',$map['user_id'],$type,$page-1),true);
        if(!$result_list){
            $result_list = $model->getListByStatus($map, intval($page), intval($total),'id,type_id,cid,cname,money,used,user_id,status,create_time');
            if(empty($result_list)) {
                $result_list['list']['data'] = [];
                $result_list['total'] = 0;
                $result_list['per_page'] = 10;
                $result_list['current_page'] = 0;
            }
            $redis->rPushDataList('provide',$map['user_id'],$type,  json_encode($result_list));
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
    public function give_destroy(&$data) {
        //验证参数ID
        if(!isset($data['id']) || intval($data['id']) === 0 || empty($data['pwd'])) {
            return errReturn('参数错误!', -501);
        }
        //添加验证码
        $redis = \org\RedisLib::get_instance();
        $phone = $redis->hgetUserinfoByID($data['user_id'],'phone');
        $code = cache('code'.$data['user_id'].$phone);
        if(empty($code) || $code != $data['verify']){
            return errReturn('验证码不正确或已过期！' , -44444);
        }
        //二级密码验证
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
        unset($user_info);
        $model = \think\Loader::model('common/UserProvide');
        $table_provide_now  = $model->getPartitionTableName(['quarter'=>$data['create_time']],'quarter',['type' => 'quarter','expr' => 3]);
        $sql = "update ".$table_provide_now." set flag = 2 where match_num=0 and user_id = ".$data['user_id']." and id = ".$data['id'];
        $result_list = Db::execute($sql);
        if($result_list == false) {
            return errReturn('取消失败!', -2);
        }
        $redis = \org\RedisLib::get_instance(); /*撤销订单时挂单次数减一*/
        $num = $redis->hget('sxh_userinfo:id:'.intval($data['user_id']),"provide_create_num");/*提出挂单的次数*/
        $redis->hset('sxh_userinfo:id:'.intval($data['user_id']),"provide_create_num",$num-1);/*成功挂单，挂单的次数-1*/
        //取消挂单需要清除的缓存
        $redis->delDataList('provide',intval($data['user_id']),1);
        return errReturn('取消成功!', '0');
    }
    
    /**
     * 提供资助的匹配详情页[业务逻辑层]
     * @param type $data
     * @return type
     * @Author 童自扬
     * @time  2016-10-04
     */
    public function give_detail($data) {
        //验证参数ID
        if(!isset($data['id']) || intval($data['id']) === 0 || empty($data['create_time'])) {
            return errReturn('参数错误!', -501);
        }
        $arr = array();
        $arr['other_id'] = intval($data['id']);
        $arr['create_time'] = $data['create_time'];
        $model = \think\Loader::model('UserMatchhelp');
        $redis = \org\RedisLib::get_instance();
        $redis->delMatchDetail('provide',$data['user_id'],$data['id']);
        $list = json_decode($redis->getMatchDetail('provide',$data['user_id'],$data['id']));
        if(!$list){
            $result_list = $model->getMatchingListByProvideID($arr,'id,pid,other_type_id,user_id,money,other_id,other_user_id,other_money,status,sign_time,create_time,name,pay_time,delayed_time_status,expiration_create_time,audit_time');
            if(empty($result_list)){
                return errReturn('数据未匹配',-23);
            }
            //整理数据返回
            $arr = array('提供资助','提供资助','接单资助');
            $status_text = array('未审核','未布施','已布施','已布施');
            $sign_text = array('未审核','未确认','未确认','已确认');
            $list = array();
            foreach($result_list as $k=>$v){
                if($v['other_user_id'] != $data['user_id']){
                    return errReturn('数据错误',-12);break;
                }
                $list[$k]['id'] = $v['id'];
                $list[$k]['pid'] = $v['pid'];
                $list[$k]['other_id'] = $v['other_id'];
                $list[$k]['matching_text'] = $arr[$v['other_type_id']];
                $list[$k]['other_money'] = $v['other_money'];
                $list[$k]['status'] = $v['status'];
                $list[$k]['status_text'] = $status_text[$v['status']];
                $list[$k]['sign_text'] = $sign_text[$v['status']];
                $list[$k]['username'] = $v['name'];
                $list[$k]['create_time'] = $v['create_time'];
                $list[$k]['pay_time'] = $v['pay_time'];
                $list[$k]['provide_overtime_status'] = 0;
                $list[$k]['accept_overtime_status'] = 0;
                //打款方未付款倒计时计算
                if($v['status'] == 1){
                    if($v['delayed_time_status']){
                        if(($v['expiration_create_time']-$_SERVER['REQUEST_TIME']) > 0){
                            $list[$k]['create_time_text'] = $v['expiration_create_time']-$_SERVER['REQUEST_TIME'];
                        }else{
                            $list[$k]['create_time_text'] = 0;
                            $list[$k]['provide_overtime_status'] = 1;
                        }
                    }else{
                        if(($_SERVER['REQUEST_TIME']-$v['audit_time']) < config('matchhelp_out_time')){
                            $list[$k]['create_time_text'] = $v['audit_time']+config('matchhelp_out_time')-$_SERVER['REQUEST_TIME'];
                        }else{
                            $list[$k]['create_time_text'] = 0;
                            $list[$k]['provide_overtime_status'] = 1;
                        }
                    }
                }else{
                    $list[$k]['create_time_text'] = 0;
                }
                if($v['status'] == 2){
                    if(($_SERVER['REQUEST_TIME']-$v['pay_time']) < config('matchhelp_out_time')){
                        $list[$k]['pay_time_text'] = $v['pay_time']+config('matchhelp_out_time')-$_SERVER['REQUEST_TIME'];
                    }else{
                        $list[$k]['pay_time_text'] = 0;
                        $list[$k]['accept_overtime_status'] = 1;
                    }
                }else{
                    $list[$k]['pay_time_text'] = 0;
                }
            }
            $redis->setMatchDetail('provide',$data['user_id'],$data['id'],json_encode($list));
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
    public function give_person_msg($data) {
        
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
        $matching = $m_match->getMatchingOneDataByID($table,$map,'id,pid,user_id,other_id,other_user_id,status,other_money,pay_image,cid,create_time');
        if($data['user_id'] != $matching['other_user_id']){
            return errReturn('数据错误!', -501);
        }
        $arr = array(7,8,10);
        if(!empty($matching)) {
            if(in_array($matching['cid'], $arr)){
                $c_info = \think\Loader::model('CompanyInfo');
                $user = $c_info->getCompanyInfo(array('company_id'=>$matching['user_id']),'business_center_id,company_name,legal_person as name,legal_alipay_account as alipay_account,mobile as phone,legal_bank_name as bank_name,legal_bank_account as bank_account');
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
                $user = $m_info->getInfo(array('user_id'=>$matching['user_id']),$matching['user_id'],'name,alipay_account,phone,weixin_account,bank_name,bank_account,tel_number,referee_id');
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
            $user['user_id'] = $matching['user_id'];
            $user['create_time'] = $matching['create_time']; 
        }else{
            return errReturn('数据错误',-23);
        }
        //合并数据数组
        return errReturn('请求成功','0',set_aes_param(['list'=>$user]));
    }
    
    
    /**
     * 上传打款图片页[业务逻辑层]
     * @param type $data
     * @return type
     * @Author 童自扬
     * @time  2016-10-06
     */
    public function upload_pay_picture(&$data) { 
            //验证参数ID
            if(!isset($data['id']) ||  intval($data['id']) == 0) {
                return errReturn('参数错误!', -501);
            }
            if(!isset($data['create_time']) ||  intval($data['create_time']) === 0){
                return errReturn('订单时间错误', -501);
            }
            $a = \think\Loader::model('common/UserMatchhelp', 'model');
            $table_match_now  = $a->getPartitionTableName(['quarter'=>$data['create_time']],'quarter',['type' => 'quarter','expr' => 3]);
            $tmp = Db::query("select pay_image,pay_time,status from ".$table_match_now." where id = ".$data['id']);
            if(count($tmp) > 0){
                if($tmp[0]['pay_time']>0 || $tmp[0]['status']!=1){
                    return errReturn('此订单提交过打款截图了!', -504);
                }
            }else{
                return errReturn('订单错误', -504);
            }
            $model = \think\Loader::model('UserPay', 'model');
            $result = $model->setUserPayImage($data);

            if($result === false) {
                return errReturn($model->err,'-2');
            }
            
            return errReturn('提交打款信息成功！','0',set_aes_param(['path'=>getQiNiuPic($data['images'])]));
    }
}
