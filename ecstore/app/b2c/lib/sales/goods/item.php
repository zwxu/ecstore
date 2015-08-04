<?php

 
/**
 * goods items基类
 * $ 2010-05-11 14:57 $
 */
class b2c_sales_goods_item extends b2c_sales_basic_prefilter_item
{
    public $default = 'b2c_sales_goods_items_goods'; // 默认处理
    public $pkey = 'goods_id'; // 要进行过滤的数据表的主键
    public $table = 'sdb_b2c_goods'; // 要进行过滤的数据表
}
?>
