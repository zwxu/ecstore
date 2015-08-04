<?php

 

/**
 * 第三方流程类型处理类接口
 */
interface b2c_interface_3rdparty_goods_processor {
    
    // 返回字符串，声明处理的类型
    public function goodsKindDetail();
    
    // 判断对于某个步骤是否有自定义的处理方法
    // $step值: goods_add, goods, goods_delete, product, product_info, product_store, product_btn, order, order_delivery
    // 返回boolean
    public function isCustom($step='goods_add');
    
    // 新增商品页面
    // from business_ctl_site_member::goods_add_go
    public function goodsAddPage($goods, $controller=null);
    
    // 编辑商品页面
    // from business_ctl_site_member::goods_edit
    public function goodsEditPage($goods, $controller=null);
    
    // 删除商品操作
    // from business_ctl_site_member::goods_delete
    public function goodsDelete($goods_id);
    
    // 商品展示页面
    // from b2c_ctl_site_product::index
    public function productPage($goods, $controller=null);
    
    // 商品展示页面商品简介区块
    // from b2c_ctl_site_product::index
    public function productInfoPageList($pagelist, $params=array());
    
    // 商品展示页面购买按钮区块
    // from b2c_ctl_site_product::index
    public function productBtnPageList($pagelist, $params=array());
    
    // 商品展示页面购买数量区块
    // from b2c_ctl_site_product::index
    public function productStoreHtml($html, $params=array());
    
    // 是否需要收货地址
    public function isNeedAddress();
    
    // 是否需要配送
    public function isNeedDelivery();
    
    // 订单操作按钮（买家中心首页、买家订单列表）
    // from business_member_orders::get_orders_html
    // 返回一段html
    public function orderHtml($params=array());
    
    // 订单状态（买家中心首页、买家订单列表、订单详情）
    public function orderStatusHtml($params=array());
}

