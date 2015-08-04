<?php
/*
 * Created on 2011-12-14
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 $db['position']=array(
         'columns'=>array(
             'position_id'=>array(
                     'type'=>'number',
                     'required'=>true,
                     'extra'=>'auto_increment',
                     'pkey'=>true,
             ),
             'position_name'=>array(
                     'type'=>'varchar(255)',
                     'required'=>false,
                     'in_list'=>true,
                     'default_in_list'=>true,
                     'searchtype' => 'has',
                     'filtertype' => 'custom',
                     'filterdefault' => true,
                     'filtercustom' =>
                              array (
                                'has' => app::get('b2c')->_('包含'),
                                'tequal' => app::get('b2c')->_('等于'),
                                'head' => app::get('b2c')->_('开头等于'),
                                'foot' => app::get('b2c')->_('结尾等于'),
                              ),
                     'label'=>app::get('poster')->_('所属版位名称'),
             ),
             'disabled' => 
                array (
                  'type' => 'bool',
                  'default' => 'false',
                  'editable' => false,
           ),
         )
 );

