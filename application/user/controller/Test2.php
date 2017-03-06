<?php

//
//    app 2.0 测试
//
namespace app\user\controller;

use app\common\controller\Base;
use think\Db;
use think\Request;
class Test2 {
    public $http_url_dev = 'http://tpapi.vn/';
    public $http_url = 'http://appdocs-test.shanxinhui.com/';
    public $http_url_fyl = 'http://www.s.com/';
    public $http_url_me = 'http://test.sxh_api.com/';
    public $http_url_aaa = 'http://api-test-monitor.shanxinhui.com/';
    public $http_url_bbb = 'http://apidocs-v2.shanxinhui.com/';
    public $http_url_ccc = 'http://apidocs.shanxinhui.com/';
    public $http_url_ddd = 'http://api.test.cc/';
    public $http_url_fff = 'http://stage-api.shanxinhui.cn/';
    /**
     * 收入记录
     */
    public function aa(){
        $dir = dirname(dirname(dirname(APP_PATH))).DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR;
        
        $redis = \org\RedisLib::get_instance();
        
        $data = json_decode($redis->rPop('sxh_user_sms'),true);
        
        if($data){
            file_put_contents($dir.'TWO.txt',date('Y-m-d H:i:s'). '---------------'.PHP_EOL,FILE_APPEND);
            file_put_contents($dir.'TWO.txt', json_encode($data,JSON_UNESCAPED_UNICODE).PHP_EOL,FILE_APPEND);
            file_put_contents($dir.'TWO.txt', $data['phone'].$data['content'].PHP_EOL,FILE_APPEND);
            $f = sendSms($data['phone'],$data['content']);
            if($f){
                $f=1;
            }else{
                $f=0;
            }

            $sdata = array();
            $sdata['user_id'] = $data['extra_data ']['user_id'];
            $sdata['phone'] = $data['extra_data ']['phone'];
            $sdata['title'] = $data['extra_data ']['title'];
            $sdata['code'] = $data['extra_data ']['code'];
            $sdata['status'] = $f;
            $sdata['ip_address'] = $data['extra_data ']['ip_address'];
            $sdata['valid_time'] = $data['extra_data ']['valid_time'];
            $sdata['create_time'] = $data['extra_data ']['create_time'];
            $sdata['update_time'] = $data['extra_data ']['update_time'];
            $m_sms = \think\Loader::model('UserSms' , 'model');
            $res = $m_sms->insertSmsinfo($sdata);file_put_contents($dir.'san.txt', $res.PHP_EOL,FILE_APPEND);
            file_put_contents($dir.'TWO.txt', json_encode($sdata,JSON_UNESCAPED_UNICODE).PHP_EOL,FILE_APPEND);
            file_put_contents($dir.'TWO.txt', json_encode('aa'.$res.'aa',JSON_UNESCAPED_UNICODE).PHP_EOL,FILE_APPEND);
        }
    }
    public function huang_test(){
        $result = validate_response($this->data,$this->sig);
        $redis = \org\RedisLib::get_instance();
        
        trace('service的upload_avatar_picture方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        $dd = $this->data;
        $acc = db('user_accepthelp_2016_4')->where(array('user_id'=>$dd['acc_id']))->field('id,money,cid,create_time')->order('id desc')->find();
        $acc_ = db('user_info_1')->where(array('user_id'=>$dd['acc_id']))->field('name')->find();
        $pro = db('user_provide_2016_4')->where(array('user_id'=>$dd['pro_id']))->field('id,money,cid,create_time')->order('id desc')->find();
        $pro_ = db('user_info_1')->where(array('user_id'=>$dd['pro_id']))->field('name')->find();
        //dump($acc);dump($acc_);dump($pro);dump($pro_);
        $arr4 = [
               'pid'=>$acc['id'],
               'user_id'=>$dd['acc_id'],
            'type_id'=>1,
            'other_type_id'=>1,
            'cid'=>$acc['cid'],
            'other_cid'=>$pro['cid'],
               'username'=>$acc_['name'],
               'other_id'=>$pro['id'],
               'other_user_id'=>$dd['pro_id'],
               'other_username'=>$pro_['name'],
               'status'=>1,
               'money'=>$acc['money'],
               'other_money'=>$pro['money'],
            'provide_money'=>$pro['money'],
               'handlers'=>1,
               'ip_address'=>ip2long('127.0.0.1'),
               'sign_time'=>0,
               'create_time'=>time(),
               'update_time'=>time(),
               'batch'=>time(),
               'audit_user_id'=>1,
               'audit_username'=>'111',
               'audit_time'=>time(),
               'sms_status'=>1,
            'provide_create_time'=>$acc['create_time'],
            'accepthelp_create_time'=>$pro['create_time']
            
           ];//dump($arr4);exit;
           $m = db('user_matchhelp_2016_4')->insert($arr4);
           $aa = db('user_accepthelp_2016_4')->where(array('id'=>$acc['id']))->update(array('used'=>$acc['money'],'status'=>1,'match_num'=>1));
           $bb = db('user_provide_2016_4')->where(array('id'=>$pro['id']))->update(array('used'=>$pro['money'],'status'=>1,'match_num'=>1));
           if($m && $aa && $bb){
                $redis->delDataList('provide',$dd['pro_id'],1);
                $redis->delDataList('provide',$dd['pro_id'],2);
                $redis->delDataList('accepthelp',$dd['acc_id'],1);
                $redis->delDataList('accepthelp',$dd['acc_id'],2);
               return errReturn('匹配成功',0);
           }else{
               return errReturn('匹配失败',0);
           }
    }

    public function huang_acc_more(){
        $result = validate_response($this->data,$this->sig);

        trace('service的upload_avatar_picture方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        $dd = $this->data;
        $acc = db('user_accepthelp_2016_4')->where(array('user_id'=>$dd['acc_id']))->field('id,money,cid,create_time')->order('id desc')->find();
        $acc_ = db('user_info_1')->where(array('user_id'=>$dd['acc_id']))->field('name')->find();
        $acc1 = db('user_accepthelp_2016_4')->where(array('user_id'=>$dd['acc1_id']))->field('id,money,cid,create_time')->order('id desc')->find();
        $acc1_ = db('user_info_1')->where(array('user_id'=>$dd['acc1_id']))->field('name')->find();
        $pro = db('user_provide_2016_4')->where(array('user_id'=>$dd['pro_id']))->field('id,money,cid,create_time')->order('id desc')->find();
        $pro_ = db('user_info_1')->where(array('user_id'=>$dd['pro_id']))->field('name')->find();
        //dump($acc);dump($acc_);dump($pro);dump($pro_);
        $arr4[0] = [
               'pid'=>$acc['id'],
               'user_id'=>$dd['acc_id'],
            'type_id'=>1,
            'other_type_id'=>1,
            'cid'=>$acc['cid'],
            'other_cid'=>$pro['cid'],
               'username'=>$acc_['name'],
               'other_id'=>$pro['id'],
               'other_user_id'=>$dd['pro_id'],
               'other_username'=>$pro_['name'],
               'status'=>1,
               'money'=>$acc['money'],
               'other_money'=>$acc['money'],
            'provide_money'=>$pro['money'],
               'handlers'=>1,
               'ip_address'=>ip2long('127.0.0.1'),
               'sign_time'=>0,
               'create_time'=>time(),
               'update_time'=>time(),
               'batch'=>time(),
               'audit_user_id'=>1,
               'audit_username'=>'111',
               'audit_time'=>time(),
               'sms_status'=>1,
            'provide_create_time'=>$acc['create_time'],
            'accepthelp_create_time'=>$pro['create_time']
            
           ];//dump($arr4);exit;
        $arr4[1] = [
               'pid'=>$acc1['id'],
               'user_id'=>$dd['acc_id'],
            'type_id'=>1,
            'other_type_id'=>1,
            'cid'=>$acc1['cid'],
            'other_cid'=>$pro['cid'],
               'username'=>$acc1_['name'],
               'other_id'=>$pro['id'],
               'other_user_id'=>$dd['pro_id'],
               'other_username'=>$pro_['name'],
               'status'=>1,
               'money'=>$acc1['money'],
               'other_money'=>$acc1['money'],
            'provide_money'=>$pro['money'],
               'handlers'=>1,
               'ip_address'=>ip2long('127.0.0.1'),
               'sign_time'=>0,
               'create_time'=>time(),
               'update_time'=>time(),
               'batch'=>time(),
               'audit_user_id'=>1,
               'audit_username'=>'111',
               'audit_time'=>time(),
               'sms_status'=>1,
            'provide_create_time'=>$acc1['create_time'],
            'accepthelp_create_time'=>$pro['create_time']
            
           ];
           $m = db('user_matchhelp_2016_4')->insert($arr4);
           $aa = db('user_accepthelp_2016_4')->where(array('id'=>$acc['id']))->update(array('used'=>$acc['money'],'status'=>1,'match_num'=>1));
           $bb = db('user_provide_2016_4')->where(array('id'=>$pro['id']))->update(array('used'=>$pro['money'],'status'=>1,'match_num'=>1));
           $aa = db('user_accepthelp_2016_4')->where(array('id'=>$acc1['id']))->update(array('used'=>$acc1['money'],'status'=>1,'match_num'=>1));
           if($m && $aa && $bb){
                $redis->delDataList('provide',$dd['pro_id'],1);
                $redis->delDataList('provide',$dd['pro_id'],2);
                $redis->delDataList('accepthelp',$dd['acc_id'],1);
                $redis->delDataList('accepthelp',$dd['acc_id'],2);
               return errReturn('匹配成功',0);
           }else{
               return errReturn('匹配失败',0);
           }
    }

    public function provide_data() {



                $arr = array(
  "ts" => time(),
  "appkey" => "C154540058CB3FC1",
    'user_id'=>1,
    'pass_token'=>224796,
//    'pro_id'=>122786
    'username'=>'jiejie_008',
    'password'=>'123456a',
    'phone_version'=>'aaaaa'
//    'pro1_id'=>167
        );
//        $data['name'] = "jsdk3243jkA SDj-a黄";dd
//        echo preg_match('/^[\x{4e00}-\x{9fa5}\w-\.\s]{1,250}$/u', $data['name']);exit;
        ksort($arr);//dump(set_password(htmlspecialchars(urldecode('123456'))));exit;
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5(urldecode($str)));
     
        $url = $this->http_url_me.'user/user/login?aa=22&sig='.$md5_str;
       
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
       
      
        $json = $this->http($url, '', $tmp, 'post');echo $json;exit;
        
        
        //返回参数aes解密
        $arr = json_decode($json,true);
        //$data = json_decode(decrypt($arr['result']['data'],substr($arr['result']['sig'],0,16),substr($arr['result']['sig'],-16,16)),true);
        //dump($json);        
        dump($arr);
        //dump($data);
        die;
    }
    public function check_api() {

    $model = \think\Loader::model('MMysql' , 'model');

    $res = $model->doSql('select * from sxh_user_1 limit 1');dump($res);exit;
    
//        $data['user_id'] = input('post.user_id');
//        $data['check_token'] = input('post.check_token');
//        $token = cache('check_token'.$data['user_id']);
//        if(!empty($token) && $token == $data['check_token']){
//            return errReturn('数据不能重复提交！', '400');
//        }else{
//            cache('check_token'.$data['user_id'],$data['check_token']);
//        }
//        echo 'suss';exit;
        $arr = array(


  "user_id" => "398765",
//  'recipient_account' => 'jiejie_009',
//  'money_type' => '1',
//  'money_sum' => '1',
//  'password' => '123456a',
//  "notes" => "转出善种子",
  'money_sum' => '500',
  'password' => '123456a',
            "ts" => time(),
  "appkey" => "C154540058CB3FC1",
  "pass_token" => "3e5b6830dd99c1c919b24bd35b4c1b2e",
  'check_token' => '3e5b6830dd99c1c919b24bd35b4c1b2e'


        );

//        $redis = \org\RedisLib::get_instance();http://apidocs.shanxinhui.com/user/provide/get_pay_person_msg?sig=DD96B90691FAA9B50498FB493C167C7C&ios=ios
//        $val = $redis->hget('liwangbing','phone_num');dump($val);
//        $redis->hIncrBy('liwangbing','phone_num',2);
//        $val = $redis->hget('liwangbing','phone_num');dump($val);exit;
        ksort($arr);
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5(urldecode($str)));
        echo $url = $this->http_url_me.'user/user_center/outmanageaccount?aa=22&sig='.$md5_str;
       
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
      
        $json = $this->http($url, '', $tmp, 'post');echo $json;exit;
        
        
        //返回参数aes解密
        $arr = json_decode($json,true);
        dump($arr);
        die;
    }

