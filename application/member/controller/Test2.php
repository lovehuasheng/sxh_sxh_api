<?php

//
//    app 2.0 测试
//
namespace app\member\controller;
use \think\Db;

class Test2{
    public $http_url_dev = 'http://tpapi.vn/';
    public $http_url = 'http://api-test-monitor.shanxinhui.com/';
    public $http_url_fyl = 'http://www.s.com/';
    public $http_url_online = 'http://api-test-monitor.shanxinhui.com/';
    /**
     * 收入记录
     */
    public function index(){
        //echo "hello world";
        $url = "http://192.168.1.170:18888/users/demo";
        //$url = "http://www.s.com/member/test2/test";
        $arr['id'] = 5;
        $arr['name'] = 1;
        //$b = http_build_query($arr);
        $json = $this->http($url, '', $arr, 'post');
        //$json = $this->http($url, '', $a, 'post');
        //echo json_decode($json);
        dump($json);  
        
        //print_R(json_decode($json));
    }
    public function test(){
       // $arr = $_POST;
        $arr = $_REQUEST;
        file_put_contents("D:/1.txt", var_export($_POST, true));
        print_R($arr);
        //echo $arr;
    }
    public function sql(){
        $sql = '';
        for($i=1;$i<100;$i++){
            $k = ($i - 1)*10000;
            $j = $i*10000;
            $sql .= "insert into sxh_user_info_1 select userid,'',name,1,2,email,phone,address,city,AlpayAccount,WeixinAccount,BankName,'',BankAccount,CardID,Referee,RefereeID,'','','',0,VerifyUid,VerifyName,province,0,town,area,ImageA,imageb,imagec,'',telnumber,createtime,updatetime,0,0,0,servicenumber,VerifyTime,VerifyDate,AssignDate,0,'',grade,avatar from o_userinfo where userid <=$j and userid >$k;"."<br />";
        }
        echo $sql;
    } 
    public function q($userid = 75,$nickid = 800,$m = 'a'){
        $sql1 = "select user_id from sxh_user_relation where full_url like '%,".$userid.",%' and nickid >= ".$nickid." and user_id != ".$userid;
        $d =DB::query($sql1);
        $sql2 = " full_url like '%,".$userid.",%' ";
        if($d != false || !empty($d)){ //同等级节点
            foreach($d as $k=>$v){
                $sql2 .= " and full_url not like '%,".$v['user_id'].",%' ";
            }
        }
        $sql3 = "update sxh_user_relation set ".$m." =  ".$userid."  where ".$sql2.";<br />";
	echo $sql3;
        //DB::execute($sql3);
    }
    public function w(){
        set_time_limit(0);
        $sql1 = "select user_id,nickid from sxh_user_relation where  nickid in (800,400,100) order by nickid desc ";
		
        $d =DB::query($sql1);
        foreach($d as $k=>$v){
            if($v['nickid'] == 800){
                $m = 'a';
            }else if($v['nickid'] == 400){
                $m = 'b';
            }else{
                $m = 'c';
            }
            $this->q($v['user_id'],$v['nickid'],$m);
        }
        echo 'done';
    } 
	 
    public function get_log_income() {
        $arr['type'] = 5;
        $arr['page'] = 1;
        $arr['current_page'] = 10;
        $arr['user_id'] = 135;
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
        
        $url = $this->http_url_dev.'member/income/get_log_list?ts=1&sig='.$md5_str;
       
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
      
        $json = $this->http($url, '', $tmp, 'post');

        
        //返回参数aes解密
        $arr = json_decode($json,true);
        
       $data = json_decode(decrypt($arr['result']['data'],substr($arr['result']['sig'],0,16),substr($arr['result']['sig'],-16,16)),true);
        dump($json);        
        dump($arr);
        dump($data);
        die;
    }
    
    
    /**
     * 支出记录
     */
    public function get_log_outgo() {
        $arr['type'] = 4;
        $arr['page'] = 1;
        $arr['current_page'] = 10;
        $arr['user_id'] = 75;
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
        
        $url = $this->http_url_fyl.'member/outgo/get_log_list?ts=1&sig='.$md5_str;
       
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
      
        $json = $this->http($url, '', $tmp, 'post');

        
        //返回参数aes解密
        $arr = json_decode($json,true);
        
       $data = json_decode(decrypt($arr['result']['data'],substr($arr['result']['sig'],0,16),substr($arr['result']['sig'],-16,16)),true);
        dump($json);        
        dump($arr);
        dump($data);
        die;
    }
    
    
    
