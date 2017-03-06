<?php

// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 童自扬 <2421886569@qq.com> 
// +----------------------------------------------------------------------
// | Function: 测试接口控制器
// +----------------------------------------------------------------------

namespace app\user\controller;

use app\common\controller\Base;

class Test {
    public $http_url_dev = 'http://tpapi.vn/';
    public $http_url = 'http://api-test-monitor.shanxinhui.com/';
    public $http_url_fyl = 'http://www.s.com/';
    
    //登录接口
    public function login() {
    
        $arr['username'] = 'admin';
        $arr['password'] = '123456';
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
       $url = $this->http_url.'user/user/login?ts=1&sig='.$md5_str;
       // $url = $this->http_url_dev.'user/user/login?ts=1&sig='.$md5_str;
      echo $url;
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
        echo '数据：<br/>';
        dump($arr);
        echo 'json后：<br/>';
        dump(json_encode($arr,JSON_UNESCAPED_UNICODE));
        echo 'sig为：<br/>';
        dump($md5_str);
        echo 'key='.substr($md5_str,0,16).'<br/>';
        echo 'iv='.substr($md5_str,-16,16).'<br/>';
        echo 'aes加密后：<br/>';
        echo $tmp['data'];
        
        $json = $this->http($url, '', $tmp, 'post');

        echo '<br/>返回数据：<br/>';
        //返回参数aes解密
        $arr = json_decode($json,true);
       // dump($arr);die;
        $data = json_decode(decrypt($arr['result']['data'],substr($arr['result']['sig'],0,16),substr($arr['result']['sig'],-16,16)),true);
        dump($json);
        dump($arr);
        dump($data);
        die;
    }

