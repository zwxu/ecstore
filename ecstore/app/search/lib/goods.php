<?php


class search_goods implements search_interface_model{
    var $name = '二元分词搜索';
    var $servicename = 'B2C商品';
    var $description = '基于zend_lucence开发的文本搜索引擎';
    function __construct(){
        $this->dir = ROOT_DIR.'/data/search/zend/lucene/';
        if(!is_dir($this->dir))  utils::mkdir_p($this->dir, 0777);
    }

    function create(){
        //$this->obj = Zend_Search_Lucene::create($this->dir,true);
		$this->obj = new Zend_Search_Lucene($this->dir,true);
        return $this->obj;
    }

    function link(){
        $this->obj = Zend_Search_Lucene::open($this->dir,true);
        return $this->obj;
    }

    function query($queryArr=array()){
    	 $this->obj = new Zend_Search_Lucene($this->dir);
    	 $this->from = $queryArr['from'];
         $this->to = $queryArr['to'];
         $this->order = $this->setorderby($queryArr['order']);

         unset($queryArr['from']);
         unset($queryArr['to']);
         unset($queryArr['orderby']);
         $queryArr = $this->prepaData($queryArr['filter']);//yindingsheng
         $index = new Zend_Search_Lucene($this->dir);
         $analyzerObj = new search_instance_analyzer_cjk;
         $analyzerObj->addPreFilter(new search_instance_analyzer_filter_goods);
         $analyzerObj->addPreFilter(new search_instance_analyzer_filter_cjk);
         $analyzerObj->addFilter(new search_instance_token_filter_lowercase);
         Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzerObj);

         if(isset($queryArr['title'])){
         	 //return false;
	         $segmentObj = search_core::segment();
	         $service_filter_cjk = new search_service_filter_cjk;
	         $service_filter_lowercase = new search_service_filter_lowercase;
	         if(is_object($service_filter_cjk)&&is_object($service_filter_lowercase)){
	             $segmentObj->pre_filter($service_filter_cjk);
	             $segmentObj->token_filter($service_filter_lowercase);
	             if(isset($queryArr['title'])){
	                 $segmentObj->set($queryArr['title'], 'utf8');
	                 while($row = $segmentObj->next()){
	                    $title[] = $row['text'];
	                 }
	                 $queryArr['title'] = @join(" ", $title);
	             }
	         }
         }

         if(is_array($queryArr['cat_id'])){
              $cat_id = '('.implode(' OR ',$queryArr['cat_id']).')';
              unset($queryArr['cat_id']);
         }
         //store_id 
         if(is_array($queryArr['store_id'])){
              $store_id = '('.implode(' OR ',$queryArr['store_id']).')';
              unset($queryArr['store_id']);
              
         }
         //brand_id 
         if(is_array($queryArr['brand_id'])){
              $brand_id = '('.implode(' OR ',$queryArr['brand_id']).')';
              unset($queryArr['brand_id']);
             
         }

         if(is_array($queryArr)){
             foreach($queryArr as $k=>$v){
                    $query[] = $k.':'.$v;
             } 
             if(!empty($cat_id)){
                $query[] .= $cat_id;
             }
             if(!empty($store_id)){
                $query[] .= $store_id;
             }
             if(!empty($brand_id)){
                $query[] .= $brand_id;
             }
              
         }
         if(is_array($query))
             $this->query = implode(' AND ',$query);
         
