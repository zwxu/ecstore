<?php 
$db['dlycorp']=array ( 
  'columns' =>
  array (
    'auto_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'hidden' => true,
    ),
    'corp_id' =>
    array (
      'type' => 'table:dlycorp@b2c',
      'label' => app::get('b2c')->_('物流公司ID'),
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'store_id' => array (
      'type' => 'table:storemanger',
      'label' => app::get('b2c')->_('店铺ID'),
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
  ),
  'version' => '$Rev$',
);