<?php

$db['cat']=array (
  'columns' => 
  array (
    'cat_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('gift')->_('分类ID'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),

    'cat_name' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'is_title' => true,
      'default' => '',
      'label' =>  app::get('gift')->_('分类名称'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'is_title'=>true,
      'default_in_list' => true,
    ),
    'p_order' => 
    array (
      'type' => 'number',
      'label' =>  app::get('gift')->_('排序'),
      'width' => 110,
      'editable' => false,
      'default' => 1,
      'in_list' => true,
    ),
    'ifpub' => 
    array (
      'type' => 'bool',
      'label' =>  app::get('gift')->_('是否发布'),
      'width' => 110,
      'editable' => false,
      'default' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label' =>  app::get('gift')->_('是否屏蔽（true：是；false：否）'),
      'width' => 110,
      'editable' => false,
    ),
    
  ),
  'comment' =>  app::get('gift')->_('类别属性值有限表'),
  'version' => '$Rev: 41329 $',
);
