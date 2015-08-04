<?php

 
class express_finder_print_tmpl
{
     var $column_edit = '操作';
    function column_edit($row){  
          
        $html = "<a target='_blank'   href=index.php?app=express&ctl=admin_delivery_printer&act=edit_tmpl&p[0]=".$row['prt_tmpl_id']."&finder_id=".$_GET['_finder']['finder_id'].">".app::get('express')->_('编辑')."</a> ";
        $html.= "<a target='_blank' href=index.php?app=express&ctl=admin_delivery_printer&act=add_same&p[0]=".$row['prt_tmpl_id']."&finder_id=".$_GET['_finder']['finder_id'].">".app::get('express')->_('添加相似单据')."</a> ";
        $html.= "<a target='download' href=index.php?app=express&ctl=admin_delivery_printer&act=download&p[0]=".$row['prt_tmpl_id'].">".app::get('express')->_('下载模板')."</a> ";
       return $html;
    }  
    
    function column_edit_width(){
        return "200px";
    }
}