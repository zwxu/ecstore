<?php

class cellphone_mdl_category extends dbeav_model{

    function __construct($app){
        parent::__construct($app);
        $this->use_meta();
    }
    function checkTreeSize(){
        $aCount = $this->count();
        if($aCount > 100){
            return false;
        }else{
            return true;
        }
    }

    function getCatParentById($id){
        $result = $this->getList('cat_id,filter,cat_name',array('parent_id'=>$id),0,-1,'p_order,cat_id DESC');
        $conf_default_view = app::get('b2c')->getConf('gallery.default_view');
        $default_view = $conf_default_view?$conf_default_view:'index';

        $oSearch = &app::get('b2c')->Model('search');
        foreach((array)$result as $cat_key=>$cat_value){
            $filter=$this->_mkFilter($cat_value['filter']);
            $result[$cat_key]['link']=$this->gen_url(array('app'=>'b2c','ctl'=>'site_gallery','act'=>$default_view,'args'=>array(null,null,'0','','',$cat_value['cat_id'])));
        }
        return $result;
    }

    function &_mkFilter($filter){
        parse_str($filter,$filter);
        if($filter['type_id']){
            $filter['type_id']=array($filter['type_id']);
        }
        $filter=$this->getFilter($filter);
        $filter['cat_id'] = (array)$filter['cat_id'];

        if($filter['props']){
            foreach($filter['props'] as $k=>$v){
                if($v!='_ANY_'){
                    $filter['p_'.$k]=$v;
                }
            }
        }
        $filter['price'][0]=floatval( $filter['pricefrom'] );
        $filter['price'][1]=$filter['priceto'];
        $filter['name'][0]=$filter['searchname'];
        return $filter;
    }

    function getTreeList($pid=0, $listMark='all'){
        if($listMark == 'all'){
            $aCat = $this->getList('cat_name,cat_id, parent_id AS pid,p_order,cat_path,type_id,filter as type',array(),0,-1,'cat_path,p_order,cat_id ASC');
        }else{
            $oSearch = &app::get('b2c')->Model('search');
            if($pid === 0){
                $sqlWhere = '(parent_id IS NULL OR parent_id='.intval($pid).')';
            }else{
                $sqlWhere = 'parent_id='.intval($pid);
            }
            $aCat = $this->db->select('SELECT cat_name, cat_id, o.parent_id AS pid, o.p_order,o.filter, o.cat_path, t.name as type_name FROM sdb_cellphone_category o
            LEFT JOIN sdb_b2c_goods_type t ON o.type_id = t.type_id
            WHERE '.$sqlWhere.' ORDER BY o.cat_path,o.p_order,o.cat_id');

            $default_view=app::get('b2c')->getConf('gallery.default_view')?app::get('b2c')->getConf('gallery.default_view'):'index';
            foreach($aCat as $k => $row){
                $aCat[$k]['pid'] = intval($aCat[$k]['pid']);
                if($row['cat_path'] == '' || $row['cat_path'] == ','){
                    $aCat[$k]['step'] = 1;
                }else{
                    $aCat[$k]['step'] = substr_count($row['cat_path'], ',') + 1;
                }
                $filters=$this->_mkFilter($aCat[$k]['filter']);
                $aCat[$k]['url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_gallery','view'=>$default_view,'args'=>array(implode(",",$filters['cat_id']),$oSearch->encode($filters))));
            }
        }
        return $aCat;
    }

    function getTree($count=true,$node=null,$disabled=true){
        $where[]=' 1 ';
        if($count==false){
           $where[]='vCat.parent_id=0';
        }
        if($node){
           $where[]='(vCat.cat_path like \'%'.$node.',%\' or vCat.cat_id='.$node.') ';
        }
        $sql = 'SELECT vCat.cat_id,vCat.disabled,vCat.url,vCat.cat_name,vCat.filter,vCat.child_count,vCat.p_order,vCat.cat_path,vCat.parent_id,vCat.child_count,vType.name as type_name from sdb_cellphone_category as vCat LEFT JOIN sdb_b2c_goods_type as vType ON vType.type_id=vCat.type_id where '.implode($where,' and ').($disabled?' AND vCat.disabled ="false" ':'').' ORDER BY vCat.p_order,vCat.cat_id';

        return $this->db->select('SELECT vCat.cat_id,vCat.disabled,vCat.url,vCat.cat_name,vCat.filter,vCat.child_count,vCat.p_order,vCat.cat_path,vCat.parent_id,vCat.child_count,vType.name as type_name from sdb_cellphone_category as vCat LEFT JOIN sdb_b2c_goods_type as vType ON vType.type_id=vCat.type_id where '.implode($where,' and ').($disabled?' AND vCat.disabled ="false" ':'').' ORDER BY vCat.p_order,vCat.cat_id');

    }

