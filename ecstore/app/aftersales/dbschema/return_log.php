<?php 
 
 
$db['return_log']=array (
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
    'order_id' => 
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'return_id' => 
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'op_id' => 
    array (
      'type' => 'number',//'table:users@desktop',
      'label' => app::get('aftersales')->_('操作员'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'op_name' => 
    array (
      'type' => 'varchar(100)',
      'label' => app::get('aftersales')->_('操作人名称'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'alttime' => 
    array (
      'type' => 'time',
      'label' => app::get('aftersales')->_('操作时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'behavior' => 
    array (
      'type' => 
      array (
        'creates' => app::get('aftersales')->_('买家创建申请'),
        'updates' => app::get('aftersales')->_('买家修改申请'),
        'refuseapp' => app::get('aftersales')->_('卖家拒绝申请'),
        'agreeapp' => app::get('aftersales')->_('卖家同意申请'),
        'agreereturn' => app::get('aftersales')->_('卖家同意退款'),
        'reship' => app::get('aftersales')->_('买家退货'),
        'refusereturn' => app::get('aftersales')->_('卖家拒绝退款'),
        'cancel' => app::get('aftersales')->_('买家撤销'),
        'intereven' => app::get('aftersales')->_('买家申请平台介入'),
        'seller_update' => app::get('aftersales')->_('卖家上传举证'),
        'intereven_refund' => app::get('aftersales')->_('平台退款给买家'),
        'intereven_agree' => app::get('aftersales')->_('平台同意退款申请'),
        'intereven_blance' => app::get('aftersales')->_('平台打款给卖家'),
        'intereven_refuse' => app::get('aftersales')->_('平台拒绝退款申请'),
      ),
      'default' => 'creates',
      'required' => true,
      'label' => app::get('aftersales')->_('操作行为'),
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
        'SUCCESS' => app::get('aftersales')->_('成功'),
        'FAILURE' => app::get('aftersales')->_('失败'),
      ),
      'required' => true,
      'label' => app::get('aftersales')->_('操作结果'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'role' => 
    array (
      'type' => 
      array (
        'member' => app::get('aftersales')->_('买家'),
        'seller' => app::get('aftersales')->_('卖家'),
        'admin' => app::get('aftersales')->_('管理员'),
      ),
      'required' => true,
      'label' => app::get('aftersales')->_('角色'),
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
    'image_file' =>
    array(
        'type' => 'varchar(255)',
        'label' => app::get('aftersales')->_('附件'),
        'width' => 75,
        'hidden' => true,
        'editable' => false,
        'in_list' => false,
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 46974 $',
);
