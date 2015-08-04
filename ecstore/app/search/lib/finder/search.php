<?php



class search_finder_search
{
    public $addon_cols='content_name,content_path';
    public $column_service_type = '服务项目';
    public $column_description = '描述';
    public $column_reindex = '索引';
    public $column_status = '状态';
    public $column_used = '使用';
    public $column_used_width = '80';
    public $column_reindex_width = '80';
    public $column_status_width = '80';
    public $column_description_width = '300';
    public $detail_config = '配置';
    public $detail_capability = '功能';

    public function detail_config($id){
        $content = app::get('base')->model('app_content')->dump(array('content_id'=>$id),'*');
        $obj = kernel::single($content['content_path']);
        if(method_exists($obj, 'finder_config')){
            return call_user_func_array(array($obj, 'finder_config'), array($content));
        }else{
            $render = app::get('search')->render();
            return $render->fetch('config/default.html');
        }
    }

    public function detail_capability($id){
    	$content = app::get('base')->model('app_content')->dump(array('content_id'=>$id),'*');
    	$obj = kernel::single($content['content_path']);
        if(method_exists($obj, 'finder_capability')){
            return call_user_func_array(array($obj, 'finder_capability'),array($content));
        }else{
            $render = app::get('search')->render();
            return $render->fetch('capability/default.html');
        }

    }

    public function column_used($row)
    {
	     if(app::get('base')->getConf('server.'.$row[$this->col_prefix.'content_name']) == $row[$this->col_prefix.'content_path']){
	     	 return '<a href="javascript:;" onClick="javascript:W.page(\'index.php?app=search&ctl=search&act=set_default&method=shut&type='.$row[$this->col_prefix.'content_name'].'&name='.$row[$this->col_prefix.'content_path'].'\')" >'.app::get('search')->_('停用').'</a>';
	     }else{
	     	 return '<a href="javascript:;" onClick="javascript:W.page(\'index.php?app=search&ctl=search&act=set_default&method=open&type='.$row[$this->col_prefix.'content_name'].'&name='.$row[$this->col_prefix.'content_path'].'\')" >'.app::get('search')->_('启用').'</a>';
	     }
    }//End Function

    public function column_service_type($row){
    	$serviceObj = kernel::servicelist($row[$this->col_prefix.'content_name']);
    	foreach($serviceObj as $service){
    		if(get_class($service) == $row[$this->col_prefix.'content_path']){
    		    $des = $service->servicename;
                break;
            }
    	}
        return $des;
    }//End Function

    public function column_description($row){
    	$serviceObj = kernel::servicelist($row[$this->col_prefix.'content_name']);
    	foreach($serviceObj as $service){
    		if(get_class($service) == $row[$this->col_prefix.'content_path']){
    		    $des = $service->description;
                break;
            }
    	}
        return $des;
    }//End Function





}//End Class
