<?php

 
$db['register']=array (
    'columns' =>
    array (
        'id' =>
        array (
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
        ),
        'app' =>
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'label' => app::get('operatorlogmanage')->_('程序目录'),
        ),
        'ctl' =>
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'label' => app::get('operatorlogmanage')->_('控制器'),
        ),
        'act' => 
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'label' => app::get('operatorlogmanage')->_('动作'),
        ),
        'operate' =>
        array (
            'type' => 'varchar(255)',
            'required' => true,
            'label' => app::get('operatorlogmanage')->_('操作'),
        ),
    ),
    'index' => 
    array (
        'ind_index' => 
        array (
          'columns' => 
          array (
            0 => 'app',
            1 => 'ctl',
            2 => 'act',
          ),
          'prefix' => 'UNIQUE',
        ),
    ),
);
