<?php

class tags_special{
    public function add( $goods_id, &$aProduct ) {
        $tfilter['rel_id'] = $goods_id;
        $obj_tag_rel = app::get( 'desktop' )->model( 'tag_rel' );
        (array)$arr_tag_rel = $obj_tag_rel->getList( 'tag_id, rel_id', $tfilter );
        foreach ( $arr_tag_rel as $v ){
            (array)$tagfilter['tag_id'][] = $v['tag_id'];
        }
        $obj_tag = app::get( 'desktop' )->model( 'tag' );
        (array)$arr_tag = $obj_tag->getList( 'tag_id,tag_name,tag_fgcolor,tag_bgcolor,params', $tagfilter );
        foreach ( $arr_tag_rel as $rel_k => $rel_v ){
            foreach ( $arr_tag as $tag_v ) {
                if ( $rel_v['tag_id'] == $tag_v['tag_id'] ){
                    (array)$arr_tags[$rel_k]['tag_id']      = $rel_v['tag_id'];
                    (array)$arr_tags[$rel_k]['goods_id']    = $rel_v['rel_id'];
                    (array)$arr_tags[$rel_k]['tag_name']    = $tag_v['tag_name'];
                    (array)$arr_tags[$rel_k]['tag_bgcolor'] = $tag_v['tag_bgcolor'];
					(array)$arr_tags[$rel_k]['tag_fgcolor'] = $tag_v['tag_fgcolor'];
                    if( $tag_v['params']['tag_opacity']=='' ) $tag_v['params']['tag_opacity']=100;
                    (array)$arr_tags[$rel_k]['params']      = $tag_v['params'];
                }
            }
        }
        foreach ( $aProduct as $product_k => $product_v ){
            $i = 0;
            foreach ( (array)$arr_tags as $tags_v ){
                if ( $product_v['goods_id'] == $tags_v['goods_id'] ){
                    $aProduct[$product_k]['tags'][$i]['tag_name']    = $tags_v['tag_name'];
                    $aProduct[$product_k]['tags'][$i]['tag_bgcolor'] = $tags_v['tag_bgcolor'];
					$aProduct[$product_k]['tags'][$i]['tag_fgcolor'] = $tags_v['tag_fgcolor'];
                    $aProduct[$product_k]['tags'][$i]['params']      = $tags_v['params'];
                    $i++;
                }
            }
        }
        foreach( kernel::servicelist("tags_special_extends.add") as $object ) {
            $object->add( $aProduct );
        }
        
    }
}