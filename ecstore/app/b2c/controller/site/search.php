<?php
 

class b2c_ctl_site_search extends b2c_frontpage{

     function __construct($app){
        parent::__construct($app);
        $shopname = $app->getConf('system.shopname');
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('搜索').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('搜索_').'_'.$shopname;
            $this->description = app::get('b2c')->_('搜索_').'_'.$shopname;
        }
    }

    function index(){
        $aBrands = array();
        $objBrand = &$this->app->model('brand');
        $this->pagedata['brand'] = $objBrand->getAll();
        $objCat = &$this->app->model('goods_cat');
        $this->pagedata['categorys'] = $objCat->get_cat_list();
        $this->pagedata['args'] = array($cat_id,$filter,$orderBy,$tab,$page);
        //print_R($this->pagedata['args']);exit;
        $this->page('site/search/index.html');
    }

    function result(){
		$this->set_no_store();
        $oSearch = &$this->app->model('search');
        $emu_static = $this->app->getConf('system.seo.emuStatic');
        if(empty($_POST)&& !empty($_GET)){
           $_POST=$_GET;
        }
        //print_r($_POST);exit;
		foreach(kernel::servicelist("search.prepare") as $obj )
		{
			$obj->parse($_POST);
		}
        //扩展属性多选
        if(isset($_POST['props'])&& $_POST['props']){
            $props=$_POST['props'];
            if(is_string($props)){
               unset($_POST['props']);
               $arr=explode(',',$props);
               $part=$arr[0];
               unset($arr[0]);
                $_POST['p_'.$part]=$arr;
            }
        }
        //品牌多选
        if(isset($_POST['brand_id'])&& $_POST['brand_id']){
            $brand_id=$_POST['brand_id'];
            if(is_string($brand_id)){
                $_POST['brand_id']=explode(',',$brand_id);
            }
        }
        // 排序，
        $orderBy=$_POST['orderby']?$_POST['orderby']:1;
        unset($_POST['orderby']);

        //分页
        $page=$_POST['page']?$_POST['page']:1;
        unset($_POST['page']);
        //如果是输入分页，则分页不能大于当前总页数。
        if(isset($_POST['totalPage'])&&$_POST['totalPage']){
            if($page>intval($_POST['totalPage']))
                $page=intval($_POST['totalPage']);
        }

        //虚拟分类
        $cat_type = $_POST['cat_type'];
        unset($_POST['cat_type']);

        //显示方式：店铺，大图，小图
        $view = $_POST['view']?$_POST['view']:'grid';
        unset($_POST['view']);


        //搜索类型：g 商品，s:店铺
        $st=$_POST['st'];//?$_POST['st']:'g';
        unset($_POST['st']);
        $url=array();
        //发货地
        if($_POST['loc'] && $_POST['loc']){
            $url[]='loc='.$_POST['loc'];
            unset($_POST['loc']);
        }
        //发货地
        if($_POST['sid'] && $_POST['sid']){
            $_POST['store_id']=$_POST['sid'];
            unset($_POST['sid']);
        }

        $cat_id = $_POST['cat_id'];
        unset($_POST['cat_id']);
        foreach($_POST as $k=>$v){
            if($k=="name" && $_POST[$k][0]){
                $_POST[$k][0]=str_replace('_','%xia%',$_POST[$k][0]);
                $_POST[$k][0] = strip_tags($_POST[$k][0]);
            }
            if($k=="price" && $_POST[$k][1]){
                $_POST[$k][0]=floatval($_POST[$k][0]);
                $_POST[$k][1]=floatval($_POST[$k][1]);

            }
        }
            if(isset($_POST['filter'])&&$filter = $oSearch->decode($_POST['filter'],$path)){
                //如果没有选择包邮则清掉filter中的选择
                if(empty($_POST['freight_bear'])){
                    unset($filter['freight_bear']);
                }
                 if(empty($_POST['goods_state'])){
                    unset($filter['goods_state']);
                }
                
                $filter = array_merge($filter,$_POST);

            }else{
                $filter = $_POST;
            }

        unset($_POST['filter']);

        $filter = $oSearch->encode($filter);
        $args=array();
        if(empty($cat_id)){//分类ID
            array_push($args,null);
        }else{
            array_push($args,$cat_id);
        }
        if(empty($filter)){//查询条件
            array_push($args,null);
        }else{
            array_push($args,$filter);

        }
        array_push($args,$orderBy);//排序
        array_push($args,null);//tab
        array_push($args,$page);//分页
        array_push($args,$cat_type);//虚拟分类
        if(empty($view)){//视图
            array_push($args,null);
        }else{
            array_push($args,$view);

        }
        array_push($args,$st);//虚拟分类
        $url_link=implode('&',$url);
        $this->sredirect(array('app'=>'b2c', 'ctl'=>'site_gallery', 'act'=>'index', 'args'=>$args),false,$url_link);

    }

    function showCat(){

        $objCat = &$this->app->model('goods_cat');
        $objBrand = &$this->app->model('brand');
        if(!empty($_POST['cat_id'])){
            $cat = $objCat->getlist('*',array('cat_id'=>$_POST['cat_id']));
            $type = $objBrand->getBidByType($cat[0]['type_id']);
            foreach($type as $key=>$val){
                 $brand_id['brand_id'][] = $val['brand_id'];
            }
            if(empty($brand_id['brand_id'])) $brand_id['brand_id'] = '-1';
            $cat['brand']  = $objBrand->getlist('*',$brand_id);

        }else{
            $cat['brand']  = $objBrand->getlist('*','');
        }
        $this->pagedata['cat'] = $cat;
        $this->display("site/search/showCat.html");
        exit;

    }

    function sredirect($url, $js_jump=false,$params=null){
        
        if(is_array($url)){
            $arg = $url['args'][1];
            $url['args'][1]=null;
            $url = $this->gen_url($url).'?scontent='.$arg;
        }
        if(!empty($params)){
             $url= $url.'&'.$params;
        }
        //exit;
        $this->_response->set_redirect($url)->send_headers();
    }
}