			return true;

    }

    function commit(){
        if(isset($this->query)){
            if(is_array($this->order))
                $result = $this->obj->find($this->query,$this->order['order_name'],$this->order['order_type'],$this->order['order_by']);
            else
                $result = $this->obj->find($this->query);
        }

        $rfilter['goods_id'] =array();
        if(!empty($result)){
            foreach($result AS $obj){
                    $document = $obj->getDocument($obj->id);
                    $goodsId = $document->getFieldValue('goods_id');
                    if(!in_array($goodsId,$rfilter['goods_id'])&&!empty($goodsId))
                        array_push($rfilter['goods_id'],$document->getFieldValue('goods_id'));

            }
        }
        $rfilter['total'] = count($rfilter['goods_id']);

 

        if(isset($this->from)&&isset($this->to)){
            $rfilter['result'] = array_slice($rfilter['goods_id'],$this->from,$this->to);
        }
        return $rfilter;
    }

    function insert($val=array()){
         $this->link();
         $index = new Zend_Search_Lucene($this->dir);
         $analyzerObj = new search_instance_analyzer_cjk;
         $analyzerObj->addPreFilter(new search_instance_analyzer_filter_goods);
         $analyzerObj->addPreFilter(new search_instance_analyzer_filter_cjk);
         $analyzerObj->addFilter(new search_instance_token_filter_lowercase);

         Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzerObj);
         $doc = new Zend_Search_Lucene_Document();

         $doc->addField(Zend_Search_Lucene_Field::Text('goods_id',$val['goods_id']));
         if(isset($val['product'][0]['price']['price']['price'])){
             $pric = $val['product'][0]['price']['price']['price'];
         }else{
             if(is_array($val['product'])){
                foreach($val['product'] as $kp=>$vp){
                     $pric = $vp['price']['price']['price'];
                }
             }
         }
         $doc->addField(Zend_Search_Lucene_Field::UnStored('cat_id', $val['category']['cat_id']));
         $doc->addField(Zend_Search_Lucene_Field::UnStored('store_id',$val['store_id']));
         $doc->addField(Zend_Search_Lucene_Field::UnStored('brand_id',$val['brand']['brand_id']));
         $doc->addField(Zend_Search_Lucene_Field::UnIndexed('last_modify',time()));
         $doc->addField(Zend_Search_Lucene_Field::UnIndexed('price',$this->priceChange($pric)));
         $doc->addField(Zend_Search_Lucene_Field::UnStored('marketable','true'));
         if(isset($val['props'])){
             for($i=1;$i<=28;$i++){
                $p = 'p_'.$i;
                $doc->addField(Zend_Search_Lucene_Field::UnStored($p,$val['props'][$p]['value']));
             }
         }
         if(is_array($val['keywords'])){
             foreach($val['keywords'] as $k=>$v){
                 $keyword.= '#'.$v['keyword'].'@';
             }
         }

         if(is_array($val['product'])){
             foreach($val['product'] as $k=>$v){
                 if(is_array($v['spec_desc']['spec_value_id'])){
                     foreach($v['spec_desc']['spec_value_id'] as $key=>$vals){
                            $spec.= '#'.$key.$vals.'@';
                     }
                 }
                 $bn.= '#'.$v['bn'].'@';
             }
         }

        $name = '#'.$val['name'].'@';

        $doc->addField(Zend_Search_Lucene_Field::UnStored('title',$name,'utf8'));
        $doc->addField(Zend_Search_Lucene_Field::UnStored('keyword',$keyword));
        $doc->addField(Zend_Search_Lucene_Field::UnStored('spec',$spec));
        $doc->addField(Zend_Search_Lucene_Field::UnStored('bn','#'.$val['bn'].'@'.$bn));
        $index->addDocument($doc);
        return $index->commit();



    }
    /*
     * 字母形式才能比较价格大小
     * */
    function priceChange($price){
        $price = strval(intval($price));
        if(strlen($price)<8){
            for ($i=1;$i<=8-strlen($price);$i++){
                $tmpstr .= "0";
            }
        }
        $result = $tmpstr.$price;
        $word = array('a','b','c','d','e','f','g','h','i','j');
        $num = array('0','1','2','3','4','5','6','7','8','9');
        $result = str_replace($num,$word,$result);
        return $result;
    }



    function update($val=array()){
         $this->link();
         $data= array();
         if(isset($val['goods_id'])){
             $this->insert($val);
             $res = $this->commit();
             if(isset($res)){
                 foreach($res AS $obj){
                     if(isset($obj->id))
                        $this->obj->delete($obj->id);
                 }
             }
         }
         return $this->insert($val);
   }

    function delete($val=array()){
         $this->link();
         $data= array();
         if(isset($val['goods_id'])){
             $data['goods_id'] = $val['goods_id'];
             $this->query($data);
             $res = $this->commit();
         }
         if(isset($res)){
             foreach($res AS $obj){
                    return $this->obj->delete($obj->id);
             }
         }

    }

    function prepaData($filter){
        if(isset($filter['from']))
            $data['from'] =  $filter['from'];
        if(isset($filter['to']))
            $data['to'] =  $filter['to'];
        if(isset($filter['orderby']))
            $data['orderby'] =  $filter['orderby'];
        if(isset($filter['last_modify']))
            $data['last_modify'] =  $filter['last_modify'];

        if(!empty($filter['goods_id']))
            $data['goods_id'] =  $filter['goods_id'][0];
        if(!empty($filter['name'])){
            $data['title'] =  '#'.$filter['name'][0].'@';
        }
        if(!empty($filter['cat_id'][0])){
        	$cat_id = $filter['cat_id'][0];
        	$filter['cat_id'][0] = 'cat_id:'.$filter['cat_id'][0];
      	    $objCat = app::get('b2c')->model('goods_cat');
        	$list = $objCat->get_cat_list();
        	foreach($list as $val){
                if($val['pid'] == $cat_id)
                    $filter['cat_id'][] = 'cat_id:'.$val['cat_id'];
        	}
        	$data['cat_id'] =  $filter['cat_id'];
        }

       
        if(!empty($filter['store_id'][0])){
            $store=array();
            foreach($filter['store_id'] as $sv){
                $store[] = 'store_id:'.$sv;
            }
            $data['store_id'] =  $store;
        }


        if(!empty($filter['bn'][0]))
            $data['bn'] =  '#'.$filter['bn'][0].'@';

        if(!empty($filter['brand_id'][0])){
            //$data['brand_id'] =  $filter['brand_id'][0];
            $brand=array();
            foreach($filter['brand_id'] as $sv){
                $store[] = 'brand_id:'.$sv;
            }
            $data['brand_id'] =  $brand;
        }
            

        for($i=1;$i<=28;$i++){
            $p = 'p_'.$i;
            if(isset($filter[$p][0])){
                $data[$p] =  $filter[$p][0];
            }
        }
        for($i=1;$i<=10;$i++){
            $s = 's_'.$i;
            if(!empty($filter[$s][0])){
                $data['spec'] =  '#'.$i.$filter[$s][0].'@';
            }
        }

        if(!empty($filter['price'][0])&&!empty($filter['price'][1])){
            $minPrice =  $this->priceChange($filter['price'][0]);
            $maxPrice =  $this->priceChange($filter['price'][1]);
            $data['price'] = '{'.$minPrice.' TO '.$maxPrice.'}';
        }

        return $data;


    }

    function reindex(&$msg){       // 重建索引
         $index = $this->create();
         $analyzerObj = new search_instance_analyzer_cjk;
         $analyzerObj->addPreFilter(new search_instance_analyzer_filter_goods);
         $analyzerObj->addPreFilter(new search_instance_analyzer_filter_cjk);
         $analyzerObj->addFilter(new search_instance_token_filter_lowercase);
         Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzerObj);
         $doc = new Zend_Search_Lucene_Document();
         $temp_cat_name=array();//不用每次循环都要去执行数据库。
         $mdl_goods=app::get('b2c')->model('goods');

         $count=$mdl_goods->count();
         $limit=1000;//每1000条循环一次
         $offset=0;
         do{
            $data = $mdl_goods->getlist('*',array(),$offset,$limit,'goods_id');
            
            //$data = $mdl_goods->getlist('*',array(),0,-1);
             foreach($data as $key=>$val){
                 $doc->addField(Zend_Search_Lucene_Field::Text('goods_id',$val['goods_id']));
                 $doc->addField(Zend_Search_Lucene_Field::UnStored('cat_id', $val['cat_id']));
                 $doc->addField(Zend_Search_Lucene_Field::UnStored('brand_id',$val['brand_id']));
                 $doc->addField(Zend_Search_Lucene_Field::UnStored('price',$this->priceChange($val['price'])));
                 $doc->addField(Zend_Search_Lucene_Field::UnStored('marketable',$val['marketable']));
                 $doc->addField(Zend_Search_Lucene_Field::UnStored('store_id',$val['store_id']));
                 $doc->addField(Zend_Search_Lucene_Field::UnIndexed('last_modify',$val['last_modify']));
                 for($i=1;$i<=28;$i++){
                    $p = 'p_'.$i;
                    $doc->addField(Zend_Search_Lucene_Field::UnStored($p,$val[$p]));
                 }
                 //不用每次都去执行数据库查询
                 //$cat_name = app::get('b2c')->model('goods_cat')->dump(array('cat_id'=>$val['cat_id']),'cat_name');//yindingsheng
                 if(isset($temp_cat_name[$val['cat_id']])&& $temp_cat_name[$val['cat_id']]){
                    $cat_name=$temp_cat_name[$val['cat_id']];
                 }else{
                    $cat_name = app::get('b2c')->model('goods_cat')->dump(array('cat_id'=>$val['cat_id']),'cat_name');//yindingsheng
                    $temp_cat_name[$val['cat_id']]=$cat_name;
                 }

    /*	         foreach($val['keywords'] as $k=>$v){
                     $keyword.= '#'.$v['keyword'].'@';

                 }*/

                 if(is_array($val['spec_desc'])){
                      foreach($val['spec_desc'] as $k=>$v){
                          foreach($v as $key=>$vals){
                              $spec.= '#'.$k.$vals['spec_value_id'].'@';
                          }
                      }
                 }

                $name = '#'.$val['name'].' '.$cat_name['cat_name'].'@';//yindingsheng 加入分类名称

                $doc->addField(Zend_Search_Lucene_Field::UnStored('title', $name,'utf-8'));//yindingsheng
                //$doc->addField(Zend_Search_Lucene_Field::UnStored('keyword',$keyword));
                $doc->addField(Zend_Search_Lucene_Field::UnStored('spec',$spec));
                $doc->addField(Zend_Search_Lucene_Field::UnStored('bn','#'.$val['bn'].'@'));
                unset($p);
                unset($spec);
                $index->addDocument($doc);
            }
            $offset+=$limit;
        }while($offset<$count);
        $msg = '重建索引成功';
        return true;
    }

    function setorderby($order){
    	switch($order){
    		case 'last_modify desc':
    		     return array(
    		         'order_name'=>'last_modify',
    		         'order_type'=>SORT_NUMERIC,
    		         'order_by'=>SORT_DESC,
    		     );
    		break;
    	    case 'last_modify':
    		     return array(
    		         'order_name'=>'last_modify',
    		         'order_type'=>SORT_NUMERIC,
    		         'order_by'=>SORT_ASC,
    		     );
    		break;
    		case 'price desc':
    		     return array(
    		         'order_name'=>'price',
    		         'order_type'=>SORT_STRING,
    		         'order_by'=>SORT_DESC,
    		     );
    		break;
    	    case 'price':
    		     return array(
    		         'order_name'=>'price',
    		         'order_type'=>SORT_STRING,
    		         'order_by'=>SORT_ASC,
    		     );
    		break;
    		default:
                  return array(
    		         'order_name'=>'goods_id',
    		         'order_type'=>SORT_NUMERIC,
    		         'order_by'=>SORT_DESC,
    		     );
            break;
    	}
    }

    function optimize(&$msg){
        if(file_exists($this->dir.'/segments.gen')){
            $this->link()->optimize();
             $msg = '优化成功';
             return true;
        }else{
            $msg = '当前服务器没有索引文件';
            return false;
        }
    }

    function finder_capability($content){
    	 $render = app::get('search')->render();
    	 $render->pagedata['type'] = $content['content_name'];
         $render->pagedata['name'] = $content['content_path'];
         return $render->fetch('capability/goods.html');
    }

    function status(&$msg){
        if(file_exists($this->dir.'/segments.gen')){
            if(is_object($this->link())){
                 $msg = '已建立连接';
                 return true;
            }else{
                 $msg = '连接状态异常';
                 return false;
            }
        }else{
            $msg = '当前服务器没有索引文件';
            return false;
        }
    }

    function clear(&$msg){
        $msg = '无清空方法';
        return false;
    }
}
