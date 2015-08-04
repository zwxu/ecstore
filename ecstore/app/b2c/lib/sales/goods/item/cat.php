<?php

 
/**
 * goods_cat_items
 * $ 2010-05-10 13:33 $
 */
class b2c_sales_goods_item_cat extends b2c_sales_goods_item
{
    public function getItem() {
        return array(
             ///////////////////////  分类属性 /////////////////////////
             'cat_cat_name'=> array('name'=>app::get('b2c')->_('分类名称'), 'path'=>'cat_name',  'type'=>'cat', 'object'=>'b2c_sales_goods_item_cat', 'operator'=>array('equal','contain','contain1','null')),
        );
    }

    public function getRefInfo() {
        return array(
                  'ref_id' => 'cat_id', // 在主表的关联字段名
                  'pkey'   => 'cat_id',   // sdb_b2c_goods_cat 里的主键
                  'table'  => 'sdb_b2c_goods_cat',
               );
    }
}
?>
