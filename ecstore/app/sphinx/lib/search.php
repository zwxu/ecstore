<?php

class sphinx_search implements search_interface_model{

    var $name = 'sphinx搜索';
    var $servicename = 'B2C商品';
    var $description = '基于sphinx开发的搜索引擎';
    var $arr_query=array();
    function __construct(){
		try{
			$is_exists = class_exists('SphinxClient');
		}catch(Exception $e){
			require(dirname(__FILE__).'/../sphinxapi/sphinxapi.php');
		}
        
    	$searchConf = unserialize(app::get('sphinx')->getConf('sphinx_search_goods'));
        $this->hosts = preg_split('/[,;\s]+/', $searchConf['sphinx_server']);
        //print_r($this->hosts);
	    $this->index = $searchConf['sphinx_index'];
	    $this->timeout = $searchConf['sphinx_time']?$searchConf['sphinx_time']:3;
        $this->max_limit = $searchConf['sphinx_max_limit'] ? $searchConf['sphinx_max_limit'] : 1000;
        $this->obj = new SphinxClient();
    }

    function get_server()
    {
        $key = array_rand($this->hosts);
        return $this->hosts[$key];
    }//End Function

    function create(){
        $hosts = $this->get_server();
        list($server, $port) = explode(':', $hosts);
    	$this->obj->SetServer($server, intval($port));
        $this->obj->SetConnectTimeout($this->timeout);
        $this->obj->setMatchMode(SPH_MATCH_EXTENDED2);
    }

    function link(){
        $hosts = $this->get_server();
        list($server, $port) = explode(':', $hosts);
    	$this->obj->SetServer($server, intval($port));
        $this->obj->SetConnectTimeout($this->timeout);
        $this->obj->setMatchMode(SPH_MATCH_EXTENDED2);
    }

    function query($queryArr=array()){
        $this->view=$queryArr['view'];
        $this->st=$queryArr['st'];
    	$this->link();
        $this->SetFilter($queryArr);
        $this->setOrder($queryArr);
        $this->setLimit($queryArr);
        $this->addQuery($queryArr);
        $this->arr_query[]='GetProduct';
        $this->SetPropquery($queryArr);
        $this->SetBrandquery($queryArr);
        //$this->SetStorequery($queryArr);
        $this->SetAllStorequery($queryArr);
        $this->SetCatquery($queryArr);
        
        return true;
    }
    function auto_complete($keyword){
        $tkeyword=$keyword;
        $matches=array();
        if(empty($keyword)){
           return array();
        }
        $this->link();
        $title=$this->split_words($keyword);
        if(is_array($title)){
            $key = implode('"|"',$title);
            if($keyword!=$key){
                $keyword='"' . $key . '"|"'.$title.'"';
            }else{
                $keyword='"' . $key . '"';
            }
            $keyword = '@(name,brief) '.$keyword;
        }
        $this->obj->AddQuery($keyword, $this->index, 'auto_complete');
        $result = $this->obj->RunQueries();
        if(!isset($result[0]))return array();
        $matches['keyword']=$tkeyword;
        $matches['_count'] = $result[0]['total_found'];
        
        return $matches;
    }
    function query_store($queryArr=array()){
        $this->view=$queryArr['view'];
        $this->st=$queryArr['st'];
    	$this->link();
        $this->SetFilter($queryArr);
        $this->setOrder($queryArr,false);
        $this->SetPropquery($queryArr);
        $this->SetBrandquery($queryArr);
        $this->SetStorequery($queryArr);
        $this->SetAllStorequery($queryArr);
        $this->SetCatquery($queryArr);
        return true;
    }    
    function query_store_goods($queryArr=array()){
        $this->obj->ResetFilters();
        $this->obj->ResetGroupBy();
        //$this->obj->RunQueries();
        $this->arr_query=array();
        $this->view=$queryArr['view'];
        $this->st=$queryArr['st'];
    	$this->link();
        $this->SetFilter($queryArr);
        $this->setOrder($queryArr);
        $this->obj->SetLimits(0,$this->max_limit, $this->max_limit );
        $this->addQuery($queryArr);
        $this->arr_query[]='GetProduct';
        return true;
    }
    function query_widget_goods($queryArr=array(),$topGoods=array()){
        $this->obj->ResetFilters();
        $this->obj->ResetGroupBy();
        $this->arr_query=array();
        $this->st=$queryArr['st'];
        $this->link();
        $this->SetFilter($queryArr);
        $this->setOrder($queryArr,false);
        if($this->order){
            $this->order='orderWeight DESC,'.$this->order;
            $this->obj->SetSortMode(SPH_SORT_EXTENDED,$this->order);
        }else{
            $this->obj->SetSortMode(SPH_SORT_EXPR,'((@weight+dorder)*orderWeight)');
        }
        $this->setLimit($queryArr);
        $this->addQuery($queryArr);
        $this->arr_query[]='GetWidgetProduct';
        return true;
    }
    function GetWidgetProduct($index,$result,&$matches){
        if(!is_array($result))return;        
        if(!isset($result[$index]))return;
        $matches['result'] = array_keys($result[$index]['matches']);
        //$matches['total'] = $result[$index]['total_found'];
    }
    function GetProduct($index,$result,&$matches){
        if(!is_array($result))return;        
        if(!isset($result[$index]))return;
        $matches['result'] = array_keys($result[$index]['matches']);
        $matches['total'] = $result[$index]['total_found'];

        $store_id=array();
        foreach($result[$index]['matches'] as $akey=>$attr){
          $store_id[$attr['attrs']['store_id']][]=$akey;
        }
        $matches['store_id']=$store_id;
    }
    

