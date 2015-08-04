<?php 

 
$db['delivery']=array (
  'columns' => 
  array (
    'delivery_id' => 
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'pkey' => true,
      'label' => app::get('b2c')->_('发货单号'),
      'extra' => 'auto_increment',
      'comment' => app::get('b2c')->_('配送流水号'),
      'editable' => false,
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'order_id' => 
    array (
      'type' => 'varchar(100)',
      'label' => app::get('b2c')->_('订单号'),
      'comment' => app::get('b2c')->_('订单号'),
      'editable' => false,
      'searchtype' => 'has',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'delivery_bn' => 
    array (
      'type' => 'varchar(32)',
      'required' => false,
      'label' => app::get('b2c')->_('发货中心单号'),
      'comment' => app::get('b2c')->_('配送流水号'),
      'editable' => false,
      'width' =>140,
      //'searchtype' => 'has',
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => false,
      'default_in_list' => false,
      'is_title' => false,
    ),
    'member_id' => 
    array (
      'type' => 'table:members',
      'label' => app::get('b2c')->_('会员用户名'),
      'comment' => app::get('b2c')->_('订货会员ID'),
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
    ),
     'money' => 
    array (
      'type' => 'money',
      'required' => true,
      'default' => 0,
      'label' => app::get('b2c')->_('物流费用'),
      'comment' => app::get('b2c')->_('配送费用'),
      'editable' => false,
      'filtertype' => 'number',
      'in_list' => true,
      'default_in_list' => true,
    ),
     'is_protect' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label' => app::get('b2c')->_('是否保价'),
      'comment' => app::get('b2c')->_('是否保价'),
      'editable' => false,
      'filtertype' => 'yes',
      'in_list' => true,
      'default_in_list' => false,
    ),
        'delivery' => 
    array (
      'type' => 'table:dlytype',
      'label' => app::get('b2c')->_('配送方式'),
      'comment' => app::get('b2c')->_('配送方式(货到付款、EMS...)'),
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'is_title' => true,
      'default_in_list' => false,
    ),
    'logi_id' => 
    array (
      'type' => 'varchar(50)',
      'comment' => app::get('b2c')->_('物流公司ID'),
      'editable' => false,
      'label' => app::get('b2c')->_('物流公司ID'),
      'in_list' => false,
    ),
    'logi_name' => 
    array (
      'type' => 'varchar(100)',
      'label' => app::get('b2c')->_('物流公司'),
      'comment' => app::get('b2c')->_('物流公司名称'),
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'logi_no' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('物流单号'),
      'comment' => app::get('b2c')->_('物流单号'),
      'editable' => false,
      'searchtype' => 'tequal',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'ship_name' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('收货人'),
      'comment' => app::get('b2c')->_('收货人姓名'),
      'editable' => false,
      'searchtype' => 'tequal',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    
   


    
   'delivery_bn' => 
    array (
      'type' => 'varchar(32)',
      'required' => false,
      'label' => app::get('b2c')->_('发货中心单号'),
      'comment' => app::get('b2c')->_('配送流水号'),
      'editable' => false,
      'width' =>140,
      //'searchtype' => 'has',
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => false,
      'is_title' => true,
    ),
    'ship_area' => 
    array (
      'type' => 'region',
      'label' => app::get('b2c')->_('收货地区'),
      'comment' => app::get('b2c')->_('收货人地区'),
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'ship_addr' => 
    array (
      'type' => 'text',
      'label' => app::get('b2c')->_('收货地址'),
      'comment' => app::get('b2c')->_('收货人地址'),
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'ship_zip' => 
    array (
      'type' => 'varchar(20)',
      'label' => app::get('b2c')->_('收货邮编'),
      'comment' => app::get('b2c')->_('收货人邮编'),
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'ship_tel' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('收货人电话'),
      'comment' => app::get('b2c')->_('收货人电话'),
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'ship_mobile' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('收货人手机'),
      'comment' => app::get('b2c')->_('收货人手机'),
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'ship_email' => 
    array (
      'type' => 'varchar(200)',
      'label' => app::get('b2c')->_('收货人Email'),
      'comment' => app::get('b2c')->_('收货人Email'),
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    't_begin' => 
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('单据创建时间'),
      'comment' => app::get('b2c')->_('单据生成时间'),
      'editable' => false,
      'filtertype' => 'time',
      'in_list' => true,
      'default_in_list' => true,
    ),
    't_send' => 
    array (
      'type' => 'time',
      'comment' => app::get('b2c')->_('单据结束时间'),
      'editable' => false,
      'label' => app::get('b2c')->_('单据结束时间'),
      'in_list' => true,
    ),
    't_confirm' => 
    array (
      'type' => 'time',
      'comment' => app::get('b2c')->_('单据确认时间'),
      'editable' => false,
      'label' => app::get('b2c')->_('单据确认时间'),
      'in_list' => true,
    
    ),
    'op_name' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('操作员'),
      'comment' => app::get('b2c')->_('操作者'),
      'editable' => false,
      'searchtype' => 'tequal',
      'filtertype' => 'normal',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'status' => 
    array (
      'type' => 
      array (
        'succ' => app::get('b2c')->_('成功到达'),
        'failed' => app::get('b2c')->_('发货失败'),
        'cancel' => app::get('b2c')->_('已取消'),
        'lost' => app::get('b2c')->_('货物丢失'),
        'progress' => app::get('b2c')->_('运送中'),
        'timeout' => app::get('b2c')->_('超时'),
        'ready' => app::get('b2c')->_('准备发货'),
      ),
      'default' => 'ready',
      'required' => true,
      'comment' => app::get('b2c')->_('状态'),
      'editable' => false,
      'label' => app::get('b2c')->_('状态'),
      'in_list' => true,
    ),
    'memo' => 
    array (
      'type' => 'longtext',
      'label' => app::get('b2c')->_('备注'),
      'comment' => app::get('b2c')->_('备注'),
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'comment' => app::get('b2c')->_('无效'),
      'editable' => false,
      'label' => app::get('b2c')->_('无效'),
      'in_list' => false,
    ),
  ),
  'comment' => app::get('b2c')->_('发货/退货单表'),
  'index' => 
  array (
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
    'ind_logi_no' => 
    array (
      'columns' => 
      array (
        0 => 'logi_no',
      ),
    ),
  ),
  'version' => '$Rev: 43384 $',
);
