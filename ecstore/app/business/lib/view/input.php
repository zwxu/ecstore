<?php

 
class business_view_input{

    function input_url($params){        
        $params['vtype']='purl';
        $params['type'] = 'text';
        return utils::buildTag($params,'input autocomplete="off" class="x-input '.$params['class'].'"');
    }
    function input_storecat($params){
        $render = new base_render(app::get('business'));
        $mdl = app::get('business')->model('storecat');
        $render->pagedata['category'] = array();
        $render->pagedata['params'] = $params;
        if($params['value']){
            $row = $mdl->getList('*',array('cat_id'=>$params['value']));
            $render->pagedata['category'] = $row[0];
        }
        $render->pagedata['controller'] ='admin_storecat';
        return $render->fetch('admin/store/input_category.html');
    }

    function input_violationcat($params){
        $render = new base_render(app::get('business'));
        $mdl = app::get('business')->model('violationcat');
        $render->pagedata['category'] = array();
        $render->pagedata['params'] = $params; 
        if($params['value']){
            $row = $mdl->getList('*',array('cat_id'=>$params['value']));  
            $render->pagedata['category'] = $row[0];
        }
        $render->pagedata['controller'] ='admin_violationcat';
        return $render->fetch('admin/store/input_category.html');
    }

    public function input_vgcat($params)
    {   
        $render = new base_render(app::get('b2c'));
        $mdl = app::get('b2c')->model('goods_cat');
        
        if($params['value']){
            $row = $mdl->getList('cat_id,cat_name',array('cat_id'=>$params['value']));
            $str = '
            <div class="object-select clearfix" id="gEditor-GCat-category">
              <div class="label" rel="'.$row[0]['cat_name'].'">'.$row[0]['cat_name'].'</div>
              <div class="handle">&nbsp;</div>
              <input type="hidden" value="'.$row[0]['cat_id'].'" name="'.$params['name'].'" id="gEditor-GCat-input">
            </div>';
            $render->pagedata['category'] = $row[0];
        }else{
            $str = '
            <div class="object-select clearfix" id="gEditor-GCat-category">
              <div class="label" rel="分类不限">分类不限</div>
              <div class="handle">&nbsp;</div>
              <input type="hidden" value="" name="'.$params['name'].'" id="gEditor-GCat-input">
            </div>';
        }        
        return $str."
            <script>
              $('gEditor-GCat-category').addEvent('click',function(){
                var handle = $('gEditor-GCat-category'),cat_id= handle.getElement('input').value;
                var url='btools-get_subcat-'+cat_id+'.html?dd='+Date.now();
                new Dialog(url,{
                  width:600,height:420,resizeable:false,
                  title:'分类选择',
                  onShow:function(){
                    this.handle=handle;
                  }
                });
              });
            </script>
        ";
    }
    public function input_vstorecat($params)
    {   
        $render = new base_render(app::get('b2c'));
        $mdl = app::get('business')->model('storecat');
        
        if($params['value']){
            $row = $mdl->getList('cat_id,cat_name',array('cat_id'=>$params['value']));
            $str = '
            <div class="object-select clearfix" id="gEditor-GStoreCat-category">
              <div class="label" rel="'.$row[0]['cat_name'].'">'.$row[0]['cat_name'].'</div>
              <div class="handle">&nbsp;</div>
              <input type="hidden" value="'.$row[0]['cat_id'].'" name="'.$params['name'].'" id="gEditor-GStoreCat-input">
            </div>';
            $render->pagedata['category'] = $row[0];
        }else{
            $str = '
            <div class="object-select clearfix" id="gEditor-GStoreCat-category">
              <div class="label" rel="分类不限">分类不限</div>
              <div class="handle">&nbsp;</div>
              <input type="hidden" value="" name="'.$params['name'].'" id="gEditor-GStoreCat-input">
            </div>';
        }        
        return $str."
            <script>
              $('gEditor-GStoreCat-category').addEvent('click',function(){
                var handle = $('gEditor-GStoreCat-category'),cat_id= handle.getElement('input').value;
                var url='btools-get_substorecat-'+cat_id+'.html?dd='+Date.now();
                new Dialog(url,{
                  width:600,height:420,resizeable:false,
                  title:'分类选择',
                  onShow:function(){
                    this.handle=handle;
                  }
                });
              });
            </script>
        ";
    }
    public function input_vg2cat($params)
    {
        $render = new base_render(app::get('b2c'));
        $mdl = app::get('b2c')->model('goods_cat');
        
        if($params['value']){
            $row = $mdl->getList('cat_id,cat_name',array('cat_id'=>$params['value']));
            $str = '
            <div class="object-select clearfix" id="gEditor-GCat-category">
              <div class="label" rel="'.$row[0]['cat_name'].'">'.$row[0]['cat_name'].'</div>
              <div class="handle">&nbsp;</div>
              <input type="hidden" value="'.$row[0]['cat_id'].'" name="'.$params['name'].'" id="gEditor-GCat-input">
            </div>';
            $render->pagedata['category'] = $row[0];
        }else{
            $str = '
            <div class="object-select clearfix" id="gEditor-GCat-category">
              <div class="label" rel="请选择">请选择</div>
              <div class="handle">&nbsp;</div>
              <input type="hidden" value="" name="'.$params['name'].'" id="gEditor-GCat-input">
            </div>';
        }        
        return $str."
            <script>
              $('gEditor-GCat-category').addEvent('click',function(){
                var handle = $('gEditor-GCat-category'),cat_id= handle.getElement('input').value;
                
                new Dialog('business-get_subcat-'+cat_id+'.html?dd='+Date.now(),{
                  width:600,height:420,resizeable:false,
                  title:'分类选择',
                  onShow:function(){
                    this.handle=handle;
                  }
                });
              });
            </script>
        ";
    }
    
    
  