    function addQuery($queryArr, $commit='search product')
    {   
        $keyword=$this->getKeywords($queryArr);//echo $keyword,'<br>';
        self::_print($keyword);
        $this->obj->AddQuery($keyword, $this->index, $commit);
    }//End Function

    function setLimit($queryArr)
    {   
        $this->obj->SetLimits(intval($queryArr['from']),intval($queryArr['to']), $this->max_limit );
    }//End Function

    function setOrder($queryArr,$setSetModel=true)
    {
        $this->order = '';
        foreach(kernel::servicelist('sphinx_search_b2c_goods.extends') AS $service){
            if(method_exists($service, 'default_order')){
                $queryArr['order'] = (empty($queryArr['order'])) ? $service->default_order() : $service->default_order() . ',' . $queryArr['order'];
            }
        }
        if(!empty($queryArr['order'])){
             $order_str=$queryArr['order'];            
        	 $order = explode(',',$order_str);
        	 foreach($order as $key => $val){
	        	 if(preg_match('/\s(DESC)/is',$val))
	                $order[$key] = $val;
	             else
	                $order[$key] = $val.' ASC';
	             if(preg_match('/goods_id/i',$val)){
                     unset($order[$key]);
                 }
        	 }
        	 $this->order = implode(',',$order);
        }        
        if($setSetModel){
            if($this->order){
                $this->order='orderWeight DESC,'.$this->order;
                $this->obj->SetSortMode(SPH_SORT_EXTENDED,$this->order);
            }else{
                $this->obj->SetSortMode(SPH_SORT_EXPR,'((@weight+dorder)*orderWeight)');
            }
        }
        
    }//End Function

    function commit(){
        $result = $this->obj->RunQueries();
        self::_print($result);
        $matches=array();
    	if(is_array($result[0]['matches'])){
            foreach($this->arr_query as $key=>$method){
                 if(method_exists($this,$method)){
                 $this->$method($key,$result,$matches);
                 }
           }
           /*$matches['result'] = array_keys($result[0]['matches']);
           $matches['total'] = $result[0]['total_found'];
           
           $store_id=array();
           foreach($result[0]['matches'] as $akey=>$attr){
              $store_id[$attr['attrs']['store_id']][]=$akey;
           }
           $matches['store_id']=$store_id;
           $res_k=1;
           unset($result[0]);
           if(isset($this->pcols)){
                if(is_array($result)){
                    foreach($result AS $p_key=>$prop_matches){
                        $k = 'p_' . $p_key;
                        if(!$this->pcols[$k])
                        break;
                        if(is_array($prop_matches['matches'])){
                            foreach($prop_matches['matches'] AS $val){
                                $nkey = $val['attrs'][$k];
                                $prop[$p_key][$nkey] = $val['attrs']['@count'];
                            }
                        }
                        $res_k++;
                    }
                }
                $matches['prop'] = $prop;
            }
            if(isset($result[$res_k])){
                $brand = array();
                $brand_matches = $result[$res_k];
                if(is_array($brand_matches['matches'])){
                    foreach($brand_matches['matches'] AS $keys => $val){
                        $nbkey = $val['attrs']['brand_id'];
                        if($nbkey){
                            $brand[$nbkey] = array('brand_id'=>$nbkey, '_count'=>$val['attrs']['@count']);
                        }
                    }
                }
                $matches['brand'] = $brand;
                $res_k++;
             }
             if(isset($result[$res_k])){
                $store = array();
                $store_matches = $result[$res_k];
                if(is_array($store_matches['matches'])){
                    foreach($store_matches['matches'] AS $keys => $val){
                        $nbkey = $val['attrs']['store_id'];
                        if($nbkey){
                            $store[$nbkey] =$val['attrs']['@count'];
                        }
                    }
                }
                $matches['store'] = $store;
                $res_k++;
             }
             if(isset($result[$res_k])){
                $cat = array();
                $cat_matches = $result[$res_k];
                if(is_array($cat_matches['matches'])){
                    foreach($cat_matches['matches'] AS $keys => $val){
                        $nbkey = $val['attrs']['cat_id'];
                        if($nbkey){
                            $cat[$nbkey] =$val['attrs']['@count'];
                        }
                    }
                }
                $matches['cat'] = $cat;
             }*/
             
        }       
        return $matches;
    }

