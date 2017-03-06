<?php
namespace app\common\model;
use think\Model;
use app\common\model\Common;

class UserOutgo extends Common {
    
    protected function initialize() {
        $this->get_month_submeter();
      
    }
    /* 转换数据 **/
    //1-善种子 2-善心币 3-善金币 4-出局钱包 5-管理钱包6-接单钱包 7-特困钱包 8-贫穷钱包 9-小康钱包 10-德善钱包 11-富人钱包
    protected function getTypeTextAttr($val , $data) {
        $array = ['' , '善种子' , '善心币' , '善金币' , '出局钱包' , '管理钱包' , '接单钱包' , '特困钱包' , '贫穷钱包' , '小康钱包' , '德善钱包' , '富人钱包'];
        return $array[$data['type']];
    }
    /** 保存支出数据
     * @param   $data   
     * @author  江雄杰
     * @time    2016-10-29
     */
    public function insertOutgo($data) {
        return $this->partition($this->info_date , $this->info_field , $this->rule)
                ->insert($data);
    }
}