    public function test_company() {
                $arr = array(
  "ts" => time(),
  "appkey" => "C154540058CB3FC1",
   'acc_id'=>8512,
   'acc1_id'=>8526,
   'pro_id'=>167,
    'user_id'=>1
        );



//        $data['name'] = "jsdk3243jkA SDj-a黄";
//        echo preg_match('/^[\x{4e00}-\x{9fa5}\w-\.\s]{1,250}$/u', $data['name']);exit;
        ksort($arr);//dump(set_password(htmlspecialchars(urldecode('123456'))));exit;
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5(urldecode($str)));
     
        echo $url = $this->http_url_aaa.'user/test2/huang_pro_more?aa=22&sig='.$md5_str;
       
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
       
      
        $json = $this->http($url, '', $tmp, 'post');echo $json;exit;
        
        
        //返回参数aes解密
        $arr = json_decode($json,true);
        //$data = json_decode(decrypt($arr['result']['data'],substr($arr['result']['sig'],0,16),substr($arr['result']['sig'],-16,16)),true);
        //dump($json);        
        dump($arr);
        //dump($data);
        die;
    }
    public function get_phone(){
                        $arr = array(
  "ts" => time(),
  "appkey" => "C154540058CB3FC1",
    'user_id'=>122786
        );
//$arr = array(
//    "ts" => time(),
//    "appkey" => "C154540058CB3FC1",
//    'recipient_account'=>13501103568,
//    'money_type'=>2,
//    'money_sum'=>500,
//    'check_token'=>28347453293843,
//    'password'=>'123546',
//    'notes'=>'djkasjk',
//    'user_id'=>156,
    
//    'user_id'=>1,
//'username'=>'huangj',
//'password'=>'1huangj',
//'rePassword'=>'1huangj',
//'name'=>'huangj',
//'phone'=>18300070888,
//    'verify'=>123456,
//'referee_name' =>'ztm'
//);


//        $data['name'] = "jsdk3243jkA SDj-a黄";
//        echo preg_match('/^[\x{4e00}-\x{9fa5}\w-\.\s]{1,250}$/u', $data['name']);exit;
        ksort($arr);//dump(set_password(htmlspecialchars(urldecode('123456'))));exit;
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5(urldecode($str)));
     
        echo $url = $this->http_url_bbb.'user/test2/check_user_data?aa=22&sig='.$md5_str;
       $this->data = array('user_id'=>2,'appkey'=>2);
       $this->sig = array('99');
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
       
      
        //$json = $this->http($url, '', $tmp, 'post');echo $json;exit;
        //$result = validate_response($this->data,$this->sig);

        trace('service的upload_avatar_picture方法请求签名加密比对返回：' . json_encode('$result', JSON_UNESCAPED_UNICODE));
        if ($aa=9 < 0) {
            //return errReturn($result['info'], $result['code'],$result['result']);
        }
        
        //返回参数aes解密
        //$arr = json_decode($this->sig,true);
        //$data = json_decode(decrypt($arr['result']['data'],substr($arr['result']['sig'],0,16),substr($arr['result']['sig'],-16,16)),true);
        //dump($json);       
        
        echo db(input('post.tid'))->where(array('user_id'=>input('post.id')))->update(array('comfortably_wallet'=>input('post.idd')));exit;
        dump($arr);
        //dump($data);
        //$result = validate_response($this->data,$this->sig);

        trace('service的upload_avatar_picture方法请求签名加密比对返回：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['code'] < 0) {
            return errReturn($result['info'], $result['code'],$result['result']);
        }
        
        
    }
    public function get_data() {
                $arr = array(
  "ts" => time(),
  "appkey" => "C154540058CB3FC1",
    'user_id'=>122786
        );



//        $data['name'] = "jsdk3243jkA SDj-a黄";
//        echo preg_match('/^[\x{4e00}-\x{9fa5}\w-\.\s]{1,250}$/u', $data['name']);exit;
        ksort($arr);//dump(set_password(htmlspecialchars(urldecode('123456'))));exit;
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5(urldecode($str)));
     
        echo $url = $this->http_url_bbb.'user/test2/check_user_data?aa=22&sig='.$md5_str;
       
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
       
      
        $json = $this->http($url, '', $tmp, 'post');echo $json;exit;
        
        
        //返回参数aes解密
        $arr = json_decode($json,true);
        //$data = json_decode(decrypt($arr['result']['data'],substr($arr['result']['sig'],0,16),substr($arr['result']['sig'],-16,16)),true);
        //dump($json);        
        dump($arr);
        //dump($data);
        die;
    }
       public function check_phone() {
                $arr = array(
  "ts" => time(),
  "id" => 6004,
    'user_id'=>87,
  "appkey" => "C154540058CB3FC1",

        );

echo sendSms(18300070879,'您好，你正在查看收款人信息,为了账户信>息安全,切勿泄露。您的验证码是200771');exit;

//        $data['name'] = "jsdk3243jkA SDj-a黄";
//        echo preg_match('/^[\x{4e00}-\x{9fa5}\w-\.\s]{1,250}$/u', $data['name']);exit;
        ksort($arr);//dump(set_password(htmlspecialchars(urldecode('123456'))));exit;
        $str = http_build_query($arr) . '&key=' . config('app_api_key');
        $md5_str = strtoupper(md5(urldecode($str)));
     
        echo $url = $this->http_url_aaa.'user/test2/check_phone?aa=22&sig='.$md5_str;
       
        //aes 加密
        $tmp['data'] = encrypt(json_encode($arr,JSON_UNESCAPED_UNICODE),substr($md5_str,0,16),substr($md5_str,-16,16));
       
      
        $json = $this->http($url, '', $tmp, 'post');echo $json;exit;
        
        
        //返回参数aes解密
        $arr = json_decode($json,true);
        //$data = json_decode(decrypt($arr['result']['data'],substr($arr['result']['sig'],0,16),substr($arr['result']['sig'],-16,16)),true);
        //dump($json);        
        dump($arr);
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
}
