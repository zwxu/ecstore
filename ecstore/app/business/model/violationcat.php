<?php
class business_mdl_violationcat extends dbeav_model{

	/**
	 * 构造方法
	 * @param object model相应app的对象
	 * @return null
	 */
    public function __construct($app){
        parent::__construct($app);
        $this->use_meta();
    }

	/**
	 * 保存的方法
	 * @param mixed 保存的数据内容
	 * @return boolean
	 */
    public function save(&$aData){
        $path=array();
        $parent_id = $aData['parent_id'];
    	while($parent_id){
            if($aData['cat_id'] && $aData['cat_id'] == $parent_id){
                return false;
                break;
            }
            array_unshift($path, $parent_id);
            $row = $this->dump(array('cat_id'=>$parent_id),'parent_id, cat_path, p_order,score');
            $parent_id = $row['parent_id'];
        }
        $aData['cat_path'] = $this->getCatPath($aData['parent_id']);
        if($aData['parent_id']!=0){
            $row = parent::dump($aData['parent_id']);
            $data['child_count'] = $row['child_count']+1;
            $data['cat_id'] = $aData['parent_id'];
            parent::save($data);
        }
        parent::save($aData);
        return $this->cat2json();
    }

	/**
	 * 得到整个分类树形结构
	 * @param null
	 * @return mixed 返回的数据
	 */
    public function getTree(){
        return $this->db->select('SELECT o.cat_name AS text,o.cat_id AS id,o.parent_id AS pid,o.p_order,o.cat_path,
                    o.type_id as type,o.child_count,o.score   FROM sdb_business_violationcat o
                    ORDER BY o.p_order,o.cat_id');
    }

	/**
	 * 注册商品分类的meta
	 * @param null
	 * @return null
	 */
    public function cat_meta_register(){
        $col = array(
            'seo_info' => array(
                  'type' => 'serialize',
                  'label' => app::get('b2c')->_('seo设置'),
                  'width' => 110,
                  'editable' => false,
             ),
        );
        $this->meta_register($col);
    }

	/**
	 * 通过上一级分类id得到下一级分类的数据
	 * @param int parent_cat_id
	 * @param string link view
	 * @return mixed 返回结果数据
	 */
    public function getCatParentById($id,$view='index'){
        if(!$id) return false;
            if(is_array($id)){
                if(implode($id,' , ')==='') return false;
				$result = $this->getList('cat_id,cat_name,score',array('parent_id|in'=>$id),0,-1,'p_order,cat_id ');
            }else{
				$result = $this->getList('cat_id,cat_name,score',array('parent_id'=>$id),0,-1,'p_order,cat_id ');
            }

            $default_view=$view?$view:$this->app->getConf('gallery.default_view');
            foreach($result as $cat_key=>$cat_value){
                $result[$cat_key]['link'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_gallery','args'=>array($cat_value['cat_id'],$default_view) ));
            }
            return $result;
     }

	/**
	 * 得到分类的树形结构图
	 * @param string depth
	 * @param int cat_id
	 * @return mixed 结果数据
	 */
    public function getMap($depth=-1,$cat_id=0){
        $var_depth = $depth;
        $var_cat_id = $cat_id;
        if(isset($this->catMap[$var_depth][$var_cat_id])){
            return $this->catMap[$var_depth][$var_cat_id];
        }
        if($cat_id>0){
			$row = $this->getList('cat_path',array('cat_id'=>intval($cat_id)));
            if($depth>0){
                $depth += substr_count($row['cat_path'],',');
            }
			$rows = $this->getList('cat_name,cat_id,parent_id,is_leaf,cat_path,type_id,score',array('cat_path|head'=>$row['cat_path'].$cat_id),0,-1,'cat_path,p_order ASC');
        }else{
			$rows = $this->getList('cat_name,cat_id,parent_id,is_leaf,cat_path,type_id,score',array(),0,-1,'p_order ASC');
        }
        $cats = array();
        $ret = array();
        foreach($rows as $k=>$row){
            if($depth<0 || substr_count($row['cat_path'],',') < $depth){
                $cats[$row['cat_id']] = array('type'=>'gcat','parent_id'=>$row['parent_id'],'title'=>$row['cat_name'],'link'=>kernel::mkUrl('gallery','index',array($row['cat_id'])));
            }
        }
        foreach($cats as $cid=>$cat){
            if($cat['parent_id'] == $cat_id){
                $ret[] = &$cats[$cid];
            }else{
                $cats[$cat['parent_id']]['items'][] = &$cats[$cid];
            }
        }
        $this->catMap[$var_depth][$var_cat_id] = $ret;
        return $ret;
    }

    function getMapTree($ss=0, $str='└'){
        $var_ss = $ss;
        $var_str = $str;
        if(isset($this->catMapTree[$var_ss][$var_str])){
            return $this->catMapTree[$var_ss][$var_str];
        }
        $retCat = $this->map($this->getTree(),$ss,$str,$no,$num);
        $this->catMapTree[$var_ss][$var_str] = $retCat;
        global $step,$cat;
        $step = '';
        $cat = array();
        return $retCat;
    }

	/**
	 * 得到当前的路径
	 * @param string cat id
	 * @param string 方法名称
	 * @return mixed 路径数据
	 */
    public function getPath($catId,$method=null){
        $cat_id['cat_id'] = $catId;
		if (!$cat_id['cat_id']) return array();

        $list_row = $this->getList("cat_path,cat_name",array('cat_id'=>$catId));
        $row = $list_row[0];
        $ret = array(array('type'=>'goodsCat','title'=>$row['cat_name'],'link'=>app::get('site')->router()->gen_url(array('app'=>'b2c', 'ctl'=>'site_gallery','act'=>'index','args'=>array($cat_id['cat_id']) ))));
        if($row['cat_path'] != ',' && $row['cat_path']){
			$rows = $this->getList('cat_name,cat_id',array('cat_id|in'=>explode(',',substr(substr($row['cat_path'],0,-1),1))),0,-1,'cat_path DESC');
            foreach($rows as $row){
                array_unshift($ret,array('type'=>'goodsCat','title'=>$row['cat_name'],'link'=>app::get('site')->router()->gen_url(array('app'=>'b2c', 'ctl'=>'site_gallery','act'=>'index','args'=>array($row['cat_id']) ))   ));
            }
        }
        array_unshift($ret,array('type'=>'goodsCat','title'=>app::get('site')->_('首页'),'link'=>kernel::base_url(1)  ));

        return $ret;
    }


    function map($data,$sID=0,$preStr='',&$cat_cuttent,&$step){
    	set_time_limit(2000);
        $step++;
        $default_view=$this->app->getConf('gallery.default_view');
        if($data){

            $tmpCat = array();
            foreach($data as $i=>$value){

                $count = substr_count( $data[$i]['cat_path'],',' );
                $id=$data[$i]['id'];
                $cls=($data[$i]['child_count']?'true':'false');

                //$link=app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_gallery','args'=>array($id) ));

                $tmpCat[$value['pid']][] =array(
                            'cat_name'=>$data[$i]['text'],
                            'cat_id'=>$data[$i]['id'],
                            'pid'=>$data[$i]['pid'],
                            'type'=>$data[$i]['type'],
                            'type_name'=>$data[$i]['type_name'],
                            'step'=> $count?$count:1,
                            'p_order'=>$data[$i]['p_order'],
                            'cat_path'=>$data[$i]['cat_path'],
                            'cls'=>$cls,
                            'score'=>$data[$i]['score'],
                            'url'=>$link
                        );
            }
            $this->_map( $cat_cuttent,$tmpCat,0 );
        }
        $step--;
        return $cat_cuttent;
    }

    function _map( &$cat_cuttent,$data,$key ){
    	if(is_array($data[$key])){
	        foreach( $data[$key] as $k => $v ){
	            $cat_cuttent[] = $v;
	            if( $data[$v['cat_id']] )
	                $this->_map( $cat_cuttent,$data, $v['cat_id']);
	        }
    	}
    }

    function checkTreeSize(){
		$aCount = $this->count();
        if($aCount > 100){
            return false;
        }else{
            return true;
        }
    }

    function get_cat_depth(){
		$row = $this->getList('cat_path',array(),0,1,'cat_path DESC');
        return count(explode(',',$row[0]['cat_path']));
    }

    function cat2json($return=false){
        $contents=$this->getMapTree(0,'');  

        base_kvstore::instance('business_store')->store('violationcat.data',$contents);

        if($return){
            return $contents;
        }else{
            return true;
        }
    }

    function getCatPath($parent_id){
        if($parent_id == 0){
            return ',';
        }
        $cat_sdf = $this->dump($parent_id);
        return $cat_sdf['cat_path'].$cat_sdf['cat_id'].",";
    }

    function getTypeList(){
		$obj_goods_type = $this->app->model('goods_type');
		return $obj_goods_type->getList('type_id,name',array('disabled'=>'false'));
    }
    function propsort($prop=array()){
        if (is_array($prop)){
            foreach($prop as $key => $val){
                $tmpP[$val['ordernum']]=$key;
            }
            ksort($tmpP);
            return $tmpP;
        }
    }



     /*根据查询字符串返回UNMAE 数组
     */
	public function getCatLikeStr($str){

         if(!$str||$str !=''){
			$filter = array(
			'cat_name|head'=>$str,
			'disabled'=>'false',
			);
         }else if($str == '_ALL_'){
			$filter = array('disabled'=>'false');
         }
		$_data = $this->getList('cat_id,cat_name',$filter);

        foreach($_data as $d){
            $result[] = $d['cat_name'].'&nbsp;'.$d['cat_id'];
        }

        return json_encode($result);
     }

    function get_cat_list($show_stable=false){

        //return $this->cat2json(true);


  
        if( base_kvstore::instance('business_store')->fetch('violationcat.data', $contents) !== false ){
            if(is_array($contents)) {
                $result=$contents;
            } else {
                $result=json_decode($contents,true);

            }
            if($result){
                if($show_stable){
                    foreach($result as $key=>$value){
                        if($result[$key]['step']>1){
                            $result[$key]['cat_name']=str_repeat(' ',($result[$key]['step']-1)*2).'└'.$result[$key]['cat_name'];
                        }
                     }
                }

                return $result;
            }else{
                return $this->cat2json(true);
            }

        }else{
            return $this->cat2json(true);
        }
      
    }
    function get_subcat_list($cat_id){
        $filter = array('parent_id'=>$cat_id);
        $list = parent::getList('*',$filter,0,-1);
        return $list;
    }
    function get_subcat_count($cat_id){
        $filter = array('parent_id'=>$cat_id);
        return parent::count($filter);
    }
    function toRemove($catid,&$msg=''){
		$aCats = $this->getList('*',array('parent_id'=>intval($catid)));
        if(count($aCats) > 0){
            $msg = '删除失败：本分类下面还有子分类';
            return false;
        }
        /*
		$obj_store = $this->app->model('storemanger');
		$aStore = $obj_store->getList('store_id',array('violationcat'=>intval($catid),'disabled'=>'false'));
        if(count($aStore) > 0){
            $msg = '删除失败：本分类下面还有店铺';
            return false;
        }
        */
		$row = $this->getList('parent_id',array('cat_id'=>intval($catid)));
        $parent_id = $row[0]['parent_id'];

        $this->db->exec('DELETE FROM sdb_business_violationcat WHERE cat_id='.intval($catid));
        $this->db->exec('UPDATE sdb_business_violationcat SET child_count = child_count-1 WHERE cat_id='.intval($parent_id));
        $this->cat2json();
        return true;
    }

    function get_new_cat($limit){
        $cat_id = $this->db->select('SELECT violationcat as cat_id FROM `sdb_business_storemanger`  where cat_id <> \'0\' group by violationcat order by store_id desc limit 0,'.$limit);
        if(is_array($cat_id)){
            foreach($cat_id as $ck=>$cv){
                $catId['cat_id'][] = $cv['cat_id'];
            }
        }
        return $this->getList('cat_id,cat_path,cat_name',$catId);
    }



}
