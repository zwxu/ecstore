<?php
$db['phone']=
    array (
       'columns' =>
       array (
          'phone_id' =>
          array (
             'type' => 'number',
             'required' => true,
             'extra' => 'auto_increment',
             'pkey' => true,
			 'label'=>'序号',
			 'filtertype'=>true,
			  'searchtype'=>true,
             ),
          'phone_number' =>
          array (
             'type' => 'varchar(100)',
             'in_list'=>true,
             'is_title'=>true,
             'default_in_list'=>true,
             'label'=>'电话号码',
             'filtertype'=>true,
			 'is_title'=>true,
             'searchtype'=>true,
             'searchtype' => 'has',
			 'required' => true,
             ),
			  'remark' =>
          array (   
             'type' => 'varchar(100)',
             'in_list'=>true,
             'default_in_list'=>true,
             'label'=>'备注 ',
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
   );