    function insert($val=array()){
        return true;
    }


    function update($val=array()){
        return true;
    }

    function delete($val=array()){
        return true;
    }

    function SetFilter($filter){
        $filter = $this->prepaData($filter['filter']);
        self::_print($filter);
        for($i=1;$i<=20;$i++){
           if($filter['p_'.$i] == 0){
              $this->obj->setFilterRange('p_'.$i,0,1000000,'');
              unset($filter['p_'.$i]);
           }
		}
        if(!empty($filter['price'][0])&&!empty($filter['price'][1])){
             $this->obj->setFilterFloatRange('price',floatval($filter['price'][0]),floatval($filter['price'][1]),'');
             unset($filter['price']);
        }
        
        if(is_array($filter)){
	        foreach($filter as $k =>$v){
	        	if(is_array($v)){
	        	   $this->obj->SetFilter($k,$v);
                   }
	        	else
	        	   $this->obj->SetFilter($k,array($v));
	        }
        }
    }

    function getKeywords($filter)
    {
        if(!isset($this->keyword)){
            $keywords = array();
            if($this->st!='s'){
                $title = trim($filter['filter']['name'][0]);
                if(!empty($title)){
                    $this->title = $this->split_words($title);
                }
                if(is_array($this->title)){
                    $GLOBALS['search_array'] =$this->title;// array($queryArr['name'][0]);
                    $keyword = implode('"|"',$this->title);
                    if($keyword!=$title){
                        $keyword='"' . $keyword . '"|"'.$title.'"';
                    }else{
                        $keyword='"' . $keyword . '"';
                    }
                    //$keyword='"'.$title.'"';
                    //$keyword = '@(name,brand_name,store_name,spec_desc) ' . $keyword . '';
                    $keyword = '(@(name,brand_name,brand_keywords) '.$keyword.')|((@(name,brand_name,brand_keywords) '.$keyword.') (@cat_name '.$keyword.'))';
                    //$keyword = '@(name,brand_name,store_name,spec_desc,brief) ' . $keyword . '';
                    $keywords[]=$keyword;
                }
                
                $this->bn = $filter['filter']['bn'];
                if(isset($this->bn)){
                    $keyword = '@bn '.$this->bn;
                    $keywords[]=$keyword;
                }
            }else{
                $store_name=trim($filter['filter']['name'][0]);
                if(!empty($store_name)){
                   $this->store_name=$this->split_words($store_name);            
                   if(is_array($this->store_name)){               
                        $GLOBALS['search_array'] =$this->store_name;
                        $keyword = implode('"|"',$this->store_name);
                        $keyword='"' . $keyword . '"|"'.$store_name.'"';
                        $keyword = ' @(store_name) ' . $keyword . '';
                        $keywords[]=$keyword;
                    }
                }
            }
            $area=trim($filter['filter']['loc'][0]);
            if(!empty($area)){
                   $this->area=$this->split_words($area);            
                   if(is_array($this->area)){
                        $keyword = implode('"|"',$this->area);
                        $keyword='"' . $keyword . '"|"'.$area.'"';
                        $keyword = ' @(area) ' . $keyword . '';
                        $keywords[]=$keyword;
                    }
                }
            if(empty($keywords)){
                $this->keyword ='';
            }else{
                $this->keyword ='('.implode(') (', $keywords).')';
            }
            
        }
        return $this->keyword;
    }//End Function

