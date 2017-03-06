<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用公共文件
// 
/**
 * 根据时间获取表的后缀，即按季度分表
 * $nowtime 时间戳
 * return array
 */
function getTable($nowtime,$order=0){
    if($nowtime>time()){
        $nowtime = time();
    }
    $nowtime = $nowtime ? $nowtime : time();
    $arr = array(1=>1,2=>1,3=>1,4=>2,5=>2,6=>2,7=>3,8=>3,9=>3,10=>4,11=>4,12=>4);
    //根据时间记录季度
    $q = $arr[date('n',$nowtime)];
    $y = date('Y',$nowtime);
    $back = array();
    $back[] = $y.'_'.$q;
    if($order==0){
        if($q == 1){
            $y = $y-1;
            $q = 4;
        }  else {
            $q = $q-1;
        }
    }else{
        $current_y = date('Y',time());
        if($y < $current_y){
            if($q == 4){
                $y = $y+1;
                $q = 1;
            }  else {
                $q = $q+1;
            } 
        }else{
            $current_q = $arr[date('n',time())];
            if($q == $current_q){
                $back[] = 0;
                return $back;
            }  else {
                $q = $q+1;
            } 
        }
    }
    $back[] = $y.'_'.$q;
    return $back;
}
//根据参数表后缀，后去前一个表后缀
function getNextTable($table){
    $arr = explode('_', $table);
    if($arr[1] == 1){
        $arr[0] = $arr[0]-1;
        $arr[1] = 4;
    }  else {
        $arr[1] = $arr[1]-1;
    }
    return $arr[0].'_'.$arr[1];
}
//分页取数据时，查看那个表的后缀
function getQueterTabel($table){
    if(!$table){
        return false;
    }
    $arr = explode('_', $table);
    if($arr[1]>1){
        $yue = $arr[1]-1;
        return $arr[0].'_'.$yue;
    }else{
        $nian = $arr[0]-1;
        if($nian>=2016){
            return $nian.'_4';
        }else{
            return false;
        }
    }
}
//缓存列表信息
function setCacheDateList($table,$user_id,$type,$page,$data){
    return cache('sxh_user_'.$table.'_list:user_id:'.$user_id.':type:'.$type.':page:'.$page,$data,60);
}
//缓存列表信息
function getCacheDateList($table,$user_id,$type,$page){
    return cache('sxh_user_'.$table.'_list:user_id:'.$user_id.':type:'.$type.':page:'.$page);
}
//缓存列表信息
function setCacheMatchListDetail($table,$user_id,$id,$data){
    return cache('sxh_user_'.$table.'_detail:user_id:'.$user_id.':id:'.$id,$data,60);
}
//缓存列表信息
function getCacheMatchListDetail($table,$user_id,$id){
    return cache('sxh_user_'.$table.'_detail:user_id:'.$user_id.':id:'.$id);
}
//返回信息
function errReturn($errorMsg, $errorCode, $result = null) {
    return ['errorMsg' => $errorMsg, 'errorCode' => $errorCode, 'result' => $result];
}
//生成不重令牌字符串
function getToken(){
    return date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}
/**
 * 验证请求方法
 * @param array $params  参数数组
 * @param type $ts       请求时间
 * @param type $sign     请求签名
 * @return string
 */
