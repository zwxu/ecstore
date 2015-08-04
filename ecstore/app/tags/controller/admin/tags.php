<?php


class tags_ctl_admin_tags extends desktop_controller{
	var $url = 'index.php?app=tags&ctl=admin_tags';

    public function index(){
    	$url_params = '&wg='.urlencode($_GET['wg']).'&type='.urlencode($_GET['type']).'&app_id='.urlencode($_GET['app_id']);
        $params = array(
            'title'   => app::get('desktop')->_('标签管理'),
            'actions' => array(
                array(
                    'label' => app::get('desktop')->_('新建普通标签'),
                    'icon'  => 'add.gif',
                    'href'  => $this->url . '&act=tag_add' . $url_params,'target'=>'dialog::{title:\''.app::get('desktop')->_('新建普通标签').'\'}'
                ),
            ),
            'base_filter'=>array(
                'tag_type'=>$_GET['type']
            ),
            'use_buildin_new_dialog'=>false,
            'use_buildin_set_tag'=>false,
            'use_buildin_recycle'=>true,
            'use_buildin_export'=>false,
        );
        $this->finder( 'desktop_mdl_tag', $params );
    }

    public function tag_edit( $id ){
       $this->url = 'index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&app_id='.$_GET['app_id'];
       $render =  app::get('desktop')->render();
       $mdl_tag = app::get('desktop')->model('tag');
       $tag = $mdl_tag->dump($id,'*');
       $this->pagedata['data'] = $tag;
       $this->page('edittag.html');
    }

    public function tag_add(){
        if ( $_POST ){
        	$this->begin();
            $obj_tag = app::get( 'desktop' )->model( 'tag' );
            if( empty($_POST['tag_name']) ){
                $this->end( false, app::get('tags')->_('标签名不能为空,保存失败') );
            }

            $arrTags = $obj_tag->getList('tag_name',array('tag_name'=>$_POST['tag_name']) );
            if(!$_POST['tag_id'] && $arrTags){
                $this->end( false, app::get('tags')->_('标签名'.$_POST['tag_name'].'已存在,保存失败') );
            }else{
                $tag_name = $obj_tag->getList('tag_name',array('tag_id'=>$_POST['tag_id']) );
                if( $tag_name[0]['tag_name'] != $_POST['tag_name'] && $arrTags){
                    $this->end( false, app::get('tags')->_('标签名'.$_POST['tag_name'].'已存在,保存失败') );
                }
            }

            $data = array(
                'tag_id'     => $_POST['tag_id'],
                'tag_name'   => $_POST['tag_name'],
                'tag_abbr'   => $_POST['tag_abbr'],
                'tag_type'   => $_POST['tag_type'],
                'app_id'     => $_POST['app_id'],
                'tag_model'  => 'normal',
                'tag_fgcolor'=> $_POST['tag_fgcolor'],
				'tag_bgcolor'=> $_POST['tag_bgcolor'],
                'params'     => $_POST['params'],
            );

            $obj_tag->save( $data );
            $this->end( true, app::get('tags')->_('保存成功') );
        }
        else {
        	if ( $_GET['type'] ){
        	    $data['tag_type'] = $_GET['type'];
        	    $data['app_id']   = $_GET['app_id'];
        	    $this->pagedata['data'] = $data;
        	}
            $this->page('edittag.html');
        }
    }
}
