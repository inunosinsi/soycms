<?php

class IndexPage extends CMSWebPageBase{

	function doPost(){

		define("SOY2ACTION_AUTO_GENERATE",true);

    	if(soy2_check_token()){

			if(isset($_POST["update_display_order"])){

				$action = SOY2ActionFactory::createInstance("Label.UpdateDisplayOrder");
				$result = $action->run();
				$this->addMessage("LABEL_UPDATE_DISPLAY_ORDER");
				$this->jump("Label");

			}else{

				$action = SOY2ActionFactory::createInstance("Label.LabelCreateAction");
				$result = $action->run();

				if($result->success()){
					$this->addMessage("LABEL_CREATE_SUCCESS");
					$this->jump("Label");
				}else{
					$this->addErrorMessage("LABEL_CREATE_FAILED");
					$this->jump("Label");
				}

			}
    	}

	}

    function __construct() {
    	WebPage::WebPage();

    	$labels = $this->getLabelLists();
    	$this->createAdd("label_lists","LabelLists",array(
    		"list" => $labels,
    	));

    	$this->createAdd("update_display_order","HTMLInput",array(
    		"type" => "submit",
    		"name" => "update_display_order",
    		"value" => CMSMessageManager::get("SOYCMS_DISPLAYORDER"),
    		"tabindex" => LabelList::$tabIndex++
    	));

    	$this->createAdd("no_label_message","Label._LabelBlankPage",array(
    		"visible" => (count($labels)<1)
    	));

    	if(count($labels)<1){
    		DisplayPlugin::hide("must_exist_label");
    	}

    	$this->createAdd("create_label","HTMLForm");
    	$this->addModel("create_label_caption",array(
    		"placeholder" => UserInfoUtil::getSiteConfig("useLabelCategory") ? $this->getMessage("SOYCMS_LABEL_CREATE_PLACEHOLDER_WITH_GROUP")//ラベル名 または 分類名/ラベル名
    		                                                                 : $this->getMessage("SOYCMS_LABEL_CREATE_PLACEHOLDER"),//ラベル名 または 分類名/ラベル名
    	));


    	$this->createAdd("reNameForm","HTMLForm",array(
    		"action"=>SOY2PageController::createLink("Label.Rename")
    	));
    	HTMLHead::addScript("root",array(
    		"script"=>'var reNameLink = "'.SOY2PageController::createLink("Label.Rename").'";' .
    				'var reDesciptionLink = "'.SOY2PageController::createLink("Label.ReDescription").'";' .
					'var ChangeLabelIconLink = "'.SOY2PageController::createLink("Label.ChangeLabelIcon").'";'
    	));

    	//アイコンリスト
    	$this->createAdd("image_list","LabelIconList",array(
    		"list" => $this->getLabelIconList()
    	));

    	//表示順更新フォーム
    	$this->createAdd("update_display_order_form","HTMLForm");

		//CSS
		HTMLHead::addLink("labelcss",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/label/label.css")
		));

    }


    /**
     *  ラベルオブジェクトのリストのリストを返す
     * @param Boolean $classified ラベルを分けるかどうか
     */
    function getLabelLists($classified = true){
    	$action = SOY2ActionFactory::createInstance("Label.CategorizedLabelListAction");
    	$result = $action->run();

    	if($result->success()){
   			return $result->getAttribute("list");
    	}else{
    		return array();
    	}
    }

    /**
     * ラベルに使えるアイコンの一覧を返す
     */
    function getLabelIconList(){

    	$dir = CMS_LABEL_ICON_DIRECTORY;

    	$files = scandir($dir);

    	$return = array();

    	foreach($files as $file){
    		if($file[0] == ".")continue;
    		if(!preg_match('/jpe?g|gif|png$/i',$file))continue;
    		if($file == "default.gif")continue;

    		$return[] = (object)array(
    			"filename" => $file,
    			"url" => CMS_LABEL_ICON_DIRECTORY_URL . $file,
    		);
    	}


    	return $return;
    }
}

class LabelLists extends HTMLList{

	function populateItem($entity, $key){
		$this->addLabel("category_name", array(
			"text" => $key,
			"visible" => !is_int($key) && strlen($key),
		));
		$this->createAdd("list","LabelList",array(
			"list" => $entity
		));

		return ( count($entity) > 0 );
	}
}

class LabelList extends HTMLList{
	public static $tabIndex = 0;

	function populateItem($entity){

		$this->createAdd("label_icon","HTMLImage",array(
			"src" => $entity->getIconUrl(),
			"onclick" => "javascript:changeImageIcon(".$entity->getId().");"
		));

		$this->createAdd("label_name","HTMLLabel",array(
			"text"=> $entity->getBranchName(),
			"style"=> "color:#" . sprintf("%06X",$entity->getColor()).";background-color:#" . sprintf("%06X",$entity->getBackgroundColor()) . ";margin:5px"
		));

		$this->createAdd("display_order","HTMLInput",array(
			"name"     => "display_order[".$entity->getId()."]",
			"value"    => $entity->getDisplayOrder(),
			"tabindex" => self::$tabIndex++
		));

		$this->createAdd("label_link","HTMLLink",array(
			"link"=>SOY2PageController::createLink("Entry.List.".$entity->getId())
		));

		$this->createAdd("detail_link","HTMLLink",array(
			"link"=>SOY2PageController::createLink("Label.Detail.".$entity->getId())
		));

		$this->createAdd("remove_link","HTMLActionLink",array(
			"link" => SOY2PageController::createLink("Label.Remove.".$entity->getId()),
			"visible" => UserInfoUtil::hasEntryPublisherRole(),
		));

		$this->createAdd("description","HTMLLabel",array(
			"text"=> (trim($entity->getDescription())) ? $entity->getDescription() : CMSMessageManager::get("SOYCMS_CLICK_AND_EDIT"),
			"onclick"=>'postDescription('.$entity->getId().',"'.addslashes($entity->getCaption()).'","'.addslashes($entity->getDescription()).'")'
		));

		//記事数
//		$this->createAdd("entry_count","HTMLLabel",array(
//			"text"=> $entity->getEntryCount(),
//		));
	}
}

class LabelIconList extends HTMLList{

	function populateItem($entity){
		$this->createAdd("image_list_icon","HTMLImage",array(
			"src" => $entity->url,
			"ondblclick" => "javascript:postChangeLabelIcon('".$entity->filename."');"
		));
	}

}
?>