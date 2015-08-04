<?php
$db['themes_tmpl']=array ( 
    'columns' => array (
        'id' =>
        array(
          'type' => 'int unsigned',
          'required' => true,
          'pkey' => true,
          'extra' => 'auto_increment', 
          'editable' => false,
        ),
        'tmpl_type' => 
        array (
            'type' => 'varchar(20)',
            'required' => true,
        ),
        'tmpl_name' => 
        array (
            'type' => 'varchar(30)',
            'required' => true,
        ),
        'tmpl_path' => 
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'is_title' => true,
        ),
        // 'version' => 
        // array (
        //     'type' => 'time',
        //     'required' => true,
        // ), 
        'theme' => 
        array (
            'type' => 'varchar(20)',
            'required' => true,
        ),
		'type'=>array(
			'type'=>'table:goods_cat@b2c',
			'required' => false,
		),
        // 'content' => 
        // array (
        //     'type' => 'text',
        // ),
        'rel_file_id' =>
        array (
            'type' => 'int',
            'required' => true,
        ),
    ),
    'version' => '$Rev: 40918 $',
    'unbackup' => true,
);
