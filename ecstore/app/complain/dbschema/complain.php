<?php


$db['complain']=array (
  'columns' =>
  array (
    'complain_id' =>
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'label' => app::get('b2c')->_('投诉编号'),
      'is_title' => true,
      'width' => 120,
      'searchtype' => 'has',
      'editable' => false,
      'filtertype' => 'custom',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'order_id' =>
    array (
      'type' => 'table:orders@b2c',
      'label' => app::get('b2c')->_('订单编号'),
      'width' => 120,
      'searchtype' => 'has',
      'editable' => false,
      'filtertype' => 'custom',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    
    'from_member_id' =>
    array (
      'type' => 'table:members@b2c',
      //'sdfpath' => 'fmembers/member_id',
      'label' => app::get('b2c')->_('投诉方'),
    ),
    'from_uname' => array (
        'type' => 'varchar(100)',
        'label' => app::get('b2c')->_('投诉人'),
        'searchtype' => 'has',
        'filtertype' => 'normal',
        'filterdefault' => 'true',
        'in_list' => true,
    ),
    'mobile' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('手机'),
      'width' => 75,
      'searchtype' => 'head',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => false,
    ),
    'to_member_id' =>
    array (
      'type' => 'table:members@b2c',
      //'sdfpath' => 'tmembers/member_id',
      'label' => app::get('b2c')->_('被投诉方'),
    ),  
    'to_uname' => array (
        'type' => 'varchar(100)',
        'label' => app::get('b2c')->_('店主名'),
        'searchtype' => 'has',
        'filtertype' => 'normal',
        'filterdefault' => 'true',
        'in_list' => true,
    ),
    'source' =>
    array (
      'type' =>
      array (
        'buyer' => app::get('b2c')->_('买家'),
        'seller' => app::get('b2c')->_('卖家')
      ),
      'default' => 'buyer',
      'required' => true,
      'label' => app::get('b2c')->_('投诉方'),
      'width' => 75,
      'hidden' => true,
      'editable' => false,
      'in_list' => true,

    ),
    'store_id' =>
    array (
      'type' => 'table:storemanger@business',
      //'sdfpath' => 'store/store_id',
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
    ),    
    'store_name' => array (
        'type' => 'varchar(100)',
        'label' => app::get('b2c')->_('店铺名'),
        'searchtype' => 'has',
        'filtertype' => 'normal',
        'filterdefault' => 'true',
        'in_list' => true,
    ),
    'reason' =>
    array (
      'type' =>
      array (
        'after' => app::get('b2c')->_('售后问题'),
        'action' => app::get('b2c')->_('行为违规')
      ),
      'default' => 'after',
      'required' => true,
      'label' => app::get('b2c')->_('投诉原因'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),    
     'status' =>
    array (
      'type' =>
      array (
        'intervene' => app::get('b2c')->_('平台介入'),
        'success' => app::get('b2c')->_('投诉成立'),
        'error' => app::get('b2c')->_('投诉不成立'),
        'cancel' => app::get('b2c')->_('投诉撤销'),
      ),
      'default' => 'intervene',
      'required' => true,
      'label' => app::get('b2c')->_('投诉状态'),
      'width' => 75,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'editable' => false,
      'in_list' => true,

    ),
    'memo' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'createtime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('申请时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'orderby' => true,
    ),
   'last_modified' =>
    array (
      'label' => app::get('b2c')->_('更新时间'),
      'type' => 'last_modify',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),    
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    )
  ),
  'index' =>
  array (
    'ind_from_member' =>
    array (
      'columns' =>
      array (
        0 => 'from_member_id',
      ),
    ),
    'ind_from_member_id' =>
    array (
      'columns' =>
      array (
        0 => 'to_member_id',
      ),
    ),
    'idx_store_id' =>
    array (
        'columns' =>
        array(
            0 => 'store_id',
            ),
        ),
    'ind_source' =>
    array (
      'columns' =>
      array (
        0 => 'source',
      ),
    ),
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
    'ind_last_modified' =>
    array (
      'columns' =>
      array (
        0 => 'last_modified',
      ),
    ),
    'ind_createtime' =>
    array (
      'columns' =>
      array (
        0 => 'createtime',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 42376 $',
);
