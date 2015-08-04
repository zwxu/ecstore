<?php
/**
 * @package cmsex
 * @subpackage article
 * @author edwin.lzh@gmail.com
 * @license 
 */
interface content_interface_node{
    
    /**
     * 添加节点
     * @var array $params
     * @access public
     */
    public function insert($params);

    /**
     * 编辑节点
     * @var int $node_id
     * @var array $params
     * @access public
     */
    public function update($node_id, $params);

    /**
     * 移除节点
     * @var int $node_id
     * @access public
     */
    public function remove($node_id);

    /**
     * 节点发布
     * @var int $node_id
     * @var boolean $pub
     * @access public
     */
    public function publish($node_id, $pub=true);

}
