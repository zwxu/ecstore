<?php
$db['themes_file']=array (
    'columns' => array (
        'id' =>
        array(
          'type' => 'int unsigned',
          'required' => true,
          'pkey' => true,
          'extra' => 'auto_increment',
          'editable' => false,
        ),
        'filename' => 
        array (
            'type' => 'varchar(300)',
        ),
        'filetype' => 
        array (
            'type' => 'varchar(30)',
        ),
        'fileuri' => 
        array (
            'type' => 'varchar(300)',
        ),
        'version' => 
        array (
            'type' => 'int',
            'required' => false,
        ),
        'theme' => 
        array (
            'type' => 'varchar(50)',
        ),
       # 'is_tmpl' =>
       # array (
       #     'type' => 'bool',
       #     'required' => true,
       #     'default'=>'false',
       # ),
        'memo' => 
        array (
            'type' => 'varchar(100)',
        ),
        'content' => 
        array (
            'type' => 'text',
        ),
    ),
    'version' => '$Rev: 40918 $',
    'unbackup' => true,
);
