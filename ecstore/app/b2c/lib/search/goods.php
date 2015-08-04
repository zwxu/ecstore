<?php


class b2c_search_goods implements search_interface_model{
    var $name = '二元分词搜索';
    function __construct(){
        $this->dir = ROOT_DIR.'/data/search/zend/lucene/';
        if(!is_dir($this->dir))  utils::mkdir_p($this->dir, 0777, true);

    }

    function create(){
        $this->obj = Zend_Search_Lucene::create($this->dir,true);
        return $this->obj;
    }

    function link(){
        $this->obj = Zend_Search_Lucene::open($this->dir,true);
        return $this->obj;
    }

    function query($queryArr=array()){
         $queryArr = $this->prepaData($queryArr);
         $index = new Zend_Search_Lucene($this->dir);
         $analyzerObj = new search_instance_analyzer_cjk;
         $analyzerObj->addPreFilter(new search_instance_analyzer_filter_goods);
         $analyzerObj->addPreFilter(new search_instance_analyzer_filter_cjk);
         $analyzerObj->addFilter(new search_instance_token_filter_lowercase);
         Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzerObj);

         $segmentObj = search_core::segment();
         $service_filter_cjk = new search_service_filter_cjk;
         $service_filter_lowercase = new search_service_filter_lowercase;
         if(is_object($service_filter_cjk)&&is_object($service_filter_lowercase)){
             $segmentObj->pre_filter($service_filter_cjk);
             $segmentObj->token_filter($service_filter_lowercase);
/*             if(isset($queryArr['title'])){
                 $segmentObj->set($queryArr['title'], 'utf8');
                 while($row = $segmentObj->next()){
                    $title[] = $row['text'];
                 }
                 $queryArr['title'] = @join(" ", $title);
             }*/
         }


         $this->from = $queryArr['from'];
         $this->to = $queryArr['to'];
         $this->order = $queryArr['orderby'];
         unset($queryArr['from']);
         unset($queryArr['to']);
         unset($queryArr['orderby']);
         if(is_array($queryArr)){
             foreach($queryArr as $k=>$v){
                    $query[] = $k.':'.$v;
             }
         }
         if(is_array($query))
             $this->query = implode(' AND ',$query);

    }

    function commit(){
        if(isset($this->query))
            $result = $this->obj->find($this->query);
        $rfilter['goods_id'] =array();
        if(!empty($result)){
            foreach($result AS $obj){
                    $document = $obj->getDocument($obj->id);
                    $goodsId = $document->getFieldValue('goods_id');
                    if(!in_array($goodsId,$rfilter['goods_id'])&&!empty($goodsId))
                        array_push($rfilter['goods_id'],$document->getFieldValue('goods_id'));

            }
        }

        if(isset($this->from)&&isset($this->to)){
            $rfilter['goods_id'] = array_slice($rfilter['goods_id'],$this->from,$this->to);
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
         $doc->addField(Zend_Search_Lucene_Field::UnStored('brand_id',$val['brand']['brand_id']));
         $doc->addField(Zend_Search_Lucene_Field::UnStored('price',$this->priceChange($pric)));
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
        //$name = '#'.$val['name'].'@'.$keyword.'#'.$val['bn'].'@'.$bn;
        $name = '#'.$val['name'].'@';

        $doc->addField(Zend_Search_Lucene_Field::UnStored('title', $name));
        $doc->addField(Zend_Search_Lucene_Field::UnStored('keyword',$keyword));
        $doc->addField(Zend_Search_Lucene_Field::UnStored('spec',$spec));
        $doc->addField(Zend_Search_Lucene_Field::UnStored('bn','#'.$val['bn'].'@'.$bn));
        $index->addDocument($doc);
        return $index->commit();



    }
    /*
     * 变成字母形式才能比较价格大小
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

        if(!empty($filter['goods_id']))
            $data['goods_id'] =  $filter['goods_id'][0];
        if(!empty($filter['name'])){
            $data['title'] =  '#'.$filter['name'][0].'@';
        }
        if(!empty($filter['cat_id'][0]))
            $data['cat_id'] =  $filter['cat_id'][0];
        if(!empty($filter['bn'][0]))
            $data['bn'] =  '#'.$filter['bn'][0].'@';
        if(!empty($filter['brand_id'][0]))
            $data['brand_id'] =  $filter['brand_id'][0];
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
            $minPrice =  kernel::single('b2c_search_goods')->priceChange($filter['price'][0]);
            $maxPrice =  kernel::single('b2c_search_goods')->priceChange($filter['price'][1]);
            $data['price'] = '{'.$minPrice.' TO '.$maxPrice.'}';
        }

        return $data;


    }

    function reindex(){       //todo 重建索引
         $this->link();
         $index = new Zend_Search_Lucene($this->dir);
         $analyzerObj = new search_instance_analyzer_cjk;
         $analyzerObj->addPreFilter(new search_instance_analyzer_filter_goods);
         $analyzerObj->addPreFilter(new search_instance_analyzer_filter_cjk);
         $analyzerObj->addFilter(new search_instance_token_filter_lowercase);
         return true;
         Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzerObj);
         $doc = new Zend_Search_Lucene_Document();

         $data = app::get('b2c')->model('goods')->getlist('*',array());

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

/*         $doc->addField(Zend_Search_Lucene_Field::UnStored('cat_id', $val['category']['cat_id']));
         $doc->addField(Zend_Search_Lucene_Field::UnStored('brand_id',$val['brand']['brand_id']));
         $doc->addField(Zend_Search_Lucene_Field::UnStored('price',$this->priceChange($pric)));
         $doc->addField(Zend_Search_Lucene_Field::UnStored('marketable','true'));
         if(isset($val['props'])){
             for($i=1;$i<=28;$i++){
                $p = 'p_'.$i;
                $doc->addField(Zend_Search_Lucene_Field::UnStored($p,$val['props'][$p]['value']));
             }
         }

         foreach($val['keywords'] as $k=>$v){
             $keyword.= '#'.$v['keyword'].'@';

         }
         foreach($val['product'] as $k=>$v){
             if(is_array($v['spec_desc']['spec_value_id'])){
                 foreach($v['spec_desc']['spec_value_id'] as $key=>$vals){
                        $spec.= '#'.$key.$vals.'@';
                 }
             }

             $bn.= '#'.$v['bn'].'@';
         }
        $name = '#'.$val['name'].'@'.$keyword.'#'.$val['bn'].'@'.$bn;

        $doc->addField(Zend_Search_Lucene_Field::UnStored('title', $name));
        $doc->addField(Zend_Search_Lucene_Field::UnStored('keyword',$keyword));
        $doc->addField(Zend_Search_Lucene_Field::UnStored('spec',$spec));
        $doc->addField(Zend_Search_Lucene_Field::UnStored('bn','#'.$val['bn'].'@'.$bn));
         $index->addDocument($doc);
         return $index->commit();
*/

    }

    function optimize(){
        return true;

    }

    function status(){
        return true;
    }

    function clear(){
       $this->create();
    }
}
?>
