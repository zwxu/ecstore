<?php

$db['widgets'] = array(
    'columns' => array(
        'id' => array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'app' => array (
            'type' => 'varchar(30)',
            'required' => true,
            'default' => '',
            'editable' => false,
        ),
        'theme' => array(
            'type' => 'varchar(30)',
            'required' => true,
            'default' => '',
            'editable' => false,
        ),
        'name' => array (
            'type' => 'varchar(30)',
            'required' => true,
            'default' => '',
            'editable' => false,
        )
    ),
    'index' => array(
        'ind_uniq' => 
        array (
          'columns' => 
          array (
            0 => 'app',
            1 => 'theme',
            2 => 'name',
          ),
        ),
    ),
    'unbackup' => true,
);
