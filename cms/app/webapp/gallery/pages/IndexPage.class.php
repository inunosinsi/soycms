<?php

class IndexPage extends WebPage{

	function __construct() {
		parent::__construct();

		$galleries = soygallery_get_gallery_objects();

		$cnt = count($galleries);
		DisplayPlugin::toggle("no_gallery", !$cnt);
		DisplayPlugin::toggle("is_gallery", $cnt);

		$this->createAdd("gallery_list", "_common.GalleryListComponent", array(
			"list" => $galleries
		));

		$images = soygallery_get_image_views();

		$cnt = count($images);
		DisplayPlugin::toggle("no_image", !$cnt);
		DisplayPlugin::toggle("is_image", $cnt);

		$this->addModel("no_image_list", array(
			"visible" => (count($images) === 0)
		));

		$this->createAdd("image_list", "_common.NewImageListComponent", array(
			"list" => $images
		));
	}
}
