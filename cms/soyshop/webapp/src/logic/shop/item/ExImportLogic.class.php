<?php
SOY2::import("logic.csv.ExImportLogicBase");
class ExImportLogic extends ExImportLogicBase{

	private $customFields = array();
	private $itemOptions = array();
	private $modules = array();

	//作業用
	private $itemAttributeDAO;

	/**
	 * CSV,TSVに変換
	 */
	function export($object){
		if(!$this->_func) $this->buildExFunc($this->getItems());
		
		$array = call_user_func($this->_func, $object, $this->getAttributes($object->getId()), $this->modules);

		return $this->encodeTo($this->implodeToLine($array));
	}

	/**
	 * CSV,TSVの一行からオブジェクトに変換
	 */
	function import($line){
		$line = $this->encodeFrom($line);
		$items = $this->explodeLine($line);
		if(!$this->_func) $this->buildImFunc($this->getItems());
		return call_user_func($this->_func, $items);
	}

	/**
	 * import用のfunction
	 */
	function buildImFunc($items){
		$function = array();
		$function[] = '$res = array();$attributes = array();$plugins=array();';

		$items = array_keys($items);
		foreach($items as $key => $item){
			if(!$item) continue;
			
			$function[] = 'if(isset($items[' . $key . '])){ ';
			$function[] = '$item = trim($items[' . $key . ']);';

			if(preg_match('/customfield\(([^\)]+)\)/', $item, $tmp)){
				$function[] = '$attributes["' . $tmp[1] . '"] = $item;';
			}else if(preg_match('/item_option\(([^\)]+)\)/', $item, $tmp)){
				$function[] = '$attributes["item_option_' . $tmp[1] . '"] = $item;';
			}else if(preg_match('/plugins\((.*)\)$/',$item,$tmp)){
				$function[] = '$plugins["' . $tmp[1] . '"] = $item;';
			}else if(preg_match('/config\(([^\)]+)\)/', $item, $tmp)){
				$function[] = '$res["config"]["' . $tmp[1] . '"] = $item;';
			}else{
				$function[] = '$res["' . $item . '"] = $item;';
			}
			$function[] = '}';
		}
		
		$function[] = 'return array($res,$attributes,$plugins);';
		$this->_func = create_function('$items', implode("\n", $function));
	}

	/**
	 * export用のfunction
	 */
	function buildExFunc($items){
		$labels = $this->getLabels();
		$usedLabels = array();
		$function = array();
		$function[] = '$config = $obj->getConfigObject();';
		$function[] = '$res = array();';
		foreach($items as $key => $item){
			if(!$item)continue;

			if(preg_match('/config\(([^\)]+)\)/', $key, $tmp)){
				$function[] = '$res[] = @$config["' . $tmp[1] . '"];';

				//label用に置換
				$key = str_replace(array("(",")"), array("[","]"), $key);
				$label = $labels[$key];

			}else if(preg_match('/customfield\(([^\)]+)\)/', $key, $tmp)){

				$fieldId = $tmp[1];

				if(isset($this->customFields[$fieldId])){
					$function[] = '$res[] = (isset($attributes["' . $fieldId . '"])) ? $attributes["' . $fieldId . '"]->getValue() : "";';
					$label = $this->customFields[$tmp[1]]->getLabel();
				}else{
					$function[] = '$res[] = "";';
					$label = "";
				}
				
			}else if(preg_match('/item_option\(([^\)]+)\)/', $key, $tmp)){
				$optionId = $tmp[1];
				
				//商品オプションはカスタムフィールドの値の扱い方と同じ
				if(isset($this->itemOptions[$optionId]["name"])){
					$fieldId = "item_option_" . $optionId;
					$function[] = '$res[] = (isset($attributes["' . $fieldId . '"])) ? str_replace("\r\n", ",", $attributes["' . $fieldId . '"]->getValue()) : "";';
					$label = $this->itemOptions[$optionId]["name"];
				}else{
					$function[] = '$res[] = "";';
					$lable = "";
				}
				
			}else if(preg_match('/plugins\((.*)\)$/', $key, $tmp)){

				$pluginId = $tmp[1];

				$function[] = '$res[] = (isset($modules["' . $pluginId . '"])) ? $modules["' . $pluginId . '"]["plugin"]->export($obj->getId()) : "";';
				$label = (isset($this->modules[$pluginId])) ? $this->modules[$pluginId]["label"] : "";

			}else{
				$getter = "get" . ucwords($key);
				$function[] = '$res[] = (method_exists($obj,"'.$getter.'")) ? $obj->' . $getter . '() : "";';

				$label = $labels[$key];
			}

			//ラベル
			$usedLabels[] = $label;
		}

		$function[] = 'return $res;';
		
		$this->_func = create_function('$obj,$attributes,$modules', implode("\n", $function));//array($obj,$attributes,$options,$modules)
		$this->setLabels($usedLabels);
	}

	function getAttributes($id){
		if(!$this->itemAttributeDAO) $this->itemAttributeDAO = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		return $this->itemAttributeDAO->getByItemId($id);
	}
	
	function getCustomFields() {
		return $this->customFields;
	}
	function setCustomFields($customFields) {
		$this->customFields = $customFields;
	}
	
	function getItemOptions(){
		return $this->itemOptions;
	}
	function setItemOptions($itemOptions){
		$this->itemOptions = $itemOptions;
	}

	function getModules() {
		return $this->modules;
	}
	function setModules($modules) {
		$this->modules = $modules;
	}
}
?>