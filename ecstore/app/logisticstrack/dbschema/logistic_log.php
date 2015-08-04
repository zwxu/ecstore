<?php

 
$db['logistic_log'] = array(
	'columns'=>array(
		'id'=>array(
			'type'=>'number','required'=>true,'pkey'=>true,'extra'=>'auto_increment',
		),
		'dtline'=>array(
			'type'=>'time',
			'required'=>true,
			'label'=>app::get('logisticstrack')->_('最后拉取时间'),//最后从中心拉取时间
			'in_list'=>true,//desktop 列表项配置中是否出现该列
			'default_in_list'=>true,//desktop 列表项配置中是否让该列处于选中状态
		),
		'delivery_id'=>array(
			'type'=>'varchar(20)',
			'required'=>true,
			'label'=>app::get('logisticstrack')->_('关联单号'),
		),
		'pulltimes'=>array(
			'type'=>'number',
			'label'=>app::get('logisticstrack')->_('拉取次数'), // 从中心拉取次数
		),
		'dly_corp'=>array(
			'type'=>'varchar(200)',
			'default'=>'',
			'comment' => app::get('logisticstrack')->_('物流公司'),
		),
		'logistic_no'=>array(
			'type'=>'varchar(64)',
			'default'=>'',
			'comment' => app::get('logisticstrack')->_('物流单号'),
		),
		'logistic_log'=>array(
			'type'=>'text',
			'default'=>'',
			'comment' => app::get('logisticstrack')->_('物流记录'),
		),
	),

	'comment' => app::get('logisticstrack')->_('物流状态记录表'),
	
	'index'=> array(
		'ind_delivery_id'=>array( // 索引名
			'columns'=>array( // 索引列
				0 => 'delivery_id',
			),
			'prefix'=>'UNIQUE', // 索引类型 fulltext unique
			'type'=>'' // 索引算法 BTREE HASH RTREE
		),
	),
	'idColumn'=>'delivery_id',
	
	'ignore_cache' => false,//
	'engine'=>'innodb',
);