function validate_response(array $params, $sign) {
 
    //验证参数
    if (empty($params) || empty($sign)) {
        $info['code'] = -11;
        $info['info'] = '参数错误';
        $info['result'] = '';
        trace('function请求签名比对返回： 参数错误');
        return $info;
    }
    $redis = \org\RedisLib::get_instance();
    if(isset($params['user_id']) && $params['user_id'] != 1){
        $pass_token = $redis->get('app_user_sign_pass_token'.$params['user_id']);
        if(!isset($params['pass_token']) || !$pass_token || $pass_token != $params['pass_token']){
            $info['code'] = -1901810;
            $info['info'] = '登录超时，请重新登录';
            $info['result'] = null;
            //trace('function请求签名比对返回： token不正确!请求的appkey：' . $params['appkey']);
            return $info;
        }
        //如果pass_token不过期，则重新设置20分钟有效,20分钟无操作失效
        $redis->set('app_user_sign_pass_token'.$params['user_id'],$pass_token,1200);
    }
    //$params = json_decode(decrypt($arr['data'],substr($sign,0,16),substr($sign,-16,16)),true);

    $ts = isset($params['ts']) ? $params['ts'] : 0;
    if (!isset($params['appkey']) || !in_array($params['appkey'], config('app_api_appkey'))) {
        $info['code'] = -14;
        $info['info'] = 'Appkey不正确！';
        $info['result'] = $_SERVER['REQUEST_TIME'];
        trace('function请求签名比对返回： Appkey不正确!请求的appkey：' . $params['appkey']);
        return $info;
    }
    //验证开始
    //判断请求时间
    $stime = intval($_SERVER['REQUEST_TIME'] - $ts);
    //验证时间，超过5分钟返回超时
    if ($stime > 300) {
        $info['code'] = -12;
        $info['info'] = '请求超时！';
        $info['result'] = null;
        trace('function请求签名比对返回： 请求超时!请求的时间戳：' . $ts . ';当前系统时间：' . $_SERVER['REQUEST_TIME']);
        return $info;
    }
    //$params['appkey'] = config('app_api_appkey');
    //排序
    ksort($params);
    //拼接字符串
    $str = http_build_query($params) . '&key=' . config('app_api_key');
    
    trace('function请求签名参数拼接字符串： ' . urldecode($str));
    //md5加密转大写
    $md5_str = strtoupper(md5(urldecode($str)));
    trace('function请求签名参数拼接字符串md5加密转大写： ' . $md5_str);
    //比对签名
    if ($sign !== $md5_str) {
        $info['code'] = -13;
        $info['info'] = '签名错误！';
        $info['result'] = $md5_str;
        trace('function请求签名比对返回： 签名错误');
        return $info;
    }

    //签名正确
    $info['code'] = 1;
    $info['info'] = '成功！';
    $info['result'] = '';
    return $info;
}
   function get_redis_field($key) {
        $redis_field_array = [
            1   => 'poor_wallet_last_changetime',
            2   => 'needy_wallet_last_changetime',
            3   => 'comfortably_wallet_last_changetime',
            4   => 'wealth_wallet_last_changetime',//富人
            5   => 'kind_wallet_last_changetime',//德善
            6   => 'big_kind_wallet_last_changetime',
        ];
        return empty($redis_field_array[$key]) ? '' : $redis_field_array[$key];
    }

/**
 * aes加密
 * @param type $input
 * @param type $key
 * @param type $iv
 * @return type
 */
function encrypt($input, $key = '', $iv = '') {
    if (empty($input) || empty($key) || empty($iv)) {
        return fase;
    }
    $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    $input = pkcs5_pad($input, $size);
    $base = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $input, MCRYPT_MODE_CBC, $iv));
    return $base;
//    $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');dump($input);die;
//    mcrypt_generic_init($td, $key, $iv);
//    $data = mcrypt_generic($td, $input);
//    mcrypt_generic_deinit($td);
//    mcrypt_module_close($td);
    //   return base64_encode($data);
}

/**
 * pk5填充
 * @param type $text
 * @param type $blocksize
 * @return type
 */
function pkcs5_pad($text, $blocksize) {
    $pad = $blocksize - (strlen($text) % $blocksize);
    return $text . str_repeat(chr($pad), $pad);
}

/**
 * 解密
 * @param type $input
 * @param type $ky
 * @param type $iv
 * @return type
 */
function decrypt($input, $key = '', $iv = '') {
    if (empty($input) || empty($key) || empty($iv)) {
        return false;
    }
    $input = base64_decode(rawurldecode($input));   //对加密后的密文进行解base64编码  
    $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');    //MCRYPT_DES代表用DES算法加解密;'cbc'代表使用cbc模式进行加解密.  
    mcrypt_generic_init($td, $key, $iv);
    $decrypted_data = mdecrypt_generic($td, $input);    //对$input进行解密  
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    //  $encrypt_str =  mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $input, MCRYPT_MODE_ECB, $iv);
    $decrypted_data = pkcs5_unpad($decrypted_data); //对解密后的明文进行去掉字符填充  
    $decrypted_data = rtrim($decrypted_data);   //去空格  
    return $decrypted_data;
}

