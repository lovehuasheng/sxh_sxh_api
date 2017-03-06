<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\user\validate;

use think\Validate;

class Provide extends Validate
{

    protected $rule = [
        'user_id'          => 'require',
    ];
    
    protected $message = [
        'user_id.require'              => '用户丢失请重新登陆！|400',
    ];
    

}