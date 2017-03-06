<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\user\validate;
use think\Validate;

class SmsVerify extends Validate
{

    protected $rule = [
        'length'            => 'require',
        'msg'               => 'require',
        'type'              => 'require',
        'validTime'         => 'require',
        'phone'             => 'require',
    ];
    
    protected $message = [
        'length.require'            => '缺省参数！|400',
        'msg.require'               => '缺省参数！|401',
        'type.require'              => '缺省参数！|402',
        'validTime'                 => '缺省参数！|403',
        'phone'                     => '缺省参数！|404',
    ];
    

}