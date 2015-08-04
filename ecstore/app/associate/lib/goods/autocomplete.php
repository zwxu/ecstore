<?php
class associate_goods_autocomplete{
	
	public function __construct($app)
	{
		$this->app = $app;
        $this->db=kernel::database();
	}
	
    public function get_data($key,$cols){        
        $key=$this->normalize(trim($key));
        $key=trim($key);
        if(!$key) return null;
        
        
        //判断是否启用索引查询
        if(app::get('base')->getConf('server.search_server.search_goods')){
           $searchApp = search_core::instance('search_goods');
           if(is_object($searchApp)){
            $queryRes = $searchApp->auto_complete($key);
            if(empty($queryRes)){
              return null;
            }
            $this->saveResult($queryRes);
            $sresult=$this->searchResult($key);
            return $sresult;
           }
        }
        
        $segmentObj = search_core::segment();
        if($segmentObj){
            $segmentObj->pre_filter(new search_service_filter_cjk);
            $segmentObj->token_filter(new search_service_filter_lowercase);
            $segmentObj->set($key, 'utf8');
            while($row = $segmentObj->next()){
                $keys[] = $row['text'];
            }
        }else{
            //$key=$this->normalize(trim($key));
            if(!$key) return null;
            $keys=explode(' ',$key);
        }
        //$filter['marketable'] = 'true';
        //|head|bn|brief
        //$filter['name|head'] = $key;
        //名字
        $filter['bn'] = $keys;
        
        $filter['name'] = $keys;
        //简介
        $filter['brief'] = $keys;
        //关键字
        $filter['keyword'] = $keys;
        //目的
        $filter['purpose'] = $keys;
        $result =$this->getNameAndID($filter,$key);
        return $result;
    }
	
	public function get_widgets_top_html()
	{
		$render = kernel::single('base_render');
		return $render->fetch('widgets/search/top.html', $this->app->app_id);
	}
	
	public function get_widgets_bottom_html()
	{
		$render = kernel::single('base_render');
		return $render->fetch('widgets/search/bottom.html', $this->app->app_id);
	}
	
     /**
     * 初始化商品过滤规
     *
     * @param array $aGoodsId // array(xxx,xxx,xxx);
     */
    private function getNameAndID($filter = array(),$key='') {      
        $sSql = "SELECT count(distinct a.goods_id) as _count from sdb_b2c_goods a 
                 left join sdb_b2c_goods_keywords  b on a.goods_id=b.goods_id
                 left join (select d.type_id,f.name from  sdb_b2c_goods_type d  
                 inner join sdb_b2c_goods_type_props e on d.type_id =e.type_id 
                inner join sdb_b2c_goods_type_props_value f on e.props_id = f.props_id) c on a.type_id = c.type_id
                 WHERE 1=1 AND a.marketable='true' and a.goods_type='normal' and ".$this->_filter_sql($filter);//."
         $aResult = $this->db->selectrow($sSql,true);
        $result=array('keyword'=>$key,'_count'=>$aResult[0]['_count']);
        $this->saveResult($result);
        return $this->searchResult($key);
    }
    /**
     *保存搜索结果
     */
    private function saveResult($aResult){
         if($aResult['_count']&&$aResult['keyword']{3}){
           if(intval($aResult['_count'])<=0)return;
            $md5_key=md5($aResult['keyword']);
            $keyResult=$this->db->selectrow("select * from sdb_associate_associate where md5_key='".$md5_key."'");
            if(empty($keyResult)){
                $sdf=array('kd'=>$aResult['keyword'],'search_rate'=>'1','counts'=>intval($aResult['_count']),'md5_key'=>$md5_key);
                $strValue ="'". implode("','",$sdf)."'";
                $strFields = implode('`,`',array_keys($sdf));
                $sql = 'INSERT INTO `sdb_associate_associate` ( `'.$strFields.'` ) VALUES ( '.$strValue.' )';
            }else{
                $sdf=$keyResult;
                $sdf['search_rate']=intval($sdf['search_rate'])+1;
                $sdf['counts']=intval($aResult['_count']);
                $sql="UPDATE sdb_associate_associate SET counts=".intval($aResult['_count']).",search_rate=search_rate+1 where md5_key='".$md5_key."'";
            } 
            $this->db->exec($sql);            
            //$this->mdl_associate->save($sdf);
        }    
    }
    private function searchResult($key){
        $sksql="select distinct kd as name ,counts as goods_id, case when kd  ='{$key}' then 1 else 0 end as norder  from sdb_associate_associate where search_rate>=1 ";
        $keys=explode(' ',$key);
        foreach($keys as $k=>$v){
                 $where[] = 'kd LIKE \'%'.trim($v).'%\'';
        }
        $sksql.=' and ('.implode(" OR ",$where).')';
        $sksql.=" order by norder DESC ,search_rate DESC LIMIT 0,10";
        //print_r($sksql);
        $res= $this->db->select($sksql);
        if(empty($res)) return false;
        return $res;
    }
    /**
     * sql过滤的where条件
     */
    private function _filter_sql($filter) {
        
       //$where[] =array();
        //商品编号
        $subfilter=array();
        if(isset($filter['bn']) && $filter['bn']){
            foreach($filter['bn'] as $k=>$v){
                 $subfilter[] = 'a.bn LIKE \'%'.$v.'%\'';
            }
            $where[]=" (".implode($subfilter,' and ').") ";
            //$where[] = 'and goods_id < 1000';
            unset($filter['bn']);
        }
        //商品名称
        $subfilter=array();
        if(isset($filter['name']) && $filter['name']){
            foreach($filter['name'] as $k=>$v){
                $subfilter[] = 'a.name LIKE \'%'.$v.'%\'';
                }
            $where[]=" (".implode($subfilter,' and ').") ";
            $filter['name'] = null;
         }
           
        //商品简介
        $subfilter=array();
       if(isset($filter['brief']) && $filter['brief']){
            foreach($filter['brief'] as $k=>$v){
                $subfilter[] = 'brief LIKE \'%'.$v.'%\'';
            }
            $where[]=" (".implode($subfilter,' and ').") ";
            unset($filter['brief']);
        }
        $subfilter=array();
        //关键字
        if(isset($filter['keyword']) && $filter['keyword']){
            foreach($filter['keyword'] as $k=>$v){
                $subfilter[] = 'b.keyword LIKE \'%'.$v.'%\'';
            }
            $where[]=" (".implode($subfilter,' and ').") ";
            unset($filter['keyword']);
        }
        //目的
        $subfilter=array();
        if(isset($filter['purpose']) && $filter['purpose']){
            foreach($filter['purpose'] as $k=>$v){
                $subfilter[] = 'c.name LIKE \'%'.$v.'%\'';
            }
            $where[]=" (".implode($subfilter,' and ').") ";
            unset($filter['purpose']);
        }
        return "(".implode($where,' or ').")";
    }
    public function normalize($input) 
    {
        $search = array(",", "/", "\\", ".", ";", ":", "\"", "!", 
                        "~", "`", "^", "(", ")", "?","？", "-", "\t", "\n", "'", 
                        "<", ">", "\r", "\r\n", "$", "&", "%", "#", 
                        "@", "+", "=", "{", "}", "[", "]", "：", "）", "（", 
                        "．", "。", "，", "！", "；", "“", "”", "‘", "’", "［", "］", 
                        "、", "—", "　", "《", "》", "－", "…", "【", "】",
        );
        return preg_replace('/\s(?=\s)/', '', str_replace($search, ' ', $input));
    }
}
