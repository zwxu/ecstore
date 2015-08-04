<?php
 

class b2c_ctl_admin_brand extends desktop_controller{

    var $workground = 'b2c.workground.goods';


    function index(){
        $this->finder('b2c_mdl_brand',array(
            'title'=>app::get('b2c')->_('商品品牌'),
            'actions'=>array(

                array('label'=>app::get('b2c')->_('添加品牌'),'icon'=>'add.gif','href'=>'index.php?app=b2c&ctl=admin_brand&act=create','target'=>'_blank'),

            )
            ));
    }

    function getCheckboxList(){
        $brand = &$this->app->model('brand');
        $this->pagedata['checkboxList'] = $brand->getList('brand_id,brand_name',null,0,-1);
        $this->page('admin/goods/brand/checkbox_list.html');
    }

    function create(){
        $oGtype = &$this->app->Model('goods_type');
        $objBrand = &$this->app->model('brand');
        $this->pagedata['type'] = $objBrand->getDefinedType();
        $this->pagedata['brandInfo']['type'][$this->pagedata['type']['default']['type_id']] = 1;
        $this->pagedata['gtype']['status'] = $oGtype->checkDefined();
        $this->singlepage('admin/goods/brand/detail.html');
    }

    function save(){
        $this->begin('index.php?app=b2c&ctl=admin_brand&act=index');
        $objBrand = &$this->app->model('brand');
        $brandname = $objBrand->dump(array('brand_name'=>$_POST['brand_name'],'brand_id'));
        if(empty($_POST['brand_id']) && is_array($brandname)){
             $this->end(false,app::get('b2c')->_('品牌名重复'));
        }
        $_POST['ordernum'] = intval( $_POST['ordernum'] );
        
       
        $type_info = array_filter(explode(',', $_POST['gtype']['linkid']));
        unset($_POST['gtype']);
        $_POST['gtype'] = $type_info;
       
        
        $data = $this->_preparegtype($_POST);
        $this->end($objBrand->save($data),app::get('b2c')->_('品牌保存成功'));

    }

    function edit($brand_id){
        $this->path[] = array('text'=>app::get('b2c')->_('商品品牌编辑'));
        $objBrand = &$this->app->model('brand');
        $this->pagedata['brandInfo'] = $objBrand->dump($brand_id);
        if(empty($this->pagedata['brandInfo']['brand_url'])) $this->pagedata['brandInfo']['brand_url'] = 'http://';

        foreach($objBrand->getBrandTypes($brand_id) as $row){
            $aType[$row['type_id']] = 1;
        }
        
      
        $type_info = array();
        foreach((array)$aType as $key => $item){
            $type_info['linkid'][] = $key;
            $type_info['info'][] = array('id'=>$key);
        }
        $type_info['linkid'] = implode(',', $type_info['linkid']);
        $type_info['info'] = json_encode($type_info['info']);
        $this->pagedata['gtype'] = $type_info;
       

        $this->pagedata['brandInfo']['type'] = $aType;
        //$this->pagedata['type'] = $objBrand->getDefinedType();
        $objGtype = &$this->app->model('goods_type');
        $this->pagedata['gtype']['status'] = $objGtype->checkDefined();
        $this->singlepage('admin/goods/brand/detail.html');
    }
    function _preparegtype($data){
        if(is_array($data['gtype'])){
            foreach($data['gtype'] as $key=>$val){
                $pdata = array('type_id'=>$val);
                $result[] = $pdata;
            }
        }
        $data['seo_info']['seo_title'] = $data['seo_title'];
        $data['seo_info']['seo_keywords'] = $data['seo_keywords'];
        $data['seo_info']['seo_description'] = $data['seo_description'];
        $data['seo_info'] = serialize($data['seo_info']);
        unset($data['seo_title']);
        unset($data['seo_keywords']);
        unset($data['seo_description']);
        $data['gtype'] = $result;
        return $data;
    }


}
