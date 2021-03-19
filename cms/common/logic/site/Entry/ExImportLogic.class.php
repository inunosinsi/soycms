<?php
SOY2::import("logic.csv.ExImportLogicBase");
class ExImportLogic extends ExImportLogicBase{

    private $customFields = array();
	private $customSearchFields = array();
	private $plugins = array();

	//作業用
	private $entryAttributeDAO;
	private $customSearchFieldDBLogic;

	/**
     * CSV,TSVに変換
     */
    function export($object){
        if(!$this->_func) $this->buildExFunc($this->getItems());
        $array = call_user_func($this->_func, $object, $this->getAttributes($object->getId()), $this->getCustomSearchFieldObject($object->getId()), $this->plugins);
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
        $function[] = '$res = array();$attributes=array();$customSearchFields=array();$plugins=array();';

        $items = array_keys($items);
        foreach($items as $key => $item){
            if(!$item) continue;

            $function[] = 'if(isset($items[' . $key . '])){ ';
            $function[] = '$item = trim($items[' . $key . ']);';

            if(preg_match('/customfield\(([^\)]+)\)/', $item, $tmp)){
                $function[] = '$attributes["' . $tmp[1] . '"] = $item;';
            }else if(preg_match('/custom_search_field\(([^\)]+)\)/', $item, $tmp)){
                $function[] = '$customSearchFields["' . $tmp[1] . '"] = $item;';
            }else if(preg_match('/plugins\(([^\)]+)\)/', $item, $tmp)){
                $function[] = '$plugins["' . $tmp[1] . '"] = $item;';
            }else{
                $function[] = '$res["' . $item . '"] = $item;';
            }
            $function[] = '}';
        }

        $function[] = 'return array($res,$attributes,$customSearchFields,$plugins);';
        $this->_func = function($items) use ($function){ return eval(implode("\n", $function)); };
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
			if(!$items)continue;

			//　カスタムフィールドアドバンスド
            if(preg_match('/customfield\(([^\)]+)\)/', $key, $tmp)){
				$fieldId = $tmp[1];

                if(isset($this->customFields[$fieldId])){
                    $function[] = '$res[] = (isset($attributes["' . $fieldId . '"])) ? $attributes["' . $fieldId . '"]->getValue() : "";';
                    $label = $this->customFields[$tmp[1]]->getLabel();
                }else{
                    $function[] = '$res[] = "";';
                    $label = "";
                }
			//カスタムサーチフィールド
			}else if(preg_match('/custom_search_field\(([^\)]+)\)/', $key, $tmp)){
				$fieldId = $tmp[1];
				if(isset($this->customSearchFields[$fieldId])){
                    $function[] = '$res[] = (isset($customSearchFields["' . $fieldId . '"])) ? $customSearchFields["' . $fieldId . '"] : "";';
                    $label = $this->customSearchFields[$tmp[1]]["label"];
                    $usedLabels[] = $label;
                }else{
                    $function[] = '$res[] = "";';
                    $usedLabels[] = "";
                }
                continue;
			//プラグイン
			}else if(preg_match('/plugins\((.*)\)$/', $key, $tmp)){
                $pluginId = $tmp[1];

                $function[] = 'if(isset($plugins["' . $pluginId . '"])) {$v=CMSPlugin::callLocalPluginEventFunc("onEntryCSVExport","' . $pluginId . '",array("entryId"=>$obj->getId()));$res[] = (isset($v["'.$pluginId.'"])) ? $v["'.$pluginId.'"] : "";}else{$res[]="";}';
                $label = (isset($this->plugins[$pluginId])) ? $this->plugins[$pluginId] : "";
            }else{
                $getter = "get" . ucwords($key);
                $function[] = '$res[] = (method_exists($obj,"'.$getter.'")) ? $obj->' . $getter . '() : "";';

                $label = $labels[$key];
            }

            //ラベル
            $usedLabels[] = $label;
        }

        $function[] = 'return $res;';

        $this->_func = function($obj,$attributes,$customSearchFields,$plugins) use ($function){ return eval(implode("\n", $function));};
        $this->setLabels($usedLabels);
    }

	function getAttributes($id){
        if(!$this->entryAttributeDAO) $this->entryAttributeDAO = SOY2DAOFactory::create("cms.EntryAttributeDAO");
        return $this->entryAttributeDAO->getByEntryId($id);
    }

	function getCustomSearchFieldObject($id){
        $res = array();
        if(file_exists(UserInfoUtil::getSiteDirectory() . ".plugin/CustomSearchField.active")){
            if(!$this->customSearchFieldDBLogic) $this->customSearchFieldDBLogic = SOY2Logic::createInstance("site_include.plugin.CustomSearchField.logic.DataBaseLogic");
            $res = $this->customSearchFieldDBLogic->getByEntryId($id);
        }
        return $res;
    }

	function setCustomFields($customFields) {
        $this->customFields = $customFields;
    }
	function setCustomSearchFields($customSearchFields){
		$this->customSearchFields = $customSearchFields;
	}
	function setPlugins($plugins) {
        $this->plugins = $plugins;
    }
}