/**
 * 对解密后的已字符填充的明文进行去掉填充字符 
 * @param type $text
 * @return boolean
 */
function pkcs5_unpad($text) {
    $pad = ord($text{strlen($text) - 1});
    if ($pad > strlen($text))
        return false;
    return substr($text, 0, -1 * $pad);
}

/**
 * http返回码
 * @staticvar array $_status
 * @param type $code
 */
function send_http_status($code) {
    $_status = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );
    if (array_key_exists($code, $_status)) {
        header('HTTP/1.1 ' . $code . ' ' . $_status[$code]);
    }

    return true;
}

/**
 * 密码加密，统一加密方式
 * @param type $pwd
 * @return type
 */
//旧数据使用这个
function set_old_password($pwd) {
    return md5($pwd);
}
//新数据使用这个
function set_password($pwd,$salt='') {
    $public_key = md5('FR4ehHBBbjD7ZBNEv_GCvXBsmNSq0zLV');
    return md5(sha1($pwd . $salt) . $public_key);
}
/**
 * 取七牛图片地址
 * @param type $pic
 * @param type $w
 * @param type $h
 * @return type
 */
function getQiNiuPic($path, $w = 0, $h = 0) {
    $url = 'http://' . config('qiniu.baseUrl') . '/';
    $picture = new \org\upload\driver\qiniu\QiniuStorage(config('qiniu'));
    return $picture->privateDownloadUrl($url . $path . "?imageView2/1/w/{$w}/h/{$h}");
}

/** 发送注册短信
 * @param   $phone 接收短信手机号码
 * @param   $smsParams  短信内容参数
 * @return  int   1成功 0失败
 * @author  江雄杰
 * @time    2016-10-06
 */
function sendRegSms($phone, $smsParams) {
    //return $service->test();
    //return $service->register();
    import('org.taobao.top.TopClient', EXTEND_PATH);
    import('org.taobao.top.TopClient', EXTEND_PATH);
    import('org.taobao.top.ResultSet', EXTEND_PATH);
    import('org.taobao.top.RequestCheckUtil', EXTEND_PATH);
    import('org.taobao.top.TopLogger', EXTEND_PATH);
    import('org.taobao.top.request.AlibabaAliqinFcSmsNumSendRequest', EXTEND_PATH);
    //将需要的类引入，并且将文件名改为原文件名.class.php的形式

    $c = new \TopClient();
    $c->appkey = config('AlidayuAppKey');
    $c->secretKey = config('AlidayuAppSecret');
    $req = new \AlibabaAliqinFcSmsNumSendRequest;
    $req->setSmsType("normal");                           //短信类型，传入值请填写normal

    $req->setSmsFreeSignName(config('AlidayuSmsFreeSignName')); //短信签名
    //$req->setSmsParam("{'name':$ReceiveName}");            //${name}，你好，欢迎成为我们的注册会员
    $req->setSmsParam(json_encode($smsParams)); //${name}，你好，欢迎成为我们的注册会员

    $req->setRecNum($phone);                      //参数为用户的手机号码
    $req->setSmsTemplateCode('SMS_9465019'); //注册通知模版 旧模版SMS_8960208 新模版 SMS_9465019
    $resp = $c->execute($req);
    //var_dump($smsParams);
    //var_dump(objectArray($resp));
    return $resp->result->success;  //返回成功标识
}

function set_aes_param($params) {
    ksort($params);
    $str = http_build_query($params) . '&key=' . config('app_api_key');
    $md5_str = strtoupper(md5($str));
    trace('set_aes_param返回： ' . json_encode($params, JSON_UNESCAPED_UNICODE));
    $tmp['data'] = rawurlencode(encrypt(json_encode($params, JSON_UNESCAPED_UNICODE), substr($md5_str, 0, 16), substr($md5_str, -16, 16)));
    $tmp['sig'] = $md5_str;
    return $tmp;
}

