<?php

 
/**
 * aggregator基类
 * $ 2010-05-09 19:39 $
 */
class b2c_sales_goods_aggregator extends b2c_sales_basic_prefilter_aggregator
{
    public $default = 'b2c_sales_goods_aggregator_combine'; // 默认处理
    public $pkey = 'goods_id'; // 要进行过滤的数据表的主键
    public $table = 'sdb_b2c_goods'; // 要进行过滤的数据表
    protected $aggregator_apps = 'b2c_sales_goods_aggregator_apps'; // aggregator servicelist
    protected $attribute_apps = 'b2c_sales_goods_item_apps'; // item servicelist
}
?>
