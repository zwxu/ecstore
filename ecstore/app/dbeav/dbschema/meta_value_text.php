<?php

 
$db['meta_value_text']=array (
  'columns' => 
  array (
    'mr_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'comment' => app::get('dbeav')->_('关联表的id id'),
    ),
    'pk' => 
    array (
      'type' => 'number',
      'required' => true, 
      'pkey' => true,
      'comment' => app::get('dbeav')->_('查询结果集定位基准'), 
    ),
    'value' => 
    array (
      'type' => 'text NOT NULL',
      'required' => true,
      'comment' => app::get('dbeav')->_('meta值'),
    ),
  ),
  'comment' => app::get('dbeav')->_('meta系统text类型存值表'),
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
);
