<?php

interface base_interface_queue
{
    public function publish($message);
    public function consume();
}
