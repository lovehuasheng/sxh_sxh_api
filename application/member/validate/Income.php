<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\member\validate;

use think\Validate;

class Income extends Validate
{

    protected $rule = [
        'type'                      => 'number|min:0',
        'total'                     => 'number|min:10',
        'user_id'                   => 'number|min:1',
        'appkey'                    => 'require',
        'ts'                        => 'require',
    ];
    
    protected $message = [
        'type.number'               => 'type参数错误，只能为数字！|400',
        'type.min'                  => 'type参数错误，不在取值范围内！|400',
        'total.number'              => 'total参数错误，只能为数字！|400',
        'total.min'                 => 'total参数错误，不在取值范围内！|400',
        'user_id.number'            => 'user_id参数错误，只能为数字！|400',
        'user_id.min'               => 'user_id参数错误，不在取值范围内！|400',
        'appkey.require'            => 'appkey参数错误！|400',
        'ts.require'                => 'ts参数错误！|400',
    ];
    
    protected $scene =  [
        //添加数据
        'get_log_list'             =>  ['type','total','user_id','appkey','ts'],
     
    ];
}