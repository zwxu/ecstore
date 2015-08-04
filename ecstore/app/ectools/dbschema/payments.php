<?php 

 
$db['payments']=array (
  'columns' => 
  array (
    'payment_id' => 
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'pkey' => true,
      'label' => app::get('ectools')->_('支付单号'),
      'width' => 110,
      'editable' => false,
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'is_title' => true,
    ),
    'money' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' =>app::get('ectools')->_('支付金额'),
      'editable' => false,
    ),
	'cur_money' => 
    array (
      'type' => 'money',
	  'label' => app::get('ectools')->_('支付货币金额'),
      'default' => '0',
      'required' => true,
      'editable' => false,
	  'width' => 75,
      'searchtype' => 'nequal',
	  'in_list' => true,
	  'default_in_list' => true,
    ),
	'member_id' => 
    array (
      'type' => 'varchar(100)',
      'label' => app::get('ectools')->_('会员用户名'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
     'status' => 
    array (
      'type' => 
      array (
        'succ' => app::get('ectools')->_('支付成功'),
        'failed' => app::get('ectools')->_('支付失败'),
        'cancel' => app::get('ectools')->_('未支付'),
        'error' => app::get('ectools')->_('处理异常'),
        'invalid' => app::get('ectools')->_('非法参数'),
        'progress' => app::get('ectools')->_('已付款至担保方'),
        'timeout' => app::get('ectools')->_('超时'),
        'ready' => app::get('ectools')->_('准备中'),
      ),
      'default' => 'ready',
      'required' => true,
      'label' => app::get('ectools')->_('支付状态'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'hidden' => true,
      'filterdefault' => true,
      'in_list' => true,
       'default_in_list' => true,
    ),
    'pay_name' => 
    array (
      'type' => 'varchar(100)',
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
    ),
    'pay_type' => 
    array (
      'type' => 
      array (
        'online' => app::get('ectools')->_('在线支付'),
        'offline' => app::get('ectools')->_('线下支付'),
        'deposit' => app::get('ectools')->_('预存款支付')
      ),
      'default' => 'online',
      'required' => true,
      'label' => app::get('ectools')->_('支付类型'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
     't_payed' => 
    array (
      'type' => 'time',
      'label' => app::get('ectools')->_('支付完成时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
     'default_in_list' => true,
    ),
   'op_id' => 
    array (
      'type' => 'number',//'table:users@desktop',
      'label' => app::get('ectools')->_('操作员'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
     'default_in_list' => true,
    ),
    
    
    
    
	'payment_bn' =>
    array (
      'type' => 'varchar(32)',
      'required' => false,
      'default' => '',
      'label' => app::get('ectools')->_('支付单唯一编号'),
      'width' => 140,
      'editable' => false,
      //'searchtype' => 'has',
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => false,
      'default_in_list' => false,
      'is_title' => true,
    ),
    'account' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('ectools')->_('收款账号'),
      'width' => 110,
      'searchtype' => 'tequal',
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'bank' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('ectools')->_('收款银行'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'pay_account' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('ectools')->_('支付账户'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'currency' => 
    array (
      'type' => 'varchar(10)',
      'label' => app::get('ectools')->_('货币'),
      'width' => 75,
      'editable' => false,
      'in_list' => true,
    ),
    
    'paycost' => 
    array (
      'type' => 'money',
      'label' => app::get('ectools')->_('支付网关费用'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    
    'pay_app_id' => 
    array (
      'type' => 'varchar(100)',
	  'label' => app::get('ectools')->_('支付方式名称'),
      'required' => true,
      'default' => 0,
      'editable' => false,
	  'in_list' => true,
      'default_in_list' => true,
    ),
    
    'pay_ver' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('ectools')->_('支付版本号'),
      'width' => 110,
      'editable' => false,
      'in_list' => false,
    ),

    'ip' => 
    array (
      'type' => 'ipaddr',
      'label' => app::get('ectools')->_('支付IP'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    't_begin' => 
    array (
      'type' => 'time',
      'label' => app::get('ectools')->_('支付开始时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
    ),
   
    't_confirm' => 
    array (
      'type' => 'time',
      'label' => app::get('ectools')->_('支付确认时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
   
    'memo' => 
    array (
      'type' => 'longtext',
	  'label' => app::get('ectools')->_('支付注释'),
      'editable' => false,
    ),
    'return_url' => 
    array (
      'type' => 'varchar(100)',
      'label' => app::get('ectools')->_('支付返回地址'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => false,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
	  'label' => app::get('ectools')->_('支付单状态'),
      'default' => 'false',
      'editable' => false,
    ),
    'trade_no' => 
    array (
      'type' => 'varchar(30)',
	  'label' => app::get('ectools')->_('支付单交易编号'),
      'editable' => false,
    ),
    'merge_payment_id' => 
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'label' => app::get('ectools')->_('合并支付单号'),
      'width' => 110,
      'editable' => false,
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'is_title' => true,
    ),
  ),
  'comment' => app::get('ectools')->_('支付记录'),
  'index' => 
  array (
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 43384 $',
);
