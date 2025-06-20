<?php

class EditorPage extends CMSWebPageBase{

	private $moduleId;
	private $modulePath;
	private $iniPath;

	function doPost(){

		if(soy2_check_token()){
			$edit = $_POST["Module"];

			//禁止文字が含まれているか？
			if(!SOY2Logic::createInstance("logic.site.Module.ModuleCreateLogic")->validate($edit["name"])){
				$this->jump("Module.Editor?invalid&moduleId=" . $_GET["moduleId"]);
			}

			//make ini
			$array = array();
			$array[] = "name=" . $edit["name"];
			$array[] = "content=" . rawurlencode($edit["content"]);

			file_put_contents($this->iniPath, implode("\n", $array));

			$funcName = str_replace(".", "_", substr($this->moduleId, strpos($this->moduleId, ".") + 1));

			//make php
			$array = array();
			$array[] = "<?php /* this script is generated by soycms. */"."\n";
			$array[] = "function soycms_" . $funcName . '(string $html, HTMLPage $htmlObj){'."\n";
			$array[] = "	ob_start();"."\n";
			$array[] = "?>";
			$array[] = trim($edit["content"]);
			$array[] = "<?php"."\n";
			$array[] = "	ob_end_flush();"."\n";
			$array[] = "}"."\n";
			$array[] = "?>";
			file_put_contents($this->modulePath, implode("",$array));

			$this->jump("Module.Editor?moduleId=" . $_GET["moduleId"]);
		}

	}

	function __construct(){

		//PHPモジュールの使用が許可されていない場合はモジュール一覧に遷移
		if(!defined("SOYCMS_ALLOW_PHP_MODULE") || !SOYCMS_ALLOW_PHP_MODULE) SOY2PageController::jump("Module");

		$this->moduleId = (isset($_GET["moduleId"])) ? htmlspecialchars(str_replace("/", ".", $_GET["moduleId"])) : null;

		$moduleDir = self::getModuleDirectory();

		$this->modulePath = $moduleDir . str_replace(".", "/", $this->moduleId) . ".php";
		$this->iniPath = $moduleDir . str_replace(".", "/", $this->moduleId) . ".ini";

		parent::__construct();

		$ini = @parse_ini_file($this->iniPath);

		$this->addForm("form");

		$this->addInput("module_id", array(
			"name" => "Module[id]",
			"value" => $this->moduleId,
			"disabled" => true,
		));

		$this->addInput("module_name", array(
			"name" => "Module[name]",
			"value" => ((isset($ini["name"]))) ? $ini["name"] : $this->moduleId,
		));

		$content = (isset($ini["content"])) ? $ini["content"] : "";
		// $this->addTextArea("module_content", array(
		// 	"name" => "Module[content]",
		// 	"value" => self::getModuleContent($content, file_get_contents($this->modulePath)),
		// ));

		$this->addLabel("module_content_ace", array(
			"text" => $this->getModuleContent($content, file_get_contents($this->modulePath))
		));

		$this->addLabel("module_example", array(
			"text" => "<!-- cms:module=\"" . $this->moduleId."\" -->\n" . @$ini["name"] . "のモジュールを読み込みます。\n<!-- /cms:module=\"" . $this->moduleId."\" -->",
		));

		//advanced_textarea
		// $this->addModel("advenced_textarea", array(
		// 	"attr:src" => SOY2PageController::createRelativeLink("js/tools/advanced_textarea.js") . "?" . SOYCMS_BUILD_TIME
		// ));
		// $this->addModel("insert_tab", array(
		// 	"attr:src" => SOY2PageController::createRelativeLink("js/tools/insert_tab.js") . "?" . SOYCMS_BUILD_TIME
		// ));

		$this->addModel("ace_editor", array(
			"attr:src" => SOY2PageController::createRelativeLink("js/ace/ace.js") . "?" . SOYCMS_BUILD_TIME
		));
	}

	private function getModuleContent($ini, $str){
		if(strlen($ini) > 0){
			preg_match('/\?>(.*)<\?php/s', $str, $match);
			return (isset($match[1])) ? trim($match[1]) : "";
		}

		$array = array();
		$array[] = "<?php";
		$array[] = "//ここにモジュールとして読み込むHTML・PHPを記述してください。";
		$array[] = '//使用可能な変数';
		$array[] = '//     $html	テンプレートに記述されたHTML';
		$array[] = '//     $htmlObj	ページオブジェクト($htmlObj->createAdd()が使えます)';
		$array[] = "?>";

		return implode("\n", $array);
	}

	private function getModuleDirectory(){
		return UserInfoUtil::getSiteDirectory() . ".module/";
	}
}