    function getGoodsCatById($cat_id=0){
        $data = $this->getList('cat_id,cat_path,parent_id,cat_name,child_count as isleaf',array('parent_id'=>intval($cat_id)),0,-1,'p_order ASC');
        $data[] = array('cat_id' => 0,
                    'cat_path' => '',
                    'parent_id' => 0,
                    'cat_name' => app::get('b2c')->_('[未分类商品]'),
                    'isleaf' => 0 );

        return $data;
    }

    function getCatById($cat_id=0){
        return $this->getList('cat_id,filter,parent_id,cat_name,child_count as isleaf',array('parent_id'=>intval($cat_id)),0,-1,'cat_path,p_order ASC');
    }

    function _getSonCatId($cat_id){
        $cat_path = $this->getList('cat_path',array('cat_id'=>intval($cat_id)));
        if($cat_path[0]['cat_path']==','){
            $cat_path[0]['cat_path'] = $cat_id.',';
        }else{
            $cat_path[0]['cat_path'] = $cat_path[0]['cat_path'].$cat_id.',';
        }

        $obj_goods_cat = app::get('b2c')->model('goods_cat');
        $result = $obj_goods_cat->getList('cat_id',array('cat_path|head'=>$cat_path[0]['cat_path']));

        return $result;
    }

    function toRemove($catid,&$msg=''){
        $aCats = $this->getList('*',array('parent_id'=>intval($catid)));
        if(count($aCats) > 0){
            $this->remove_errmsg = '删除失败：本分类下面还有子分类';
            return false;
        }

        $res = kernel::single('b2c_predelete_virtualcat')->pre_delete($catid);
        if(is_array($res)){
            if(is_bool($res[0])&&!$res[0]){
                $this->remove_errmsg = $res[1];
                return false;
            }
        }
        $row = $this->getList('parent_id',array('cat_id'=>intval($catid)),0,1);

        $parent_id = $row[0]['parent_id'];
        $this->db->exec('DELETE FROM sdb_cellphone_category WHERE cat_id='.intval($catid));
        $this->db->exec('UPDATE sdb_cellphone_category SET child_count = child_count-1 WHERE cat_id ='.intval($parent_id));
        $this->cat2json();
        $this->cat2json(true,false);
        return true;
    }

    function get_cat_depth(){
        $row = $this->getList('cat_path',array(),0,1,'cat_path DESC');
        return count(explode(',',$row[0]['cat_path']));
    }

    function updateOrder($p_order){
        if(is_array($p_order)){
            foreach($p_order as $k=>$v){
                $this->update(array('p_order'=>intval($v)),array('cat_id'=>intval($k)));
            }
        }
        $this->cat2json(true,false);
        $this->cat2json();
        return true;
    }

    function updateChildCount($id){
        if(!$id){
            return false;
        }

        $filter = array('parent_id'=>intval($id));
        $row = $this->count($filter);
        if($row){
            $aData['is_leaf'] = 'false';
        }else{
            $aData['is_leaf'] = 'true';
        }
        $aData['child_count'] = $row;
        $sql = $this->Update($aData,array('cat_id'=>intval($id)));
        if($sql){
            return true;
        }else{
            return false;
        }
    }

