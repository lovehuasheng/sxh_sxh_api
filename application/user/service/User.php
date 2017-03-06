<?php
namespace app\user\service;
use think\Model;
use \think\Request;

class User extends Model {

    public function register() {
        //经过过滤
        $sig    = Request::instance()->get('sig');
        $params = Request::instance()->post();
        //验证参数
        if (empty($params) || empty($sig)) {
            return errReturn('参数错误',-11);
        } 
        
        $data = json_decode(decrypt($params['data'],substr($sig,0,16),substr($sig,-16,16)),true);
        //写入日志
        trace('service过滤的post参数：' . json_encode($data, JSON_UNESCAPED_UNICODE));
        trace('service过滤的sig参数：' . $sig);
        
        //签名比对
        $result = validate_response($data, $sig);
        trace('service请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code']);
        }
        unset($result);
        unset($sig);
        
        $logic = \think\Loader::model('User', 'logic');
        return $logic->register($data);
        
        
        
    }
    
    
    /**登录
     * 
     */
    public function login() {
                //经过过滤
        $sig    = Request::instance()->get('sig');
        $params = Request::instance()->post();
        //验证参数
        if (empty($params) || empty($sig) || !isset($params['data'])) {
            return errReturn('参数错误',-11);
        } 
        
        $data = json_decode(decrypt($params['data'],substr($sig,0,16),substr($sig,-16,16)),true);
        //写入日志
        trace('service过滤的post参数：' . json_encode($data, JSON_UNESCAPED_UNICODE));
        trace('service过滤的sig参数：' . $sig);
        
        //签名比对
        $result = validate_response($data, $sig);
        trace('service请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        

        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
       
        $logic = \think\Loader::model('User', 'logic');
        return $logic->login($data);
    }

}
