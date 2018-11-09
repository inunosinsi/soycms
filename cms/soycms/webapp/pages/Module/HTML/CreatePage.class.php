<?php

class CreatePage extends CMSWebPageBase{

	private $moduleId;
	private $moduleName;
	private $modulePath;
	private $iniPath;

	function doPost(){
		$this->moduleId = (isset($_POST["Module"]["id"])) ? htmlspecialchars($_POST["Module"]["id"]) : null;
		$this->moduleName = $_POST["Module"]["name"];
		if(strlen($this->moduleName) < 1) $this->moduleName = $this->moduleId;

		//禁止文字が含まれているか？
		if(!SOY2Logic::createInstance("logic.site.Module.ModuleCreateLogic")->validate($this->moduleName)){
			$this->jump("Module.HTML.Create?invalid&moduleId=" . $this->moduleId);
		}

		$moduleDir = self::getModuleDirectory();

		$this->modulePath = $moduleDir . str_replace(".", "/", $this->moduleId) . ".php";
		$this->iniPath = $moduleDir . str_replace(".", "/", $this->moduleId) . ".ini";

		if(soy2_check_token()){
			if(preg_match('/^[a-zA-Z0-9_]+$/', $this->moduleId) && !file_exists($this->modulePath)){
				@mkdir(dirname($this->modulePath), 0766, true);
				file_put_contents($this->modulePath, "<?php ?>");
				file_put_contents($this->iniPath, "name=" . $this->moduleName);

				$this->jump("Module.HTML.Editor?updated&moduleId=" . $this->moduleId);
			}else{
				//
			}
		}
	}

	function __construct(){
		parent::__construct();

		if($this->moduleId) DisplayPlugin::visible("failed");

		$this->addForm("form");

		$this->addInput("module_id", array(
			"name" => "Module[id]",
			"value" => (isset($_GET["moduleId"])) ? htmlspecialchars($_GET["moduleId"], ENT_QUOTES, "UTF-8") : $this->moduleId
		));

		$this->addInput("module_name", array(
			"name" => "Module[name]",
			"value" => $this->moduleName,
		));
	}

	private function getModuleDirectory(){
		return UserInfoUtil::getSiteDirectory() . ".module/html/";
	}
}
