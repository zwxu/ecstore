<?php

 
$db['print_tmpl']=array (
  'columns' => 
  array (
    'prt_tmpl_id' => 
    array (
      'type' => 'int(10)',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'in_list' => false,
      'label' => app::get('express')->_('单据id'),
    ),
    'prt_tmpl_title' => 
    array (
      'type' => 'varchar(100)',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('express')->_('单据名称'),
    ),
    'shortcut' => 
    array (
      'type' => 
      array(
        'true' => app::get('express')->_('是'),
        'false' => app::get('express')->_('否'),
      ),
      'default' => 'false',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('express')->_('是否启用'),
    ),
    'prt_tmpl_width' =>
    array(
      'type' => 'tinyint(3) unsigned',
      'editable' => false,
      'required' => true,
      'in_list' => true,
      'default_in_list' => false,
      'label' => app::get('express')->_('单据宽度(mm)'),
    ),
    'prt_tmpl_height' => 
    array (
      'type' => 'tinyint(3) unsigned',
      'editable' => false,
      'required' => true,
      'in_list' => true,
      'default_in_list' => false,
      'label' => app::get('express')->_('单据高度(mm)'),
    ),
    'prt_tmpl_offsetx' =>
    array (
      'type' => 'tinyint',
      'default' => 0,
      'label' => app::get('express')->_('打印偏移(左)mm'),
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'prt_tmpl_offsety' =>
    array (
      'type' => 'tinyint',
      'default' => 0,
      'label' => app::get('express')->_('打印偏移(右)mm'),
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'prt_tmpl_data' =>
    array(
        'type' => 'longtext',
        'editable' => false,
        'label' => app::get('express')->_('数据'),
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 50868 $',
);