     function prepaData($filter){
        if(!empty($filter['goods_id']))
            $data['goods_id'] =  $filter['goods_id'][0];
        //商品类型
        if(!empty($filter['cat_id'][0])){
        	$cat_id = $filter['cat_id'][0];
      	    $objCat = app::get('b2c')->model('goods_cat');
        	/*$list = $objCat->get_cat_list();
        	foreach($list as $val){
                if($val['pid'] == $cat_id)
                    $filter['cat_id'][] = $val['cat_id'];
        	}*/
            if(is_array($cat_id)){
                $list = $objCat->get_allsubcat_1($cat_id);
            }else{
                $cat_ids=explode(',',$cat_id);
                $list = $objCat->get_allsubcat_1($cat_ids);
            }
            foreach($list as $val){
                    $filter['cat_id'][] = $val;
        	}
        	$data['cat_id'] =  $filter['cat_id'];
        }
        //店铺ID
        if(!empty($filter['store_id'][0]))
            $data['store_id'] =  array_values($filter['store_id']);//$filter['store_id'][0];
        
        if(!empty($filter['brand_id'][0]))
            $data['brand_id'] =  array_values($filter['brand_id']);//[0];
            //print_r($data);
        for($i=1;$i<=20;$i++){
            $p = 'p_'.$i;
            if(isset($filter[$p][0])){
                $data[$p] =  $filter[$p][0];
            }
        }
        if(!empty($filter['tag'][0]) && $filter['tag'][0]!='_ANY_')
            $data['tag_id'] =  $filter['tag'][0];
        for($i=1;$i<=10;$i++){
            $s = 's_'.$i;
            if(!empty($filter[$s][0])){
                $data['spec_id'] = $i;
                $data['spec_value_id'] = $filter[$s][0];
            }
        }
        //包邮：0，不包邮：1
        if(!empty($filter['freight_bear'][0])){
            $data['freight_bear']=array($filter['freight_bear'][0]=='business'? 0:1);
        }
        if(!empty($filter['price'][0])&&!empty($filter['price'][1])){
            $data['price'][0] = $filter['price'][0];
            $data['price'][1] = $filter['price'][1];
        }
        return $data;
    }

    function split_words($words){
        $segmentObj = search_core::segment();
        $segmentObj->pre_filter(new search_service_filter_cjk);
        $segmentObj->token_filter(new search_service_filter_lowercase);
        $segmentObj->set($words, 'utf8');
        while($row = $segmentObj->next()){
            $res[] = $row['text'];
        }
        return $res;
    }


    function reindex(&$msg){
        $msg = '无需重建索引';
        return false;
    }

    function optimize(&$msg){
        $msg = '无需优化索引';
        return false;
    }

    function status(&$msg){
        if(method_exists($this->obj, 'status')){
            $this->link();
            $res = $this->obj->status();
            if($res[1][0] == 'connections'){
                 $msg = '已建立连接';
                 return true;
            }else{
                 $msg = '连接状态异常';
                 return false;
            }
        }else{
            $msg = '服务器API接口无status方法';
            return false;
        }
    }

