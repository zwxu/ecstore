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
            'label' => app::get('operatorlog')->_('操作员'),
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
            'label' => app::get('operatorlog')->_('姓名'),
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
            'label' => app::get('operatorlog')->_('操作时间'),
            'filtertype' => 'yes',
            'filterdefault' => true,
            'width' => 120,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'operate_type' => 
        array (
          'type' => 
          array (
            'normal' => app::get('operatorlog')->_('普通'),
            'members' => app::get('operatorlog')->_('会员'),
            'goods' => app::get('operatorlog')->_('商品'),
            'orders' => app::get('operatorlog')->_('订单'),
          ),
          'default' => 'normal',
          'label' => app::get('operatorlog')->_('操作类型'),
          'width' => 110,
          'filtertype' => 'yes',
          'filterdefault' => true,
          'in_list' => true,
        ),
        'operate_key' =>
        array (
            'type' => 'varchar(255)',
            'label' => app::get('operatorlog')->_('主关键字'),
            'width' => 200,
            'searchtype' => 'has',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'memo' => 
        array (
            'type' => 'longtext',
            'label' => app::get('operatorlog')->_('操作内容'),
            'width' => 200,
            'in_list' => true,
            'default_in_list' => true,
        ),
//        'reg_ip' => 
//        array (
//            'type' => 'varchar(16)',
//            'label' => app::get('operatorlog')->_('登录IP'),
//            'width' => 110,
//            'in_list' => true,
//        ),
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
        'ind_operate_key' => 
        array (
          'columns' => 
          array (
            0 => 'operate_key',
          ),
        ),
    ),
);
