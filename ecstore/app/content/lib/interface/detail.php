<?php

 /**
 *
 * @package cmsex
 * @subpackage article
 * @author edwin.lzh@gmail.com
 * @license 
 */
interface site_interface_detail{

    /**
     * 添加文章
     * @var array $index
     * @var array $body
     * @access public
     */
    public function add($index, $body);
    
    /**
     * 编辑文章
     * @var int $artile_id
     * @var array $index
     * @var array $body
     * @access public
     */
    public function edit($article_id, $index, $body);

    /**
     * 发布文章
     * @var int $artile_id
     * @var boolean $pub
     * @access public
     */
    public function publish($article_id, $pub=true);

    /**
     * 移除文章
     * @var int $artile_id
     * @access public
     */
    public function remove($article_id);

    /**
     * 恢复文章
     * @var int $artile_id
     * @access public
     */
    public function restore($article_id);

    /**
     * 移动文章
     * @var int $artile_id
     * @var int $node_id
     * @access public
     */
    public function move($article_id, $node_id);

    /**
     * 复制文章
     * @var int $artile_id
     * @var int $node_id
     * @access public
     */
    public function copy($article_id, $node_id);

}
