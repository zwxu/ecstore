<?php


/**
 * 这个类快递单模版管理表的实体
 * 
 * @version 0.1
 * @package express.model.print
 */
class express_mdl_print_tmpl extends dbeav_model
{   
	/**
	 * @var 某块使用标签
	 */ 
    var $has_tag = false;
    
    /**
     * 获取快递单模版中的所有属性列对应的中文名称
     * @param null
     * @return array 中文名称数组
     */
    public function getElements(){
        $elements = array(
            'ship_name'=>app::get('express')->_('收货人-姓名'),

            'ship_area_0'=>app::get('express')->_('收货人-地区1级'),
            'ship_area_1'=>app::get('express')->_('收货人-地区2级'),
            'ship_area_2'=>app::get('express')->_('收货人-地区3级'),

            'ship_addr'=>app::get('express')->_('收货人-地址'),
            'ship_tel'=>app::get('express')->_('收货人-电话'),
            'ship_mobile'=>app::get('express')->_('收货人-手机'),
            'ship_zip'=>app::get('express')->_('收货人-邮编'),
            'dly_name'=>app::get('express')->_('发货人-姓名'),
            'ship_detail_addr'=>app::get('express')->_('收货人-地区+详细地址'),

            'dly_area_0'=>app::get('express')->_('发货人-地区1级'),
            'dly_area_1'=>app::get('express')->_('发货人-地区2级'),
            'dly_area_2'=>app::get('express')->_('发货人-地区3级'),

            'dly_address'=>app::get('express')->_('发货人-地址'),
            'dly_tel'=>app::get('express')->_('发货人-电话'),
            'dly_mobile'=>app::get('express')->_('发货人-手机'),
            'dly_zip'=>app::get('express')->_('发货人-邮编'),
            'date_y'=>app::get('express')->_('当日日期-年'),
            'date_m'=>app::get('express')->_('当日日期-月'),
            'date_d'=>app::get('express')->_('当日日期-日'),
            'order_print'=>app::get('express')->_('订单条码'),
            'order_id'=>app::get('express')->_('订单-订单号'),
            'order_price'=>app::get('express')->_('订单总金额'),
            'order_weight'=>app::get('express')->_('订单物品总重量'),
            'order_count'=>app::get('express')->_('订单-物品数量'),
            'order_memo'=>app::get('express')->_('订单-备注'),
            'ship_time'=>app::get('express')->_('订单-送货时间'),
            'shop_name'=>app::get('express')->_('网店名称'),
            'tick'=>app::get('express')->_('对号 - √'),
            'text'=>app::get('express')->_('自定义内容'),
            'member_name'=>app::get('express')->_('会员用户名'),

            'order_name'=>app::get('express')->_('订单商品名称'),
            'order_name_a'=>app::get('express')->_('订单商品名称+数量'),
            'order_name_as'=>app::get('express')->_('订单商品名称+规格+数量'),
            'order_name_ab'=>app::get('express')->_('订单商品名称+货号+数量'),
           # 'order_print_id'=>app::get('express')->_('订单打印编号'),
            #'delivery_print'=>app::get('express')->_('订单打印编号-条形码'),
        );
        return $elements;
    }
}