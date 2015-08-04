<?php


/*
 * @package content
 * @subpackage article
 * @author edwin.lzh@gmail.com
 * @license 
 */

class content_ctl_admin_article extends content_admin_controller 
{
    
    var $workground = 'site.wrokground.theme';

    public function index() 
    {
        $this->finder('content_mdl_article_indexs', array(

            'title'=>app::get('content')->_('页面列表'),

            'use_buildin_set_tag' => true,
            'use_buildin_filter' => true,
            'actions'=>array(
                            array('label'=>app::get('content')->_('添加文章'),'icon'=>'add.gif','href'=>'index.php?app=content&ctl=admin_article_detail&act=add&type=1','target'=>'_blank'),
                            array('label'=>app::get('content')->_('添加单独页'),'icon'=>'add.gif','href'=>'index.php?app=content&ctl=admin_article_detail&act=add&type=2','target'=>'_blank'),
                            array('label'=>app::get('content')->_('添加自定义页'),'icon'=>'add.gif','href'=>'index.php?app=content&ctl=admin_article_detail&act=add&type=3','target'=>'_blank'),
                        )
            ));
    }//End Function

}//End Class
