<?php

 
class desktop_finder_tagcols{
    var $column_tag = '标签';
    var $column_tag_order = COLUMN_IN_TAIL;
    function column_tag($row){
        $rel_id = $row[$row['idColumn']];
        $oTagRel = app::get('desktop')->model('tag_rel');
        $oTag = app::get('desktop')->model('tag');
        $filter = array('rel_id'=>$rel_id,'tag_type'=>$row['tag_type'],'app_id'=>$row['app_id']);
        $tag_ids = $oTagRel->getList('tag_id',$filter);
        foreach($tag_ids as $id){
            $ids[] = $id['tag_id'];
        }
        if($ids){
            $rows = $oTag->getList('*',array('tag_id'=>$ids));
            foreach($rows as $row){
                $color_str = '';
                
                if($row['tag_fgcolor']){
                    $color[] = 'color:'.$row['tag_fgcolor'];
                }
                if($row['tag_bgcolor']){
                    $color[] = 'background-color:'.$row['tag_bgcolor'];
                }
                if($row['tag_bgcolor']&&is_array($color)) $color_str = implode(';',$color);
                $return .= '<span class="tag-label" style="'.$color_str.'">'.$row['tag_name'].'</span>';
            }
            return $return;
        }
    }

}
