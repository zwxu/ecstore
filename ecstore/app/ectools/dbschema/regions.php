<?php 

 
/**
 * @table regions;
 * @package Schemas
 * @version $
 * @license Commercial
 */

$db['regions']=array (
    'columns' =>
    array (
        'region_id' =>
        array (
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'local_name' =>
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'default' => '',
            'label'=>app::get('ectools')->_('当地名称'),
            'width'=>100,
            'default_in_list'=>true,
            'in_list'=>true,
            'editable' => false,
        ),
        'package' =>
        array (
            'type' => 'varchar(20)',
            'required' => true,
            'default' => '',
            'label'=>app::get('ectools')->_('数据包'),
            'width'=>100,
            'default_in_list'=>true,
            'in_list'=>true,
            'editable' => false,
        ),
        'p_region_id' =>
        array (
            'type' => 'int unsigned',
            'editable' => false,
        ),
        'region_path' =>
        array (
            'type' => 'varchar(255)',
            'width'=>300,
            'editable' => false,
        ),
        'region_grade' =>
        array (
            'type' => 'number',
            'editable' => false,
        ),
        'p_1' =>
        array (
            'type' => 'varchar(50)',
            'editable' => false,
        ),
        'p_2' =>
        array (
            'type' => 'varchar(50)',
            'editable' => false,
        ),
        'ordernum' =>
        array (
            'type' => 'number',
            'editable' => true,
        ),
        'disabled' =>
        array (
            'type' => 'bool',
            'default' => 'false',
            'editable' => false,
        ),
    ),
    'index' => 
  array (
    'ind_p_regions_id' =>
    array (
        'columns' =>
        array (
          0 => 'p_region_id',
          1 => 'region_grade',
          2 => 'local_name',
        ),
        'prefix' => 'unique',
    ),
  ),
);
