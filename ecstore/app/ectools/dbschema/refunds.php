<?php 
 $db['refunds']=array (
  'columns' => 
  array (
    'refund_id' => 
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'pkey' => true,
      'label' => '退款单号',
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
      'editable' => false,
    ),
    'cur_money' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '支付金额',
      'width' => 75,
      'searchtype' => 'nequal',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'member_id' => 
    array (
      'type' => 'varchar(100)',
      'label' => '会员用户名',
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'account' => 
    array (
      'type' => 'varchar(50)',
      'label' => '收款账号',
      'width' => 110,
      'searchtype' => 'tequal',
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'bank' => 
    array (
      'type' => 'varchar(50)',
      'label' => '收款银行',
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'pay_account' => 
    array (
      'type' => 'varchar(50)',
      'label' => '支付账户',
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'currency' => 
    array (
      'type' => 'varchar(10)',
      'label' => '货币',
      'width' => 75,
      'editable' => false,
      'in_list' => true,
    ),
    'paycost' => 
    array (
      'type' => 'money',
      'label' => '支付网关费用',
      'width' => 110,
      'editable' => false,
      'in_list' => false,
    ),
    'pay_type' => 
    array (
      'type' => 
      array (
        'online' => '在线支付',
        'offline' => '线下支付',
        'deposit' => '预存款支付',
      ),
      'default' => 'online',
      'required' => true,
      'label' => '支付类型',
      'width' => 110,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'status' => 
    array (
      'type' => 
      array (
        'succ' => '支付成功',
        'failed' => '支付失败',
        'cancel' => '未支付',
        'error' => '处理异常',
        'invalid' => '非法参数',
        'progress' => '处理中',
        'timeout' => '超时',
        'ready' => '准备中',
      ),
      'default' => 'ready',
      'required' => true,
      'label' => '支付状态',
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
    'pay_ver' => 
    array (
      'type' => 'varchar(50)',
      'label' => '支付版本号',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'op_id' => 
    array (
      'type' => 'number',
      'label' => '操作员',
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'refund_bn' => 
    array (
      'type' => 'varchar(32)',
      'required' => false,
      'default' => '',
      'label' => '退款唯一单号',
      'width' => 140,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => false,
      'default_in_list' => false,
      'is_title' => true,
    ),
    'pay_app_id' => 
    array (
      'type' => 'varchar(100)',
      'label' => '支付方式',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    't_begin' => 
    array (
      'type' => 'time',
      'label' => '支付开始时间',
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
    ),
    't_payed' => 
    array (
      'type' => 'time',
      'label' => '支付完成时间',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    't_confirm' => 
    array (
      'type' => 'time',
      'label' => '支付确认时间',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'memo' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'trade_no' => 
    array (
      'type' => 'varchar(30)',
      'editable' => false,
    ),
    'refund_type' => 
    array (
      'type' => 
      array (
        1 => '退款单',
        2 => '结算单',
      ),
      'required' => false,
      'label' => '单据种类',
      'width' => 110,
      'editable' => false,
    ),
    'profit' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '抽成金额',
      'width' => 75,
      'searchtype' => 'nequal',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'score_cost' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '积分费用',
      'width' => 75,
      'searchtype' => 'nequal',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'is_safeguard' => 
    array (
      'type' => 
      array (
        1 => '售前',
        2 => '售后',
      ),
      'comment' => '售后类型',
      'editable' => false,
      'default' => '1',
      'required' => true,
    ),
  ),
  'comment' => '支付记录',
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
  'version' => '$Rev: 41103 $',
);