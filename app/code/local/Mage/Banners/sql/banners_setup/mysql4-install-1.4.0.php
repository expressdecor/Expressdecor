<?php

$installer = $this;

$installer->startSetup();

$installer->run("
				
-- DROP TABLE IF EXISTS {$this->getTable('banners')};
CREATE TABLE {$this->getTable('banners')} (                                 
   `banners_id` int(11) unsigned NOT NULL auto_increment,  
   `title` varchar(255) NOT NULL default '',               
   `bannerimage` varchar(255) NOT NULL default '',         
   `filethumbgrid` text,                                   
   `link` varchar(255) default NULL,                       
   `target` varchar(255) default NULL,                     
   `textblend` varchar(255) default NULL,                  
   `content` text NOT NULL,    
   `sort_order` int(11) default '0',
   `status` smallint(6) NOT NULL default '0',              
   `created_time` datetime default NULL,                   
   `update_time` datetime default NULL,                    
   PRIMARY KEY  (`banners_id`)                             
 ) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('banners_store')};
CREATE TABLE {$this->getTable('banners_store')} (                                
 `banners_id` int(11) NOT NULL,                               
 `store_id` smallint(5) unsigned NOT NULL,                    
 PRIMARY KEY  (`banners_id`,`store_id`),                      
 KEY `FK_BANNERS_STORE_STORE` (`store_id`)                    
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Banners Stores';
");


$installer->setConfigData('advanced/modules_disable_output/Mage_Banners','0');
$installer->setConfigData('banners/general/banner_width','564');
$installer->setConfigData('banners/general/banner_height','345');
$installer->setConfigData('banners/general/banner_backgroundcolor','FFFFFF');
$installer->setConfigData('banners/textsettings/text_size','12');
$installer->setConfigData('banners/textsettings/text_color','');
$installer->setConfigData('banners/textsettings/text_area_width','200');
$installer->setConfigData('banners/textsettings/text_line_spacing','0');
$installer->setConfigData('banners/textsettings/text_margin_left','12');
$installer->setConfigData('banners/textsettings/text_letter_spacing','-0.5');
$installer->setConfigData('banners/textsettings/text_margin_bottom','5');
$installer->setConfigData('banners/textsettings/text_background_blur','1');
$installer->setConfigData('banners/textsettings/text_background_transparency','30');
$installer->setConfigData('banners/textsettings/text_background_color','333333');
$installer->setConfigData('banners/transition/transition_type','4');
$installer->setConfigData('banners/transition/transition_blur','1');
$installer->setConfigData('banners/transition/transition_speed','10');
$installer->setConfigData('banners/transition/transition_delay_time_fixed','10');
$installer->setConfigData('banners/transition/transition_random_effects','0');
$installer->setConfigData('banners/transition/transition_delay_time_per_word','.5');
$installer->setConfigData('banners/showhide/show_timer_clock','0');
$installer->setConfigData('banners/showhide/show_next_button','0');
$installer->setConfigData('banners/showhide/show_back_button','0');
$installer->setConfigData('banners/showhide/show_number_buttons','0');
$installer->setConfigData('banners/showhide/show_number_buttons_always','0');
$installer->setConfigData('banners/showhide/show_number_buttons_horizontal','1');
$installer->setConfigData('banners/showhide/show_number_buttons_ascending','1');
$installer->setConfigData('banners/showhide/show_play_pause_on_timer','1');
$installer->setConfigData('banners/showhide/align_buttons_left','0');
$installer->setConfigData('banners/showhide/align_text_top','0');
$installer->setConfigData('banners/showhide/auto_play','0');
$installer->setConfigData('banners/general/auto_play','1');
$installer->setConfigData('banners/general/image_resize_to_fit','1');
$installer->setConfigData('banners/general/image_randomize_order','0');

$installer->endSetup(); 

?>