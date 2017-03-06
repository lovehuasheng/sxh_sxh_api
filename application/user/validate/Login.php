<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\user\validate;

use think\Validate;

class Login extends Validate
{

    protected $rule = [
        'username'          => 'require',
        'password'          => 'require',
    ];
    
    protected $message = [
        'username.require'       => '用户名不能为空|301',
        'password.require'              => '密码不能为空|302',
    ];
    

}