  function input_vimage($params){

        $params['type'] = 'image';
        $ui = new base_component_ui($this);
        $domid = $ui->new_dom_id();
        
        $input_name = $params['name'];
        $input_value = $params['value'];
      
        $image_src = base_storager::image_path($input_value,'s');
        
        
        
        if(!$params['width']){
           $params['width']=50;
        }
        
        if(!$params['height']){
         $params['height']=50;
        }
        $url=app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_tools','act'=>'alertpages'));
        $urlgoto=app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_tools','act'=>'image_broswer'));
        $url="&quot;".$url.'?dd=&quot;+Date.now()+&quot;&goto='.urlencode($urlgoto)."&quot;";
        $imageInputWidth = $params['width']+24;        
        $html = '<div class="image-input clearfix" style="width:'.$imageInputWidth.'px;" gid="'.$domid.'">';
            $html.= '<div class="flt"><div class="image-input-view" style="font-size:12px;text-align:center;width:';
            $html.=  $params['width'].'px;line-height:'.$params['height'].'px;height:'.$params['height'].'px;overflow:hidden;">';
            if(!$image_src){
                $image_src = app::get('desktop')->res_url.'/transparent.gif';
            }
            $html.= '<img src="'.$image_src.'" onload="$(this).zoomImg('.$params['width'].','.$params['height'].',function(mw,mh,v){this.setStyle(&quot;marginTop&quot;,(mh-v.height)/2)});"/>';
                          
            
            $html.= '</div></div>';
            $html.= '<div class="image-input-handle" onclick="Ex_Loader(&quot;modedialog&quot;,function(){new imgDialog('.$url.',{handle:this});}.bind(this));" style="width:20px;height:'.$params['height'].'px;">'.app::get('desktop')->_('选择')."".$ui->img(array('src'=>'bundle/arrow-down.gif','app'=>'desktop'));
            $html.= '</div>';
            $html.= '<input type="hidden" name="'.$input_name.'" value="'.$input_value.'"/>';
            $html.= '</div>';
            
        
        
        return $html;
    }
    function input_vhtml($params){
        if(defined('EDITOR_ALL_SOUCECODE')&&EDITOR_ALL_SOUCECODE){
			$params['width'] = $params['width']?$params['width']:'100%';
			$params['height'] = $params['height']?$params['height']:'100%';
			$html = "<div class='input-soucecode-panel' style='border:1px #e9e9e9 solid;background:#fff;height:300px;'>";
			$html.=$this->input_soucecode($params);
			$html.= "</div>";
			
			return $html;
		}
		
		
        $id = 'mce_'.substr(md5(rand(0,time())),0,6);
        
        $editor_type=app::get('desktop')->getConf("system.editortype");
        $editor_type==''?$editor_type='wysiwyg':$editor_type='wysiwyg';
        $includeBase=$params['includeBase']?$params['includeBase']:true;
        $params['id']=$id;
        $img_src = app::get('desktop')->res_url;
        
        $cimg_src = app::get('business')->res_url;

        $render = new base_render(app::get('desktop'));
        
        $render->pagedata['id'] = $id;
        $render->pagedata['img_src'] = $img_src;
        $render->pagedata['includeBase'] = $includeBase;
        $render->pagedata['params'] = $params;
        $render->pagedata['res_url']=$cimg_src;
        $render->pagedata['shop_base'] = app::get('business')->base_url();
        $style2=$render->fetch('site/tools/editor/html_style2.html','business');
        //$style2="";
        if($editor_type =='textarea'||$params['editor_type']=='textarea'){
            $html=$style2;
        }else{
            $style1 = $render->fetch('site/tools/editor/html_style1.html','business');
            $html=$style1;
            $html.=$style2;
        }
        return $html;
    }
    