    function SetPropquery($queryArr=array()){
        if($queryArr['scount']){
   	        for($i=1;$i<=$queryArr['scount'];$i++){
			   if(!isset($propz['p_'.$i]))
			       $pcols['p_'.$i] = true;
		    }
            $this->pcols = $pcols;
        }
        $this->obj->setLimits(0, 50, 50);
        if(is_array($pcols)){
	        foreach($pcols as $k =>$v){
	           $this->obj->setGroupBy($k, SPH_GROUPBY_ATTR, "@count desc");
	           $this->addQuery($queryArr, 'search gruop');               
               $this->arr_query[]='GetPropquery';
	        }
        }
    }
    function GetPropquery($index,$result,&$matches){
        if(!is_array($result))return;        
        if(!isset($result[$index]))return;
        if(isset($matches['prop'])){
            $prop=$matches['prop'];
        }
        if(isset($this->pcols)){
            $k = 'p_' .$index;
            if(!$this->pcols[$k]){
                return;
            }
            $prop_matches=$result[$index];
            if(is_array($prop_matches['matches'])){
                foreach($prop_matches['matches'] AS $val){
                    $nkey = $val['attrs'][$k];
                    $prop[$index][$nkey] = $val['attrs']['@count'];
                }
            }
            $matches['prop'] = $prop;
        }
    }
    function SetBrandquery($queryArr=array()){
        $this->obj->setLimits(0, 200, 200);
	    $this->obj->setGroupBy('brand_id', SPH_GROUPBY_ATTR, "@count desc");
	    $this->addQuery($queryArr, 'search brand');
        $this->arr_query[]='GetBrandquery';
    }
    function GetBrandquery($index,$result,&$matches){
        if(!is_array($result))return;
        if(!isset($result[$index]))return;
        $brand = array();
        $brand_matches = $result[$index];
        if(is_array($brand_matches['matches'])){
            foreach($brand_matches['matches'] AS $keys => $val){
                $nbkey = $val['attrs']['brand_id'];
                if($nbkey){
                    $brand[$nbkey] = array('brand_id'=>$nbkey, '_count'=>$val['attrs']['@count']);
                }
            }
        }
        $matches['brand'] = $brand;
    }
    function SetAllStorequery($queryArr=array()){
        //$this->obj->setLimits(0, 2000, 2000);
        $this->obj->SetLimits(0,$this->max_limit, $this->max_limit );
        $sorder='';
        if($this->order){
            $sorder='orderWeight DESC,'.$this->order;
        }else{
            $sorder='myexpr DESC';
            $this->obj->setSelect("*,(@weight+dorder)*orderWeight as myexpr");
        }
        $this->obj->setGroupBy('store_id', SPH_GROUPBY_ATTR, $sorder);
        $this->addQuery($queryArr, 'search store');
        $this->arr_query[]='GetAllStorequery';
    }
    function GetAllStorequery($index,$result,&$matches){
        if(!is_array($result))return;
        if(!isset($result[$index]))return;
        $store = array();
        $store_matches = $result[$index];
        if(is_array($store_matches['matches'])){
            foreach($store_matches['matches'] AS $keys => $val){
                $nbkey = $val['attrs']['store_id'];
                if($nbkey){
                    $store[$nbkey] =$val['attrs']['@count'];
                }
            }
        }
        $matches['all_store'] = $store;
    }
    function SetStorequery($queryArr=array()){
        //$this->obj->setLimits(0, 2000, 2000);
        $this->obj->SetLimits(intval($queryArr['from']),intval($queryArr['to']), $this->max_limit );
        $sorder='';
        if($this->order){
            $sorder='orderWeight DESC,'.$this->order;
        }else{
            $sorder='myexpr DESC';
            $this->obj->setSelect("*,(@weight+dorder)*orderWeight as myexpr");
        }
        $this->obj->setGroupBy('store_id', SPH_GROUPBY_ATTR, $sorder);
        $this->addQuery($queryArr, 'search store');
        $this->arr_query[]='GetStorequery';
    }
    function GetStorequery($index,$result,&$matches){
        if(!is_array($result))return;
        if(!isset($result[$index]))return;
        $store = array();
        $store_matches = $result[$index];
        if(is_array($store_matches['matches'])){
            foreach($store_matches['matches'] AS $keys => $val){
                $nbkey = $val['attrs']['store_id'];
                if($nbkey){
                    $store[$nbkey] =$val['attrs']['@count'];
                }
            }
        }
        $matches['store'] = $store;
    }
    function SetCatquery($queryArr=array()){
        $this->obj->setLimits(0, 2000, 2000);
        $this->obj->setGroupBy('cat_id', SPH_GROUPBY_ATTR, "@count desc");
        $this->addQuery($queryArr, 'search cat');
        $this->arr_query[]='GetCatquery';
    }
    function GetCatquery($index,$result,&$matches){
        if(!is_array($result))return;
        if(!isset($result[$index]))return;
        $cat = array();
        $cat_matches = $result[$index];
        if(is_array($cat_matches['matches'])){
            foreach($cat_matches['matches'] AS $keys => $val){
                $nbkey = $val['attrs']['cat_id'];
                if($nbkey){
                    $cat[$nbkey] =$val['attrs']['@count'];
                }
            }
        }
        $matches['cat'] = $cat;
    }
    function finder_config($content){
        $config = unserialize(app::get('sphinx')->getConf('sphinx_search_goods'));
        $render = app::get('sphinx')->render();
        $render->pagedata['config'] = $config;
        return $render->fetch('config/search.html');
    }

    function finder_capability($content){
    	 $render = app::get('sphinx')->render();
    	 $render->pagedata['type'] = $content['content_name'];
         $render->pagedata['name'] = $content['content_path'];
         return $render->fetch('capability/sphinx.html');
    }

    function clear(&$msg){
        $msg = '无清空方法';
        return false;
    }
static function _print($dd){
        if(isset($_GET['debug']) && $_GET['debug']=='show'){
            echo '<pre>';
            print_r($dd);
            echo '</pre>';
        }
    }

}