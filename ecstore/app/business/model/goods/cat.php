<?php

class business_mdl_goods_cat extends dbeav_model{

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
            if($aData['custom_cat_id'] && $aData['custom_cat_id'] == $parent_id){
                return false;
                break;
            }
            array_unshift($path, $parent_id);
            $row = $this->dump(array('custom_cat_id'=>$parent_id),'parent_id, cat_path, p_order');
            $parent_id = $row['parent_id'];
        }
        $aData['cat_path'] = $this->getCatPath($aData['parent_id']);
        if($aData['parent_id']!=0){
            $row = parent::dump($aData['parent_id']);
            $data['child_count'] = $row['child_count']+1;
            $data['custom_cat_id'] = $aData['parent_id'];
            parent::save($data);
        }
        parent::save($aData);
        return true;
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

    function checkTreeSize(){
		$aCount = $this->count();
        if($aCount > 100){
            return false;
        }else{
            return true;
        }
    }


    function getCatPath($parent_id){
        if($parent_id == 0){
            return ',';
        }
        $cat_sdf = $this->dump($parent_id);
        return $cat_sdf['cat_path'].$cat_sdf['custom_cat_id'].",";
    }

    function toRemove($catid,&$msg=''){
        
        $aCats_conn = app::get('business')->model('goods_cat_conn');

        $objCat = app::get('business')->model('goods_cat');

        foreach($catid as $v){
            $subCats = $objCat->getList('custom_cat_id',array('parent_id'=>$v));
            if(count($subCats) > 0){
                $msg = '删除失败：分类下面还有子类';
                return false;
            }
            $cats_conn = $aCats_conn->getList('goods_id',array('cat_id'=>$v));
            if(count($cats_conn) > 0){
                $msg = '删除失败：分类下面还有商品';
                return false;
            }
        }
        //--end

        foreach($catid as $v){

            $row = $this->getList('parent_id',array('custom_cat_id'=>intval($v)));
            $parent_id = $row[0]['parent_id'];

            $this->db->exec('DELETE FROM sdb_business_goods_cat WHERE custom_cat_id='.intval($v));
            $this->db->exec('UPDATE sdb_business_goods_cat SET child_count = child_count-1 WHERE custom_cat_id='.intval($parent_id));
        }

        return true;
    }



}
