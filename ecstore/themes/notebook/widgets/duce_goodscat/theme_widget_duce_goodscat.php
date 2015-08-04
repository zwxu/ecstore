<?php

function theme_widget_duce_goodscat(&$setting, &$system) {
    if($system->_request){
        $setting["show"] = ($system->_request->get_ctl_name() == "default" && $system->_request->get_act_name() == "index" && $system->_request->get_app_name() == "site") ? "index" : "";
    }
    if(get_class($system) == 'site_admin_render'){//复制移动上传的图片至指定路径
        if(isset($setting['newCatPic']) && $setting['newCatPic']== 1 && !empty($setting['cat_pic'])){
            $dir = explode('images',$setting['cat_pic']);
            $dir = explode('?',$dir[1]);
            $cat_pic_dir = PUBLIC_DIR.'/images'.$dir[0];
            if(file_exists($cat_pic_dir)){
                $new_dir = THEME_DIR.'/simple/images/indexicon/iicon.png';
                copy($cat_pic_dir,$new_dir);
            }
        }
    }
    $mdl_goodsCat = app::get('b2c')->model('goods_cat');
    $mdl_goodsVirtualCat = app::get('b2c')->model('goods_virtual_cat');
    $mdl_brand = app::get('b2c')->model('brand');
    foreach($setting['cat'] as $k=>$v){
        $gCat = $mdl_goodsCat->dump($v['cat_id']);
        $setting['cat'][$k]['hidden'] = $gCat['hidden'];
        //处理图片路径
        $setting['cat'][$k]['cat_pic']['pic'] = url_replace($v['cat_pic']['pic']);
        if(is_array($v['ad'])){
            foreach($v['ad'] as $key=>$value){
                $setting['cat'][$k]['ad'][$key]['pic'] = url_replace($value['pic']);
            }
        }

        //获取2,3级分类
        $children = getTreeListCat($v['cat_id']);
        $setting['cat'][$k]['children'] = $children;

        //获取子分类id
        $children_cat_ids = array();
        foreach($children as $key=>$value){
            $children_cat_ids[] = $value['cat_id'];
        }
        unset($children);

        //获取虚拟分类
        if(is_array($v['virtualcat1'])){
            foreach($v['virtualcat1'] as $key=>$value){
                $virtualcat = $mdl_goodsVirtualCat->dump(array('virtual_cat_id'=>$value),'*');
                $setting['cat'][$k]['virtualcat1'][$key] = $virtualcat;
            }
        }

        //获取虚拟分类
        if(is_array($v['virtualcat2'])){
            foreach($v['virtualcat2'] as $key=>$value){
                $virtualcat = $mdl_goodsVirtualCat->dump(array('virtual_cat_id'=>$value),'*');
                $setting['cat'][$k]['virtualcat2'][$key] = $virtualcat;
            }
        }

        $children_cat_ids[] = $v['cat_id'];
        //获取推荐品牌
        $brands = $mdl_brand->getBrandByCatId($children_cat_ids,$setting['cat_brand_num']);
        if(!empty($brands)){
            $brand_ids = array();
            foreach($brands as $key=>$value){
                if(!in_array($value['brand_id'],$brand_ids)){
                    $brand_ids[] = $value['brand_id'];
                    $args = array( 
                        '',
                        '',//urlencode('b,'.$v['brand_id'].'_'),
                        1,
                        0,
                        1,
                        '',
                        'grid',
                        'g'
                    );
                    $bfilter = array(
                                'marketable' => true,
                                'store_id'=> array(),
                                'cat_id' => array(),
                                'goods_type' => 'normal'
                              );
                    $brands[$key]['args'] = $args;
                    $brands[$key]['args1'] = $args[1];
                    $args[1] = null;
                    $brands[$key]['args2'] = $args;
                    $brands[$key]['column_id'] = 'brand_id';
                    $brands[$key]['bfilter'] = $bfilter;
                }else{
                    unset($brands[$key]);
                }
                
            }
            $setting['cat'][$k]['brand'] = $brands;
        }
        unset($brands);
    }
    return $setting;
    
}

/**
  *根据一级分类获取2，3级分类
  *并分别放于children键值下
  **/
function getTreeListCat($cat_id){
    $mdl_goodsCat = app::get('b2c')->model('goods_cat');
    $children = $mdl_goodsCat->get_subcat_list($cat_id);
    foreach($children as $k=>$v){
        $children_2 = $mdl_goodsCat->get_subcat_list($v['cat_id']);
        if(!empty($children_2)){
            $children[$k]['children'] = $children_2;
        }
    }

    return $children;
}

function url_replace($url){
    $root_dir = kernel::base_url();
    $result = str_replace('%THEME%',$root_dir,$url);
    return $result;
}
?>
