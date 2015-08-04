<?php
/**
 * @author chris.zhang
 * 
 */
class b2c_widgets_article extends b2c_widgets_public {
    //节点返回数据格式
    protected $_outData = array(
        'nodeId'    => 'node_id',       //节点ID
        'nodeName'  => 'node_name',     //节点名称
        'nodeLink'  => '_link_',        //节点链接
        'articles'  => 'articles',      //节点下属文章（可选）
    );
    //文章返回数据格式
    protected $_articleData = array(
        'articleId'     => 'article_id',    //文章ID
        'articleTitle'  => 'title',         //文章标题
        'articleLink'   => '_link_',        //文章链接
        'articleAuthor' => 'author',        //作者
        'articleTime'   => '_time_',        //发布时间
    );
    
    /**
     * 获取节点及其下属文章，下属节点及其文章
     * @param int $node_id      //节点ID
     * @param bool $article     //是否获取其下属文章
     * @param bool $child       //是否获取其下属节点
     */
    public function getNodeMap($node_id = null, $article = false, $child = true){
        $node_id = $this->addslashes($node_id);
        $child   = $this->get_bool($child);
        $article = $this->get_bool($article);
        
        return $this->_getNodeMap($node_id, $article, $child);
    }
    
    /**
     * 获取某节点下的所有文章（不包括其子节点的文章）
     * @param int $node_id  //节点ID
     */
    public function getNodeArticles($node_id){
        $node_id = $this->addslashes($node_id);
        
        $sql = "SELECT article_id FROM sdb_content_article_indexs WHERE node_id = '$node_id'";
        $rows = $this->db->select($sql);
        $data = array();
        foreach ((array)$rows as $row){
            $data[$row['article_id']] = $this->_getArticleInfo($row['article_id']);
        }
        return $data;
    }
    
    /**
     * 获取所有文章
     * @param mix $article_id   //文章ID
     * array(1,3,4)/1;
     */
    public function getArticleList($article_id=null){
        $data = array();
        if ($article_id){
            if (is_array($article_id)){
                foreach ($article_id as $a_id){
                    $_tmp = $this->_getArticleInfo($article_id);
                    if (empty($_tmp)) continue;
                    $data[$a_id] = $_tmp;
                }
            }else {
                $data[$article_id] = $this->_getArticleInfo($article_id);
            }
        }else {
            $sql = "SELECT article_id, title, author, pubtime 
                    FROM sdb_content_article_indexs 
                    WHERE disabled = 'false'";
            $rows = $this->db->select($sql);
            foreach ((array)$rows as $row){
                $row['_time_'] = $row['pubtime'] ? date('Y-m-d',$row['pubtime']) : '';
                $row['_link_'] = $this->getArticleLink($row['article_id']);
                $data[$row['article_id']] = $this->_getOutData($row, $this->_articleData);
            }
        }
        return $data;
    }
    
    /**
     * 获取某文章的链接
     * @param int $article_id   //文章ID
     */
    public static function getArticleLink($article_id){
        return self::get_link(array('app'=>'content', 'ctl'=>'site_article', 'act'=>'index', 'arg0'=>$article_id));
    }
    
    /**
     * 获取某文章的信息
     * @param int $article_id   //文章ID
     */
    private function _getArticleInfo($article_id){
        //$all   = $this->get_bool($all);
        $article_id = $this->addslashes($article_id);
        /*
        $sql = "SELECT ai.article_id, ai.title, ai.author, ab.content, ab.image_id 
                FROM sdb_content_article_indexs ai 
                    JOIN sdb_content_article_bodys ab 
                        ON ai.article_id = ab.article_id 
                    WHERE ai.disabled = 'false' 
                        AND ai.article_id = '$article_id'";
        */
        
        $sql = "SELECT article_id, title, author, pubtime 
                FROM sdb_content_article_indexs 
                WHERE disabled = 'false' 
                    AND article_id = '$article_id'";
        $row = $this->db->selectrow($sql);
        if (empty($row)) return array();
        //if ($all == false) unset($row['content'],$row['image_id']);
        $row['_time_'] = $row['pubtime'] ? date('Y-m-d',$row['pubtime']) : '';
        $row['_link_'] = $this->getArticleLink($row['article_id']);
        
        return $this->_getOutData($row, $this->_articleData);
    }
    
    private function _getNodeLink($node_id, $homepage){
        if ($homepage=='true') {
            return $this->get_link(array('app'=>'content', 'ctl'=>'site_article', 'act'=>'nodeindex', 'arg0'=>$node_id));
        }
        return $this->get_link(array('app'=>'content', 'ctl'=>'site_article', 'act'=>'lists', 'arg0'=>$node_id));
    }
    
    private function _getNodeMap($node_id = null, $article = false, $child = true){
        $sql    = $this->_getSql($node_id, $child);
        $data   = $this->db->select($sql);
        
        $prefix = $this->prefix;
        $aFix   = 'articles';
        $_data  = array();
        foreach ((array)$data as $row){
            $path       = $row['node_path'];
            $n_id       = $row['node_id'];
            $p_id       = $row['parent_id'];
            $homepage   = $row['homepage'];
            
            $row['articles'] = $this->getNodeArticles($n_id);
            $row['_link_']   = $this->_getNodeLink($n_id, $homepage);
            
            $row = $this->_getOutData($row);
            $parents = array_filter(explode(',', $path));
            
            if(count($parents) == 1 || ($node_id && $node_id == end($parents))){
                $_data[$n_id] = $row;
            }else {
                krsort($parents);
                $out = array();
                foreach ($parents as $v){
                    $_tmp = array();
                    if ($v == $n_id){
                        $out[$n_id] = $row;
                        continue;
                    }else{
                        $_tmp[$v][$prefix] = $out;
                    }
                    $out = $_tmp;
                    if ($node_id && $v == $node_id) break;
                }
                $_data = $this->array_merge_recursive($_data, $out);
            }
        }
        return $_data;
    }
    
    private function _getSql($node_id, $child){
        $columns = ' node_id, parent_id, node_name, node_path, homepage';
        $orderBy = ' ORDER BY node_id ASC ';
        if (!$node_id) {
            $sql = "SELECT $columns FROM sdb_content_article_nodes 
                    WHERE disabled = 'false' $orderBy";
            return $sql;
        }
        $sql = "SELECT parent_id,has_children FROM sdb_content_article_nodes 
                WHERE disabled = 'false' 
                    AND node_id = '$node_id'";
        $node = $this->db->selectrow($sql);
        
        if ($child && $node['has_children'] == 'true') {
            if ($node['parent_id'] == 0){
                $sql = "SELECT $columns FROM sdb_content_article_nodes 
                        WHERE disabled = 'false' 
                            AND (node_id  = '$node_id' 
                            OR node_path LIKE '$node_id,%') $orderBy";
            }else {
                $sql = "SELECT $columns FROM sdb_content_article_nodes 
                        WHERE disabled = 'false' 
                            AND (node_id  = '$node_id' 
                            OR node_path LIKE '%,$node_id,%') $orderBy";
            }
        }else {
            $sql = "SELECT $columns FROM sdb_content_article_nodes 
                    WHERE disabled = 'false' 
                        AND node_id  = '$node_id' $orderBy";
        }
        return $sql;
    }
    
}