<?php

 
class site_mdl_route_statics extends dbeav_model 
{
    
    public function searchOptions() 
    {
        $arr = parent::searchOptions();
        return array_merge($arr, array(
                'static' => app::get('site')->_('URL规则'),
                'url' => app::get('site')->_('目标链接'),
            ));
    }//End Function

    public function has_static($static) 
    {
        $rows = $this->getList('*', array('static'=>$static));
        if(count($rows)){
            return $rows[0];
        }else{
            return false;
        }
    }//End Function

    public function has_url($url) 
    {
        $rows = $this->getList('*', array('url'=>$url));
        if(count($rows)){
            return $rows[0];
        }else{
            return false;
        }
    }//End Function

    public function insert(&$data) 
    {
        if($this->has_static($data['static']) || $this->has_url($data['url'])){
            return false;
        }
        $res = parent::insert($data);
        if($res){
            kernel::single('site_route_static')->set_dispatch($data['static'], $data);
            kernel::single('site_route_static')->set_genurl($data['url'], $data);
        }
        return $res;
    }//End Function

    public function update($data, $filter, $mustUpdate = null) 
    {
        $old_rows = $this->getList('*', $filter);
        $res = parent::update($data, $filter, $mustUpdate);
        if($res){
            foreach($old_rows AS $row){
                kernel::single('site_route_static')->del_dispatch($row['static']);
                kernel::single('site_route_static')->del_genurl($row['url']);
            }
            $rows = $this->getList('*', $filter);
            foreach($rows AS $row){
                kernel::single('site_route_static')->set_dispatch($row['static'], $row);
                kernel::single('site_route_static')->set_genurl($row['url'], $row);
            }
        }
        return $res;
    }//End Function

    public function delete($filter,$subSdf = 'delete') 
    {
        $rows = $this->getList('*', $filter);
        $res = parent::delete($filter,$subSdf);
        if($res){
            foreach($rows AS $row){
                kernel::single('site_route_static')->del_dispatch($row['static']);
                kernel::single('site_route_static')->del_genurl($row['url']);
            }
        }
        return $res;
    }//End Function

}//End Class
