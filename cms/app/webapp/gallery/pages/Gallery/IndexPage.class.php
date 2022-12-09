<?php

class IndexPage extends WebPage{

    function __construct() {
    	parent::__construct();

		$galleries = soygallery_get_gallery_objects(100000);

		$cnt = count($galleries);
		DisplayPlugin::toggle("no_gallery", !$cnt);
		DisplayPlugin::toggle("is_gallery", $cnt);
    	
    	$this->createAdd("gallery_list","_common.GalleryListComponent",array(
    		"list" => $galleries
    	));
    }
}