    function input_vobject($params){
        $url=app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_tools','act'=>'object_rows'));
        $return_url = $params['return_url']?$params['return_url']:$url;
        $callback = $params['callback']?$params['callback']:'';
        $init = $params['data']['init']?$params['data']['init']:'';
        $params['breakpoint'] = isset($params['breakpoint'])?$params['breakpoint']:20;

        $object = $params['object'];
        if(strpos($params['object'],'@')!==false){
            list($object,$app_id) = explode('@',$params['object']);
            $params['object'] = $object;
        }elseif($params['app']){
            $app_id = $params['app'];
        }else{
            $app_id = $this->app->app_id;
        }

        $app = app::get($app_id);        
        $o = $app->model($object);
        $render = new base_render(app::get('business'));
        $ui = new base_component_ui($app);


        $dbschema = $o->get_schema();

        $params['app_id'] = $app_id;

        if(isset($params['filter'])){
            if(!is_array($params['filter'])){
                parse_str($params['filter'],$params['filter']);
            }
        }

        $params['domid'] = substr(md5(uniqid()),0,6);

        $key = $params['key']?$params['key']:$dbschema['idColumn'];
        $textcol = $params['textcol']?$params['textcol']:$dbschema['textColumn'];
        
        
        //显示列 可以多列显示 不完全修改 。。。。。。。 
        $textcol = explode(',',$textcol);
        $_textcol = $textcol;
        $textcol = $textcol[0];


        $tmp_filter = $params['filter']?$params['filter']:null;
        $count = $o->count($tmp_filter);
        if($count<=$params['breakpoint']&&!$params['multiple']&&$params['select']!='checkbox'){
            if(strpos($textcol,'@')===false){
                $list = $o->getList($key.','.$textcol,$tmp_filter);
                if(!$list[0]) $type=array();
                foreach($list as $row){
                    $label = $row[$textcol];
                    if(!$label&&method_exists($o,'title_modifier')){
                        $label = $o->title_modifier($row[$key]);
                    }
                    $type[$row[$key]] = $label;
                }
                
            }else{
                list($name,$table,$app_id) = explode('@',$textcol);
                $app = $app_id?app::get($app_id):$app;
                $mdl = $app->model($table);
                $list = $o->getList($key,$tmp_filter);
                foreach($list as $row){
                    $tmp_row = $mdl->getList($name,array($mdl->idColumn=>$row[$key]),0,1);
                    $label = $tmp_row[0][$name];
                    if(!$label&&method_exists($o,'title_modifier')){
                        $label = $o->title_modifier($row[$key]);
                    }
                    $type[$row[$key]] = $label;
                }

            }
            $tmp_params['name'] = $params['name'];
            $tmp_params['value'] = $params['value'];
            $tmp_params['type'] = $type;
            if($callback)
                $tmp_params['onchange'] = $callback.'(this)';
            $str_filter = $ui->input($tmp_params);
            unset($tmp_params);
            return $str_filter;

        }

        $params['idcol'] = $keycol['keycol'] = $key;
        $params['textcol'] = implode(',',$_textcol);
        
        $params['_textcol'] = $_textcol;
        if($params['value']){
            if(strpos($params['view'],':')!==false){
                list($view_app,$view) = explode(':',$params['view']);
                $params['view_app'] = $view_app;
                $params['view'] = $view;
            }
            if(is_string($params['value'])){
                $params['value'] = explode(',',$params['value']);
            }
            $params['items'] = &$o->getList('*',array($key=>$params['value']),0,-1);
            
            //过滤不存在的值
            //某些数据被添加后 可能原表数据已删除，但此处value中还存在。
            $_params_items_row_key = array();
            foreach( $params['items'] as $_params_items_row ) {
                $_params_items_row_key[] = $_params_items_row[$key];
            }
            $params['value'] = implode(',',$_params_items_row_key);
        }
        
        if(isset($params['multiple']) && $params['multiple']){
            if(isset($params['items']) && count($params['items'])){
                $params['display_datarow'] = 'true';
            }
            $render->pagedata['_input'] = $params;
            $render->pagedata['desktop_res_url'] = app::get('desktop')->res_url;
            $render->pagedata['domid'] = "list_datas_".$params['domid'];
            return $render->fetch('site/tools/input.html','business');
        }else{
            if($params['value'] && $params['select'] != 'checkbox'){
                $string = $params['items'][0][$textcol];
            }else{
                $string = $params['emptytext']?$params['emptytext']:app::get('desktop')->_('请选择...');
            }
            $str_app = $params['app'];
            unset($params['app']);

            if($params['data']){
                $_params = (array)$params['data'];
                unset($params['data']);
                $params = array_merge($params,$_params);
            }

            if($params['select']=='checkbox'){
                if($params['default_id'] ) $params['domid'] = $params['default_id'];
                $params['type'] = 'checkbox';
            }else{
                $id = "handle_".$params['domid'];
                $params['type'] = 'radio';
                $getdata = '&singleselect=radio';
            }
            if(is_array($params['items'])){
                foreach($params['items'] as $key=>$item){
                    $items[$key] = $item[$params['idcol']];
                }
            }
            $params['return_url'] = urlencode($return_url);
            $vars = $params;
            $vars['items'] = $items;
            
            $object = utils::http_build_query($vars);
            
            //$url = 'btools-alertpages.html?goto='.urlencode('beditor-finder_common.html?app_id='.$app_id.'&'.$object.$getdata).'&sign=site';
            $url = 'shopadmin?app=desktop&act=alertpages&goto='.urlencode('index.php?app=desktop&ctl=editor&act=finder_common&sign=site&app_id='.$app_id.'&'.$object.$getdata).'&sign=site';
           
            $render->pagedata['string'] = $string;
            $render->pagedata['url'] = $url;
            $render->pagedata['app'] = 'app='.$str_app;
            $render->pagedata['return_url'] = $return_url;
            $render->pagedata['id'] = $id;
            $render->pagedata['params'] = $params;
            $render->pagedata['object'] = $object;
            $render->pagedata['callback'] = $callback;
            $render->pagedata['init'] = $init;
            return $render->fetch('site/tools/input_radio.html','business');
        }
    }
    
