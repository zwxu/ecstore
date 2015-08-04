<?php


$db['associate']=array (
  'columns' =>
  array (
    'kd' =>
    array (
      'type' => 'varchar(255)',
      'required' => true,
      'pkey' => true,
      'editable' => false,
      'label' => app::get('b2c')->_('keyword'),
    ),
    'search_rate' =>
    array (
      'type' => 'number',
      'defult'=>'0',
      'required' => true,
      'editable' => false,
      'label' => app::get('b2c')->_('搜索频次'),
    ),
    'counts' =>
    array (
      'type' => 'number',
      'defult'=>'0',
      'required' => true,
      'editable' => false,
      'label' => app::get('b2c')->_('商品数量'),
    ),
    'md5_key' =>
    array (
      'type' => 'varchar(32)',
      'defult'=>'',
      'required' => true,
      'editable' => false,
      'label' => app::get('b2c')->_('md5key'),
    )
  ),
  'comment' => app::get('b2c')->_('搜索热词表'),
  'index' =>
  array (
    'ind_md5_key' =>
    array (
      'columns' =>
      array (
        0 => 'md5_key',
      ),
    ),    
    'ind_search_rate' =>
    array (
      'columns' =>
      array (
        0 => 'search_rate',
      ),
    )
    ),
  'version' => '$Rev: 40654 $',
);
