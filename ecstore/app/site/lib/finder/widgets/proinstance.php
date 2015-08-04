<?php

 

class site_finder_widgets_proinstance
{
    
    public $column_edit = '编辑';
    public $column_edit_width = 50;
    public function column_edit($row){
        return '<a href="index.php?app=site&ctl=admin_widget_proinstance&act=editor&id='.$row['widgets_id'].'" target="open::{width:950,height:700,top:100,left:100}">编辑</a>';
    }

    public $column_code = '代码';
    public $column_code_width = 50;
    public function column_code($row){
        return '<a href="index.php?app=site&ctl=admin_widget_proinstance&act=createcode&id='.$row['widgets_id'].'" target="dialog::{frameable:true, title:\''. app::get('site')->_('挂件代码生成').'\', width:600, height:320}">代码</a>';
    }
}//End Class