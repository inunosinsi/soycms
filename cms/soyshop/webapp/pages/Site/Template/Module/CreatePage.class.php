<?php
/**
 * @class Site.Template.EditorPage
 * @date 2009-11-27T03:36:27+09:00
 * @author SOY2HTMLFactory
 */
class CreatePage extends WebPage{

	private $moduleId;
	private $modulePath;
	private $iniPath;
	private $moduleName;

	function doPost(){

		$this->moduleId = (isset($_POST["moduleId"])) ? htmlspecialchars(str_replace("/", ".", $_POST["moduleId"]), ENT_QUOTES, "UTF-8") : null;
		$this->moduleName = $_POST["moduleName"];
		if(strlen($this->moduleName) < 1) $this->moduleName = $this->moduleId;

		//禁止文字が含まれているか？
		if(!SOY2Logic::createInstance("logic.site.module.ModuleCreateLogic")->validate($this->moduleName)){
			SOY2PageController::jump("Site.Template.Module.CreatePage?invalid&moduleId=" . $this->moduleId);
		}

		$moduleDir = SOYSHOP_SITE_DIRECTORY . ".module/";

		$this->modulePath = $moduleDir . str_replace(".","/",$this->moduleId) . ".php";
		$this->iniPath = $moduleDir . str_replace(".","/",$this->moduleId) . ".ini";

		if(soy2_check_token()){
			if(preg_match('/^[a-zA-Z0-9\._]+$/', $this->moduleId) &&
			   strpos($this->moduleId, ".") &&
			   !preg_match("/^common./", $this->moduleId) &&
			   !preg_match("/^html./", $this->moduleId) &&
			   !file_exists($this->modulePath)
			){
				@mkdir(dirname($this->modulePath), 0766, true);
				file_put_contents($this->modulePath, "<?php ?>");
				file_put_contents($this->iniPath,"name=" . $this->moduleName);

				SOY2PageController::jump("Site.Template.Module.EditorPage?updated&moduleId=" . $this->moduleId);
			}else{
				//SOY2PageController::jump("Site.Template.Module.EditorPage?failed&moduleId=" . $this->moduleId);
			}
		}

	}

	function __construct($args){

		//PHPモジュールの使用が許可されていない場合はモジュール一覧に遷移
		if(!defined("SOYCMS_ALLOW_PHP_MODULE") || !SOYCMS_ALLOW_PHP_MODULE) SOY2PageController::jump("Site.Template");

		parent::__construct();

		if($this->moduleId)DisplayPlugin::visible("failed");
		DisplayPlugin::toggle("invalid", isset($_GET["invalid"]));

		$this->addForm("create_form");

		$this->addInput("module_id", array(
			"name" => "moduleId",
			"value" => (isset($_GET["moduleId"])) ? str_replace("/", ".", htmlspecialchars($_GET["moduleId"], ENT_QUOTES, "UTF-8")) : $this->moduleId
		));

		$this->addInput("module_name", array(
			"name" => "moduleName",
			"value" => $this->moduleName
		));
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("モジュールの追加", array("Site.Template#module_list" => "テンプレート管理"));
	}
}
