<?php

 

require dirname(__FILE__)."/../lib/softvcode.php";
$vcode_model = new pam_softvcode;
$vcode_model->init(4);
$vcode_model->output();
