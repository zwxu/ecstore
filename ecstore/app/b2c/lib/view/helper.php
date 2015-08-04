<?php

 
class b2c_view_helper{

    function __construct($app){
        $this->app = $app;
    }

    function function_goodsmenu($params,&$smarty){
        return $smarty->_fetch_compile_include('b2c','site/product/menu.html',$params);
    }

    function function_selector($params, &$smarty){
        $filter = $params['filter'];
        if(is_numeric($params['key'])){           
            if($params['del']&&isset($filter['p_'.$params['key']])){
                 unset($filter['p_'.$params['key']]);
            }else{
                $data = &$filter['p_'.$params['key']];
            }
        }elseif ($params['key']=="spec"){
            $tmp=explode(",",$params['value']);
            $data = &$filter['s_'.$tmp[0]];
        }else{
            $data = &$filter[$params['key']];
        }

        if($params['mod']=='append'){
            $data[] = $params['value'];
        }elseif($params['mod']=='remove'){
            $data = array_flip($data);
            unset($data[$params['value']]);
            $data = array_flip($data);
        }else{
            if ($params['key']=="spec"){
                $tmpData = explode(",",$params['value']);
                $data = array($tmpData[1]);
            }
            elseif(!$params['del']){
                $data = array($params['value']);
            }

        }
        
        if(isset($params['pageIndex']) && $params['pageIndex'] == 1){
            $searchtools = $this->app->model('search');
        }else{
            $searchtools = $smarty->app->model('search');
        }
        $args = $params['args'];

        $args[1] = $searchtools->encode($filter);

        $args[4]=1;
        return app::get('site')->router()->gen_url(array('app'=>'b2c', 'ctl'=>'site_gallery','full'=>1,'args'=>$args));
    }

