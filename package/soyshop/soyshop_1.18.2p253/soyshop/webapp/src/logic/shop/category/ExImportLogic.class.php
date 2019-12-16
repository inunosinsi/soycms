<?php
SOY2::import("logic.csv.ExImportLogicBase");
SOY2::import("util.SOYShopPluginUtil");
class ExImportLogic extends ExImportLogicBase{

    private $customFields = array();

    //作業用
    private $categoryAttributeDAO;

    /**
     * CSV,TSVに変換
     */
    function export($object){
        if(!$this->_func) $this->buildExFunc($this->getItems());

        $array = call_user_func($this->_func, $object, $this->getAttributes($object->getId()));

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
        $function[] = '$res = array();$attributes = array();';

        $items = array_keys($items);
        foreach($items as $key => $item){
            if(!$item) continue;

            $function[] = 'if(isset($items[' . $key . '])){ ';
            $function[] = '$item = trim($items[' . $key . ']);';

            if(preg_match('/customfield\(([^\)]+)\)/', $item, $tmp)){
                $function[] = '$attributes["' . $tmp[1] . '"] = $item;';
            }else{
                $function[] = '$res["' . $item . '"] = $item;';
            }
            $function[] = '}';
        }

        $function[] = 'return array($res,$attributes);';
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

            if(preg_match('/customfield\(([^\)]+)\)/', $key, $tmp)){

                $fieldId = $tmp[1];

                if(isset($this->customFields[$fieldId])){
                    $function[] = '$res[] = (isset($attributes["' . $fieldId . '"])) ? $attributes["' . $fieldId . '"]->getValue() : "";';
                    $label = $this->customFields[$tmp[1]]->getLabel();
                }else{
                    $function[] = '$res[] = "";';
                    $label = "";
                }
            }else{
                $getter = "get" . ucwords($key);
                $function[] = '$res[] = (method_exists($obj,"'.$getter.'")) ? $obj->' . $getter . '() : "";';

                $label = $labels[$key];
            }

            //ラベル
            $usedLabels[] = $label;
        }

        $function[] = 'return $res;';

        $this->_func = function($obj,$attributes) use ($function) { return eval(implode("\n", $function)); };
        $this->setLabels($usedLabels);
    }

    function getAttributes($id){
        if(!$this->categoryAttributeDAO) $this->categoryAttributeDAO = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
        return $this->categoryAttributeDAO->getByCategoryId($id);
    }

    function getCustomFields() {
        return $this->customFields;
    }
    function setCustomFields($customFields) {
        $this->customFields = $customFields;
    }

    function setLanguageItems($languages){
        if(count($languages)){
            foreach($languages as $key => $v){
                if($key == UtilMultiLanguageUtil::LANGUAGE_JP) continue;
                $fieldId = "category_name_" . $key;
                $obj = new SOYShop_CategoryAttributeConfig();
                $obj->setFieldId($fieldId);
                $obj->setLabel("カテゴリ名(" . $key . ")");
                $this->customFields[$fieldId] = $obj;
            }
        }
    }
}
