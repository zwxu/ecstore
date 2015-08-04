<?php

interface search_interface_model
{
    public function create();

    public function link();

    public function query($queryArr=array());

    public function commit();

    public function insert($val=array());

    public function update($val=array());

    public function delete($val=array());

    public function reindex(&$msg);

    public function optimize(&$msg);

    public function status(&$msg);

    public function clear(&$msg);
}