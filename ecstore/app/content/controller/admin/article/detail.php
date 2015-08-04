<?php
/*
 * @package content
 * @subpackage article
 * @author edwin.lzh@gmail.com
 * @license 
 */

class content_ctl_admin_article_detail extends content_admin_controller
{
    
    public function _editor($type=1) 
    {
        switch($type)
        {
            case 1:
                $sections = array(
                    'basic'=>array(
                        'label'=>app::get('content')->_('基本信息'),
                        'options'=>'',
                        'file'=>'admin/article/detail/basic.html',
                    ),
                    'ext'=>array(
                        'label'=>app::get('content')->_('扩展属性'),
                        'options'=>'',
                        'file'=>'admin/article/detail/ext.html',
                    ),
                    'articleimage'=>array(
                        'label'=>app::get('content')->_('文章配图'),
                        'options'=>'',
                        'file'=>'admin/article/detail/articleimage.html',
                    ),
                    'body'=>array(
                        'label'=>app::get('content')->_('文章内容'),
                        'options'=>'',
                        'file'=>'admin/article/detail/body.html',
                    ),
                    'seo'=>array(
                        'label'=>app::get('content')->_('SEO设置'),
                        'options'=>'',
                        'file'=>'admin/article/detail/seo.html',
                    ),
                );
            break;
            case 2:
                $sections = array(
                    'basic'=>array(
                        'label'=>app::get('content')->_('基本信息'),
                        'options'=>'',
                        'file'=>'admin/article/detail/basic.html',
                    ),
                    'seo'=>array(
                        'label'=>app::get('content')->_('SEO设置'),
                        'options'=>'',
                        'file'=>'admin/article/detail/seo.html',
                    ),
                    'single'=>array(
                        'label'=>app::get('content')->_('可视化编辑'),
                        'options'=>'',
                        'file'=>'admin/article/detail/single.html',
                    ),
                );
            break;
            case 3:
                $sections = array(
                    'basic'=>array(
                        'label'=>app::get('content')->_('基本信息'),
                        'options'=>'',
                        'file'=>'admin/article/detail/basic.html',
                    ),
                    'seo'=>array(
                        'label'=>app::get('content')->_('SEO设置'),
                        'options'=>'',
                        'file'=>'admin/article/detail/seo.html',
                    ),
                    'custom'=>array(
                        'label'=>app::get('content')->_('自定义内容'),
                        'options'=>'',
                        'file'=>'admin/article/detail/custom.html',
                    ),
                );
            break;
            default:
        }//End Switch
        
        
        foreach( kernel::servicelist('content_article_detail_sections') as $object ) {
            if( !method_exists($object,'get_section') ) continue;
            $sections = array_merge($sections,$object->get_section());
            
            //设置页面信息 
            if( !method_exists($object,'set_page_data') ) continue;
            $object->set_page_data( $this );
        }
        
        $this->pagedata['sections'] = $sections;
        
        $selectmaps = kernel::single('content_article_node')->get_selectmaps();
        $this->pagedata['selectmaps'] = $selectmaps;
    }//End Function

    public function add() 
    {
        $node_id = $this->_request->get_get('node_id');
        $type = $this->_request->get_get('type');
        $this->_editor($type);
        $article['indexs']['node_id'] = ($node_id > 0) ? $node_id : 0;
        $article['indexs']['type'] = $type;
        $this->pagedata['article'] = $article;
        if($type == 3){
            $this->pagedata['article']['bodys']['content'] = '[header][nav][footer]';
        }
        header("Cache-Control:no-store");
        $this->singlepage('admin/article/detail/editor.html');
    }//End Function

    public function edit() 
    {
        $this->begin('index.php?app=content&ctl=admin_article');
        $article_id = $this->_request->get_get('article_id');
        $article['indexs'] = app::get('content')->model('article_indexs')->dump($article_id, '*');
        if(empty($article['indexs'])) $this->end(false, app::get('content')->_('错误请求'));
        $this->_editor($article['indexs']['type']);
        $article['bodys'] = kernel::single('content_article_detail')->get_body($article_id);
        $goods_info = $article['bodys']['goods_info'];
        $article['ext']['goods']['goodskeywords'] = $goods_info['goodskeywords'];
        $article['ext']['goods']['goodsnums'] = $goods_info['goodsnums'];
        $hot_link = $article['bodys']['hot_link'];
        if(is_array($hot_link)){
            foreach($hot_link['linkwords'] AS $key=>$val){
                $article['ext']['hot'][$key]['linkwords'] = $val;
                $article['ext']['hot'][$key]['linkurl'] = $hot_link['linkurl'][$key];
            }
        }
        $this->pagedata['article'] = $article;
        header("Cache-Control:no-store");
        $this->singlepage('admin/article/detail/editor.html');
    }//End Function

    public function save() 
    {
        $this->begin();
        $dtime = $this->_request->get_post('_DTIME_');
        $post = $this->_request->get_post('article');
        $article_id = $this->_request->get_post('article_id');
        if(!empty($post['indexs']['pubtime'])){
            $post['indexs']['pubtime'] = $post['indexs']['pubtime'] . ' ' . $dtime['H']['article[indexs']['pubtime'] . ':' . $dtime['M']['article[indexs']['pubtime'];
            $post['indexs']['pubtime'] = strtotime($post['indexs']['pubtime']);
        }
        if($post['ext']['enable_goods_info'] > 0){
            $post['bodys']['goods_info'] = $post['ext']['goods'];
        }else{
            $post['bodys']['goods_info'] = '';
        }
        if($post['ext']['enable_hot_link'] > 0){
            $post['bodys']['hot_link'] = $post['ext']['hot'];
        }else{
            $post['bodys']['hot_link'] = '';
        }
        if($article_id > 0){
            $res = app::get('content')->model('article_indexs')->update($post['indexs'], array('article_id'=>$article_id));
            if($res){
                $res = app::get('content')->model('article_bodys')->update($post['bodys'], array('article_id'=>$article_id));
                if($res){
                    $services = kernel::servicelist('content_article_index');
                    foreach($services AS $service){
                        if($service instanceof content_interface_index){
                            $service->update($article_id, $post);
                        }
                    }
                    $this->end(true, app::get('content')->_('保存成功'));
                }else{
                    $this->end(false, app::get('content')->_('保存失败'));
                }                
            }else{  
                $this->end(false, app::get('content')->_('保存失败'));
            }
        }else{
            $res = app::get('content')->model('article_indexs')->insert($post['indexs']);
            if($res){
                $post['bodys']['article_id'] = $res;
                $res = app::get('content')->model('article_bodys')->insert($post['bodys']);
                if($res){
                    $services = kernel::servicelist('content_article_index');
                    foreach($services AS $service){
                        if($service instanceof content_interface_index){
                            $service->insert($post);
                        }
                    }
                    $this->end(true, app::get('content')->_('添加成功'), null, array('id'=>$post['bodys']['article_id']));
                }else{
                    $this->end(false, app::get('content')->_('添加失败'));
                }                
            }else{  
                $this->end(false, app::get('content')->_('添加失败'));
            }
        }
    }//End Function

    public function updatetime() 
    {
        $article_id = $this->_request->get_get('article_id');
        if($article_id > 0){
            app::get('content')->model('article_indexs')->update_time(array('article_id'=>$article_id));
        }
    }//End Function

}//End Class
