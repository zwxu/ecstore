<?php

 

class site_mdl_themes_file extends dbeav_model 
{
    public function save(&$data,$mustUpdate = null){
       if($data['content']) 
            $data['content'] = base64_encode($data['content']);
        return parent::save($data,$mustUpdate);
    }

    public function getList($cols='*', $filter='null', $offset=0, $limit=-1, $orderby=null)
    {
        $list = parent::getList($cols, $filter, $offset, $limit, $orderby);
        foreach($list as $key=>$row){
            $list[$key]['content'] = base64_decode($row['content']);
        }
        return $list;
    }

}//End Class
