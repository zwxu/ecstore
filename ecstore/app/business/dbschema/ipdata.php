<?php 
$db['ipdata'] = array( 
    'columns' => 
    array(
        'start' =>
        array (
          'type' => 'double(53,0)',
          'required' => true,
          'default' => 0,
          'pkey' => true,
          'editable' => false,
        ),
        'end' =>
        array (
          'type' => 'double(53,0)',
          'required' => true,
          'default' => 0,
          'pkey' => true,
          'editable' => false,
        ),
        'city' =>
        array (
          'type' => 'varchar(10)',
          'editable' => false,
        ),
    ),
);