    function function_gpagers($params, &$smarty){
        //print_r($params);
	    if(!$params['data']['current'])$params['data']['current'] = 1;
        
        if(!$params['data']['total'])$params['data']['total'] = 1;
        //最多显示100页。
        if(intval($params['data']['total'])>100){
            $params['data']['total']=100;
        }
        if($params['data']['total']<2){
            return '';
        }
        $args=$params['data']['args'];
        $formAction=$params['data']['formAction'];
        $hidden=array();
        $url=array();
        if($args){
            if($args['filter']){
                $filter=$args['filter'];
                if($filter['price']){
                    $hidden[]='<input type="hidden" name="price[0]" value="'.$filter['price'][0].'">';
                    $hidden[]='<input type="hidden" name="price[1]" value="'.$filter['price'][1].'">';
                }
                if($filter['cat_id'][0]){                    
                    $hidden[]='<input type="hidden" name="cat_id" value="'.$filter['cat_id'][0].'">';
                }
                if($filter['name']){                    
                    $hidden[]='<input type="hidden" name="name" value="'.$filter['name'].'">';
                }
                if($filter['brand_id']){                    
                    $hidden[]='<input type="hidden" name="brand_id" value="'.implode(',',array_values($filter['brand_id'])).'">';
                }
                if($filter['freight_bear']){                    
                    $hidden[]='<input type="hidden" name="freight_bear" value="business">';
                }
                if($filter['goods_state']){                    
                    $hidden[]='<input type="hidden" name="goods_state" value="used">';
                }
            }
            if($args['orderby']){
                $hidden[]='<input type="hidden" name="orderby" value="'.$args['orderby'].'">';
            }
            if($args['tab']){                
                $hidden[]='<input type="hidden" name="tab" value="'.$args['tab'].'">';
            }
            if($args['cat_type']){                
                $hidden[]='<input type="hidden" name="cat_type" value="'.$args['cat_type'].'">';
            }
            if($args['view']){                
                $hidden[]='<input type="hidden" name="view" value="'.$args['view'].'">';
            }
            if($args['st']){                
                $hidden[]='<input type="hidden" name="st" value="'.$args['st'].'">';
            }
            if($args['store_id']){                
                $hidden[]='<input type="hidden" name="sid" value="'.$args['store_id'].'">';
                $url[]='sid='.$args['store_id'];
            }
            if($args['loc']){                
                $hidden[]='<input type="hidden" name="loc" value="'.$args['loc'].'">';
                $url[]='loc='.$args['loc'];
            }
            /*if($params['scon']['scontent']){
                $hidden[]='<input type="hidden" name="scontent" value="'.$params['scon']['scontent'].'">';
                if(strpos($params['data']['link'],'scontent')===false){
                    $url[]='scontent='.$params['scon']['scontent'];
                }
            }*/
        }
        if($params['data']['urlFilter']){
            $hidden[]='<input type="hidden" name="filter" value="'.$params['data']['urlFilter'].'">';                
        }
        $hs=implode(' ',$hidden);
        
        $url_link=implode('&',$url);
        if(!empty($url)){
            if(strpos($params['data']['link'],'?')===false){
                $url_link='?'.$url_link;
            }else{
                $url_link='&'.$url_link;
            }
            $params['data']['link']=$params['data']['link'].$url_link;
        }

    if($params['type']=='mini'){
		$prev = $params['data']['current']>1?
        '<a href="'.str_replace($params['data']['token'],$params['data']['current']-1,$params['data']['link']).'" class="ui-page-s-prev" title='.app::get('b2c')->_("上一页").'>&lt;</a>':
        '<b title="'.app::get('b2c')->_("上一页").'" class="ui-page-s-prev">&lt;</b>';

    $next = $params['data']['current']<$params['data']['total']?
      '<a href="'.str_replace($params['data']['token'],$params['data']['current']+1,$params['data']['link']).'" class="ui-page-s-next" title='.app::get('b2c')->_("下一页").'>&gt;</a>':
        '<b title="'.app::get('b2c')->_("下一页").'" class="ui-page-s-next">&gt;</b>';

        return <<<EOF
    <p class="ui-page-s"><b class="ui-page-s-len">{$params['data']['current']}/{$params['data']['total']}</b>{$prev}{$next}</p>
EOF;
    }else{      
		$prev = $params['data']['current']>1?
        '<a href="'.str_replace($params['data']['token'],$params['data']['current']-1,$params['data']['link']).'" class="ui-page-prev" title='.app::get('b2c')->_("上一页").'>&lt;&lt;'.app::get('b2c')->_('上一页').'</a>':
        '<b class="ui-page-prev">&lt;&lt;上一页</b>';

    $next = $params['data']['current']<$params['data']['total']?
      '<a href="'.str_replace($params['data']['token'],$params['data']['current']+1,$params['data']['link']).'" class="ui-page-next" title='.app::get('b2c')->_("下一页").'>'.app::get('b2c')->_('下一页').'&gt;&gt;</a>':
        '<b class="ui-page-prev">'.app::get('b2c')->_('下一页').'&gt;&gt;</b>';

        $c = $params['data']['current']; $t=$params['data']['total']; $v = array();  $l=$params['data']['link']; $p=$params['data']['token'];
		$curl=str_replace($p,$c,$l);
        if($t<11){
            $v[] = $this->g_pager_link(1,$t,$l,$p,$c);
            //123456789
        }else{
            if($t-$c<8){
                $v[] = $this->g_pager_link(1,3,$l,$p);
                $v[] = $this->g_pager_link($t-2,$t,$l,$p,$c);
                //12..50 51 52 53 54 55 56 57
            }elseif($c<8){
                $v[] = $this->g_pager_link(1,max($c+2,4),$l,$p,$c);
                $v[] = $this->g_pager_link($t-1,$t,$l,$p);
                //1234567..55
            }else{
                $v[] = $this->g_pager_link(1,2,$l,$p);
                $v[] = $this->g_pager_link($c-2,$c+2,$l,$p,$c);
                $v[] = $this->g_pager_link($t-1,$t,$l,$p);
                //123 456 789
            }
        }
        $links = implode('<b class="ui-page-break">...</b>',$v);

        return <<<EOF
    <div class="ui-page">
	<div class="ui-page-wrap">
	    <b class="ui-page-num">{$prev}{$links}{$next}</b>
		<b class="ui-page-skip">
		<form action="{$formAction}" method="post" name="filterPageForm">
		{$hs}
		<input type="hidden" value="{$params['data']['total']}" name="totalPage">
		共{$params['data']['total']}页，到第<input type="text" value="{$params['data']['current']}" size="3" class="ui-page-skipTo" name="page">页<button atpanel="2,pageton,,,,20,footer," class="ui-btn-s" type="submit">确定</button></form></b>
	</div>
	</div>    
EOF;
    }

    }
     

	function g_pager_link($from,$to,$l,$p,$c=null){
    for($i=$from;$i<$to+1;$i++){
        if($c==$i){
            $r[]=' <b class="ui-page-cur">'.$i.'</b> ';
        }else{
        $r[]=' <a href="'.str_replace($p,$i,$l).'">'.$i.'</a> ';
        }
    }
    return implode(' ',$r);
}