    function input_vgoodsfilter($params){

        $render = new base_render(app::get('b2c'));

        $obj_type = app::get('b2c')->model('goods_type');
        
        $store_id = (isset($params['shop']) && !empty($params['shop']))?$params['shop']:0;
        $region_id = (isset($params['region']) && !empty($params['region']))?(array)$params['region']:array(0);
        $objBGoods = &app::get('business')->model('goods');
        $obj_cat = &app::get('b2c')->model('goods_cat');
        $aCatId = $objBGoods->getCats($region_id);
        $catinfo = $obj_cat->getList('type_id', array('cat_id|in'=>$aCatId['cat_id']));
        $type_ids = array();
        foreach($catinfo as $items){
            $type_ids[$items['type_id']] = $items['type_id'];
        }

        $input_name = $params['name'];

        parse_str($params['value'],$value);

        $params =array(
                'gtype'=>$obj_type->getList('*',array('type_id|in'=>$type_ids),0,-1),
                'view' => 'admin/goods/finder_filter.html',
                'params' => $params['params'],
                'json' => json_encode($data),
                'data' => $value,
                'from'=>$params['value'],
                'domid' => substr(md5(rand(0,time())),0,6),
                'name' =>$input_name
        );
        $type_id = '_ANY_';
        $params['value'] = $value;
        if($params['value']['type_id']) $type_id = $params['value']['type_id'];

        $render->pagedata['params'] = $params;
        $goods_filter = kernel::single('business_member_goodsfilter');
        $return = $goods_filter->member_goodsfilter($type_id,$aCatId['cat_id'],$type_ids,app::get('b2c'));
        
        $oBBrand = app::get('business')->model('brand');
        $aBBrand = array();
        foreach((array)$oBBrand->getList('brand_id', array('store_id'=>$store_id,'status'=>'1'),0,-1) as $rows){
            $aBBrand[] = $rows['brand_id'];
        }
        $return['brands'] = app::get('b2c')->model('brand')->getList('*',array('brand_id'=>$aBBrand),0,-1);
        
        $render->pagedata['filter'] = $return;
        $member_info = kernel::single('business_ctl_site_member');
        if($member_info->issue_type == 1 || $member_info->issue_type == 3){
            $render->pagedata['filter']['brands'] = $member_info->store_brand;
        }
        $render->pagedata['type_id'] = $type_id;
        $render->pagedata['filter_items'] = array();
        foreach(kernel::servicelist('goods_filter_item') as $key=>$object){
            
            if(is_object($object)&&method_exists($object,'get_item_html')){
                $render->pagedata['filter_items'][] = $object->get_item_html();
            }
        }

        return $render->fetch('site/goods/goods_filter.html','business');
    }
    
