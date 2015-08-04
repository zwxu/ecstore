<?php

 

class b2c_mdl_member_favgoods extends dbeav_model{
    
    function __construct(&$app){
        $this->app = $app;
        $this->columns = array(
                        'goods_name'=>array('label'=>app::get('b2c')->_('商品名称'),'width'=>200),
                        'goods_bn'=>array('label'=>app::get('b2c')->_('商品编号'),'width'=>200),
                        'fav_total'=>array('label'=>app::get('b2c')->_('收藏次数'),'width'=>100),
                   );

        $this->schema = array(
                'default_in_list'=>array_keys($this->columns),
                'in_list'=>array_keys($this->columns),
                'idColumn'=>'attr_id',
                'columns'=>&$this->columns
            );  
    }
    
    function get_schema(){
        return $this->schema;
    }

    function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null){
        $data = self::get_fav_goods();
        return $data;
    }

    function count($filter=null){
        return count($this->getList());
    }
 
    ##获取收藏商品列表
    static function get_fav_goods(){
      
        $obj_goods = app::get('b2c')->model('goods');
        $member_goods = app::get('b2c')->model('member_goods');
        $data = $member_goods->getList('distinct goods_id',array('type'=>'fav'));
        $result = array();
        $i=0;
        foreach($data as $favgoods ){
            $i++;
            $aTotal = $member_goods->getList('count(*) total',array('goods_id'=>$favgoods['goods_id'],'type'=>'fav'));
            $total = $aTotal[0]['total'];
            $sdf = $obj_goods->dump($favgoods['goods_id']);
            $goods_name = $sdf['name'];
            $goods_bn = $sdf['bn'];
            $result[] = array('attr_id'=>$favgoods['goods_id'],'goods_name'=>$goods_name,'fav_total'=>$total,'goods_bn'=>$goods_bn);
        }
        return $result;
        
             }
   

}  
