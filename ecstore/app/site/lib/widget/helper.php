<?php



class site_widget_helper
{
    function function_header(){
        $ret='<base href="'.kernel::base_url(1).'"/>';
        $path = app::get('site')->res_full_url;

		$css_mini = (defined('DEBUG_CSS') && constant('DEBUG_CSS')) ? '' : '_mini';
        $ret.= '<link rel="stylesheet" href="'.$path.'/css'.$css_mini.'/framework.css" type="text/css" />';
        $ret.='<link rel="stylesheet" href="'.$path.'/css'.$css_mini.'/widgets_edit.css" type="text/css" />';

        $ret.=kernel::single('base_component_ui')->lang_script(array('src'=>'lang.js', 'app'=>'site'));
        if(defined('DEBUG_JS') && constant('DEBUG_JS')){
            $ret.= '<script src="'.$path.'/js/mootools.js"></script>
            <script src="'.$path.'/js/moomore.js"></script>
            <script src="'.$path.'/js/jstools.js"></script>
            <script src="'.$path.'/js/coms/switchable.js"></script>
            <script src="'.$path.'/js/dragdropplus.js"></script>
            <script src="'.$path.'/js/widgetsinstance.js"></script>';
        }else{
            $ret.= '<script src="'.$path.'/js_mini/moo_min.js"></script>
				<script src="'.$path.'/js_mini/tools_min.js"></script>
				<script src="'.$path.'/js_mini/ui_min.js"></script>
                <script src="'.$path.'/js_mini/widgetsinstance_min.js"></script>';
        }

        if($theme_info=(app::get('site')->getConf('site.theme_'.app::get('site')->getConf('current_theme').'_color'))){
            $theme_color_href=kernel::base_url(1).'/themes/'.app::get('site')->getConf('current_theme').'/'.$theme_info;
            $ret.="<script>
            window.addEvent('domready',function(){
                new Element('link',{href:'".$theme_color_href."',type:'text/css',rel:'stylesheet'}).injectBottom(document.head);
             });
            </script>";
        }
        /*$ret .= '<script>
					window.addEvent(\'domready\',function(){(parent.loadedPart[1])++});
                    parent.document.getElementById(\'loadpart\').style.display="none";
                    parent.document.getElementById(\'body\').style.display="block";
                </script>';
*/
        foreach(kernel::servicelist('site_theme_view_helper') AS $service){
            if(method_exists($service, 'function_header')){
                $ret .= $service->function_header();
            }
        }

        return $ret;
    }

    function function_footer(){
        return '<div id="drag_operate_box" class="drag_operate_box" style="visibility:hidden;">
		<div class="drag_handle_box">
		  <table cellpadding="0" cellspacing="0" width="100%">
			<tr>
			  <td width="117" align="left" style="text-align:left;"><span class="add-widgets-wrap"><a class="btn-operate btn-edit-widgets" title="'.app::get('site')->_('编辑此挂件').'">'.app::get('site')->_('编辑').'</a><!--<a class="btn-operate btn-save-widgets">'.app::get('site')->_('另存为样例').'</a>--> <a class="btn-operate btn-add-widgets" id="btn_add_widget"><i class="icon"></i>'.app::get('site')->_('添加挂件').'</a><ul class="widget-drop-menu" id="add_widget_dropmenu"><li class="before" title="'.app::get('site')->_('添加到此挂件上方').'">'.app::get('site')->_('添加到上方').'</li><li class="after" title="'.app::get('site')->_('添加到此挂件下方').'">'.app::get('site')->_('添加到下方').'</li></ul></span></td>
			  <td class="operate-title" style="_width:85px;" align="center"><a class="btn-operate btn-up-slot" title="'.app::get('site')->_('上移').'">&#12288;</a> <a class="btn-operate btn-down-slot" title="'.app::get('site')->_('下移').'">&#12288;</a></td>
			  <td width="36" align="right" style="text-align:right;"><a class="btn-operate btn-del-widgets" title="'.app::get('site')->_('删除此挂件').'">'.app::get('site')->_('删除').'</a></td>
			</tr>
		  </table>
		</div>
		</div>
		<div id="drag_ghost_box" class="drag_ghost_box" style="visibility:hidden"></div>
		<script>new top.DropMenu($("btn_add_widget"), {menu:$("add_widget_dropmenu"),eventType:"mouse",offset:{x:-1, y:0},temppos:true,relative:$$(".add-widgets-wrap")[0]});</script>';
    }


}//End Class