    public function input_storepoint($params)
    {   
        $objComment = app::get('business')->model('comment_stores_point');
        $store_info = $objComment->getStoreInfo($params['store']);
     
		$arr_number=app::get('business')->model('customer_service')->getList('number,type',array('store_id'=>$params['store'],'is_default'=>'1'));
		$number=$arr_number[0];
		$store_id=$params['store'];
 
        if($params['show_name'] && $params['show_name']=='true'){
            $list = "<div id=\"store_title\" class=\"det_title\" title=\"{$store_info['store_name']}\">";
         //   $member_info = kernel::single('b2c_frontpage')->get_current_member();
            $dir = strtolower(kernel::request()->get_schema()).'://'.kernel::request()->get_host().app::get('business')->res_url;
            if(!$number || !$store_id ){
                $list .= "<span style=\"cursor:pointer;float:left;height: 50px; \">{$store_info['store_name']}</span>";
            }else{
                $list .= "<span style=\"cursor:pointer;float:left;height: 50px;width:84px;overflow:hidden; \">{$store_info['store_name']}</span><span style=\"cursor:pointer;float:right;margin-top:14px; \" id=\"szmall_{$params['id']}\" >";
				
                if($number['type']=='ww'){
				    $list.="<a target='_blank' href='http://amos.im.alisoft.com/msg.aw?v=2&uid={$number['number']}&site=cntaobao&s=1&charset=utf-8' ><img border='0' src='http://amos.im.alisoft.com/online.aw?v=2&uid={$number['number']}&site=cntaobao&s=1&charset=utf-8'/></a>";
				}elseif($number['type']=='qq'){
				    $list.="<a target='_blank' href='http://wpa.qq.com/msgrd?v=3&uin={$number['number']}&site=qq&menu=yes'><img border='0' src='http://wpa.qq.com/pa?p=2:{$number['number']}:51' alt='点击这里给我发消息' title='点击这里给我发消息'/></a>";
				}
				$list.="</span>";
			}	
			$list .= "</div>";
        }else{
            $list = "";
		}
        $list .= "<p class=\"shopdsr-title\">店铺动态评分<span>与同行业相比</span></p><ul class=\"shopdsr-con\">";
        foreach($store_info['store_point'] as $item){
            $list .= "<li><span class='de_score'>{$item['name']}：</span><span class=\"sdc-num\">{$item['avg_point']}</span>";
            if($item['avg_percent'] > 0)
                $list .= "<b class=\"sdc-low\">低于</b>";
            elseif($item['avg_percent'] < 0)
                $list .= "<b class=\"sdc-high\">高于</b>";
            else
                $list .= "<b class=\"sdc-low\">持平</b>";
            $list .= "<span class=\"sdc-low\">".abs($item['avg_percent'])."%</span></li>";
        }
        return $str = <<<EOD
        <div class="shopheader-dsr p_0 border_b">
          {$list}
        </ul>
        </div>
EOD;
    }
    
