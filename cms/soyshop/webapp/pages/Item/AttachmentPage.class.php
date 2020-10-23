<?php
SOY2HTMLFactory::importWebPage("Item.DetailPage");

class AttachmentPage extends DetailPage{

	function doPost(){

		if(isset($_POST["target_image"]) && soy2_check_token()){
			$url = $_POST["target_image"];
			$filepath = str_replace(soyshop_get_site_url(), SOYSHOP_SITE_DIRECTORY, $url);

			if(file_exists($filepath)){
				unlink($filepath);
			}

			SOY2PageController::jump("Item.Attachment.". $this->id . "?deleted");
		}

		if(isset($_POST["thumbnail"]) && soy2_check_token()){
			$url = $_POST["thumbnail"]["target_image"];
			$filepath = str_replace(soyshop_get_site_url(), SOYSHOP_SITE_DIRECTORY, $url);
			$width = $_POST["thumbnail"]["width"];
			$height = $_POST["thumbnail"]["height"];

			$savepath = dirname($filepath)."/thumb-".basename($filepath);

			if(file_exists($filepath)){
				copy($filepath, $savepath);
				//soy2_resize_image($filepath, $savepath,$width, $height);
			}

			SOY2PageController::jump("Item.Attachment." . $this->id . "?updated");
		}

		if(isset($_FILES["upload"])){
			$url = $this->uploadImage();

			if($url){
				SOY2PageController::jump("Item.Attachment." . $this->id . "?updated");
			}else{
				SOY2PageController::jump("Item.Attachment." . $this->id . "?failed");
			}
		}
	}

	var $id;

	function __construct($args){
		$this->id = (isset($args[0])) ? (int)$args[0] : null;

		DetailPage::__construct($args);

		$this->addLink("item_detail_link", array(
			"link" => SOY2PageController::createLink("Item.Detail.". $this->id)
		));

		$this->addForm("remove_form");

		$this->addForm("thumbnail_form");

	}

	/**
     * 添付ファイル取得(新しい順にする)
     */
    function getAttachments(SOYShop_Item $item){
    	$res = $item->getAttachments();

    	usort($res, array($this, "sortUrlByFilemtime"));

    	return $res;
    }

    function sortUrlByFilemtime($file1, $file2){
    	$file1 = str_replace(soyshop_get_site_url(), SOYSHOP_SITE_DIRECTORY, $file1);
    	$file2 = str_replace(soyshop_get_site_url(), SOYSHOP_SITE_DIRECTORY, $file2);

		//filemtimeが使用出来ないサーバ対策
    	return (@filemtime($file1) <= @filemtime($file2));
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("商品画像の管理", array("Item" => "商品管理", "Item.Detail." . $this->id => "商品詳細"));
	}
}
