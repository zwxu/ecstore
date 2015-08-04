<?php

 
/**
 * goods_type_items
 * $ 2010-05-09 16:15 $
 */
class b2c_sales_goods_item_type extends b2c_sales_goods_item
{
    public function getItem() {
        return array(
             ///////////////////////  商品类型属性 /////////////////////////
             'type_name'=> array('name'=>app::get('b2c')->_('类型名称'),'path'=>'name','type'=>'type', 'object'=>'b2c_sales_goods_item_type', 'operator'=>array('equal','contain','contain1','null')),
        );
    }

    // 和主表的关系
    public function getRefInfo() {
        return array(
                  'ref_id' => 'type_id', // 在主表的关联字段名
                  'pkey'   => 'type_id',   // sdb_b2c_goods_type 里的主键
                  'table'  => 'sdb_b2c_goods_type',
               );
    }
}
?>
