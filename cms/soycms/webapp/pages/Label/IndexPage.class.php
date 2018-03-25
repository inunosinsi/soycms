<?php

class IndexPage extends CMSWebPageBase{

	function doPost(){

		define("SOY2ACTION_AUTO_GENERATE", true);

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
		parent::__construct();

		include_once(SOY2HTMLConfig::PageDir() . "_component/Label/LabelListComponent.class.php");
		$labels = self::getLabelLists();
		$this->createAdd("label_lists", "_component.Label.LabelsListComponent", array(
			"list" => $labels,
		));

		$this->addInput("update_display_order", array(
			"type" => "submit",
			"name" => "update_display_order",
			"value" => CMSMessageManager::get("SOYCMS_DISPLAYORDER"),
			"tabindex" => LabelListComponent::$tabIndex++
		));

		if( !count($labels) ){
			$this->addMessage("SOYCMS_NO_LABEL");
		}

		DisplayPlugin::toggle("must_exist_label", count($labels));

		$this->addForm("create_label");
		$this->addModel("create_label_caption",array(
			"placeholder" => UserInfoUtil::getSiteConfig("useLabelCategory") ? $this->getMessage("SOYCMS_LABEL_CREATE_PLACEHOLDER_WITH_GROUP")//ラベル名 または 分類名/ラベル名
																			 : $this->getMessage("SOYCMS_LABEL_CREATE_PLACEHOLDER"),//ラベル名 または 分類名/ラベル名
		));

		$this->addForm("reNameForm", array(
			"action"=>SOY2PageController::createLink("Label.Rename")
		));
		$this->addScript("js_param_for_label",array(
			"script"=>'var reNameLink = "'.SOY2PageController::createLink("Label.Rename").'";' .
					'var reDesciptionLink = "'.SOY2PageController::createLink("Label.ReDescription").'";' .
					'var ChangeLabelIconLink = "'.SOY2PageController::createLink("Label.ChangeLabelIcon").'";'
		));

		//アイコンリスト
		$this->createAdd("image_list","_component.Label.LabelIconListComponent",array(
			"list" => self::getLabelIconList()
		));

		//表示順更新フォーム
		$this->addForm("update_display_order_form");

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
	private function getLabelLists($classified = true){
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
	private function getLabelIconList(){

		$files = scandir(CMS_LABEL_ICON_DIRECTORY);

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
