<?php

 
class desktop_mdl_tag_rel extends dbeav_model{

    function save( &$item ){
        $list = parent::getList('*',array('tag_id'=>$item['tag']['tag_id'],'rel_id'=>$item['rel_id']));
        if($list && count($list)>0){
            $item = $list[0];
        }else{
            parent::save($item);
        }
    }
}
