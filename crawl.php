<?php

require_once 'app/Mage.php';

Mage::app('default');
//Mage::run('','store');

error_reporting(-1);
$conf['error_level'] = 2;
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set("memory_limit", "1024M");
set_time_limit(0);


$model= Mage::getModel("enterprise_pagecache/crawler"); 
//new Enterprise_PageCache_Model_Crawler;

//print_r(get_class_methods($model));
$model->crawl();

echo "Done";
