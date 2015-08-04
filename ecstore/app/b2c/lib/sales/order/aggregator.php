<?php

 
/**
 * order aggregator基类
 * $ 2010-05-09 19:39 $
 */
class b2c_sales_order_aggregator extends b2c_sales_basic_postfilter_aggregator
{
    public $default = 'b2c_sales_order_aggregator_combine'; // 默认处理
    protected $aggregator_apps = 'b2c_sales_order_aggregator_apps'; // aggregator servicelist
    protected $attribute_apps = 'b2c_sales_order_item_apps'; // item servicelist

    public function create_auto() {
        return parent::create_auto('admin_sales_order');
    }
}
?>
