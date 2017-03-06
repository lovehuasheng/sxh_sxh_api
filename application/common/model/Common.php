<?php
namespace app\common\model;
use think\Model;
use think\view\driver\Think;

class Common extends Model
{
    
    //用户分表
    protected $rule = [
        'type' => 'id',     // 分表方式,按id范围分表
        'expr'  => 1000000  // 每张表的记录数
    ];
    
    /**
     * 按月分表
     * @param type $month_num
     */
    protected function get_month_submeter($month_num = 3) {
        //分表规则
        $this->rule = [
            'type' => 'quarter', // 分表方式,按月分表
            'expr' => $month_num      // 按3月一张表分
        ];
        //分表数据
        $this->info_date = [
            'now_time' => $_SERVER['REQUEST_TIME']
        ];
        $this->info_field = 'now_time';
    }
    
    
    /** 按数量分表
     */
    protected function get_id_submemter() {
        //分表规则
        $this->rule = [
            'type' => 'id',     // 分表方式,按id范围分表
            'expr'  => $this->rule['expr']  // 每张表的记录数
        ];
        
    }
}
