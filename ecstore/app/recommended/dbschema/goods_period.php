<?php

 
$db['goods_period'] = array (
    'columns' => array (
        'primary_goods_id' => array (
            'type' => 'bigint unsigned',
            'required' => true,
        ),
        'secondary_goods_id' => array(
            'type' => 'varchar(200)',
        ),
        'last_modified' => array(
            'type' => 'time',
            'required' => true,
        ),
    ),
    
    'index' => array(
        'ind_goods_id' => array(
            'columns' => array(
                0 => 'primary_goods_id',
            ),
        ),
    ),
);