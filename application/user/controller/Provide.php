<?php

// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 童自扬 <2421886569@qq.com> 
// +----------------------------------------------------------------------
// | Function: 提供资助控制器
// +----------------------------------------------------------------------

namespace app\user\controller;

use app\common\controller\Base;

class Provide extends Base {
    
    /**
     * 提供资助列表
     * @return type
     */
    public function provide_list() {
       
        //写入日志
        trace('controller中provide_list方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        $service = \think\Loader::model('Provide', 'service');

        return $service->give_list($this->data,$this->sig);
    }
    
    
    /**
     * 取消提供资助
     * @return type
     *  @Author 童自扬
     * @time  2016-10-03 
     */
    public function provide_destroy() {
        return errReturn('请到电脑端进行取消订单操作！' , -101);exit;
        trace('controller中provide_destroy方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        $service = \think\Loader::model('Provide', 'service');

        return $service->give_destroy($this->data,$this->sig);
    }
    
    
    /**
     * 提供资助的匹配详情页
     * @return type
     * @Author 童自扬
     * @time  2016-10-04
     */
   public function get_provide_detail() {
       
        //写入日志
        trace('controller中get_provide_detail方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        $service = \think\Loader::model('Provide', 'service');

        return $service->give_detail($this->data,$this->sig);
   }
   
   
   /**
    * 进入打款人页面
    * @Author 童自扬
    * @time  2016-10-04
    * @return type
    */
   public function get_pay_person_msg() {
       
        //写入日志
        trace('controller中get_pay_person_msg方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        $service = \think\Loader::model('Provide', 'service');

        return $service->give_person_msg($this->data,$this->sig);
   }
   
   
   /**
    * 上传打款截图
    * @Author 童自扬
    * @time  2016-10-06
    * @return type
    */
   public function upload_pay_picture() {
        //trace('service的upload_pay_picture方法过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));
        //trace('service的upload_pay_picture方法过滤的file参数：' . json_encode($_FILES, JSON_UNESCAPED_UNICODE));
        //签名比对
        $result = validate_response($this->data,$this->sig);
        //trace('service的upload_pay_picture方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        $info = new \org\Upload(config('upload_picture'),'Qiniu',config('qiniu'));
        $tmp = $info->upload();
        if(!$tmp) {
            return errReturn($info->getError(),-1);
        }
        $this->data['images'] = $tmp['file']['savename'];
        unset($tmp);
        //调用业务逻辑
        $logic = \think\Loader::model('Provide', 'logic');
        return $logic->upload_pay_picture($this->data);
   }
   

}
