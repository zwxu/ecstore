<?php

 
class b2c_finder_extend_members{
    function get_extend_colums(){
            $db['members']=array (
              'columns' => 
              array (
                'member_key' => 
                array (
                  'type' => 'varchar(50)',
                  'label' => app::get('b2c')->_('会员用户名'),
                  'width' => 75,
                  'searchtype' => 'has',
                  'editable' => true,
                  'filtertype' => 'has',
                  'filterdefault' => 'true',
                  'in_list' => true,
                  'is_title'=>true,
                  'default_in_list' => false,
                ),
                'refer_id' => 
                array (
                  'type' => 'varchar(200)',
                  'required' => true,
                  'default' => 0,
                  'label' => app::get('b2c')->_('首次来源ID'),
                  'width' => 75,
                  'editable' => true,
                  'filtertype' => 'yes',
                  'filterdefault' => true,
                  'in_list' => true,
                  'default_in_list' => true,
                ),
                 'refer_url' => 
                array (
                  'type' => 'varchar(200)',
                  'required' => true,
                  'default' => 0,
                  'label' => app::get('b2c')->_('首次来源URL'),
                  'width' => 75,
                  'editable' => true,
                  'filtertype' => 'yes',
                  'filterdefault' => true,
                  'in_list' => true,
                  'default_in_list' => true,
                ),
                 'refer_time' => 
                array (
                  'type' => 'time',
                  'required' => true,
                  'default' => 0,
                  'label' => app::get('b2c')->_('首次来源时间'),
                  'width' => 75,
                  'editable' => true,
                  'filtertype' => 'yes',
                  'filterdefault' => true,
                  'in_list' => true,
                  'default_in_list' => true,
                ),
                 'c_refer_id' => 
                array (
                  'type' => 'varchar(200)',
                  'required' => true,
                  'default' => 0,
                  'label' => app::get('b2c')->_('本次来源ID'),
                  'width' => 75,
                  'editable' => true,
                  'filtertype' => 'yes',
                  'filterdefault' => true,
                  'in_list' => true,
                  'default_in_list' => true,
                ),
                 'c_refer_url' => 
                array (
                  'type' => 'varchar(200)',
                  'required' => true,
                  'default' => 0,
                  'label' => app::get('b2c')->_('本次来源URL'),
                  'width' => 75,
                  'editable' => true,
                  'filtertype' => 'yes',
                  'filterdefault' => true,
                  'in_list' => true,
                  'default_in_list' => true,
                ),
                 'c_refer_time' => 
                array (
                  'type' => 'time',
                  'required' => true,
                  'default' => 0,
                  'label' => app::get('b2c')->_('本次来源时间'),
                  'width' => 75,
                  'editable' => true,
                  'filtertype' => 'yes',
                  'filterdefault' => true,
                  'in_list' => true,
                  'default_in_list' => true,
                )));
        if(app::get('bdlink')->is_actived())
        {    
            return $db;
        }
    }
}

