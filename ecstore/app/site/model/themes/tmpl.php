<?php

 

class site_mdl_themes_tmpl extends dbeav_model 
{
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        $arr_themes_tmpl = parent::getList($cols, $filter, $offset, $limit, $orderType);
        $obj_themes_file = app::get('site')->model('themes_file');

        foreach ($arr_themes_tmpl as $key=>$arr)
        {
            if ($arr['rel_file_id']){
                $themes_file_content = $obj_themes_file->getList('theme,content',array('id'=>$arr['rel_file_id']));
                $arr_themes_tmpl[$key]['content'] = $themes_file_content['0']['content'];
                $arr_themes_tmpl[$key]['version'] = $themes_file_content['0']['version'];
            }
        }
        return $arr_themes_tmpl;
    }

    function dump($filter,$field = '*',$subSdf = null){
        $dumpData = &parent::dump($filter,$field,$subSdf);
        $obj_themes_file = app::get('site')->model('themes_file');

        if ($dumpData['rel_file_id']){
            $themes_file_content = $obj_themes_file->getList('content',array('id'=>$dumpData['rel_file_id']));
            $dumpData['content'] = $themes_file_content['0']['content'];
        }

        return $dumpData;
    }




}//End Class
