<?php

 
$db['logs']=array (
    'columns' =>
    array (
        'id' =>
        array (
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
        ),
        'username' => 
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'label' => app::get('operatorlogmanage')->_('操作员'),
            'searchtype' => 'has',
            'filtertype' => 'yes',
            'filterdefault' => true,
            'width' => 70,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'realname' => 
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'label' => app::get('operatorlogmanage')->_('姓名'),
            'searchtype' => 'has',
            'filtertype' => 'yes',
            'filterdefault' => true,
            'width' => 70,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'dateline' => 
        array (
            'type' => 'time',
            'required' => true,
            'label' => app::get('operatorlogmanage')->_('操作时间'),
            'filtertype' => 'yes',
            'filterdefault' => true,
            'width' => 120,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'app' =>
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'label' => app::get('operatorlogmanage')->_('程序目录'),
            'width' => 60,
            'in_list' => true,
        ),
        'ctl' =>
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'label' => app::get('operatorlogmanage')->_('控制器'),
            'width' => 60,
            'in_list' => true,
        ),
        'act' => 
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'label' => app::get('operatorlogmanage')->_('动作'),
            'width' => 60,
            'in_list' => true,
        ),
        'operate' =>
        array (
            'type' => 'varchar(255)',
            'required' => true,
            'label' => app::get('operatorlogmanage')->_('操作'),
            'width' => 400,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'memo' => 
        array (
            'type' => 'varchar(255)',
            'label' => app::get('operatorlogmanage')->_('备注'),
            'width' => 200,
            'in_list' => true,
            'default_in_list' => true,
        )
    ),
    'index' => 
    array (
        'ind_dateline' => 
        array (
          'columns' => 
          array (
            0 => 'dateline',
          ),
        ),
        'ind_username' => 
        array (
          'columns' => 
          array (
            0 => 'username',
          ),
        ),
    ),
);
