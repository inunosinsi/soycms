<?php

class IndexPage extends WebPage{

    function __construct() {
    	WebPage::__construct();
    	
    	$dao = SOY2DAOFactory::create("SOYGallery_GalleryDAO");
    	try{
    		$galleries = $dao->get();
    	}catch(Exception $e){
    		$galleries = array();
    	}
    	
    	$this->createAdd("gallery_list","_common.GalleryListComponent",array(
    		"list" => $galleries
    	));
    }
}
?>