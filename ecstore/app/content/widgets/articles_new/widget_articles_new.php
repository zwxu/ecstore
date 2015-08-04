<?php

 
    function widget_articles_new(&$setting,&$smarty){
        $setting['order'] or $setting['order'] = 'desc';
        $setting['order_type'] or $setting['order_type'] = 'pubtime';
        $func = array('asc'=>'ksort','desc'=>'krsort');
        
        $oAN = kernel::single("content_article_node");
        $oMAI = app::get('content')->model('article_indexs');
        $iNodeId = $setting['node_id'];
        $lv = $setting['lv'];
        $limit = $setting['limit'];
        $tmp = $oAN->get_node($iNodeId, true);
        article_new_foo($lv, $iNodeId, $limit, $setting['showallart'], $oAN, $oMAI, $tmp['child'], $setting);
        $html = array();
        
        article_new_show($smarty, $tmp['child'], $setting, $html, 0, $limit);
        if( !$setting['shownode'] ) {
            $func[$setting['order']]($html);
        }
        $html = implode(' ',$html);
        $filter = array();
        $filter['ifpub'] = 'true';
        $filter['pubtime|than'] = time();
        $arr = $oMAI->getList( 'pubtime',$filter,0,1,' pubtime ASC' );
        if( $arr ) { //设置缓存过期时间
            reset( $arr );
            $arr = current($arr);
            cachemgr::set_expiration($arr['pubtime']);
        }
        
        $tmp['__html'] = $html;
        $tmp['__shownode'] = $setting['shownode'];
        $tmp['__stripparenturl'] = $setting['stripparenturl'];
        
        if( $tmp['homepage']=='true' ) 
            $tmp['node_url'] = app::get('site')->router()->gen_url( array('app'=>'content', 'ctl'=>'site_article', 'act'=>'i', 'arg0'=>$setting['node_id']) );
        else 
            $tmp['node_url'] = app::get('site')->router()->gen_url( array('app'=>'content', 'ctl'=>'site_article', 'act'=>'l', 'arg0'=>$setting['node_id']) );
        //echo '<pre>';print_r($tmp);exit;
        return $tmp;
    }
    
    function article_new_foo($lv=1, $iNodeId=1, $limit, $showallart, $oAN, $oMAI, &$tmp, $setting) {
        if($lv<0)return;
        $aNodes = $oAN->get_nodes($iNodeId);
        
        if(is_array($aNodes)) {
            foreach ($aNodes as $val) {
                if($val['ifpub']=='false')continue;
                article_new_foo(($lv-1), $val['node_id'], $limit, $showallart, $oAN, $oMAI, $tmp['child'][$val['node_id']], $setting);
                if(empty($tmp['child'][$val['node_id']])) unset($tmp[$val['node_id']]);
                $tmp['child'][$val['node_id']]['info'] = $val;
            }
        }
        if( $showallart ) {
            
            if(!$limit) return ;
            #if( $lv==$setting['lv'] ) return false;
            $tmp['article'] = $oMAI->getList_1('*', array('node_id'=>$iNodeId, 'ifpub'=>'true', 'pubtime|lthan'=>time(),'nochildren'=>true),0, $limit,"{$setting['order_type']} {$setting['order']} ");
        } 
    }
    
    
    function article_new_show(&$smarty, $tmp, $setting, &$html, $lv=0, &$limit) {
        if($setting['shownode'] && $lv!=0) {
            $typeClass = 'node-list';
            if(is_object($smarty) && method_exists($smarty, 'gen_url')) {
                if( $tmp['info']['homepage']=='true' ){
                    $url = $smarty->gen_url(array('app'=>'content', 'ctl'=>'site_article', 'act'=>'i', 'arg0'=>$tmp['info']['node_id']));
                    $typeClass = 'node-index';
                } else{
                     $url = $smarty->gen_url(array('app'=>'content', 'ctl'=>'site_article', 'act'=>'l', 'arg0'=>$tmp['info']['node_id']));
                } 
                   
            }
            $html[] = article_new_html($lv, $url, $tmp['info']['node_name'],$typeClass);
        }
        
        if( !$setting['shownode'] ) {
            if( $limit<=0 ) return;
            #$tmp['article'] = array_slice( $tmp['article'], 0, $setting['limit'] );
        }
        
        if($tmp['article']) {
            if($setting['styleart']) {
                $tmp_lv = $setting['shownode'] ? ($setting['lv'] + 1) : 2;
            } else {
                $tmp_lv = $lv + 1;
            }

            $len = $limit;
            foreach ($tmp['article'] as $idx=>$row) {
                if(is_object($smarty) && method_exists($smarty, 'gen_url'))
                    $url = $smarty->gen_url(array('app'=>'content', 'ctl'=>'site_article', 'act'=>'index', 'arg0'=>$row['article_id']));
                $key = $row[$setting['order_type']];
                while(true) {
                    if( !isset($html[$key]) )break;
                    $key++;
                }
                $article_title = "<span>".$row['title']."</span>";
                if($setting['showuptime']){
                    $article_title.="<i>".date('y-m-d',$row['uptime'])."<em>".date('H:i',$row['uptime'])."</em>"."</i>";
                }

                if($limit>0){
                    if($idx == 0){
                        $html[$key] = "<li class='article-index-list'><ul>".article_new_html($tmp_lv, $url, $article_title,'article-index');
                    }elseif($idx == ($len-1)){
                        $html[$key] = article_new_html($tmp_lv, $url, $article_title,'article-index')."</ul></li>";
                    }else{
                        $html[$key] = article_new_html($tmp_lv, $url, $article_title,'article-index');
                    }
                }
                    
                
                $limit--;
            }
        }
        if($tmp['child']) {
            foreach ($tmp['child'] as $row) {
                article_new_show($smarty, $row, $setting, $html, $lv+1, $limit);
            }
        }
    }
    
    function article_new_html($lv, $url, $name,$type) {
    	
    	switch ($lv){
    		case 3:
    		return <<<EOF
<li class="subNav{$lv}">
    <a href="{$url}">{$name}</a>
</li>
EOF;
break;
    		default:
    			return <<<EOF
<li class="subNav{$lv}  subNav{$lv}Show">
    <a href="{$url}">{$name}</a>
</li>
EOF;
    	}
      
    }
?>
