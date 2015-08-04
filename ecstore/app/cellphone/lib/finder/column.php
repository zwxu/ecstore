<?php

class cellphone_finder_column{
 
    function __construct($app){
        $this->app = $app;
        $this->router = app::get('desktop')->router();
        $this->column = $this->app->model('column');
    }//End
    

 	var $column_edit = '操作';
	public $column_edit_width = 110;

    function column_edit($row){


        $row = $this->column->getList('*',array('column_id'=>$row['column_id']));
        $row = $row[0];
		$html = '';
		if($row['disabled']=='false'){
			
	    $html .= '<a href="'. $this->router->gen_url( array('app'=>'cellphone','ctl'=>'admin_column','act'=>'edit','column_id'=>$row['column_id']) ) .'" >'.app::get('cellphone')->_('编辑').'</a>&nbsp;&nbsp;';
			
		if($row['is_active']=='false'){
				$html .= '<a href="'. $this->router->gen_url( array('app'=>'cellphone','ctl'=>'admin_column','act'=>'openActivity','column_id'=>$row['column_id']) ) .'" >'.app::get('cellphone')->_('开启').'</a>&nbsp;&nbsp;';
		}else{
				$html .= '<a href="'. $this->router->gen_url( array('app'=>'cellphone','ctl'=>'admin_column','act'=>'closeActivity','column_id'=>$row['column_id']) ) .'" >'.app::get('cellphone')->_('关闭').'</a>&nbsp;&nbsp;';
			}
	  }
        return $html;

    }
    
    /*var $detail_edit = '详细列表';
    function detail_edit($id){
		//echo 'heh';
        $render = app::get('mybook')->render();
        $oItem = kernel::single("mybook_mdl_bookinfo");
        $items = $oItem->getList('bn, bookinfo_name, price,bookinfo_createtime,imagesrc',
                     array('bookinfo_id' => $id), 0, 1);
        $render->pagedata['item'] = $items[0];
        $render->display('admin/bookinfo/itemdetail.html');
        //return 'detail';    
    }*/

	var $column_picture = '缩略图';
    function column_picture($row){
        $column =  app::get('cellphone')->model('column');
		$g =$column->db_dump(array('column_id'=>$row['column_id']),'image_id');
		$img_id= base_storager::image_path($g['image_id'],'s' );
		if(!$img_id)return '';
		return "<a href='$img_id' class='img-tip pointer' target='_blank'
		        onmouseover='bindFinderColTip(event);'>
		<span>&nbsp;pic</span></a>";
      }

 
 
 
 
 
 
 }
