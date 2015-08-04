<?php

$db['reports']=array (
  'columns' =>
  array (
    'reports_id' =>
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'label' => app::get('b2c')->_('举报编号'),
      'is_title' => true,
      'width' => 120,
      'searchtype' => 'has',
      'editable' => false,
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'cat_id' =>
    array (
      'type' => 'table:reports_cat',
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('举报类型'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),    
    'goods_id' =>
    array (
      'type' => 'table:goods@b2c',
      'label' => app::get('b2c')->_('商品ID'),
      'width' => 120,
      'editable' => false,
      'filtertype' => 'custom',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'member_id' =>
    array (
      'type' => 'table:members@b2c',
      'label' => app::get('b2c')->_('投诉方'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
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
    'store_member_id' =>
    array (
      'type' => 'table:members@b2c',
      'label' => app::get('b2c')->_('被投诉方'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),  
    'store_uname' => array (
        'type' => 'varchar(100)',
        'label' => app::get('b2c')->_('店主名'),
        'searchtype' => 'has',
        'filtertype' => 'normal',
        'filterdefault' => 'true',
        'in_list' => true,
    ),
    'store_id' =>
    array (
      'type' => 'table:storemanger@business',
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
    ),
     'status' =>
    array (
      'type' =>
      array (
        'intervene' => app::get('b2c')->_('平台介入'),
        'voucher' => app::get('b2c')->_('取证中'),
        'success' => app::get('b2c')->_('举报成立'),
        'error' => app::get('b2c')->_('举报不成立'),
        'cancel' => app::get('b2c')->_('举报撤销'),
        'finish' => app::get('b2c')->_('完成'),
      ),
      'default' => 'intervene',
      'required' => true,
      'label' => app::get('b2c')->_('举报状态'),
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
    'ind_store_member_id' =>
    array (
      'columns' =>
      array (
        0 => 'store_member_id',
      ),
    ),
    'ind_report_member_id' =>
    array (
      'columns' =>
      array (
        0 => 'member_id',
      ),
    ),
    'idx__report_store_id' =>
    array (
        'columns' =>
        array(
            0 => 'store_id',
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