    function addNew($data){
        $oSearch = &app::get('b2c')->model('search');
        $filters=$this->_mkFilter($data['filter']);
        parse_str($data['filter'],$filter);
        $data['disabled'] = (($data['disabled']=='true')?$data['disabled']:'false');
        $data['type_id']=$filter['type_id'];
        $data['parent_id'] = intval($data['parent_id']);
        $data['addon']['meta']['title'] = htmlspecialchars($data['title']);
        $data['addon']['meta']['keywords'] = htmlspecialchars($data['keywords']);
        $data['addon']['meta']['description'] = htmlspecialchars($data['description']);
        $parent_id = $data['parent_id'];
        $path=array();

        while($parent_id){
            if($data['cat_id'] && $data['cat_id'] == $parent_id){
                return false;
                break;
            }
            array_unshift($path, $parent_id);
            $row = $this->getList('parent_id, cat_path, p_order',array('cat_id'=>intval($parent_id)),0,1);
            $parent_id = $row[0]['parent_id'];
        }
        $data['cat_path'] = ','.implode(',',$path).',';
        //add by HUoxh 缓存过期
         array_unshift($path,0);
         foreach($path as $key=>$pid){
             cachemgr::co_start();
             $co_end=cachemgr::co_end();
             $co_end['expires']=time();
             cachemgr::set('cellphone_goodscat_tree_'.$pid, array(), $co_end);
             cachemgr::set('cellphone_goodscat_child_'.$pid, array(), $co_end);
         }
        //end
        if($data['cat_id']){
            if($data['type_id']=='_ANY_')
            $data['type_id'] = null;
            $sDefine=$this->getList('parent_id',array('cat_id'=>intval($data['cat_id'])),0,1);
            $sql = $this->Update($data,array('cat_id'=>$data['cat_id']));
            if($sql){
                $cat_id=$data['cat_id'];

                if($sDefine[0]['parent_id']!=$data['parent_id']){
                    $this->updatePath($data['cat_id'],$data['cat_path']);
                    $this->updateChildCount($sDefine[0]['parent_id']);
                    $this->updateChildCount($data['parent_id']);
                }
                $this->cat2json();
                $this->cat2json(true,false);
                return true;
            }else{
                return false;
            }
        }else{
            if($data['type_id'] == '_ANY_')
                unset($data['type_id']);
            unset($data['cat_id']);
            if(parent::save($data)){
                $cat_id=$this->db->lastInsertId();
                $this->db->exec('UPDATE sdb_cellphone_category SET url = \''.app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_gallery','args'=>array(null,null,0,'','',$cat_id) ) ).'\' WHERE cat_id = '.$cat_id );

                $this->updateChildCount($data['parent_id']);
                $this->cat2json();
                $this->cat2json(true,false);
                return true;
            }else{
                return false;
            }
        }
    }

   function updatePath($cat_id,$cat_path){
        $result = $this->db->select('SELECT cat_id,cat_path FROM sdb_cellphone_category WHERE cat_path like \''.$cat_id.',%\' or parent_id='.$cat_id);
        foreach($result as $k=>$v){
            $path=$cat_path.substr($v['cat_path'],strpos($v['cat_path'],$cat_id.','),strlen($v['cat_path']));
            $this->update(array('cat_path'=>$this->db->quote($path)),array('cat_id'=>intval($v['cat_id'])));
        }
    }

    function get_cat_list($show_stable=false , $disabled = true,$catid=null){
        $kvName = $disabled?'cellphone_cat.data':'cellphone_cat.all.data';
        if(base_kvstore::instance('b2c_goods')->fetch($kvName, $contents) !== false){
            if(is_array($contents))
                $result=$contents;
            else
                $result=json_decode($contents,true);
            if($result){
                if($show_stable){
                    foreach($result as $key=>$value){
                        if($result[$key]['step']>1){
                            $result[$key]['cat_name']='└'.$result[$key]['cat_name'];
                        }
                        //编辑虚拟分类时，不显示本身及其子分类
                        if($result[$key]['cat_id']==$catid){
                            $pid = $result[$key]['pid'];
                            if($pid == 0){
                                $pid = $catid;
                            }
                            unset($result[$key]);
                        }
                        if(strpos($result[$key]['cat_path'],$pid) !== false)
                            unset($result[$key]);
                        //end
                    }
                }

                return $result;
            }else{
                $this->cat2json( false,$disabled?false:true );
                return $this->cat2json(true,$disabled);
            }
        }else{
            $this->cat2json( false,$disabled?false:true );
            return $this->cat2json(true,$disabled);
        }
    }

    function cat2json($return=false,$disabled=true){
        $kvName = $disabled?'cellphone_cat.data':'cellphone_cat.all.data';
        $contents=$this->getMapTree(0,'',null,$disabled);
        base_kvstore::instance('b2c_goods')->store($kvName,$contents);
        if($return){
            return $contents;
        }else{
            return true;
        }
    }


    function getMapTree($ss=0, $str='└',$node=null,$disabled=true){
        $retCat = $this->map($this->getTree(true,$node,$disabled),$ss,$str,$cat,$step);
        global $step,$cat;
        $step = '';
        $cat = array();
        return $retCat;
    }

