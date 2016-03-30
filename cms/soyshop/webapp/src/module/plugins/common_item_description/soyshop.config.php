<?php
include(dirname(__FILE__) . "/common.php");
class CommonItemDescriptionConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		$form = SOY2HTMLFactory::createInstance("CommonItemDescriptionConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "商品詳細情報追加設定";
	}

}
SOYShopPlugin::extension("soyshop.config","common_item_description","CommonItemDescriptionConfig");

class CommonItemDescriptionConfigFormPage extends WebPage{

	function CommonItemDescriptionConfigFormPage(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}
	
	function doPost(){
		
		if(soy2_check_token()){
			if(isset($_POST["item_description_plugin"])){
				
				$names = $_POST["item_description_plugin"];
				$columns = $_POST["item_description_column"];
				$values = $_POST["item_description_html"];
				
				$array = array();
				for($i=0;$i<count($names);$i++){
					if(strlen($values[$i]) > 0){
						$obj = array();
						$obj["name"] = $names[$i];
						$obj["column"] = $columns[$i];
						$obj["value"] = $values[$i];
						$array[] = $obj;
					}
					
				}
				
				if(count($array) > 0){
					$value = soy2_serialize($array);
				}else{
					$value 	= "";
				}
				
				SOYShop_DataSets::put("item_description",$value);	
				SOY2PageController::jump("Config.Detail?plugin=common_item_description&updated");
			}
		}
	}
	
	function execute(){
		WebPage::WebPage();
		
		$this->createAdd("updated","HTMLModel", array(
			"visible" => (isset($_GET["updated"]))
		));
		
		$this->addForm("form");
		
		$class = new ItemDescriptionClass();

		$obj = SOYShop_DataSets::get("item_description", null);

		$html = array();		
		$html[] = "<h1>詳細説明の設定</h1>";
		$html[] = "<dl>";
		
		$counter = 1;
		if(!is_null($obj)){
			$values = soy2_unserialize($obj);
			
			for($i=0;$i<count($values);$i++){
				
				$html[] = "<dt>設定" . $counter."</dt>";
				$html[] = "<dd>";
			
				$html[] = "項目名 : " . $class->buildNameArea($values[$i]["name"])."<br />";
				$html[] = "項目ID : " . $class->buildColumnArea($values[$i]["column"])." (半角英数字)<br />";
				$html[] = "項目内容(HTML可) : " . $class->buildTextArea($values[$i]["value"])."<br />";
			
				$html[] = "</dd>";
				
				$counter++;
			}
			
		}
		
		$html[] = "<dt>設定" . $counter."</dt>";
		$html[] = "<dd>";
		
		$html[] = "項目名 : " . $class->buildNameArea() . "<br />";
		$html[] = "項目ID : " . $class->buildColumnArea() . " (半角英数字)<br />";
		$html[] = "項目内容(HTML可) : " . $class->buildTextArea() . "<br />";
		
		$html[] = "</dd>";
		
		$html[] = "</dl>";
		
		$this->createAdd("html","HTMLLabel", array(
			"html" => implode("\n", $html)
		));
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__) . "/soyshop.config.html";
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
}
?>