    function function_pagers($params, &$smarty){
    if(!$params['data']['current'])$params['data']['current'] = 1;
    if(!$params['data']['total'])$params['data']['total'] = 1;
    if($params['data']['total']<2){
        return '';
    }

    $prev = $params['data']['current']>1?
        '<a href="'.str_replace($params['data']['token'],$params['data']['current']-1,$params['data']['link']).__('" class="prev" title='.app::get('b2c')->_("上一页").'>&laquo;'.app::get('b2c')->_('上一页').'</a>'):
        '<span title='.app::get('b2c')->_("已经是第一页").' class="unprev">'.app::get('b2c')->_("已经是第一页").'</span>';

    $next = $params['data']['current']<$params['data']['total']?
      '<a href="'.str_replace($params['data']['token'],$params['data']['current']+1,$params['data']['link']).__('" class="next last" title='.app::get('b2c')->_("下一页").'>'.app::get('b2c')->_('下一页').'&raquo;</a>'):
        '<span title='.app::get('b2c')->_("已经是最后一页").' class="unnext">'.app::get('b2c')->_("已经是最后一页").'</span>';

    if($params['type']=='mini'){
        return <<<EOF
    <div class="pager"><strong class="pagecurrent">{$params['data']['current']}</strong><span class="line">/</span><span class="pageall">{$params['data']['total']}</span>{$prev}{$next}</div>
EOF;
    }else{

        $c = $params['data']['current']; $t=$params['data']['total']; $v = array();  $l=$params['data']['link']; $p=$params['data']['token'];

        if($t<11){
            $v[] = $this->_pager_link(1,$t,$l,$p,$c);
            //123456789
        }else{
            if($t-$c<8){
                $v[] = $this->_pager_link(1,3,$l,$p);
                $v[] = $this->_pager_link($t-8,$t,$l,$p,$c);
                //12..50 51 52 53 54 55 56 57
            }elseif($c<10){
                $v[] = $this->_pager_link(1,max($c+3,10),$l,$p,$c);
                $v[] = $this->_pager_link($t-1,$t,$l,$p);
                //1234567..55
            }else{
                $v[] = $this->_pager_link(1,3,$l,$p);
                $v[] = $this->_pager_link($c-2,$c+3,$l,$p,$c);
                $v[] = $this->_pager_link($t-1,$t,$l,$p);
                //123 456 789
            }
        }
        $links = implode('<span>...</span>',$v);

//    str_replace($params['data']['token'],4,$params['data']['link']);
//    if($params['data']['total']
        return <<<EOF
    <div class="pager">{$prev}{$links}{$next}</div>    
EOF;
    }
}

function function_goods_pager($params, &$smarty){
	if(!$params['data']['current'])$params['data']['current'] = 1;
    if(!$params['data']['total'])$params['data']['total'] = 1;
    if($params['data']['total']<2){
        return '';
    }
	
	$v = array();
	$params['data']['current']>1?($v[] = ('<span class="first" rel="request" href="'.str_replace($params['data']['token'],1,$params['data']['link']).'">&laquo;&laquo;</span><span class="prev" rel="request" href="'.str_replace($params['data']['token'],$params['data']['current']-1,$params['data']['link']).'">&laquo;</span>'.(($params['data']['current']>2)?'<span class="andson">...</span>':''))):($v[] = ('<span class="first disabled">&laquo;&laquo;</span><span class="prev disabled">&laquo;</span>'.(($params['data']['current']>3)?'<span class="andson">...</span>':'')));
	
	$links = '';
	$c = $params['data']['current'];
	$t = $params['data']['total'];  
	$l=$params['data']['link']; 
	$p=$params['data']['token'];
	
	if($t<4){
		$v[] = $this->_pager_links(1,$t,$l,$p,$c);
		//123456789
	}else{
		if($c==1){
			$v[] = $this->_pager_links(1,3,$l,$p,$c);
		}else{
			if ($c+1>$t)
				$v[] = $this->_pager_links($c-2,$t,$l,$p,$c);
			else
				$v[] = $this->_pager_links($c-1,$c+1,$l,$p,$c);
		}
	}
	
	$params['data']['current']<$params['data']['total']?($v[] = (((intval($params['data']['total']-$params['data']['current'])>1)?'<span class="andson">...</span>':'').'<span class="next" rel="request" href="'.str_replace($params['data']['token'],$params['data']['current']+1,$params['data']['link']).'">&raquo;</span><span class="last" rel="request" href="'.str_replace($params['data']['token'],$params['data']['total'],$params['data']['link']).'">&raquo;&raquo;</span>')):($v[] = ('<span class="next disabled">&raquo;</span><span class="last disabled">&raquo;&raquo;</span>'));
	
	$links = implode('',$v);
	
	return <<<EOF
    <div class="pager"><div class="pagernum">{$prev}{$links}{$next}</div></div>  
EOF;
}

function _pager_links($from,$to,$l,$p,$c=null){
	for($i=$from;$i<$to+1;$i++){
        if($c==$i){
            $r[]=' <span class="current">'.$i.'</span> ';
        }else{
        $r[]=' <span rel="request" href="'.str_replace($p,$i,$l).'">'.$i.'</span> ';
        }
    }
    return implode(' ',$r);
}

function _pager_link($from,$to,$l,$p,$c=null){
    for($i=$from;$i<$to+1;$i++){
        if($c==$i){
            $r[]=' <strong class="pagecurrent">'.$i.'</strong> ';
        }else{
        $r[]=' <a class="pagernum" href="'.str_replace($p,$i,$l).'">'.$i.'</a> ';
        }
    }
    return implode(' ',$r);
}

    function modifier_paddingleft($vol,$empty,$fill)
    {
        return str_repeat($fill,$empty).$vol;

    }

    
    function modifier_lazyimg($v){  
     //   $result = preg_replace("/(\<img[\s\S]+)src=/Us","\\1lazy-load-img=",$v); 
        return $v;
    }

    function function_goodsspec($params) {
        return kernel::single("b2c_goods_detail_spec")->show($params['goods_id'], $aGoods, array('spec_node_new'=>$params['spec_node_new'],'spec_node'=>$params['spec_node']));
    }

}
