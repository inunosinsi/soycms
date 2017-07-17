<?php
class SOYShopUploadImageBase implements SOY2PluginAction{

  function convertFileName(SOYShop_Item $item, $pathinfo){
    return "";
  }
}
class SOYShopUploadImageDeletageAction implements SOY2PluginDelegateAction{

  private $_name;
  private $mode = "name";
  private $item;
  private $pathinfo;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
    if($action instanceof SOYShopUploadImageBase){
      switch($this->mode){
        case "name":
          $this->_name = $action->convertFileName($this->item, $this->pathinfo);
          break;
        default:
          //何もしない
      }
		}
	}

  function getName(){
    return $this->_name;
  }

  function setItem($item){
    $this->item = $item;
  }

  function setPathinfo($pathinfo){
    $this->pathinfo = $pathinfo;
  }
}
SOYShopPlugin::registerExtension("soyshop.upload.image","SOYShopUploadImageDeletageAction");
