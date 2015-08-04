<?php

 
$db['meta_value_varchar']=array (
  'columns' => 
  array (
    'mr_id' => 
    array (
      'type' => 'number',
      'required' => false,
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
      'type' => 'varchar(255) NOT NULL default \'\'',
      'required' => true,
      'comment' => app::get('dbeav')->_('meta值'),
    ),
  ),
  'comment' => app::get('dbeav')->_('meta系统varchar类型存值表'),
  'index' => 
  array (
    'ind_value' => 
    array (
      'columns' => 
      array (
        0 => 'value',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
);
