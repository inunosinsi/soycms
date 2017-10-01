<?php

class SettingPage extends WebPage{

	private $configObj;
	private $fieldId;

	private $config;
	private $dbLogic;

	private $categories = array();

	function __construct(){
		$this->fieldId = (isset($_GET["field_id"])) ? $_GET["field_id"] : null;
		SOY2::import("module.plugins.user_custom_search_field.util.UserCustomSearchFieldUtil");
		$this->config = UserCustomSearchFieldUtil::getConfig();
		$this->dbLogic = SOY2Logic::createInstance("module.plugins.user_custom_search_field.logic.UserDataBaseLogic");
		$this->categories = self::getCategories();
		SOY2::import("domain.user.SOYShop_User");
	}

	function doPost(){

		if(soy2_check_token()){

			if(isset($_POST["set"])){

				if(count($_POST["users"])){
					foreach($_POST["users"] as $userId){
						$values = (isset($_POST["user_custom_search"]) && count($_POST["user_custom_search"])) ? $_POST["user_custom_search"] : null;
						$customs = $this->dbLogic->getByUserId($userId);
						foreach($values as $key => $v){
							$customs[$key] = $v;
						}
						$this->dbLogic->save($userId, $customs);
					}
				}

				$this->configObj->redirect("collective&field_id=" . $this->fieldId . "&updated");
			}
		}

	}

	function execute(){

		MessageManager::addMessagePath("admin");

		parent::__construct();

		DisplayPlugin::toggle("updated", isset($_GET["updated"]));

		self::buildSearchForm();

		$this->addForm("form");

		$field = $this->config[$this->fieldId];
		$this->addLabel("field_label", array(
			"text" => (isset($field["label"])) ? $field["label"] : ""
		));

		$this->addLabel("prefix", array(
			"text" => UserCustomSearchFieldUtil::PLUGIN_PREFIX
		));

		$this->addLabel("field_id", array(
			"text" => $this->fieldId
		));

		$this->addLabel("csf_form", array(
			"html" => self::buildForm($field)
		));

		SOY2::import("domain.config.SOYShop_ShopConfig");
		$this->createAdd("user_list", "_common.User.UserListComponent", array(
			"list" => self::getUsers(),
			"detailLink" => SOY2PageController::createLink("User.Detail."),
//			"config" => SOYShop_ShopConfig::load(),
			"appLimit" => true
		));
	}

	private function buildSearchForm(){

		//リセット
		if(isset($_POST["search_condition"])){
			foreach($_POST["search_condition"] as $key => $value){
				if(is_array($value)){
					//
				}else{
					if(!strlen($value)){
						unset($_POST["search_condition"][$key]);
					}
				}
			}
		}

		if(isset($_POST["search"]) && !isset($_POST["search_condition"])){
			self::setParameter("search_condition", null);
			$cnd = array();
		}else{
			$cnd = self::getParameter("search_condition");
		}
		//リセットここまで


		$this->addModel("search_area", array(
			"style" => (isset($cnd) && count($cnd)) ? "display:inline;" : "display:none;"
		));

		$this->addForm("search_form");

		$this->addLabel("csf_label", array(
			"text" => $this->config[$this->fieldId]["label"]
		));

		$this->addCheckBox("nothing_check", array(
			"name" => "search_condition[nothing]",
			"value" => 1,
			"selected" => (isset($cnd["nothing"])),
			"label" => "値の設定なし"
		));

		$this->addLabel("csf_cnd_form", array(
			"html" => self::buildSearchConditionForm($this->config[$this->fieldId], $cnd)
		));

		foreach(array("name", "reading") as $t){
			$this->addInput("search_" . $t, array(
				"name" => "search_condition[" . $t . "]",
				"value" => (isset($cnd[$t])) ? $cnd[$t] : ""
			));
		}
	}

	private function buildForm($field){
		SOY2::import("module.plugins." . $this->configObj->getModuleId() . ".component.FieldFormComponent");
		$h = array();
		$h[] = FieldFormComponent::buildForm($this->fieldId, $field);
		return implode("\n", $h);
	}

	private function buildSearchConditionForm($field, $cnd){
		SOY2::import("module.plugins." . $this->configObj->getModuleId() . ".component.FieldFormComponent");
		$h = array();
		$h[] = FieldFormComponent::buildSearchConditionForm($this->fieldId, $field, $cnd);
		return implode("\n", $h);
	}

	private function getUsers(){
		$searchLogic = SOY2Logic::createInstance("module.plugins." . $this->configObj->getModuleId() . ".logic.admin.SearchLogic", array("fieldId" => $this->fieldId));
		$searchLogic->setLimit(50);	//仮
		$searchLogic->setCondition(self::getParameter("search_condition"));
		return $searchLogic->get();
	}

	private function getCategories(){
		try{
			return SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->get();
		}catch(Exception $e){
			return array();
		}
	}

	private function getParameter($key){
		if(array_key_exists($key, $_POST)){
			$value = $_POST[$key];
			self::setParameter($key,$value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("Custom.Search:" . $key);
		}
		return $value;
	}
	private function setParameter($key,$value){
		SOY2ActionSession::getUserSession()->setAttribute("Custom.Search:" . $key, $value);
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
