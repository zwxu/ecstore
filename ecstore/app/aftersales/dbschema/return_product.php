<?php 

 
$db['return_product']=array (
  'columns' => 
  array (
    'order_id' => 
    array (
      'type' => 'table:orders@b2c',
      'default' => '0',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => '3',
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'label' => app::get('aftersales')->_('订单号'),
    ),
    'member_id' => 
    array (
      'type' => 'table:members@b2c',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => '4',
      'label' => app::get('aftersales')->_('申请人'),
    ),
    'return_id' => 
    array (
      'type' => 'bigint(20)',
      'required' => true,
      'pkey' => true,
      'editable' => false,
      'in_list' => true,	  
      'searchtype' => 'has',
      'filtertype' => 'yes',
	  'default_in_list' => true,
      'label' => app::get('aftersales')->_('退货记录流水号'),
      'order' => '5',
    ),
    'old_return_id' => 
    array (
      'type' => 'bigint(20)',
      'editable' => false,
      'in_list' => true,	  
      'searchtype' => 'has',
      'filtertype' => 'yes',
	  'default_in_list' => true,
      'label' => app::get('aftersales')->_('原退货记录流水号'),
      'order' => '6',
    ),
    'return_bn' =>
    array (
      'type' => 'varchar(32)',
      'required' => false,
      'label' => app::get('aftersales')->_('退货记录流水号标识'),
      'comment' => app::get('aftersales')->_('退货记录流水号标识'),
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
      'is_title' => true,
    ),
    'content' =>
    array(
        'type' => 'longtext',
        'editable' => false,
        'label' => app::get('aftersales')->_('退货内容'),
    ),
    'status' => 
    array (
      'type' => 
      array(
        '1' => app::get('aftersales')->_('退款协议等待卖家确认'),
        '2' => app::get('aftersales')->_('审核中'),
        '3' => app::get('aftersales')->_('同意退款'),
        '4' => app::get('aftersales')->_('完成'),
        '5' => app::get('aftersales')->_('拒绝'),
        '6' => app::get('aftersales')->_('已收货'),
        '7' => app::get('aftersales')->_('已质检'),
        '8' => app::get('aftersales')->_('补差价'),
        '9' => app::get('aftersales')->_('已拒绝退款'),
        '10' => app::get('aftersales')->_('已取消'),
        '11' => app::get('aftersales')->_('卖家不同意协议，等待买家修改'),
        '12' => app::get('aftersales')->_('买家已退货，等待卖家确认收货'),
        '13' => app::get('aftersales')->_('已修改'),
        '14' => app::get('aftersales')->_('卖家收到退货，拒绝退款'),
        '15' => app::get('aftersales')->_('卖家同意退款，等待卖家打款至平台'),
        '16' => app::get('aftersales')->_('卖家已退款，等待系统结算'),
      ),
      'default' => '1',
      'required' => true,
      'comment' => app::get('aftersales')->_('退货记录状态'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('aftersales')->_('售后服务状态'),
      'order' => '6',
    ),
    'image_file' =>
    array(
        'type' => 'varchar(255)',
        'label' => app::get('aftersales')->_('附件'),
        'width' => 75,
        'hidden' => true,
        'editable' => false,
        'in_list' => false,
    ),
    'image_file1' =>
    array(
        'type' => 'varchar(255)',
        'label' => app::get('aftersales')->_('附件1'),
        'width' => 75,
        'hidden' => true,
        'editable' => false,
        'in_list' => false,
    ),
    'image_file2' =>
    array(
        'type' => 'varchar(255)',
        'label' => app::get('aftersales')->_('附件2'),
        'width' => 75,
        'hidden' => true,
        'editable' => false,
        'in_list' => false,
    ),
    'intereven_image' =>
    array(
        'type' => 'varchar(255)',
        'label' => app::get('aftersales')->_('卖家举证'),
        'width' => 75,
        'hidden' => true,
        'editable' => false,
        'in_list' => false,
    ),
    'intereven_comment' =>
    array(
        'type' => 'longtext',
        'editable' => false,
        'label' => app::get('aftersales')->_('卖家留言举证'),
    ),
    'product_data' =>
    array(
        'type' => 'longtext',
        'editable' => false,
        'label' => app::get('aftersales')->_('退货货品记录'),
    ),
    'comment' =>
    array(
        'type' => 'longtext',
        'editable' => false,
        'label' => app::get('aftersales')->_('管理员备注'),
    ),
    'add_time' =>
    array(
        'type' => 'time',
        'depend_col' => 'marketable:true:now',
        'label' => app::get('aftersales')->_('售后处理时间'),
        'width' => 110,
        'editable' => false,
        'in_list' => true,
        'default_in_list' => true,
        'order' => '7',
    ),
    'amount'=>array (
          'type' => 'money',
          'required' => false,
          'label' => app::get('aftersales')->_('退款金额'),
          'width' => 110,
          'editable' => false,
    ),
    'seller_amount'=>array (
          'type' => 'money',
          'required' => false,
          'label' => app::get('aftersales')->_('商家承担金额'),
          'width' => 110,
          'editable' => false,
    ),
    'shipping_amount'=>array (
          'type' => 'money',
          'required' => false,
          'label' => app::get('aftersales')->_('运费退款金额'),
          'width' => 110,
          'editable' => false,
    ),
    'close_time'=>array (
          'type' => 'time',
          'required' => false,
          'label' => app::get('aftersales')->_('关闭时间'),
          'width' => 110,
          'editable' => false,
    ),
    'store_id'=>array(
          'type'=>'table:storemanger@business',
          'required' => true,
          'label' => app::get('b2c')->_('店铺'),
          'width' => 110,
          'editable' => false,
          'default' => '0',
          'in_list' => true,
          'default_in_list' => true,
    ),
    'refund_type' => 
    array (
      'type' => 
      array(
        '1' => app::get('aftersales')->_('取消订单'),
        '2' => app::get('aftersales')->_('需要退货'),
        '3' => app::get('aftersales')->_('已收到，无需退货'),
        '4' => app::get('aftersales')->_('未收到货'),
      ),
      'default' => '1',
      'required' => true,
      'comment' => app::get('aftersales')->_('退款类型'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('aftersales')->_('售后服务类型'),
      'order' => '8',
    ),
    'is_intervene' => 
    array (
      'type' => 
      array(
        '1' => app::get('aftersales')->_('平台未介入'),
        '2' => app::get('aftersales')->_('等待卖家举证'),
        '3' => app::get('aftersales')->_('平台已介入'),
        '4' => app::get('aftersales')->_('平台已处理'),
      ),
      'default' => '1',
      'required' => true,
      'comment' => app::get('aftersales')->_('平台介入状态'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('aftersales')->_('平台介入状态'),
      'order' => '9',
    ),
    'intervene_reason' => 
    array (
      'type' => 
      array(
        '1' => app::get('aftersales')->_('空包裹，少货'),
        '2' => app::get('aftersales')->_('快递问题'),
        '3' => app::get('aftersales')->_('卖家发错货'),
        '4' => app::get('aftersales')->_('虚假发货'),
        '5' => app::get('aftersales')->_('多拍，搓牌，不想要'),
        '6' => app::get('aftersales')->_('其他'),
      ),
      'comment' => app::get('aftersales')->_('平台介入原因'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('aftersales')->_('平台介入原因'),
      'order' => '9',
    ),
    'intervene_phone' => 
    array (
      'type' => 'varchar(30)',
      'label' => app::get('b2c')->_('平台介入手机'),
      'width' => 110,
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'intervene_mail' => 
    array (
      'type' => 'varchar(20)',
      'label' => app::get('b2c')->_('平台介入邮箱'),
      'width' => 110,
      'editable' => true,
      'filtertype' => 'normal',
      'in_list' => true,
      'default_in_list' => false,
    ),
    'ship_cost'=>array (
          'type' => 'money',
          'required' => false,
          'label' => app::get('aftersales')->_('退邮金额'),
          'width' => 110,
          'editable' => false,
    ),
    'amount_seller'=>array (
          'type' => 'money',
          'required' => false,
          'label' => app::get('aftersales')->_('商品剩余金额'),
          'width' => 110,
          'editable' => false,
    ),
    'seller_reason'=>array (
        'type' => 'longtext',
        'editable' => false,
        'label' => app::get('aftersales')->_('卖家拒绝原因'),
    ),
    'seller_comment'=>array (
        'type' => 'longtext',
        'editable' => false,
        'label' => app::get('aftersales')->_('卖家留言'),
    ),
    'is_safeguard'=>array(
        'type' => 
          array(
            '1' => app::get('aftersales')->_('售前'),
            '2' => app::get('aftersales')->_('售后'),
          ),
          'comment' => app::get('aftersales')->_('售后类型'),
          'editable' => false,
          'default' => '1',
          'required' => true,
    ),
    'safeguard_type'=>array(
        'type' => 
          array(
            '1' => app::get('aftersales')->_('商品问题'),
            '2' => app::get('aftersales')->_('七天无理由退换货'),
            '3' => app::get('aftersales')->_('发票无效'),
            '4' => app::get('aftersales')->_('退回多付的运费'),
            '5' => app::get('aftersales')->_('未收到货'),
          ),
          'comment' => app::get('aftersales')->_('售后要求'),
          'editable' => false,
          'default' => '1',
          'required' => true,
    ),
    'safeguard_require'=>array(
        'type' => 
          array(
            '1' => app::get('aftersales')->_('不退货部分退款'),
            '2' => app::get('aftersales')->_('需要退货退款'),
            '3' => app::get('aftersales')->_('要求换货'),
            '4' => app::get('aftersales')->_('要求维修'),
            '5' => app::get('aftersales')->_('已经退货，要求退款'),
            '6' => app::get('aftersales')->_('要求退款'),
          ),
          'comment' => app::get('aftersales')->_('服务类型'),
          'editable' => false,
          'default' => '1',
          'required' => true,
    ),
    'refund_address' =>
    array (
      'type' => 'table:dlyaddress@business',
      'editable' => false,
      'in_list' => false,
      'label' => '退货地址',
    ),
    'return_score' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'image_upload' =>
    array(
        'type' => 'varchar(255)',
        'label' => app::get('aftersales')->_('付款证明'),
        'width' => 75,
        'hidden' => true,
        'editable' => false,
        'in_list' => false,
    ),
    'is_return_money'=>array(
        'type' => 
          array(
            '1' => app::get('aftersales')->_('未打款'),
            '2' => app::get('aftersales')->_('已打款'),
          ),
          'comment' => app::get('aftersales')->_('卖家是否对款'),
          'editable' => false,
          'default' => '1',
          'required' => true,
    ),
    'return_money_id' => 
    array (
      'type' => 'varchar(50)',
      'label' => '流水单号',
      'width' => 110,
      'searchtype' => 'tequal',
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
);
