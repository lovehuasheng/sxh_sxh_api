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

namespace app\member\logic;

use think\Model;

class Wallet extends Model {
    
    /**
     * 查看会员钱包
     * @param type $data
     * @return type
     */
    public function get_user_wallet(&$data) {
        //字段
        $field         = ['big_kind_wallet','kind_wallet','wealth_wallet','comfortably_wallet','needy_wallet','poor_wallet','user_id','activate_currency','guadan_currency','wallet_currency','manage_wallet','invented_currency','order_taking'];
        //示例模型
        $model         = model('user/UserAccount');
        //数据
        $result_list   = $model->getUserAccount($data['user_id'],$field);
        $redis = \org\RedisLib::get_instance();
        if($result_list['big_kind_wallet'] >0){
            $bkw['money'] = $result_list['big_kind_wallet'];
            $bkw['com'] = '大德钱包';
            $time = $redis->hget("sxh_userinfo:id:".$data['user_id'],"big_kind_wallet_last_changetime");
            if(!$time){
                $time = time();
            }
            $bkw['time'] = $time;
            $result_list['d'][] = $bkw;
        }
        if($result_list['kind_wallet']>0){
            $kw['money'] = $result_list['kind_wallet'];
            $kw['com'] = '德善钱包';
            $time = $redis->hget("sxh_userinfo:id:".$data['user_id'],"kind_wallet_last_changetime");
            if(!$time){
                $time = time();
            }
            $kw['time'] = $time;
            $result_list['d'][] = $kw;
        }
        if($result_list['wealth_wallet']>0){
            $ww['money'] = $result_list['wealth_wallet'];
            $ww['com'] = '富人钱包';
            $time = $redis->hget("sxh_userinfo:id:".$data['user_id'],"wealth_wallet_last_changetime");
            if(!$time){
                $time = time();
            }
            $ww['time'] = $time;
            $result_list['d'][] = $ww;
        }
        if($result_list['comfortably_wallet']>0){
            $cw['money'] = $result_list['comfortably_wallet'];
            $cw['com'] = '小康钱包';
            $time = $redis->hget("sxh_userinfo:id:".$data['user_id'],"comfortably_wallet_last_changetime");
            if(!$time){
                $time = time();
            }
            $cw['time'] = $time;
            $result_list['d'][] = $cw;
        }
        if($result_list['needy_wallet']>0){
            $nw['money'] = $result_list['needy_wallet'];
            $nw['com'] = '贫穷钱包';
            $time = $redis->hget("sxh_userinfo:id:".$data['user_id'],"needy_wallet_last_changetime");
            if(!$time){
                $time = time();
            }
            $nw['time'] = $time;
            $result_list['d'][] = $nw;
        }
        if($result_list['poor_wallet']>0){
            $pw['money'] = $result_list['poor_wallet'];
            $pw['com'] = '特困钱包';
            $time = $redis->hget("sxh_userinfo:id:".$data['user_id'],"poor_wallet_last_changetime");
            if(!$time){
                $time = time();
            }
            $pw['time'] = $time;
            $result_list['d'][] = $pw;
        }
        $result_list['check_token'] = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        unset($data);
        return errReturn('请求成功','0',set_aes_param($result_list));
    }
   
    /**
     * 转让善种子
     * @param type $data
     * @return type
     */
    public function attorn_activate_currency(&$data) {
        //查看接受善种子用户是否正确，是否是五级内用户
        
        //校验用户是否是自己
        
        //查看二级密码是否正确
        //校验是否为管理员
        //查看钱包数量是否足够
        
        return errReturn('请求成功','0',set_aes_param($result_list));
    }
}

