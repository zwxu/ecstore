<?php
 class cellphone_finder_banner{
 
    function __construct($app){
        $this->app = $app;
        $this->router = app::get('desktop')->router();
        $this->banner = $this->app->model('banner');
    }//End
    

 	var $column_edit = '操作';
	public $column_edit_width = 110;

    function column_edit($row){


        $row = $this->banner->getList('*',array('id'=>$row['id']));
        $row = $row[0];
		$html = '';
		if($row['disabled']=='false'){
			
	    $html .= '<a href="'. $this->router->gen_url( array('app'=>'cellphone','ctl'=>'admin_banner','act'=>'edit','id'=>$row['id']) ) .'" >'.app::get('cellphone')->_('编辑').'</a>&nbsp;&nbsp;';
			
		if($row['is_active']=='false'){
				$html .= '<a href="'. $this->router->gen_url( array('app'=>'cellphone','ctl'=>'admin_banner','act'=>'openActivity','id'=>$row['id']) ) .'" >'.app::get('cellphone')->_('开启').'</a>&nbsp;&nbsp;';
		}else{
				$html .= '<a href="'. $this->router->gen_url( array('app'=>'cellphone','ctl'=>'admin_banner','act'=>'closeActivity','id'=>$row['id']) ) .'" >'.app::get('cellphone')->_('关闭').'</a>&nbsp;&nbsp;';
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
        $obanner =  app::get('cellphone')->model('banner');
		$g =$obanner->db_dump(array('id'=>$row['id']),'image_id');
		$img_id= base_storager::image_path($g['image_id'],'s' );
		if(!$img_id)return '';
		return "<a href='$img_id' class='img-tip pointer' target='_blank'
		        onmouseover='bindFinderColTip(event);'>
		<span>&nbsp;pic</span></a>";
      }

 
 
 
 
 
 
 }

