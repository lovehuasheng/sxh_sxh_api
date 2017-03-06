<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\user\validate;
use think\Validate;
/**
 * Description of Userinfo
 *
 * @author shanhubao
 */
class Userinfo extends Validate{
    protected $rule = [
        //'id'        =>  'require|max:2',
        'verify'            => 'require',
        'username'          => 'require|checkInPreg:/[^\x80-\xff]/|betweenStrlen:6,16',
        'password'          => 'require|betweenStrlen:6,16',
        'rePassword'        => 'require|confirm:password',
        'Phone'             => 'require|checkInPreg:/^1[34578]\d{9}$/',
        'name'              => 'require|checkInPreg:/[^x80-xff]/|betweenStrlen:3,24',
        'referee_name'      => 'require',
    ];
    
    protected $message = [
        //'id.max'  =>  '长度最大不能超过2|200',
        'verify'                    => '验证码不能为空|300',
        'username'                  => '帐户名不能为空|301',
        'username.checkInPreg'      => '帐户名不能包含中文|302',
        'username.betweenStrlen'    => '帐户名必需在6-16个字符之间|303',
        'password'                  => '密码不能为空|304',
        'password.betweenStrlen'    => '密码长度必需为6-16个字符之间|305',
        'rePassword'                => '确认密码不能为空|306',
        'rePassword.confirm'        => '两次密码不一致|307',
        'name'                      => '姓名不能为空|308',
        'name.checkInPreg'          => '姓名必需为中文|309',
        'name.betweenStrlen'        => '姓名长度需在2-8个中文字符之间|310',
        'phone'                     => '手机号码不能为空|311',
        'phone.checkInPreg'         => '手机号码格式有误|312',
        'referee_name'              => '推荐人不能为空|313',
    ];
    
    //正则验证
    protected function checkInPreg($val , $preg) {
        if(preg_match($preg , $val)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * between验证两值是否相等（改。。。。）
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $rule  验证规则
     * @return bool
     */
    protected function betweenStrlen($value, $rule)
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }
        list($min, $max) = $rule;
        return strlen($value) >= $min && strlen($value) <= $max;
    }
}