     public function get_user_wallet() {
        //$arr['type'] = 5;
        //$arr['page'] = 1;
        //$arr['current_page'] = 10;
        $arr['user_id'] = 398770;
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
        
        $url = $this->http_url_fyl.'member/wallet/get_user_wallet?ts=1&sig='.$md5_str;
       
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
      
        $json = $this->http($url, '', $tmp, 'post');

        dump($json); 
        //返回参数aes解密
        $arr = json_decode($json,true);
        
        $data = json_decode(decrypt($arr['result']['data'],substr($arr['result']['sig'],0,16),substr($arr['result']['sig'],-16,16)),true);
              
        dump($arr);
        dump($data);
        die;
    }
    public function together_wallet() {
        //$arr['type'] = 5;
        //$arr['page'] = 1;
        //$arr['current_page'] = 10;
        $arr['user_id'] = 398770;
        $arr['check_token'] = 4011201;
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
        
        $url = $this->http_url_fyl.'member/wallet/together_wallet?ts=1&sig='.$md5_str;
       
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
      
        $json = $this->http($url, '', $tmp, 'post');

        dump($json); 
        //返回参数aes解密
        $arr = json_decode($json,true);
        dump($arr);
       $data = json_decode(decrypt($arr['result']['data'],substr($arr['result']['sig'],0,16),substr($arr['result']['sig'],-16,16)),true);
              
       
        dump($data);
        die;
    }
    public function save_accept() {
        $arr['money'] = 1000;
        $arr['user_id'] = 398770;
        $arr['password'] = '123456a';
        $arr['check_token'] = '123456aa';
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
        
        $url = $this->http_url_fyl.'member/wallet/save_accept?ts=1&sig='.$md5_str;
       
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
       
      
        $json = $this->http($url, '', $tmp, 'post');
        
        
        //返回参数aes解密
        $arr = json_decode($json,true);
       // $data = json_decode(decrypt($arr['result']['data'],substr($arr['result']['sig'],0,16),substr($arr['result']['sig'],-16,16)),true);
        dump($json);        
        dump($arr);
       // dump($data);
        die;
    }
    public function provide_data() {
        //echo 123;die();
        $arr['money'] = 1000;
        $arr['user_id'] = 398770;
        $arr['password'] = '123456a';
        $arr['check_token'] = '12345671';
        
        $arr['cid'] = 2;
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        //echo  config('app_api_key');die();
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        //echo $str;die();
        //echo $str;die();
        $md5_str = strtoupper(md5($str));
        //echo $md5_str;die();
        // echo $url = $this->http_url_fyl.'member/provide/provide_data?ts=1&sig='.$md5_str;
        //die();
        $url = $this->http_url_fyl.'member/provide/provide_data?ts=1&sig='.$md5_str;
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
       
        //print_R($tmp);die();
        $json = $this->http($url, '', $tmp, 'post');
        //echo $json;die();
        dump($json);  
        //返回参数aes解密
        $arr = json_decode($json,true);
        //$data = json_decode(decrypt($arr['result']['data'],substr($arr['result']['sig'],0,16),substr($arr['result']['sig'],-16,16)),true);
        dump($arr);
        //dump($data);
        die;
    }
    public function provide_sel() {
        $arr['user_id'] = 398770;
        $arr['cid'] = 2;
        $arr['check_token'] = '1234567';
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
        
        echo $url = $this->http_url_fyl.'member/provide/provide_sel?ts=1&sig='.$md5_str;
       
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
       
      
        $json = $this->http($url, '', $tmp, 'post');
        
        
        //返回参数aes解密
        $arr = json_decode($json,true);
        //$data = json_decode(decrypt($arr['result']['data'],substr($arr['result']['sig'],0,16),substr($arr['result']['sig'],-16,16)),true);
        dump($json);        
        dump($arr);
        //dump($data);
        die;
    }
    public function scan_code() {
        $arr['user_id'] = 2;
        $arr['time'] = '1479457171';
        $arr['code_id'] = '1479457171gfV2MG0eNmS0x';
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
        
        echo $url = $this->http_url_fyl.'member/scan/scan_code?ts=1&sig='.$md5_str;
       
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
       
      
        $json = $this->http($url, '', $tmp, 'post');
        
        
        //返回参数aes解密
        $arr = json_decode($json,true);
        //$data = json_decode(decrypt($arr['result']['data'],substr($arr['result']['sig'],0,16),substr($arr['result']['sig'],-16,16)),true);
        dump($json);        
        dump($arr);
        //dump($data);
        die;
    }
    function souhu(){
        //$url = 'http://www.shanxinhui.com';
        //echo file_get_contents('http://www.s1.com/member/provide/provide_data?ts=1&sig=6A6F8A8D65CF80A32FA0FA78CBF6F31E');
        //die();
         //   $url = 'http://www.demo.com/';
        $url = 'http://www.s.com/member/provide/provide_data?ts=1&sig=6A6F8A8D65CF80A32FA0FA78CBF6F31E';
        $json = $this->http($url, '', [], 'post');
        dump($json);
    }
    function http1($url, $param, $data, $httpType = 'get', $header = 'utf-8') {
        //echo $url;die();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
        /*
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); //SSL证书认证
        curl_setopt($ch, CURLOPT_URL, $url);
        $header = array('Accept-Charset: ' . $header);
        $header[] = 'charset: utf-8';
       // $header[] = 'Content-Type: application/json ,Content-Length: ' . strlen($data);
        $header[] = 'Content-Type: application/x-www-form-urlencoded';
        //$header[] = 'Content-Type: multipart/form-data';
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //严格认证
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // post传输数据
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // post传输数据
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 显示输出结果

        $tmpInfo = curl_exec($ch);
        curl_close($ch);
        return $tmpInfo;*/
    }
    function http($url, $param, $data, $httpType = 'get', $header = 'utf-8') {

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