    public function header_shop_set_extends(&$arr_shop){
        $shop = json_decode($arr_shop,true);
        $shop['url']['fav_store'] = app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_favorite','act'=>'ajax_fav_store'));
        $arr_shop = json_encode($shop);
    }

    /**
     * input_storegoodsfilter description 店铺商品筛选
     * @author 曹辰吟
     * @param  [array] $params 参数
     * @return [null]
     */
    function input_storegoodsfilter($params){

        $member_id = $params['member_id'];
        $store = kernel::single('business_memberstore',$member_id)->storeinfo;
        
        $brands = $store['store_brand'];

        $store_id = $store['store_id'];
        $mdl_goods_cat = app::get('business')->model('goods_cat');
        // $cats = $mdl_goods_cat->getList('*',array('store_id'=>$store_id),0,-1,'p_order');
        $cats = $mdl_goods_cat->db->select("select a.cat_name,a.custom_cat_id AS cat_id,(CASE WHEN b.p_order is null THEN 1 ELSE 2 END) AS step,(CASE WHEN b.p_order is null THEN CONCAT(a.p_order,',','0') ELSE CONCAT(b.p_order,',',a.p_order) END) AS ob from sdb_business_goods_cat a LEFT JOIN sdb_business_goods_cat b ON a.parent_id = b.custom_cat_id WHERE a.store_id=".intval($store_id)." ORDER BY ob,a.parent_id");
        $filter['brands'] = $brands;
        $filter['cats'] = $cats;

        parse_str($params['value'],$value);
        $params =array(
                'view' => 'admin/goods/finder_filter.html',
                'params' => $params['params'],
                'json' => json_encode($data),
                'data' => $value,
                'from'=> $params['value'],
                'domid' => substr(md5(rand(0,time())),0,6),
        );
   
        $render = new base_render(app::get('b2c'));
        $render->pagedata['filter'] = $filter;
        $render->pagedata['params'] = $params;

        return $render->fetch('site/store/store_goods_filter.html','business');
    }
    
