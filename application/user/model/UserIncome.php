<?php
namespace app\user\model;
use think\Model;
use think\Db;
use app\common\model\Common;

class UserIncome extends Common
{
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'CreateTime';
    protected $updateTime = 'UpdateTime';
    
    public static $type_arr = ['善种子','善心币','善金币','管理金','布施收益'];
    //初始化
    protected function initialize() {
        $this->get_month_submeter();
      
    }

    public function setDataByMap($type,$income,$user_id,$provide_id=0,$match_id=0) {
        $data = $this->getMessageByType($type);
        $data['InCome'] = $income;
        $data['UserID'] = $user_id;
        $data['PID'] = $provide_id;
        $data['CatID'] = $match_id;
        return $this->create($data);
    }
    
    
    
    public static function getMessageByType($code) {
        
        $arr = [];
        
        switch ($code) {
             case 1:
                $arr['Message'] = $arr['Info'] = '收益管理钱包【App】';
                $arr['Type'] = '管理钱包';
                break;
            case 2:
                $arr['Message'] = $arr['Info'] = '布施收益【App】';
                $arr['Type'] = '出局钱包';
                break;
            case 3:
                $arr['Message'] = '布施收益';
                $arr['Info'] = '接单布施收益【App】';
                $arr['Type'] = '接单钱包';
                break;
            case 4:
                $arr['Message'] = $arr['Info'] = '收益善金币【App】';
                $arr['Type'] = '善金币';
                break;
            default : break;
        }
        
        
        return $arr;
    }
    
    
    public function get_list($map,$page=1,$r=20,$field=['*'],$order = 'id desc') {//dump($map);die;
         $tmp = cache('income_log_'.implode('_', $map).'_'.$page);
         if(empty($tmp)) {
            $config['page'] = $page;
            if($map['flag'] == 1) {
                $type = $map['Type'];
                unset($map['Type']);       
                unset($map['flag']);       
                $list = $this->where($map)->where($type)->field($field)->order($order)->paginate($r, false, $config);
               
            }else {
                 unset($map['flag']);    
                $list = $this->where($map)->field($field)->order($order)->paginate($r, false, $config);
            }
            
            $list = get_obj_to_object($list);
            cache('income_log_'.implode('_', $map).'_'.$page,  serialize($list),600);
         }else {
             $list = unserialize($tmp);
         }
        
         return $list;
    }
    
    
    public function get_count($map) {
        return $this->where($map)->count();
    }
    /*
     * 插入数据
     */
    public function incomeInsert($data){
        return $this->partition($this->info_date,$this->info_field,$this->rule)->insert($data);
    }
}