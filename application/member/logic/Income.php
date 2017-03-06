<?php
// +----------------------------------------------------------------------
// | 善心汇集团 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.shanxinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 童自扬 <2421886569@qq.com> 
// +----------------------------------------------------------------------
// | Function: 接受资助业务逻辑层
// +----------------------------------------------------------------------

namespace app\member\logic;

use think\Model;

class Income extends Model {
    public function get_log_list(&$data) {
        
        switch ($data['type']) {
            case 0:
                $map['flag'] = 0;
                $map['Type'] = '善种子';
                break;
            case 1:
                $map['flag'] = 0;
                $map['Type'] = '善心币';
                break;
            case 2:
                $map['flag'] = 0;
                $map['Type'] = '善金币';
                break;
            case 3:
                $map['Type'] = 'Type = "管理奖" or Type = "管理钱包" ';
                $map['flag'] = 1;
                break;
            case 4:
                $map['Type'] = 'Type = "出局钱包" or Type = "接单钱包" ';
                $map['flag'] = 1;
                break;
        }
        //用户ID
        $map['UserID'] = $data['user_id'];
        //页码
        $page          = (isset($data['page']))?$data['page']:1;
        //条数
        $total         = (isset($data['total']))?$data['total']:config('app_list_rows');
        //字段
        $user_name = '(select username from sxh_user where id = pid) as username'; 
        $field         = ['ID as id','InCome as income','Message as message','PID as pid','Info as info','CreateTime as create_time',$user_name];
        //示例模型
        $model         = model('user/UserIncome');
        //总条数
        //$count         = $model->get_count();
        //数据
        $result_list   = $model->get_list($map,$page,$total,$field);
        if(!empty($result_list)) {
             //如果总条数除以每页条数，获得的总页数大于当前页数时。返回下一页；【也就是查看有没有下一页】
             if(ceil($result_list['total']/$result_list['per_page']) > $result_list['current_page']){
                 $result_list['current_page'] += 1;
             }else {
                 $result_list['current_page']  = 0;
             }
         }else {
             $result_list['data'] = [];
             $result_list['total'] = 0;
             $result_list['per_page'] = $total;
             $result_list['current_page'] = 0;
         }

         return errReturn('请求成功','0',set_aes_param($result_list));
    }
}

