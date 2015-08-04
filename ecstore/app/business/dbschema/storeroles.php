<?php 

 
$db['storeroles']=array ( 
  'columns' => 
  array (
    'role_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'width' => 50,
      'label' => app::get('desktop')->_('工作组id'),
      'hidden' => 1,
      'editable' => false,
      'extra' => 'auto_increment',
      'in_list' => true,
    ),
    'role_name' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'label' => app::get('desktop')->_('角色名'),
      'width' => 310,
      'in_list' => true,
      'is_title' => true,
      'default_in_list' => true,
    ),
    'workground' => 
    array (
      'label' => app::get('desktop')->_('工作场景'),
      'type' => 'text',
      'editable' => false,
      'in_list' => false,
      'hidden' => true,
    ),

     'store_id' =>
    array (
      'type' => 'table:storemanger@business',
      'label' => app::get('b2c')->_('店铺'),
      'width' => 150,
      'comment' => app::get('b2c')->_('店铺id'),
      'editable' => false,
      'pkey'=>false,
      'in_list' => true,
      'default_in_list' => false,
    ),
   
    'regtime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('角色建立时间'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

  ),
  'version' => '$Rev: 40654 $',
);
