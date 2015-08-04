<?php
class poster_ctl_site_poster extends b2c_frontpage{
    /*
    **更新广告的点击量
    */
    public function clickcount(){
        if(isset($_GET['posterid']) && $_GET['posterid'] !=''){
            $posterid = intval($_GET['posterid']);
            $m=$this->app->model('poster');
            $m->db->exec('update sdb_poster_poster set poster_clickcount=poster_clickcount+1 where poster_id='.$posterid);
            echo 'ok';
        }else{
            echo 'error';
        }
    }
}