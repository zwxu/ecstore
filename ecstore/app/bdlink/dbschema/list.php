<?php

$db['list']=array (
 'columns' => array (
    'id' =>
         array (
          'type' => 'bigint unsigned',
          'required' => true,
          'pkey' => true,
          'extra' => 'auto_increment',
          'label' => 'ID',
          'width' => 110,
          'hidden' => true,
          'editable' => false,
          'in_list' => false,
        ),
    'generatecode' =>
        array (
          'type' => 'varchar(255)',
          'label' => app::get('bdlink')->_('投放链接'),
          'width' => 280,
          'in_list' => true,
          'default_in_list' => true,
        ),
    'time' =>
        array (
          'type' => 'time',
          'label' => app::get('bdlink')->_('创建时间'),
          'width' => 140,
          'in_list' => true,
          'default_in_list' => true,
        ),
    'user_id' =>
        array (
          'type' => 'number',
          'label' => app::get('bdlink')->_('创建用户id'),
          'width' => 75,
        ),
    'validtime' =>
        array (
          'type' => 'time',
          'label' => app::get('bdlink')->_('有效期'),
          'width' => 150,
        ),
   ),
  'version' => '$Rev: 41137 $',
  'engine' => 'innodb',
);