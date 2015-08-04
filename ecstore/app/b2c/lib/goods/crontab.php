<?php
class b2c_goods_crontab{


    function run($goods_id){
        $this->logFile = DATA_DIR.'/logs/access.log.php';
        $this->now = time();
        $this->viewStat($goods_id);
    }

    function viewStat($goods_id){
        if(!file_exists($this->logFile)){
            file_put_contents($this->logFile,"<?php exit()?>\n");
        }
        $action = 'product:index';
        error_log($this->now."\t".$action."\t".$goods_id."\n", 3, $this->logFile);
        if(!file_exists($this->logFile.'.time') || filemtime($this->logFile.'.time') < $this->now-300){ //5分钟处理一次log
            touch($this->logFile.'.time');
            $work = dirname($this->logFile).'/tmp.'.$this->now.'.php';
            copy($this->logFile,$work);
            unlink($this->logFile);
            while($lines = $this->parseLog($work)){
                foreach($lines as $line){
                    if($line[1]=='product:index'){
                        $pdtView[$line[2]][$this->day($line[0])]++;
                    }
                }
            }
            unlink($work);
        }

        $today = $this->day(time());

        $ObjGoods = app::get('b2c')->model('goods');


        if($pdtView>0){
            foreach($ObjGoods->getlist('view_count,view_w_count,view_m_count,count_stat,goods_id',array('goods_id'=>array_keys($pdtView))) as $row){

                if(!($stat = unserialize($row['count_stat']))){
                    $stat=array('view'=>array(),'buy'=>array());
                }

                foreach($pdtView[$row['goods_id']] as $day=>$count){
                    $stat['view'][$day]+=$count;
                }

                $w_count = 0;
                $m_count = 0;
                foreach($stat['view'] as $day=>$count){
                    if($day<$today-90){//todo:只保留最近90天
                        unset($stat['view'][$day]);
                    }elseif($day>$today-7){
                        $w_count+=$count;
                        $m_count+=$count;
                    }elseif($day>$today-30){
                        $m_count+=$count;
                    }

                }
                $row['view_w_count']=$w_count;
                $row['view_m_count']=$m_count;
                $row['view_count']+=array_sum($pdtView[$row['goods_id']]); //浏览量增加
                $stat = $ObjGoods->db->quote(serialize($stat));


                $ObjGoods->db->exec("update sdb_b2c_goods set view_w_count=".intval($row['view_w_count']).",view_m_count=".intval($row['view_m_count']).",count_stat="
                        .$stat.",view_count=".intval($row['view_count'])." where goods_id=".intval($row['goods_id']),true);

            }
        }

    }

    /**
     * parseLog
     * 节省内存的分段式分析log
     *
     * @param mixed $file
     * @access public
     * @return void
     */
    function parseLog($file){
        if(!isset($this->fs[$file])){
            $this->fs[$file] = fopen($file,'r');
            if(!$this->fs[$file])
                return false;
        }
        if(feof($this->fs[$file])){
            fclose($this->fs[$file]);
            $this->fs[$file] = true;
            return false;
        }else{
            $contents = fread($this->fs[$file], 8192);
            if($p = strrpos($contents,"\n")){
                $end = substr($contents,$p+1);
                $contents = $this->fend[$file].substr($contents,0,$p);
                $this->fend[$file] = $end;
                $return = array();
                foreach(explode("\n",$contents) as $line){
                    if($line{0}!='#' && $line){
                        $return[] = explode("\t",$line);
                    }
                }
            }else{
                $return = array($this->fend[$file].$contents);
                $this->fend[$file] = null;
            }
            return $return;
        }
    }

    function day($time=null){
	    if(!isset($GLOBALS['_day'][$time])){
	        return $GLOBALS['_day'][$time] = floor($time/86400);
	    }else{
	        return $GLOBALS['_day'][$time];
	    }
    }
}

