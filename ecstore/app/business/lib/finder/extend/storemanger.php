<?php
class business_finder_extend_storemanger{
     function get_extend_colums(){
            $db['storemanger']=array (
              'columns' => 
              array (
                 'issue_type' => 
                array (
                  'type' =>
                  array (
                    0 => app::get('b2c')->_('卖场型旗舰店'),
                    1 => app::get('b2c')->_('专卖店'),
                    2 => app::get('b2c')->_('专营店'),
                    3 => app::get('b2c')->_('品牌旗舰店'),
                  ),
                  'default' => 0,
                  'label' => app::get('b2c')->_('店铺类型'),
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