    function getPath($cat_id,$method=null){
        if (!$cat_id) return array();
        $method = app::get('b2c')->getConf('gallery.default_view');
        if(!$oSearch)$oSearch = &app::get('b2c')->model('search');
        $row = $this->getList('cat_id,cat_path,cat_id,filter,cat_name as cat_name',array('cat_id'=>intval($cat_id)));
        $row = $row[0];
        $filters=$this->_mkFilter($row['filter']);
        $filters=$this->getFilter($filters);

        $ret = array(array('type'=>'goodsCat','title'=>$row['cat_name'],'link'=> app::get('site')->router()->gen_url(array('app'=>'b2c', 'ctl'=>'site_gallery','act'=>'index','args'=>array(null,null,0,'','',$row['cat_id'])))  ));

        if($row['cat_path'] != ',' && $row['cat_path']){
            $arr_rows = $this->getList('cat_name as cat_name,filter,cat_id,cat_id',array('cat_id|in'=>explode(',',substr($row['cat_path'],0,-1))),0,-1,'cat_path DESC');
            foreach($arr_rows as $row){
                $filters=$this->_mkFilter($row['filter']);
                $filters=$this->getFilter($filters);

                array_unshift($ret,array('type'=>'goodsCat','title'=>$row['cat_name'],'link'=>app::get('site')->router()->gen_url(array('app'=>'b2c', 'ctl'=>'site_gallery','act'=>'index','args'=>array(null,null,0,'','',$row['cat_id'])))  ));
            }
        }
        array_unshift($ret,array('type'=>'goodsCat','title'=>app::get('site')->_('首页'),'link'=>kernel::base_url(1)  ));
        return $ret;
    }

    function &getFilter($filter){
        $filter = array_merge(array('marketable'=>"true",'disabled'=>"false",'goods_type'=>"normal"),$filter);
        if($GLOBALS['runtime']['member_lv']){
            $filter['mlevel'] = $GLOBALS['runtime']['member_lv'];
        }
        return $filter;
    }

    function map($data,$sID=0,$preStr='',&$cat,&$step){
        $step++;

        if($preStr){
            for($i=1; $i<$step; $i++){
                $stepStr = str_repeat("&nbsp;",$step).$preStr;
            }
        }

        $base_url=$this->app->base_url();
        $default_view=app::get('b2c')->getConf('gallery.default_view');

        if($data){
            foreach($data as $i=>$value){
                $id=$data[$i]['cat_id'];
                $filter=$data[$i]['filter'];
                    $cls=$data[$i]['child_count']?'true':'false';

                if(!is_array($filters['cat_id'])){
                    $filters['cat_id']=array($filters['cat_id']);
                }

                if(!$sID){ //第一轮圈套
                    if(empty($data[$i]['parent_id'])){ //原始节点
                        $cat[]=array(
                            'cat_name'=>$data[$i]['cat_name'],
                            'cat_id'=>$id,
                            'pid'=>$data[$i]['parent_id'],
                            'step'=>$step,
                            'disabled' => $data[$i]['disabled'],
                            'filter'=>$filter,
                            'type_name'=>$data[$i]['type_name'],
                            'p_order'=>$data[$i]['p_order'],
                            'cat_path'=>$data[$i]['cat_path'],
                            'cls'=>$cls,
                            'url'=>$data[$i]['url']
                        );

                        unset($data[$i]);
                        $this->map($data,$id,$preStr,$cat,$step);
                    }else{ //
                        continue;
                    }
                }else{ //子节点
                    if($sID==$data[$i]['parent_id']){
                        $cat[]=array(
                            'cat_name'=>$stepStr.$data[$i]['cat_name'],
                            'cat_id'=>$id,
                            'pid'=>$data[$i]['parent_id'],
                            'type'=>$type,
                            'type_name'=>$data[$i]['type_name'],
                            'step'=>$step,
                            'disabled' => $data[$i]['disabled'],
                            'p_order'=>$data[$i]['p_order'],
                            'filter'=>$filter,
                            'cat_path'=>$data[$i]['cat_path'],
                            'cls'=>$cls,
                            'url'=>$data[$i]['url']
                        );
                        unset($data[$i]);
                        $this->map($data,$id,$preStr,$cat,$step);
                    }
                }
            }
        }

        $step--;
        return $cat;
    }
}