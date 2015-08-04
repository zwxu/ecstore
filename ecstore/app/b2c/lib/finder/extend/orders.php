<?php

 
class b2c_finder_extend_orders{
    function get_extend_colums(){
            $db['orders']=array (
              'columns' => 
              array (
                'payment' => 
                array (
                  'type' => 'table:payment_cfgs@ectools',
                  'required' => true,
                  'default' => 0,
                  'label' => app::get('b2c')->_('支付方式'),
                  'width' => 75,
                  'editable' => true,
                  'filtertype' => 'yes',
                  'filterdefault' => true,
                  'in_list' => true,
                )));
                
            if(app::get('bdlink')->is_actived())
            {
                $db['orders']['columns']['refer_id'] = array(
                    'type' => 'varchar(200)',
                    'required' => true,
                    'default' => 0,
                    'label' => app::get('b2c')->_('首次来源ID'),
                    'width' => 75,
                    'editable' => true,
                    'filtertype' => 'yes',
                    'filterdefault' => true,
                    'in_list' => true,
                );
                $db['orders']['columns']['refer_url'] = array(
                    'type' => 'varchar(200)',
                    'required' => true,
                    'default' => 0,
                    'label' => app::get('b2c')->_('首次来源URL'),
                    'width' => 75,
                    'editable' => true,
                    'filtertype' => 'yes',
                    'filterdefault' => true,
                    'in_list' => true,
                );
                $db['orders']['columns']['refer_time'] = array(
                    'type' => 'time',
                    'required' => true,
                    'default' => 0,
                    'label' => app::get('b2c')->_('首次来源时间'),
                    'width' => 75,
                    'editable' => true,
                    'filtertype' => 'yes',
                    'filterdefault' => true,
                    'in_list' => true,
                );
                $db['orders']['columns']['c_refer_id'] = array(
                    'type' => 'varchar(200)',
                    'required' => true,
                    'default' => 0,
                    'label' => app::get('b2c')->_('本次来源ID'),
                    'width' => 75,
                    'editable' => true,
                    'filtertype' => 'yes',
                    'filterdefault' => true,
                    'in_list' => true,
                );
                $db['orders']['columns']['c_refer_url'] = array(
                    'type' => 'varchar(200)',
                    'required' => true,
                    'default' => 0,
                    'label' => app::get('b2c')->_('本次来源URL'),
                    'width' => 75,
                    'editable' => true,
                    'filtertype' => 'yes',
                    'filterdefault' => true,
                    'in_list' => true,
                );
                $db['orders']['columns']['c_refer_time'] = array(
                    'type' => 'time',
                    'required' => true,
                    'default' => 0,
                    'label' => app::get('b2c')->_('本次来源时间'),
                    'width' => 75,
                    'editable' => true,
                    'filtertype' => 'yes',
                    'filterdefault' => true,
                    'in_list' => true,
                );
            }
            
        return $db;
    }
}

