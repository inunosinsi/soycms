<?php

class ReplacementStringConfigPage extends WebPage {

  private $configObj;

  function __construct(){
    SOY2::import("module.plugins.replacement_string.util.ReplacementStringUtil");
  }

  function doPost(){
    if(soy2_check_token()){
      $list = ReplacementStringUtil::getConfig();

      if(isset($_POST["add"])){

				$values = array();
				$values["symbol"] = trim(htmlspecialchars($_POST["symbol"], ENT_QUOTES, "UTF-8"));
				$values["string"] = trim(htmlspecialchars($_POST["string"], ENT_QUOTES, "UTF-8"));

				$list[] = $values;
				ReplacementStringUtil::saveConfig($list);
        $this->configObj->redirect("updated");
			}

      if(isset($_POST["change"])){
				foreach($list as $key => $values){
					if(isset($_POST["string"][$key])){
						$values["string"] = trim(htmlspecialchars($_POST["string"][$key], ENT_QUOTES, "UTF-8"));
					}

					$list[$key] = $values;
				}

        ReplacementStringUtil::saveConfig($list);
        $this->configObj->redirect("updated");
			}
    }
  }

  function execute(){
    WebPage::__construct();

    if(isset($_GET["remove"])){
			self::remove();
		}

    $this->addForm("form");
    $list = ReplacementStringUtil::getConfig();

    DisplayPlugin::toggle("has_symbol_list", count($list));

		$this->addForm("change_form");

		SOY2::import("module.plugins.replacement_string.component.ReplacementStringListComponent");
		$this->createAdd("string_list", "ReplacementStringListComponent", array(
			"list" => $list
		));
  }

  private function remove(){
		$list = ReplacementStringUtil::getConfig();
		if(isset($list[$_GET["remove"]])){
			unset($list[$_GET["remove"]]);
			//要素を詰める
			$array = array();
			if(count($list)){
				foreach($list as $values){
					$array[] = $values;
				}
			}

      ReplacementStringUtil::saveConfig($list);
      $this->configObj->redirect("updated");
		}
	}

  function setConfigObj($configObj){
    $this->configObj = $configObj;
  }
}
