<?php 

 
$db['member_advance']=array (
  'columns' => 
  array (
    'log_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('日志id'),
      'width' => 110,
      'comment' => app::get('b2c')->_('日志id'),
      'editable' => false,
      'hidden' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'member_id' => 
    array (
      'type' => 'table:account@pam',
      'required' => true,
      'default' => 0,
      'label' => app::get('b2c')->_('用户名'),
      'width' => 110,
      'comment' => app::get('b2c')->_('用户id'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'money' => 
    array (
      'type' => 'money',
      'required' => true,
      'default' => 0,
      'label' => app::get('b2c')->_('出入金额'),
      'width' => 110,
      'comment' => app::get('b2c')->_('出入金额'),
      'editable' => false,
      'hidden' => true,
      'in_list' => true,
    ),
    'message' => 
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('管理备注'),
      'is_title' => true,
      'width' => 110,
      'comment' => app::get('b2c')->_('管理备注'),
      'editable' => true,
      'in_list' => true,
    ),
    'mtime' => 
    array (
      'type' => 'time',
      'required' => true,
      'default' => 0,
      'label' => app::get('b2c')->_('交易时间'),
      'width' => 75,
      'comment' => app::get('b2c')->_('交易时间'),
      'editable' => false,
      'in_list' => true,
    ),
    'payment_id' => 
    array (
      'type' => 'varchar(20)',
      'label' => app::get('b2c')->_('支付单号'),
      'width' => 110,
      'comment' => app::get('b2c')->_('支付单号'),
      'searchtype' => 'has',
      'editable' => false,
      'in_list' => true,
    ),
    'order_id' => 
    array (
      'type' => 'table:orders',
      'label' => app::get('b2c')->_('订单号'),
      'width' => 110,
      'comment' => app::get('b2c')->_('订单号'),
      'searchtype' => 'has',
      'editable' => false,
      'in_list' => true,
    ),
    'paymethod' => 
    array (
      'type' => 'varchar(100)',
      'label' => app::get('b2c')->_('支付方式'),
      'width' => 110,
      'comment' => app::get('b2c')->_('支付方式'),
      'editable' => false,
      'in_list' => true,
    ),
    'memo' => 
    array (
      'type' => 'varchar(100)',
      'label' => app::get('b2c')->_('业务摘要'),
      'width' => 110,
      'comment' => app::get('b2c')->_('业务摘要'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'import_money' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('存入金额'),
      'width' => 110,
      'comment' => app::get('b2c')->_('存入金额'),
      'editable' => false,
      'in_list' => true,
    ),
    'explode_money' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('支出金额'),
      'width' => 110,
      'comment' => app::get('b2c')->_('支出金额'),
      'editable' => false,
      'in_list' => true,
    ),
    'member_advance' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('当前余额'),
      'width' => 110,
      'comment' => app::get('b2c')->_('当前余额'),
      'editable' => false,
      'in_list' => true,
    ),
    'shop_advance' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('商店余额'),
      'width' => 110,
      'comment' => app::get('b2c')->_('商店余额'),
      'editable' => false,
      'hidden' => true,
      'in_list' => true,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'comment' => app::get('b2c')->_('失效'),
      'editable' => false,
      'label' => app::get('b2c')->_('失效'),
      'in_list' => true,
    ),
  ),
  'comment' => app::get('b2c')->_('预存款历史记录'),
  'index' => 
  array (
    'ind_mtime' => 
    array (
      'columns' => 
      array (
        0 => 'mtime',
      ),
    ),
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
);