    function input_fixation($params){
        $mdl_store = app::get('business')->model('storemanger');
        $data_info = array();
        $member_info = kernel::single('b2c_frontpage')->get_current_member();
        //if(!$member_info['member_id']) return '';
        $dir = strtolower(kernel::request()->get_schema()).'://'.kernel::request()->get_host().app::get('business')->res_url;
        if(isset($params['account']) && !empty($params['account'])){
            $data_info = $mdl_store->db->selectrow("select m.im_webcall,s.store_id from sdb_b2c_members as m join sdb_business_storemanger as s on m.member_id=s.account_id where m.im_webcall='{$params['account']}'");
        }elseif(isset($params['store_id']) && !empty($params['store_id'])){
            $data_info = $mdl_store->db->selectrow("select m.im_webcall,s.store_id from sdb_b2c_members as m join sdb_business_storemanger as s on m.member_id=s.account_id and s.store_id=".intval($params['store_id']));
        }else{
            return "<span style='cursor:pointer;' id='szmall_{$params['id']}' >
                <img src='{$dir}/images/superCat1.png' />
                </span>";
        }
        $account = $data_info['im_webcall'];
        $store_id = $data_info['store_id'];
        if(!$account || !$store_id || app::get('b2c')->getConf('webcall.service.enabled') != 'true'){
            return '';
        }else{
            $account = urlencode($account);
            $host = defined('WEBCALL_HOST')?WEBCALL_HOST:'';
            $str = '';
            
            switch(intval($params['position'])){
                case 0:
                // 商品列表页客服代码
                $str = <<<EOD
                <span id="szmall_{$params['id']}" style="cursor: pointer">
                <img src="{$dir}/images/superCat.gif" />
                <img src="{$dir}/images/superCat1.png" style="display:none;" />
                </span>
EOD;
                $str .= !$member_info['member_id']?
                    "<script type='text/javascript' src='{$host}/IMMeForPartner3.aspx?email={$account}&accountid=B2B2C.szmall.com&IMME_Icon=szmall_{$params['id']}'></script>".
                    "<script>if($('szmall_{$params['id']}'))$('szmall_{$params['id']}').removeEvents('click').addEvent('click',function(e){Message.error('请先登录'),location.href='".app::get('site')->router()->gen_url(array('app' => 'b2c', 'ctl' => 'site_passport', 'act' => 'login', 'arg' =>''))."';});</script>":
                    "<script type='text/javascript' src='{$host}/IMMeForPartner2.aspx?email={$account}&accountid=B2B2C.szmall.com&IMME_Icon=szmall_{$params['id']}'></script>";
                break;
                case 1:
                // 商品详细页和店铺首页客服代码
                $str = <<<EOD
                <span style="cursor:pointer; " id="szmall_{$params['id']}" >
                <img src="{$dir}/images/superCat.gif" />
                <img src="{$dir}/images/superCat1.png" style="display:none;" />
                </span>
EOD;
                $str .= !$member_info['member_id']?
                    "<script type='text/javascript' src='{$host}/IMMeForPartner3.aspx?email={$account}&accountid=B2B2C.szmall.com&LL=0&IMME_Icon=szmall_{$params['id']}&noImg=2' ></script>".
                    "<script>if($('szmall_{$params['id']}'))$('szmall_{$params['id']}').removeEvents('click').addEvent('click',function(e){Message.error('请先登录'),location.href='".app::get('site')->router()->gen_url(array('app' => 'b2c', 'ctl' => 'site_passport', 'act' => 'login', 'arg' =>''))."';});</script>":
                    "<script type='text/javascript' src='{$host}/IMMeForPartner.aspx?email={$account}&accountid=B2B2C.szmall.com&LL=0&IMME_Icon=szmall_{$params['id']}&noImg=2' ></script>";
                break;
                case 2:
                // 店铺首页顶部客服代码
                $str = <<<EOD
                <span style="cursor:pointer; " id="szmall_{$params['id']}" >
                <img src="{$dir}/images/superCat2.png" />
                <img src="{$dir}/images/superCat3.png" style="display:none;" />
                </span>
EOD;
                $str .= !$member_info['member_id']?
                    "<script type='text/javascript' src='{$host}/IMMeForPartner3.aspx?email={$account}&accountid=B2B2C.szmall.com&LL=0&IMME_Icon=szmall_{$params['id']}&noImg=2' ></script>".
                    "<script>if($('szmall_{$params['id']}'))$('szmall_{$params['id']}').removeEvents('click').addEvent('click',function(e){Message.error('请先登录'),location.href='".app::get('site')->router()->gen_url(array('app' => 'b2c', 'ctl' => 'site_passport', 'act' => 'login', 'arg' =>''))."';});</script>":
                    "<script type='text/javascript' src='{$host}/IMMeForPartner.aspx?email={$account}&accountid=B2B2C.szmall.com&LL=0&IMME_Icon=szmall_{$params['id']}&noImg=2' ></script>";
                break;
            }
            return $str;
        }
    }
    
