<?php

class ImageListComponent extends HTMLList {

	protected function populateItem($entity){
		$isImage = (is_string($entity) && strlen($entity));

		$this->addModel("is_image", array(
			"visible" => $isImage
		));

		$this->addLink("modal_link", array(
			"link" => "javascript:void(0)",
			"onclick" => ($isImage) ? "openModal('" . $entity . "');" : "return false;"
		));

		$this->addImage("image", array(
			"src" => ($isImage) ? "/" . SOYSHOP_ID . "/im.php?src=" . $entity . "&width=100" : null
		));

		$this->addModel("remove_onclick", array(
			"attr:onclick" => ($isImage) ? "remove_image(\"" . BulletinBoardUtil::path2filename($entity) . "\");" : "return false;"
		));
	}
}
