<?php 

 
$db['dlycorp']=array (
  'columns' => 
  array (
    'corp_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('物流公司ID'),
      'width' => 110,
      'editable' => false,
      'hidden' => true,
    ),
    'type' => 
    array (
      'type' => 'varchar(6)',
      'editable' => false,
      'is_title' => true,
    ),
	'corp_code' => 
    array (
      'type' => 'varchar(200)',
      'label' => app::get('b2c')->_('物流公司代码'),
      'width' => 180,
      'editable' => false,
      'default_in_list' => false,
      'in_list' => true,
    ),
    'name' => 
    array (
      'type' => 'varchar(200)',
      'label' => app::get('b2c')->_('物流公司'),
      'width' => 180,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'ordernum' => 
    array (
      'type' => 'smallint(4) unsigned',
      'label' => app::get('b2c')->_('排序'),
      'width' => 180,
      'editable' => true,
      'in_list' => true,
    ),
    'website' => 
    array (
      'type' => 'varchar(200)',
      'label' => app::get('b2c')->_('物流公司网址'),
      'width' => 180,
      'editable' => true,
      'default_in_list' => true,
      'in_list' => true,
    ),
    'request_url' => 
    array (
      'type' => 'varchar(200)',
      'label' => app::get('b2c')->_('查询接口网址'),
      'width' => 180,
      'hidden'=>false,
      'editable' => true,
      'in_list' => true,
    ),
  ),
  'comment' => app::get('b2c')->_('物流公司表'),
  'index' => 
  array (
    'ind_type' => 
    array (
      'columns' => 
      array (
        0 => 'type',
      ),
    ),
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
    'ind_ordernum' => 
    array (
      'columns' => 
      array (
        0 => 'ordernum',
      ),
    ),
  ),
  'version' => '$Rev$',
);
