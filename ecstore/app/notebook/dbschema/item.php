<?php
$db['item']=array (
    'columns' =>
    array (
        'item_id' =>  array (
            'type' => 'number',
            'required' => true,
            'extra' => 'auto_increment',
            'pkey' => true
        ),
        /* 'item_subject' => array ( 'type' => 'varchar(100)' ),
        'item_content' => array ( 'type' => 'text' ),
        'item_posttime' => array ( 'type' => 'time' ),
        'item_email' => array ( 'type' => 'email'), */
        //前台数据在后台可以显示
        'item_subject' =>
        array (
            'type' => 'varchar(100)',
            'in_list'=>true,
            'is_title'=>true,
            'default_in_list'=>true,
            'label'=>'书名',
            'filtertype'=>true,
            'searchtype'=>true,
            'searchtype' => 'has',
        ),
        'item_content' =>
        array (
            'lable' => '内容',
            'type' => 'text',
        ),
        'item_posttime' =>
        array (
            'in_list'=>true,
            'default_in_list' => true,
            'label' => '提交时间',
            'type' => 'time',
        ),
        'item_email' =>
        array (
            'in_list'=>true,
            'default_in_list' => true,
            'label' => 'email',
            'type' => 'email',
        ),
    ),
    
);
?>