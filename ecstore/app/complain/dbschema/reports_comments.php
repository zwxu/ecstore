<?php


$db['reports_comments']=array (
  'columns' =>
  array (
    'comments_id' =>
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'reports_id' =>
    array (
      'type' => 'table:reports',
      'label' => app::get('b2c')->_('举报编号'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),    
    'source' =>
    array (
      'type' =>
      array (
        'buyer' => app::get('b2c')->_('举报人'),
        'seller' => app::get('b2c')->_('被举报人'),
        'platform' => app::get('b2c')->_('平台')
      ),
      'default' => 'buyer',
      'required' => true,
      'label' => app::get('b2c')->_('留言方'),
      'width' => 75,
      'hidden' => true,
      'editable' => false,
      'in_list' => true,

    ),
    'author_id' => array(
        'type'=>'mediumint(8)',
        'in_list' => false,
        'label' => app::get('b2c')->_('发表ID'),
        'default' => 0,
        'default_in_list' => false,
    ),
    'author' => array (
        'type' => 'varchar(100)',
        'label' => app::get('b2c')->_('发表人'),
        'searchtype' => 'has',
        'filtertype' => 'normal',
        'filterdefault' => 'true',
        'in_list' => true,
    ),
    'comment' => array(
        'type'=>'longtext',
        'label' => app::get('b2c')->_('内容'),
        'in_list' => true,
        'searchtype' => 'has',
        'filtertype' => 'normal',
        'filterdefault' => 'true',
        'default_in_list' => true,
    ),
    'image_0' =>
    array (
      'type' => 'varchar(32)',
      'label' => app::get('b2c')->_('图片0'),
      'width' => 75,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'image_1' =>
    array (
      'type' => 'varchar(32)',
      'label' => app::get('b2c')->_('图片1'),
      'width' => 75,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'image_2' =>
    array (
      'type' => 'varchar(32)',
      'label' => app::get('b2c')->_('图片2'),
      'width' => 75,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'image_3' =>
    array (
      'type' => 'varchar(32)',
      'label' => app::get('b2c')->_('图片3'),
      'width' => 75,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'image_4' =>
    array (
      'type' => 'varchar(32)',
      'label' => app::get('b2c')->_('图片4'),
      'width' => 75,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
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
  'engine' => 'innodb',
  'version' => '$Rev: 42376 $',
);
