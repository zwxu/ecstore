<?php

 
class desktop_finder_builder_to_export extends desktop_finder_builder_prototype{


    function main(){
        $oIo = kernel::servicelist('desktop_io');
        foreach( $oIo as $aIo ){
            if( $aIo->io_type_name == ($_POST['_io_type']?$_POST['_io_type']:'csv') ){
                $oImportType = $aIo;
                break;
            }
        }
        unset($oIo);

        $oName = substr($this->object_name,strlen($this->app->app_id.'_mdl_'));
        $model = app::get($this->app->app_id)->model( $oName );
        $model->filter_use_like = true;
        $oImportType->init($model);
        $offset = 0;
        $data = array('name'=> $oName );
        if($_POST['view']){
            $_view = $this->get_views();
            if(count($this->get_views())){
                $view_filter = (array)$_view[$_POST['view']]['filter'];
                $_POST = array_merge($_POST,$view_filter);
            }
        }
        /** 合并base filter **/
        $base_filter = (array)$this->base_filter;
        $_POST = array_merge($_POST,$base_filter);
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog')){
            if(method_exists($obj_operatorlogs,'exportlog')){
                $obj_operatorlogs->exportlog($oName);
            }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        /** end **/
        if( method_exists($model,'fgetlist_'.$_POST['_io_type']) ){
            /** 到处头部 **/
            $oImportType->export_header( $data,$model,$_POST['_export_type'] );
            $method_name = 'fgetlist_'.$_POST['_io_type'];
            while( $listFlag = $model->$method_name($data,$_POST,$offset,$_POST['_export_type']) ){
                $offset++;
            }    
            
            $oImportType->export( $data,$offset,$model,$_POST['_export_type'] );
            if($oName =='orders'){
            	$item_offset = 0;
            	$order_item = app::get('b2c')->model( 'order_items' );
            	$items = array();
	            while( $listFlag = $order_item->fgetlist_csv(&$items,$_POST,$item_offset,$_POST['_export_type'])){
	           		$item_offset++;
	            }
	            
	            $oImportType->export( $items,$offset,$order_item,$_POST['_export_type'] );            	
            } elseif ($oName =='refunds') {
            	$item_offset = 0;
            	$order_item = app::get('ectools')->model( 'refunds' );
            	$items = array();
	            while( $listFlag = $order_item->fgetlistitems_csv(&$items,$_POST,$item_offset,$_POST['_export_type'])){
	           		$item_offset++;
	            }
	            
	            $oImportType->export( $items,$offset,$order_item,$_POST['_export_type'] );               	
            } elseif ($oName =='payments') {
            	$item_offset = 0;
            	$order_item = app::get('ectools')->model( 'payments' );
            	$items = array();
	            while( $listFlag = $order_item->fgetlistitems_csv(&$items,$_POST,$item_offset,$_POST['_export_type'])){
	           		$item_offset++;
	            }
	            
	            $oImportType->export( $items,$offset,$order_item,$_POST['_export_type'] );
            }
        }else{
            /** 到处头部 **/
            $oImportType->export_header( $data,$model,$_POST['_export_type'] );
            while( $listFlag = $oImportType->fgetlist($data,$model,$_POST,$offset,$_POST['_export_type']) ){
                $offset++;
                $oImportType->export( $data,$offset,$model,$_POST['_export_type'] );
            }
        }
    }


}
