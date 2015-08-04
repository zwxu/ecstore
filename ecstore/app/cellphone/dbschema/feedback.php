<?php

 $db['feedback']= array(
 'columns' =>
  array(
    'id' => 
    array(
      'type' => 'bigint unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('cellphone')->_('ID'),
      'editable' => false,

    ),
   'member_id' =>
    array(
      'type' => 'table:account@pam',
      'label' => app::get('cellphone')->_('反馈人'),
      'width' => 80,
      'editable' => false,
      'order'=> 2,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'content' =>
    array (
      'type' =>'text',
      'label' => app::get('cellphone')->_('反馈内容'),
	  'default'=>'',
      'width' => 200,
      'order'=> 20,
      'editable' => false,
      'in_list' => true,
      'default_in_list'=>true,

    ), 
    'contact' => 
     array(
      'type'=>'varchar(20)',
      'label'=>app::get('cellphone')->_('联系方式'),
	  'width' => 200,
      'default' => '',
      'editable'=> false,
      'order'=> 5,
	  'in_list' => true,
      'default_in_list' => true,
     ),
		),
 
 ) ;



