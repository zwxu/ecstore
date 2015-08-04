<?php
//轮播表
 $db['banner']= array(
 'columns' =>
  array (
    'id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('cellphone')->_('序号'),
      'comment' => app::get('cellphone')->_('序号'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),
   'associate_id' =>
    array (
      'type' => 'bigint unsigned',
      'label' => app::get('cellphone')->_('关联ID'),
      'width' => 150,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
 'associate_type' =>
    array (
      'type' => array(
                "goods"=>__('商品'),
                "activity"=>__('活动'),
                "article"=>__('文章'),
            ),
      'label' => app::get('cellphone')->_('关联类型'),
      'width' => 150,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
	'image_id' =>
          array (
             'type' => 'varchar(100)',
             'in_list'=>false,
             'default_in_list'=>false,
             'label'=>'图片',
			 
			 ),

  'd_order' =>
    array (
      'type' => 'number',
      'default' => 1,
      'required' => true,
      'label' => app::get('b2c')->_('排序'),
      'width' => 30,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => false,
    ),

 'start_time' => 
    array (
      'type' => 'time',
      'label' => app::get('cellphone')->_('开始时间'),
      'width' => 150,
      'required' => true,
      'default' => 0,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'end_time' => 
    array (
      'type' => 'time',
      'label' => app::get('cellphone')->_('终止时间'),
      'width' => 150,
      'required' => true,
      'default' => 0,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),
  'is_active' => array(
            'type'=>'bool',
            'label'=>__('开启'),
            'default'=>'false',
            'editable'=>false,
			 'in_list' => true,
             'default_in_list' => true,
        ),
  'disabled'=>array(
			'type'=>'bool',
			'default'=>'false',
		),

),
) ;



