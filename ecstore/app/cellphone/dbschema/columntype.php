<?php
$db['columntype']=
    array (
       'columns' =>
       array (
          'columntype_id' =>
          array (
             'type' => 'number',
             'required' => true,
             'extra' => 'auto_increment',
             'pkey' => true,
			 'label'=>'序号',
			 'filtertype'=>true,
			  'searchtype'=>true,
             ),
          'columntype_name' =>
          array (
             'type' => 'varchar(100)',
             'in_list'=>true,
             'is_title'=>true,
             'default_in_list'=>true,
             'label'=>'栏目名称',
             'filtertype'=>true,
			 'is_title'=>true,
             'searchtype'=>true,
             'searchtype' => 'has',
			 'required' => true,
             ),
			  'columntype_description' =>
          array (
             'type' => 'varchar(100)',
             'in_list'=>true,
             'default_in_list'=>true,
             'label'=>'栏目描述',
             ),
			'd_order' =>
       array (
      'type' => 'number',
      'default' => 1,
      'required' => true,
      'label' => app::get('b2c')->_('排序'),
      'width' => 50,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => false,
        ),
        'css_type'=>
		array (
		'type'=>array(
		 '1'=>__('样式1'),
		 '2'=>__('样式2'),
		 ),
		'label' => app::get('cellphone')->_('样式类型'),
        'width' => 150,
	    'default'=>'1',
		'required'=>true,
        'editable' => false,
        'in_list' => true,
        'default_in_list' => true,	
		
		),
			
          'columntype_createtime' =>
          array (
             'in_list'=>true,
             'default_in_list' => true,
             'label' => '录入时间',
             'type' => 'time',
			'required' => true,
             ),

          ),
       );


?>