<?php
SOY2::import("logic.csv.ExImportLogicBase");
class ExImportLogic extends ExImportLogicBase{

	private $func;

	/**
	 * CSV,TSVに変換
	 */
	function export($object){
		if(!$this->_func)$this->buildExFunc($this->getItems());
		$array = call_user_func($this->_func,$object);
		return $this->encodeTo($this->implodeToLine($array));
	}

	/**
	 * CSV,TSVの一行からオブジェクトに変換
	 */
	function import($line){
		$line = $this->encodeFrom($line);
		$items = $this->explodeLine($line);
		if(!$this->_func)$this->buildImFunc($this->getItems());
		return call_user_func($this->_func,$items);
	}

	/**
	 * import用のfunction
	 */
	function buildImFunc($items){
		$function = array();
		$function[] = '$res = array();';

		$items = array_keys($items);
		foreach($items as $key => $item){
			if(!$item)continue;

			$function[] = 'if(isset($items['.$key.'])){ ';
			$function[] = '$item = trim($items['.$key.']);';
			$function[] = '$res["'.$item.'"] = $item;';
			$function[] = '}';
		}

		$function[] = 'return $res;';
		$this->_func = function($items) use ($function) { return eval(implode("\n", $function)); };
	}

	/**
	 * export用のfunction
	 */
	function buildExFunc($items){
		$labels = $this->getLabels();
		$usedLabels = array();
		$function = array();
		$function[] = '$res = array();';
		foreach($items as $key => $item){
			if(!$item)continue;

			$getter = "get" . ucwords($key);
			$function[] = '$res[] = (method_exists($obj,"'.$getter.'")) ? $obj->' . $getter . '() : "";';

			$label = $labels[$key];

			//ラベル
			$usedLabels[] = $label;
		}
		$function[] = 'return $res;';

		$this->_func = function($obj) use ($function) { return eval(implode("\n", $function)); };
		$this->setLabels($usedLabels);
	}
}
?>
