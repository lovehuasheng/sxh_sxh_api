<?php

/**
 * 个人中心
 * @author huanghuasheng
 * @time 20161115
 */
namespace app\user\controller;
use app\common\controller\Base;

class UserCenter extends Base{
    /* 
     * 头像上传
     * @author:huanghuasheng
     * @time:20161115
     */
    public function avatarPhoto(){
        //写入日志
        trace('service的upload_avatar_picture方法过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));
        trace('service的upload_avatar_picture方法过滤的file参数：' . json_encode($_FILES, JSON_UNESCAPED_UNICODE));

        //签名比对
         $result = validate_response($this->data,$this->sig);

        trace('service的upload_avatar_picture方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        //上传七牛云
        $info = new \org\Upload(config('upload_picture'),'Qiniu',config('qiniu'));
        $tmp = $info->upload();
        if(!$tmp) {
            return errReturn($info->getError(),-1);
        } 
 
        $this->data['images'] = $tmp['file']['savename'];
        unset($tmp);
        //调用业务逻辑，保存头像照片
        $logic = \think\Loader::model('UserCenter', 'logic');
        return $logic->upload_avatar_picture($this->data);
    }
    /**
     * 查找账户详细
     * @author huanghuasheng
     */
    public function userAccountInfo(){
        //写入日志
        trace('controller中userAccountInfo方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        //签名比对
         $result = validate_response($this->data,$this->sig);

        trace('controller中userAccountInfo方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }

        //调用业务逻辑，取出数据
        $logic = \think\Loader::model('UserCenter', 'logic');
        return $logic->accountInfo($this->data);
    }
    /**
     * 转出操作
     * @author:huanghuasheng
     * @return array
     */
    public function outputAccount(){
        //写入日志
        trace('controller中outputAccount方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        //签名比对
         $result = validate_response($this->data,$this->sig);

        trace('controller中outputAccount方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        //调用业务逻辑，处理数据
        $logic = \think\Loader::model('UserCenter', 'logic');
        return $logic->outputAccount($this->data);
    }
    /**
     * 提取管理奖
     * @author huanghuasheng
     * @param type $data
     * @return array
     */
    public function outManageAccount(){
        //写入日志
        trace('controller中outManageAccount方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        //签名比对
         $result = validate_response($this->data,$this->sig);

        trace('controller中outManageAccount方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        //调用业务逻辑，处理数据
        $logic = \think\Loader::model('UserCenter', 'logic');
        return $logic->outManageAccount($this->data);
    }
    /**
     * 获取手机验证码
     * @author huanghuasheng
     */
    public function getPhoneCode(){
        //写入日志
        trace('controller中getPhoneCode方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        //签名比对
        if(empty($this->data) || empty($this->sig)){
            return errReturn('参数为空！', 0);
        }
         $result = validate_response($this->data,$this->sig);
         
        trace('controller中getPhoneCode方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        //调用业务逻辑，处理数据
        $logic = \think\Loader::model('UserCenter', 'logic');
        return $logic->getPhoneCode($this->data);
    }
    /**
     * 完善资料
     * @author huanghuasheng
     * @return array
     */
    public function perfectUserInfo(){
        //trace('huanghuasehng：' . json_encode($_FILES, JSON_UNESCAPED_UNICODE));
        //return errReturn('你只是到了这里，没有执行到程序', -1);die();
        trace('controller中perfectUserInfo方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));
        
        //签名比对
        if(empty($this->data) || empty($this->sig)){
            return errReturn('参数为空！', -1);
        }

         $result = validate_response($this->data,$this->sig);

        trace('controller中perfectUserInfo方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        $img = array();
        $img_image = $_FILES;
        if((isset($img_image['image_a']) && !empty($img_image['image_a'])) || (isset($img_image['image_b']) && !empty($img_image['image_b'])) || (isset($img_image['image_c']) && !empty($img_image['image_c']))){
            //上传七牛云
            $info = new \org\Upload(config('upload_picture'),'Qiniu',config('qiniu'));
            $tmp = $info->upload();
            if(!$tmp) {
                return errReturn($info->getError(),-1);
            }
            if(isset($img_image['image_a']) && !empty($img_image['image_a'])){
                $img['image_a'] = $tmp['image_a']['savename'];
            }
            if(isset($img_image['image_b']) && !empty($img_image['image_b'])){
                $img['image_b'] = $tmp['image_b']['savename'];
            }
            if(isset($img_image['image_c']) && !empty($img_image['image_c'])){
                $img['image_c'] = $tmp['image_c']['savename'];
            }
        }

        unset($tmp);
        //调用业务逻辑，处理数据
        $logic = \think\Loader::model('UserCenter', 'logic');
        return $logic->perfectUserInfo($this->data,$img);
    }
    /**
     * 修改密码
     * @author huanghuasheng
     * @return array
     */
    public function modUserPassword(){
        trace('controller中modUserPassword方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        //签名比对
         $result = validate_response($this->data,$this->sig);

        trace('controller中modUserPassword方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        //调用业务逻辑，处理数据
        $logic = \think\Loader::model('UserCenter', 'logic');
        return $logic->modUserPassword($this->data);
    }
    /**
     * 查看个人资料
     * @author huanghuasheng
     */
    public function getUserInfo(){
        trace('controller中getUserInfo方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        //签名比对
         $result = validate_response($this->data,$this->sig);

        trace('controller中getUserInfo方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        //调用业务逻辑，处理数据
        $logic = \think\Loader::model('UserCenter', 'logic');
        return $logic->getUserInfo($this->data);
    }
    /**
     * 验证查看收款人信息的验证码
     * @return array
     */
    public function checkPhoneCode(){
        trace('controller中checkPhoneCode方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        //签名比对
         $result = validate_response($this->data,$this->sig);

        trace('controller中checkPhoneCode方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        //调用业务逻辑，处理数据
        $logic = \think\Loader::model('Check', 'logic');
        return $logic->checkPhoneCode($this->data);
    }
    /**
     * 验证查看收款人信息的二级密码
     * @return array
     */
    public function checkUserPassword(){
        trace('controller中checkUserPassword方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        //签名比对
         $result = validate_response($this->data,$this->sig);

        trace('controller中checkUserPassword方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        //调用业务逻辑，处理数据
        $logic = \think\Loader::model('Check', 'logic');
        return $logic->checkUserPassword($this->data);
    }
    /**
     * 发送查看收货人信息的验证码
     * @return array
     */
    public function getAccountCode(){
        trace('controller中getAccountCode方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        //签名比对
         $result = validate_response($this->data,$this->sig);

        trace('controller中getAccountCode方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        //调用业务逻辑，处理数据
        $logic = \think\Loader::model('Check', 'logic');
        return $logic->getPhoneCode($this->data);
    }
    /**
     * 验证用户名唯一
     * @return array
     */
    public function checkUserName(){
        trace('controller中getAccountCode方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        //签名比对
         $result = validate_response($this->data,$this->sig);

        trace('controller中getAccountCode方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        //调用业务逻辑，处理数据
        $logic = \think\Loader::model('Check', 'logic');
        return $logic->checkUserName($this->data);
    }
    /**
     * 根据账号查询用户姓名
     * @return array
     */
    public function findUserName(){
        trace('controller中getAccountCode方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        //签名比对
         $result = validate_response($this->data,$this->sig);

        trace('controller中getAccountCode方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        //调用业务逻辑，处理数据
        $logic = \think\Loader::model('Check', 'logic');
        return $logic->findUserName($this->data);
    }
    /**
     * 个人中心刷新信息
     * @return array
     */
    public function getCenterInfo(){
        trace('controller中getAccountCode方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        //签名比对
         $result = validate_response($this->data,$this->sig);

        trace('controller中getAccountCode方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        //调用业务逻辑，处理数据
        $logic = \think\Loader::model('Check', 'logic');
        return $logic->getCenterInfo($this->data);
    }
    /**
     * 异常登录
     */
    public function sendLoginCode(){
        trace('controller中sendLoginCode方法接受未过滤的post参数：' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

        //签名比对
         $result = validate_response($this->data,$this->sig);

        trace('controller中sendLoginCode方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        //调用业务逻辑，处理数据
        $logic = \think\Loader::model('Check', 'logic');
        return $logic->sendLoginCode($this->data);
    }
}
