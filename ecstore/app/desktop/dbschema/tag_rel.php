<?php

 
$db['tag_rel']=array (
  'columns' => 
  array (
    'tag_id' => 
    array (
      'type' => 'table:tag',
      'sdfpath' => 'tag/tag_id',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'rel_id' => 
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'app_id' => 
    array (
      'type' => 'varchar(32)',
      'label' => app::get('desktop')->_('应用'),
      'required' => true,
      'width' => 100,
      'in_list' => true,
    ),
    'tag_type' => 
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'label' => app::get('desktop')->_('标签对象'),
      'editable' => false,
      'in_list' => true,
    ),
  ),
  'version' => '$Rev$',
);
