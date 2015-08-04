<?php



class complain_mdl_reports_cat extends dbeav_model{

	/**
	 * 构造方法
	 * @param object model相应app的对象
	 * @return null
	 */
    public function __construct($app){
        parent::__construct($app);
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
            $row = $this->dump(array('cat_id'=>$parent_id),'parent_id, cat_path, p_order');
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
	 * 得到整个类型树形结构
	 * @param null
	 * @return mixed 返回的数据
	 */
    public function getTree(){
        return $this->db->select('SELECT cat_name AS text,cat_id AS id,parent_id AS pid,p_order,cat_path,
                    is_leaf,child_count FROM sdb_complain_reports_cat o ORDER BY p_order,cat_id');
    }

	/**
	 * 通过上一级类型id得到下一级类型的数据
	 * @param int parent_cat_id
	 * @param string link view
	 * @return mixed 返回结果数据
	 */
    public function getCatParentById($id,$view='index'){
        if(!$id) return false;
        if(is_array($id)){
            if(implode($id,' , ')==='') return false;
            $result = $this->getList('cat_id,cat_name',array('parent_id|in'=>$id),0,-1,'p_order,cat_id ');
        }else{
            $result = $this->getList('cat_id,cat_name',array('parent_id'=>$id),0,-1,'p_order,cat_id ');
        }
        return $result;
     }

	/**
	 * 得到类型的树形结构图
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
			$rows = $this->getList('cat_name,cat_id,parent_id,is_leaf,cat_path',array('cat_path|head'=>$row['cat_path'].$cat_id),0,-1,'cat_path,p_order ASC');
        }else{
			$rows = $this->getList('cat_name,cat_id,parent_id,is_leaf,cat_path',array(),0,-1,'p_order ASC');
        }
        $cats = array();
        $ret = array();
        foreach($rows as $k=>$row){
            if($depth<0 || substr_count($row['cat_path'],',') < $depth){
                $cats[$row['cat_id']] = array('type'=>'gcat','parent_id'=>$row['parent_id'],'title'=>$row['cat_name']);
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


    function map($data,$sID=0,$preStr='',&$cat_cuttent,&$step){
    	set_time_limit(2000);
        $step++;
        if($data){

            $tmpCat = array();
            foreach($data as $i=>$value){

                $count = substr_count( $data[$i]['cat_path'],',' );
                $id=$data[$i]['id'];
                $cls=($data[$i]['child_count']?'true':'false');
                $tmpCat[$value['pid']][] =array(
                            'cat_name'=>$data[$i]['text'],
                            'cat_id'=>$data[$i]['id'],
                            'pid'=>$data[$i]['pid'],
                            'step'=> $count?$count:1,
                            'p_order'=>$data[$i]['p_order'],
                            'cat_path'=>$data[$i]['cat_path'],
                            'cls'=>$cls
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
        base_kvstore::instance('complain_reports')->store('reports_cat.data',$contents);
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
        if( base_kvstore::instance('complain_reports')->fetch('reports_cat.data', $contents) !== false ){
            if(is_array($contents))
                $result=$contents;
            else
                $result=json_decode($contents,true);
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
            $msg = '删除失败：本类型下面还有子类型';
            return false;
        }
		$obj_goods = $this->app->model('reports');
		$aGoods = $obj_goods->getList('reports_id',array('cat_id'=>intval($catid),'disabled'=>'false'));
        if(!empty($aGoods)&&count($aGoods) > 0){
            $msg = '删除失败：本类型下面还有举报信息';
            return false;
        }
		$row = $this->getList('parent_id',array('cat_id'=>intval($catid)));
        $parent_id = $row[0]['parent_id'];

        $this->db->exec('DELETE FROM sdb_complain_reports_cat WHERE cat_id='.intval($catid));
        $this->db->exec('UPDATE FROM sdb_complain_reports_cat SET child_count = child_count-1 WHERE cat_id='.intval($parent_id));
        $this->cat2json();
        return true;
    }
}
