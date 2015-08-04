<?php 

 
$db['order_log']=array (
  'columns' => 
  array (
    'log_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'rel_id' => 
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'op_id' => 
    array (
      'type' => 'number',//'table:users@desktop',
      'label' => app::get('b2c')->_('操作员'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'op_name' => 
    array (
      'type' => 'varchar(100)',
      'label' => app::get('b2c')->_('操作人名称'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'alttime' => 
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('操作时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
    ),
   'bill_type' => 
    array (
      'type' => 
      array (
        'order' => app::get('b2c')->_('订单支付'),
        'recharge' => app::get('b2c')->_('预存款充值'),
        'joinfee' => app::get('b2c')->_('加盟费'),
        'prepaid_recharge' => app::get('ectools')->_('消费卡'),
      ),
      'default' => 'order',
      'required' => true,
      'label' => app::get('b2c')->_('支付类型'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'behavior' => 
    array (
      'type' => 
      array (
        'creates' => app::get('b2c')->_('创建'),
        'updates' => app::get('b2c')->_('修改'),
        'payments' => app::get('b2c')->_('支付'),
        'refunds' => app::get('b2c')->_('退款'),
        'delivery' => app::get('b2c')->_('发货'),
        'reship' => app::get('b2c')->_('退货'),
        'finish' => app::get('b2c')->_('完成'),
        'cancel' => app::get('b2c')->_('取消'),
        'change_price' => app::get('b2c')->_('修改价格'),
        'extend_time' => app::get('b2c')->_('延长收货时间'),
      ),
      'default' => 'payments',
      'required' => true,
      'label' => app::get('b2c')->_('操作行为'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'result' => 
    array (
      'type' => 
      array (
        'SUCCESS' => app::get('b2c')->_('成功'),
        'FAILURE' => app::get('b2c')->_('失败'),
      ),
      'required' => true,
      'label' => app::get('b2c')->_('操作结果'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'log_text' => 
    array (
      'type' => 'longtext',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'addon' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 46974 $',
);