    //提供资助列表
     public function provide_list() {
        $arr['type'] = 1;
        $arr['page'] = 1;
        $arr['current_page'] = 10;
        $arr['user_id'] = 77;
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
        $url = $this->http_url_fyl.'user/provide/provide_list?ts=1&sig='.$md5_str;
      //  $url = $this->http_url_dev.'user/provide/provide_list?ts=1&sig='.$md5_str;
       
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
        //取消提供资助
     public function provide_destroy() {
        $arr['pwd'] = '123456a';
        $arr['id'] = 4214;
        $arr['user_id'] = 9;
        $arr['create_time'] = 1482115538;
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
        $url = $this->http_url.'user/provide/provide_destroy?ts=1&sig='.$md5_str;
      //  $url = $this->http_url_dev.'user/provide/provide_list?ts=1&sig='.$md5_str;
       
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
    
    
    //提供资助的匹配详情页
     public function provide_detail() {
        $arr['id'] = 6455;
        $arr['user_id'] = 82;
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
        $url = $this->http_url.'user/provide/get_provide_detail?ts=1&sig='.$md5_str;
        //$url = $this->http_url_dev.'user/provide/get_provide_detail?ts=1&sig='.$md5_str;
       
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
    
    //进入打款人页面请求
     public function pay_person_msg() {
         if($_SERVER['REQUEST_TIME'] - (strtotime('2016-10-14 21:54:18')+86400) > 0) {
             echo 1;die;
         }
        echo  date('Y-m-d H:i:s',(strtotime('2016-10-14 21:54:18')+(4*3600)));
         echo '<br>';
         echo date('Y-m-d H:i:s',(strtotime('2016-10-14 21:54:18')+86400));
         echo 0;die;
        $arr['id'] = 2537;
        $arr['user_id'] = 82;
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
        $url = $this->http_url.'user/provide/get_pay_person_msg?ts=1&sig='.$md5_str;
      //  $url = $this->http_url_dev.'user/provide/get_pay_person_msg?ts=1&sig='.$md5_str;
       
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
    
    
    //进入打款人页面请求
     public function pay_person_msg1() {
         if($_SERVER['REQUEST_TIME'] - (strtotime('2016-10-14 21:54:18')+86400) > 0) {
             echo 1;die;
         }
        echo  date('Y-m-d H:i:s',(strtotime('2016-10-14 21:54:18')+(4*3600)));
         echo '<br>';
         echo date('Y-m-d H:i:s',(strtotime('2016-10-14 21:54:18')+86400));
         echo 0;die;
        $arr['id'] = 2537;
        $arr['user_id'] = 82;
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
        $url = $this->http_url.'user/provide/get_pay_person_msg?ts=1&sig='.$md5_str;
      //  $url = $this->http_url_dev.'user/provide/get_pay_person_msg?ts=1&sig='.$md5_str;
       
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
    
    
    
    
    // 接受资助内页
     public function get_accept_detail() {
        $arr['id'] = 1298;
        $arr['user_id'] = 82;
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
        $url = $this->http_url.'user/accept/get_accept_detail?ts=1&sig='.$md5_str;
        //$url = $this->http_url_dev.'user/accept/get_accept_detail?ts=1&sig='.$md5_str;
       
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
    
    
    
     // 查看收款人
     public function accept_person_msg() {
        $arr['id'] = 1925;
        $arr['user_id'] = 82;
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
        $url = $this->http_url.'user/accept/get_accept_person_msg?ts=1&sig='.$md5_str;
      //  $url = $this->http_url_dev.'user/accept/get_accept_person_msg?ts=1&sig='.$md5_str;
       
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
   public function upload12() {
        $arr['id'] = 201;
        $arr['user_id'] = 401255;
        $arr['pwd'] = '123456a';
        //$arr['check_token'] = 14817980131;
        $arr['create_time'] = 1482305413;
        $arr['ts'] = time();
        
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
        $url = $this->http_url_fyl.'user/provide/upload_pay_picture?ts=1&sig='.$md5_str;
       // $url = $this->http_url_dev.'user/accept/accept_collections?ts=1&sig='.$md5_str;
       
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
       
        $json = $this->http($url, '', $tmp, 'post');

        
        //返回参数aes解密
        //$arr = json_decode($json,true);
        
      //  $data = json_decode(decrypt($arr['result']['data'],substr($arr['result']['sig'],0,16),substr($arr['result']['sig'],-16,16)),true);
        dump($json);
     //   dump($arr);
        //dump($data);
        die;
    }
    // 收款
     public function accept_collections() {
        $arr['id'] = 483;
        $arr['user_id'] = 21;
        $arr['pwd'] = 'hhb1234';
        $arr['check_token'] = 1482839052;
        $arr['create_time'] = 1482839052;
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
        //$url = $this->http_url_fyl.'user/accept/accept_collections?ts=1&sig='.$md5_str;
       // $url = $this->http_url_dev.'user/accept/accept_collections?ts=1&sig='.$md5_str;
        
		$url = 'http://stage-api.shanxinhui.cn/user/accept/accept_collections?ts=1&sig='.$md5_str;
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
       
        $json = $this->http($url, '', $tmp, 'post');

        
        //返回参数aes解密
        //$arr = json_decode($json,true);
        
      //  $data = json_decode(decrypt($arr['result']['data'],substr($arr['result']['sig'],0,16),substr($arr['result']['sig'],-16,16)),true);
        dump($json);
     //   dump($arr);
        //dump($data);
        die;
    }
    
    
    
    // 延时打款
     public function accept_delayed() {
        $arr['id'] = 206;
        $arr['user_id'] = 401120;
        $arr['pwd'] = '123456a';
        $arr['check_token'] = '123456a';
        $arr['delayed_time'] = 4;
        $arr['create_time'] = 1482115538;
        $arr['ts'] = time();
        $arr['appkey'] = 'C154540058CB3FC1';
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5($str));
     
        $url = $this->http_url_fyl.'user/accept/accept_delayed?ts=1&sig='.$md5_str;
       // $url = $this->http_url_dev.'user/accept/accept_delayed?ts=1&sig='.$md5_str;
       
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
       
        $json = $this->http($url, '', $tmp, 'post');

        
        //返回参数aes解密
        //$arr = json_decode($json,true);
        
      //  $data = json_decode(decrypt($arr['result']['data'],substr($arr['result']['sig'],0,16),substr($arr['result']['sig'],-16,16)),true);
        dump($json);
     //   dump($arr);
        //dump($data);
        die;
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
    
    
    public function upload() {
        
//        $array=array(
//             'filename'=>$_FILES['file']['name'],  //图片相对于网站根目录的路径
//             'content-type'=>$_FILES['file']['type'],//'image/jpeg',  //文件类型
//             'filelength'=>$_FILES['file']['size'],//图文大小
//         );
//        $real_path = "{$_SERVER['DOCUMENT_ROOT']}{$array['filename']}";
//        $data= array("media"=>"@{$real_path}",'form-data'=>$array);
           
//        $_FILES['file']['tmp_name'] = $_SERVER['HTTP_REFERER'].$_FILES['file']['tmp_name'];
//        $arr['ts'] = time();
//        $arr['appkey'] = 'C154540058CB3FC1';
//        $arr['file'] = $_FILES;
//        ksort($arr);
//        $str = http_build_query($arr) . '&key=' . config('app_api_key');
//        $md5_str = strtoupper(md5($str));
//        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
//        
//       $url = $this->http_url_dev.'user/upload/picture?ts=1';
////        $ch = curl_init();
////         curl_setopt($ch, CURLOPT_USERPWD, 'joe:secret' );
////        curl_setopt($ch, CURLOPT_URL, $url);
////        curl_setopt($ch, CURLOPT_POST, true );
////        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
////        curl_setopt($ch, CURLOPT_HEADER, false);
////        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
////
////        $return_data = curl_exec($ch);
////        curl_close($ch);
////   
////     //   echo $result ; //输出 页面结果
//       
//       
//      // $_FILES['file'] = realpath($_SERVER['DOCUMENT_ROOT'].'/'.$_FILES['file']['name']);//"{$_SERVER['DOCUMENT_ROOT']}/{$_FILES['file']['name']}";
//          $result = $this->http1($url, '', $_FILES, 'post');
        
        $url = $this->http_url_fyl.'user/upload/picture?ts=1';
        $file = $_SERVER['DOCUMENT_ROOT'].'/'. $_FILES['file']['name']; //要上传的文件
        $fields['f'] = '@'.$file;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_POST, 1 );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields );
        curl_exec( $ch );
        if ($error = curl_error($ch) ) {
          die($error);
        }
        curl_close($ch); 


       dump($fields);die;
    }
    
    
    function http1($url, $param, $data, $httpType = 'get', $header = 'utf-8') {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); //SSL证书认证
        curl_setopt($ch, CURLOPT_URL, $url);
        $header = array('Accept-Charset: ' . $header);
        $header[] = 'charset: utf-8';
        $header[] = 'Content-Type: Multipart/form-data';
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
    
    public function addr() {
        
        $id = 77;
        
       
       
        $arr = [
            'Type'=>'提供资助',
            'TypeID'=>0,
            'Sum'=>10000,
            'CID'=>2,
            'CName'=>'小康社区',
            'UserID'=>$id,
            'Status'=>0,
            'Sign'=>0,
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'Matching'=>0,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Used'=>0,
        ];
        
      
         
          $arr9 = [
            'Type'=>'接受资助',
            'TypeID'=>0,
            'Sum'=>2000,
            'CID'=>1,
            'CName'=>'贫穷社区',
            'UserID'=>$id,
            'Status'=>0,
            'Sign'=>0,
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'Matching'=>0,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Used'=>0,
        ];
        
        $b = db('UserAccepthelp')->insertGetId($arr);
       
        $b4 = db('UserAccepthelp')->insertGetId($arr9);
       echo $b,$b4;
    }
    
    public function add() {
        
        $id = 82;
        $sid = 90;
        $sid1 = 118;
        $sid2 = 119;
        $sid3 = 120;
        $sid4 = 121;
        $id_ = db('Userinfo')->where(['UserID'=>$id])->field('Name')->find();
        $sid_ = db('Userinfo')->where(['UserID'=>$sid])->field('Name')->find();
        $sid1_ = db('Userinfo')->where(['UserID'=>$sid1])->field('Name')->find();
        $sid2_ = db('Userinfo')->where(['UserID'=>$sid2])->field('Name')->find();
        $sid3_ = db('Userinfo')->where(['UserID'=>$sid3])->field('Name')->find();
        $sid4_ = db('Userinfo')->where(['UserID'=>$sid4])->field('Name')->find();
        $arr = [
            'Type'=>'接受资助',
            'TypeID'=>0,
            'Sum'=>2000,
            'CID'=>1,
            'CName'=>'贫穷社区',
            'UserID'=>$id,
            'Status'=>0,
            'Sign'=>0,
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'Matching'=>1,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Used'=>2000,
        ];
        
      
        $arr1 = [
            'Type'=>'提供资助',
            'TypeID'=>0,
            'Sum'=>2000,
            'CID'=>1,
            'CName'=>'贫穷社区',
            'UserID'=>$sid,
            'Status'=>0,
            'Sign'=>0,
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'Matching'=>1,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Used'=>2000,
        ];
        
        $arr2 = [
            'Type'=>'提供资助',
            'TypeID'=>0,
            'Sum'=>2000,
            'CID'=>1,
            'CName'=>'贫穷社区',
            'UserID'=>$sid1,
            'Status'=>0,
            'Sign'=>0,
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'Matching'=>1,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Used'=>2000,
        ];
        
         $arr6 = [
            'Type'=>'提供资助',
            'TypeID'=>0,
            'Sum'=>2000,
            'CID'=>1,
            'CName'=>'贫穷社区',
            'UserID'=>$sid2,
            'Status'=>0,
            'Sign'=>0,
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'Matching'=>1,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Used'=>2000,
        ];
         
         
         $arr7 = [
            'Type'=>'提供资助',
            'TypeID'=>0,
            'Sum'=>2000,
            'CID'=>1,
            'CName'=>'贫穷社区',
            'UserID'=>$sid2,
            'Status'=>0,
            'Sign'=>0,
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'Matching'=>1,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Used'=>2000,
        ];
         
          $arr9 = [
            'Type'=>'提供资助',
            'TypeID'=>0,
            'Sum'=>2000,
            'CID'=>1,
            'CName'=>'贫穷社区',
            'UserID'=>$sid4,
            'Status'=>0,
            'Sign'=>0,
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'Matching'=>1,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Used'=>2000,
        ];
        
        $b = db('UserProvide')->insertGetId($arr1);
        $b1 = db('UserProvide')->insertGetId($arr2);
        $b3 = db('UserProvide')->insertGetId($arr7);
        $b2 = db('UserProvide')->insertGetId($arr6);
        $b4 = db('UserProvide')->insertGetId($arr9);
        $a = db('UserAccepthelp')->insertGetId($arr);
        
        $arr3 = [
            'PID'=>$a,
            'UserID'=>$id,
            'UserName'=>$id_['Name'],
            'OtherID'=>$b,
            'OtherUserID'=>$sid,
            'OtherUserName'=>$sid_['Name'],
            'Status'=>0,
            'Sign'=>0,
            'Sum'=>10000,
            'OtherSum'=>2000,
            'Handlers'=>1,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'SignTime'=>'0000-00-00 00:00:00',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'AuditStatus'=>1,
            'AuditUserID'=>1,
            'AuditUserName'=>'111',
            'AuditTime'=>date('Y-m-d H:i:s',time()),
            'SmsStatus'=>1,
        ];
        $m = db('UserMatchhelp')->insert($arr3);
        
        $arr4 = [
            'PID'=>$a,
            'UserID'=>$id,
            'UserName'=>$id_['Name'],
            'OtherID'=>$b1,
            'OtherUserID'=>$sid1,
            'OtherUserName'=>$sid1_['Name'],
            'Status'=>0,
            'Sign'=>0,
            'Sum'=>10000,
            'OtherSum'=>2000,
            'Handlers'=>1,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'SignTime'=>'0000-00-00 00:00:00',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'AuditStatus'=>1,
            'AuditUserID'=>1,
            'AuditUserName'=>'111',
            'AuditTime'=>date('Y-m-d H:i:s',time()),
            'SmsStatus'=>1,
        ];
        $m = db('UserMatchhelp')->insert($arr4);
        
        
        
         $arr5 = [
            'PID'=>$a,
            'UserID'=>$id,
            'UserName'=>$id_['Name'],
            'OtherID'=>$b2,
            'OtherUserID'=>$sid2,
            'OtherUserName'=>$sid2_['Name'],
            'Status'=>0,
            'Sign'=>0,
            'Sum'=>10000,
            'OtherSum'=>2000,
            'Handlers'=>1,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'SignTime'=>'0000-00-00 00:00:00',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'AuditStatus'=>1,
            'AuditUserID'=>1,
            'AuditUserName'=>'111',
            'AuditTime'=>date('Y-m-d H:i:s',time()),
            'SmsStatus'=>1,
        ];
        $m = db('UserMatchhelp')->insert($arr5);
        
        
        $arr8 = [
            'PID'=>$a,
            'UserID'=>$id,
            'UserName'=>$id_['Name'],
            'OtherID'=>$b3,
            'OtherUserID'=>$sid3,
            'OtherUserName'=>$sid3_['Name'],
            'Status'=>0,
            'Sign'=>0,
            'Sum'=>10000,
            'OtherSum'=>2000,
            'Handlers'=>1,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'SignTime'=>'0000-00-00 00:00:00',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'AuditStatus'=>1,
            'AuditUserID'=>1,
            'AuditUserName'=>'111',
            'AuditTime'=>date('Y-m-d H:i:s',time()),
            'SmsStatus'=>1,
        ];
        $m = db('UserMatchhelp')->insert($arr8);
        
        
        $arr10 = [
            'PID'=>$a,
            'UserID'=>$id,
            'UserName'=>$id_['Name'],
            'OtherID'=>$b4,
            'OtherUserID'=>$sid4,
            'OtherUserName'=>$sid4_['Name'],
            'Status'=>0,
            'Sign'=>0,
            'Sum'=>10000,
            'OtherSum'=>2000,
            'Handlers'=>1,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'SignTime'=>'0000-00-00 00:00:00',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'AuditStatus'=>1,
            'AuditUserID'=>1,
            'AuditUserName'=>'111',
            'AuditTime'=>date('Y-m-d H:i:s',time()),
            'SmsStatus'=>1,
        ];
        $m = db('UserMatchhelp')->insert($arr10);
    }

    
    
     public function add1() {
        
        $id = 118;
        $sid = 90;
        $id_ = db('Userinfo')->where(['UserID'=>$id])->field('Name')->find();
        $sid_ = db('Userinfo')->where(['UserID'=>$sid])->field('Name')->find();
        $arr = [
            'Type'=>'接受资助',
            'TypeID'=>0,
            'Sum'=>2000,
            'CID'=>1,
            'CName'=>'贫穷社区',
            'UserID'=>$id,
            'Status'=>1,
            'Sign'=>1,
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'Matching'=>1,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Used'=>2000,
        ];
        
      
        $arr1 = [
            'Type'=>'提供资助',
            'TypeID'=>0,
            'Sum'=>2000,
            'CID'=>1,
            'CName'=>'贫穷社区',
            'UserID'=>$sid,
            'Status'=>1,
            'Sign'=>1,
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'Matching'=>1,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Used'=>2000,
        ];
        $b = db('UserProvide')->insertGetId($arr1);
        $a = db('UserAccepthelp')->insertGetId($arr);
        
        $arr3 = [
            'PID'=>$a,
            'UserID'=>$id,
            'UserName'=>$id_['Name'],
            'OtherID'=>$b,
            'OtherUserID'=>$sid,
            'OtherUserName'=>$sid_['Name'],
            'Status'=>1,
            'Sign'=>1,
            'Sum'=>2000,
            'OtherSum'=>2000,
            'Handlers'=>1,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'SignTime'=>'0000-00-00 00:00:00',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'AuditStatus'=>1,
            'AuditUserID'=>1,
            'AuditUserName'=>'111',
            'AuditTime'=>date('Y-m-d H:i:s',time()),
            'SmsStatus'=>1,
        ];
        $m = db('UserMatchhelp')->insert($arr3);
    }
    
    
    public function add2() {
        
        $id = 118;
        $sid = 90;
        $id_ = db('Userinfo')->where(['UserID'=>$id])->field('Name')->find();
        $sid_ = db('Userinfo')->where(['UserID'=>$sid])->field('Name')->find();
        $arr = [
            'Type'=>'接受资助',
            'TypeID'=>0,
            'Sum'=>2000,
            'CID'=>1,
            'CName'=>'贫穷社区',
            'UserID'=>$id,
            'Status'=>0,
            'Sign'=>0,
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'Matching'=>0,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Used'=>0,
        ];
        
      
        $arr1 = [
            'Type'=>'提供资助',
            'TypeID'=>0,
            'Sum'=>2000,
            'CID'=>1,
            'CName'=>'贫穷社区',
            'UserID'=>$sid,
            'Status'=>0,
            'Sign'=>0,
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'Matching'=>0,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Used'=>0,
        ];
        $b = db('UserProvide')->insertGetId($arr1);
        $a = db('UserAccepthelp')->insertGetId($arr);
        
//        $arr3 = [
//            'PID'=>$a,
//            'UserID'=>$id,
//            'UserName'=>$id_['Name'],
//            'OtherID'=>$b,
//            'OtherUserID'=>$sid,
//            'OtherUserName'=>$sid_['Name'],
//            'Status'=>1,
//            'Sign'=>1,
//            'Sum'=>2000,
//            'OtherSum'=>2000,
//            'Handlers'=>1,
//            'MatchingID'=>0,
//            'IPAddress'=>'127.0.0.1',
//            'SignTime'=>'0000-00-00 00:00:00',
//            'CreateTime'=>date('Y-m-d H:i:s',time()),
//            'UpdateTime'=>date('Y-m-d H:i:s',time()),
//            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
//            'AuditStatus'=>1,
//            'AuditUserID'=>1,
//            'AuditUserName'=>'111',
//            'AuditTime'=>date('Y-m-d H:i:s',time()),
//            'SmsStatus'=>1,
//        ];
//        $m = db('UserMatchhelp')->insert($arr3);
    }
    
    
    
    
    public function add_provide_test() {
        
        $id = 77;
        $array = array(77);
        
        $id_ = db('Userinfo')->where(['UserID'=>$id])->field('Name')->find();
         $arr = [
            'Type'=>'提供资助',
            'TypeID'=>0,
            'Sum'=>10000,
            'CID'=>2,
            'CName'=>'小康社区',
            'UserID'=>$id,
            'Status'=>0,
            'Sign'=>0,
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'Matching'=>1,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Used'=>10000,
        ];
         
        $b = db('UserProvide')->insertGetId($arr);
        
        
        for($i=0;$i<count($array);$i++) {
            $sid_ = db('Userinfo')->where(['UserID'=>$array[$i]])->field('Name')->find();
            $arr1 = [
                'Type'=>'接受资助',
                'TypeID'=>0,
                'Sum'=>10000,
                'CID'=>1,
                'CName'=>'贫穷社区',
                'UserID'=>$array[$i],
                'Status'=>0,
                'Sign'=>0,
                'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
                'Matching'=>1,
                'MatchingID'=>0,
                'IPAddress'=>'127.0.0.1',
                'CreateTime'=>date('Y-m-d H:i:s',time()),
                'UpdateTime'=>date('Y-m-d H:i:s',time()),
                'Used'=>10000,
            ];
           
            $b1 = db('UserAccepthelp')->insertGetId($arr1);
            
             $arr4 = [
               'OtherID'=>$b,
               'OtherUserID'=>$id,
               'OtherUserName'=>$id_['Name'],
               'PID'=>$b1,
               'UserID'=>$array[$i],
               'UserName'=>$sid_['Name'],
               'Status'=>0,
               'Sign'=>0,
               'Sum'=>10000,
               'OtherSum'=>10000,
               'Handlers'=>1,
               'MatchingID'=>0,
               'IPAddress'=>'127.0.0.1',
               'SignTime'=>'0000-00-00 00:00:00',
               'CreateTime'=>date('Y-m-d H:i:s',time()),
               'UpdateTime'=>date('Y-m-d H:i:s',time()),
               'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
               'AuditStatus'=>1,
               'AuditUserID'=>1,
               'AuditUserName'=>'111',
               'AuditTime'=>date('Y-m-d H:i:s',time()),
               'SmsStatus'=>1,
           ];
           $m = db('UserMatchhelp')->insert($arr4);
                 
        }
        
        
    }
    
    
    public function add_accept_test() {
        
        $id = 77;
        $array = array(77);
        
        $id_ = db('Userinfo')->where(['UserID'=>$id])->field('Name')->find();
         $arr = [
            'Type'=>'接受资助',
            'TypeID'=>0,
            'Sum'=>10000,
            'CID'=>2,
            'CName'=>'小康社区',
            'UserID'=>$id,
            'Status'=>0,
            'Sign'=>0,
            'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
            'Matching'=>1,
            'MatchingID'=>0,
            'IPAddress'=>'127.0.0.1',
            'CreateTime'=>date('Y-m-d H:i:s',time()),
            'UpdateTime'=>date('Y-m-d H:i:s',time()),
            'Used'=>10000,
        ];
         
        $b = db('UserAccepthelp')->insertGetId($arr);
        
        
        for($i=0;$i<count($array);$i++) {
            $sid_ = db('Userinfo')->where(['UserID'=>$array[$i]])->field('Name')->find();
            $arr1 = [
                'Type'=>'提供资助',
                'TypeID'=>0,
                'Sum'=>10000,
                'CID'=>1,
                'CName'=>'贫穷社区',
                'UserID'=>$array[$i],
                'Status'=>0,
                'Sign'=>0,
                'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
                'Matching'=>1,
                'MatchingID'=>0,
                'IPAddress'=>'127.0.0.1',
                'CreateTime'=>date('Y-m-d H:i:s',time()),
                'UpdateTime'=>date('Y-m-d H:i:s',time()),
                'Used'=>10000,
            ];
           
            $b1 = db('UserProvide')->insertGetId($arr1);
            
             $arr4 = [
               'PID'=>$b,
               'UserID'=>$id,
               'UserName'=>$id_['Name'],
               'OtherID'=>$b1,
               'OtherUserID'=>$array[$i],
               'OtherUserName'=>$sid_['Name'],
               'Status'=>0,
               'Sign'=>0,
               'Sum'=>10000,
               'OtherSum'=>10000,
               'Handlers'=>1,
               'MatchingID'=>0,
               'IPAddress'=>'127.0.0.1',
               'SignTime'=>'0000-00-00 00:00:00',
               'CreateTime'=>date('Y-m-d H:i:s',time()),
               'UpdateTime'=>date('Y-m-d H:i:s',time()),
               'Batch'=>strtotime(date('Y-m-d 0:0:0',time())),
               'AuditStatus'=>1,
               'AuditUserID'=>1,
               'AuditUserName'=>'111',
               'AuditTime'=>date('Y-m-d H:i:s',time()),
               'SmsStatus'=>1,
           ];
           $m = db('UserMatchhelp')->insert($arr4);
                 
        }
        
        
    }

}