function get_invented_currency_num($cid = 0) {


    $num = 100;
    switch ($cid) {
        case 1:
            $num = 100;
            break;
        case 2:
            $num = 200;
            break;
        case 3:
            $num = 300;
            break;
        case 4:
            $num = 400;
            break;
        case 5:
            $num = 500;
            break;
        default : $num = 100;
            break;
    }

    return $num;
}
function get_invented_currency_name($cid) {
    
    switch ($cid) {
        case 1:
            $name = '善种子';
            break;
        case 2:
            $name = '善心币';
            break;
        case 3:
            $name = '善金币';
            break;
        case 4:
            $name = '管理钱包';
            break;
        case 5:
            $name = '出局钱包';
            break;
        default : $name = '善种子';
            break;
    }

    return $name;
}
function get_ziduan($cid) {
    
    switch ($cid) {
        case 1:
            $name = 'activate_currency';
            break;
        case 2:
            $name = 'guadan_currency';
            break;
        case 3:
            $name = 'invented_currency';
            break;
        case 4:
            $name = 'manage_wallet';
            break;
        case 5:
            $name = 'wallet_currency';
            break;
        default : $name = 'activate_currency';
            break;
    }

    return $name;
}
/**
 * 随机数
 * @param type $num
 * @return type
 */
function get_rand_num($num = 6) {
    $str = '0123456789';
    $len = strlen($str);
    $r = '';
    for ($i = 0; $i < 6; $i++) {
        $rand = mt_rand(0, $len - 1);
        $r .= substr($str, $rand, 1);
    }

    return $r;
}

/**
 * http请求[post]
 * @param type $url
 * @param type $param
 * @param type $data
 * @param type $httpType
 * @param type $header
 * @return type
 */
function api_http_request($url, $data, $header = array()) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); //SSL证书认证
    curl_setopt($ch, CURLOPT_URL, $url);
//        $header = array('Accept-Charset: utf-8');
//        $header[] = 'charset: utf-8';
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

/**
 * 对象转对象
 * @param type $obj
 * @return type
 */
function get_obj_to_object($obj) {
    return json_decode(json_encode($obj),true);
}
/**
 * 个人中心注册手机验证码
 * @param type $mobile
 * @param type $rand
 * @param type $text
 * @return boolean
 */

function sendRegisterSms($mobile,$rand,$text='你正在进行账号注册,有效时间为5分钟。您好，您的验证码是') {
     
            $data =$text . $rand ;
            $post_data = array();
            $post_data['account'] = iconv('GB2312', 'GB2312',"vip-sxh8");
            $post_data['pswd'] = iconv('GB2312', 'GB2312',"Sxh88888");
            $post_data['mobile'] =$mobile;
            $post_data['msg']=mb_convert_encoding("{$data}",'UTF-8', 'UTF-8');
            $post_data['needstatus']='true';
            $url='http://222.73.117.156/msg/HttpBatchSendSM?'; 
            $post_data = http_build_query($post_data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            $result = curl_exec($ch) ;
           
            $p = explode(',', trim($result));
            if(empty($p)) {
                return false;
            }
            $p1 = substr($p[1],0,1);
           
            if($p1 == '0') {
                return true;
            }
            return false;
        }
        
        function sendSms($mobile,$text='你正在进行账号注册,有效时间为5分钟。您好，您的验证码是') {
     
            $data =$text;
            $post_data = array();
            $post_data['account'] = iconv('GB2312', 'GB2312',"vip-sxh8");
            $post_data['pswd'] = iconv('GB2312', 'GB2312',"Sxh88888");
            $post_data['mobile'] =$mobile;
            $post_data['msg']=mb_convert_encoding("{$data}",'UTF-8', 'UTF-8');
            $post_data['needstatus']='true';
            $url='http://222.73.117.156/msg/HttpBatchSendSM?'; 
            $post_data = http_build_query($post_data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            $result = curl_exec($ch) ;
           
            $p = explode(',', trim($result));
            if(empty($p)) {
                return false;
            }
            $p1 = substr($p[1],0,1);
           
            if($p1 == '0') {
                return true;
            }
            return false;
        }