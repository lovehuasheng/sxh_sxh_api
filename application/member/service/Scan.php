<?php

namespace app\member\service;

use think\Model;
use \think\Request;

class Scan extends Model {
    /*
     * 扫码登录
     */
    public function scan_code_action($data,$sig){
        
        //签名比对
        if(!is_numeric($data['user_id'])&&$data['user_id']<1){
            return errReturn('user_id参数错误，只能为大于1数字！', '400');
        }
        //签名比对
        if(!is_numeric($data['time'])&&$data['time']<(time()-180)){
            return errReturn('二维码过期，请重新登录', '400');
        }
        $result = validate_response($data, $sig);
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        unset($result);
        unset($sig);
        $arr['user_id'] = $data["user_id"];
        $arr['time'] = $data["time"];
        $arr['code_id'] = $data["code_id"];
        //$url = "http://newsxh.cc/user/index/qrLogin";
        $url = "http://newsxh.cc/user/index/qrLogin";
        //$url = "http://www.d.com/data/test/index";
        $arr = [];
        $c = $this->http_url($url,"",$arr,"post");
        $arr = json_decode($c,true); 
        if($arr['errorCode'] == 0 && !empty($arr)){
            $return['errorCode'] = 0;
            $return['errorMsg']  = '登录成功';
            $return['result']    = [];
        }else{
            $return['errorCode'] = 1;
            $return['errorMsg']  = '登录失败';
            $return['result']    = [];
        }
        return $return; 
    }
    function http_url($url, $param, $data, $httpType = 'get', $header = 'utf-8') {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); //SSL证书认证
        curl_setopt($ch, CURLOPT_URL, $url);
        $header = array('Accept-Charset: ' . $header);
        $header[] = 'charset: utf-8';
        //$header[] = 'Content-Type: application/x-www-form-urlencoded';
      //  $header[] = 'Content-Type: multipart/form-data';
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //严格认证
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // post传输数据
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 显示输出结果
        $tmpInfo = curl_exec($ch);
        curl_close($ch);
        return $tmpInfo;
    }
    
}