    function input_object_select($params){
        $return_url = $params['return_url']?$params['return_url']:'index.php?app=desktop&ctl=editor&act=object_rows'; 
        $callback = $params['callback']?$params['callback']:'';
        $init = $params['data']['init']?$params['data']['init']:'';
        $params['breakpoint'] = isset($params['breakpoint'])?$params['breakpoint']:20;

        $object = $params['object'];
        if(strpos($params['object'],'@')!==false){
            list($object,$app_id) = explode('@',$params['object']);
            $params['object'] = $object;
        }elseif($params['app']){
            $app_id = $params['app'];
        }else{
            $app_id = app::get('b2c')->app_id;
        }

        $app = app::get($app_id);        
        $o = $app->model($object);
        $render = new base_render(app::get('business'));
        $ui = new base_component_ui($app);


        $dbschema = $o->get_schema();

        $params['app_id'] = $app_id;

        if(isset($params['filter'])){
            if(!is_array($params['filter'])){
                parse_str($params['filter'],$params['filter']);
            }
        }

        $params['domid'] = substr(md5(uniqid()),0,6);

        $key = $params['key']?$params['key']:$dbschema['idColumn'];
        $textcol = $params['textcol']?$params['textcol']:$dbschema['textColumn'];
        
        
        //显示列 可以多列显示 不完全修改 。。。。。。。 
        $textcol = explode(',',$textcol);
        $_textcol = $textcol;
        $textcol = $textcol[0];


        $tmp_filter = $params['filter']?$params['filter']:null;
        $count = $o->count($tmp_filter);
        if($count<=$params['breakpoint']&&!$params['multiple']&&$params['select']!='checkbox'){
            if(strpos($textcol,'@')===false){
                $list = $o->getList($key.','.$textcol,$tmp_filter);
                if(!$list[0]) $type=array();
                foreach($list as $row){
                    $label = $row[$textcol];
                    if(!$label&&method_exists($o,'title_modifier')){
                        $label = $o->title_modifier($row[$key]);
                    }
                    $type[$row[$key]] = $label;
                }
                
            }else{
                list($name,$table,$app_id) = explode('@',$textcol);
                $app = $app_id?app::get($app_id):$app;
                $mdl = $app->model($table);
                $list = $o->getList($key,$tmp_filter);
                foreach($list as $row){
                    $tmp_row = $mdl->getList($name,array($mdl->idColumn=>$row[$key]),0,1);
                    $label = $tmp_row[0][$name];
                    if(!$label&&method_exists($o,'title_modifier')){
                        $label = $o->title_modifier($row[$key]);
                    }
                    $type[$row[$key]] = $label;
                }

            }
            $tmp_params['name'] = $params['name'];
            $tmp_params['value'] = $params['value'];
            $tmp_params['type'] = $type;
            if($callback)
                $tmp_params['onchange'] = $callback.'(this)';
            $str_filter = $ui->input($tmp_params);
            unset($tmp_params);
            return $str_filter;
        }

        $params['idcol'] = $keycol['keycol'] = $key;
        $params['textcol'] = implode(',',$_textcol);
        $params['_textcol'] = $_textcol;
        unset($params['app']);

        if($params['data']){
            $_params = (array)$params['data'];
            unset($params['data']);
            $params = array_merge($params,$_params);
        }
		
        if(is_array($params['items'])){
            foreach($params['items'] as $key=>$item){
                $items[$key] = $item[$params['idcol']];
            }
        }
        $params['return_url'] = urlencode($params['return_url']);
        $vars = $params;
        $vars['items'] = $items;
        
        $object = utils::http_build_query($vars);

        $url = 'index.php?app=business&ctl=admin_default&act=finder_object_select';
        
        $render->pagedata['string'] = $string;
        $render->pagedata['url'] = $url;
        $render->pagedata['return_url'] = $return_url;
        $render->pagedata['id'] = $id;
        $render->pagedata['params'] = $params;
        $render->pagedata['object'] = $object;
        $render->pagedata['callback'] = $callback;
        $render->pagedata['init'] = $init;
        $render->pagedata['value'] = $params['value'];
        /** 得到商品的数量 **/
        if ($params['value']){
            $arr_values = json_decode($params['value']);
            $render->pagedata['goods_cnt'] = count($arr_values);
        }
        return $render->fetch('admin/object/input_radio.html');
    }
}
