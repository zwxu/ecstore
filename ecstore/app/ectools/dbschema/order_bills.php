<?php 

 
$db['order_bills']=array ( 
  'columns' => 
  array (
    'rel_id' => 
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'pkey' => true,
      'default' => 0,
      'editable' => false,
    ),
    'bill_type' => 
    array (
      'type' => 
      array (
        'payments' =>  app::get('ectools')->_('付款单'),
        'refunds' =>  app::get('ectools')->_('退款单'),
        'blances' =>  app::get('ectools')->_('结算单'),
      ),
      'default' => 'payments',
      'required' => true,
      'label' => app::get('ectools')->_( '单据类型'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'pay_object' => 
    array (
      'type' => 
      array (
        'order' =>  app::get('ectools')->_('订单支付'),
        'recharge' =>  app::get('ectools')->_('预存款充值'),
        'joinfee' =>  app::get('ectools')->_('加盟费'),
		'prepaid_recharge' => app::get('ectools')->_('消费卡'),
        'earnest' =>  app::get('ectools')->_('支付保证金'),
      ),
      'default' => 'order',
      'required' => true,
      'label' =>  app::get('ectools')->_('支付类型'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'bill_id' => 
    array (
      'type' => 'varchar(20)',
      'pkey' => true,
      'required' => true,
      'label' =>  app::get('ectools')->_('关联单号'),
      'width' => 110,
      'editable' => false,
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'money' => 
    array (
      'type' => 'money',
      'editable' => false,
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
);
