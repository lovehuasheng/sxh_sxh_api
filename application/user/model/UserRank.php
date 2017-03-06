<?php
namespace app\user\model;
use think\Model;

/**====================================================
 * 用户模型：表user
 * ====================================================
 */
class UserRank extends Model
{
    
    /**根据条件查询一条数据 表user
     * @param   $where  array条件
     * @return  存在返回array 否则false
     * @author 江雄杰
     * @time 2016-10-06
     */
   public function getInfoByLevelIn($where,$field) {
        $result = db('UserRank')->where('id','in',$where)->field($field)->select();
      
        if($result) {
              $arr = [];
              for($i=0;$i<count($result);$i++) {
                  if(isset($result[$i]['Level'])) {
                      $arr[$result[$i]['Level']] = $result[$i];
                  }
              }
             
              return $arr;
        }
        return false;
    }
    
    
    
}