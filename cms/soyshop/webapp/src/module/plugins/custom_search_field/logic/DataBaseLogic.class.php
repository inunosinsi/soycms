<?php

class DataBaseLogic extends SOY2LogicBase{

    const MODE_ITEM = "item";
    const MODE_CATEGORY = "category";

    //モードは必ず指定
    private $mode = self::MODE_ITEM;
    const TABLE_NAME = "soyshop_custom_search";

    function __construct(){
        SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
        SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
    }

    function getTableName(){
      switch($this->getMode()){
        case self::MODE_CATEGORY:
          return "soyshop_category_custom_search";
        default:
          return "soyshop_custom_search";
      }
    }

    function addColumn($key, $type){
        if(!preg_match("/^[[0-9a-zA-Z-_]+$/", $key)) return false;

        $sql = "ALTER TABLE " . $this->getTableName() . " ADD COLUMN " . $key . " ";

        switch($type){
            case CustomSearchFieldUtil::TYPE_INTEGER:
            case CustomSearchFieldUtil::TYPE_RANGE:
                $sql .= "INTEGER";
                break;
            case CustomSearchFieldUtil::TYPE_TEXTAREA:
            case CustomSearchFieldUtil::TYPE_RICHTEXT:
                $sql .= "TEXT";
                break;
            default:
                $sql .= "VARCHAR(255)";
        }

        $dao = new SOY2DAO();

        try{
            $dao->executeUpdateQuery($sql, array());
        }catch(Exception $e){
            return false;
        }

        return true;
    }

    function deleteColumn($key){
        if(!preg_match("/^[[0-9a-zA-Z-_]+$/", $key)) return;

        $dao = new SOY2DAO();
        try{
            $dao->executeUpdateQuery("ALTER TABLE " . $this->getTableName() . " DROP COLUMN " . $key, array());
        }catch(Exception $e){
            //SQLiteではカラムを削除できない
        }
    }

    /**
     * @params itemId integer, values array(array("field_id" => string))
     */
    function save($id, $values, $lang = null){

        $sets = array();
        if(is_null($lang)) $lang = SOYSHOP_PUBLISH_LANGUAGE;
        $langId = UtilMultiLanguageUtil::getLanguageId($lang);

        switch($this->getMode()){
			case self::MODE_CATEGORY:
				$configs = CustomSearchFieldUtil::getCategoryConfig();
				break;
			default:
				$configs = CustomSearchFieldUtil::getConfig();
        }


        foreach($configs as $key => $field){
            if(!isset($values[$key])) {
                $sets[$key] = null;
                continue;
            }

            switch($field["type"]){
                case CustomSearchFieldUtil::TYPE_INTEGER:
                case CustomSearchFieldUtil::TYPE_RANGE:
                    $sets[$key] = (is_numeric($values[$key])) ? (int)$values[$key] : null;
                    break;
                case CustomSearchFieldUtil::TYPE_CHECKBOX:
                    if(is_array($values[$key]) && count($values[$key])){
                        $sets[$key] = implode(",", $values[$key]);

                    //一括更新の際は、そのまま値を入れなければならない 一応条件分岐は残しておく
                    }elseif(strpos($values[$key], ",")){
                        $sets[$key] = trim($values[$key]);

                    //値が一つの時はカンマがないので未加工で挿入する
                    }elseif(strlen($values[$key])){
                        $sets[$key] = trim($values[$key]);

                    //その他の処理
                    }else{
                        $sets[$key] = null;
                    }
                    break;
                default:
                    $sets[$key] = (strlen($values[$key])) ? $values[$key] : null;
            }
        }
        $this->insert($id, $sets, $langId);
    }

    function insert($id, $sets, $langId){
        $columns = array();
        $values = array();
        $binds = array();

        switch($this->getMode()){
        	case self::MODE_CATEGORY:
        		$columns[] = "category_id";
        		break;
        	default:
            	$columns[] = "item_id";
        }

        $values[] = (int)$id;
        $columns[] = "lang";
        $values[] = (int)$langId;

        foreach($sets as $key => $value){
            $columns[] = $key;
            $values[] = ":" . $key;
            $binds[":" . $key] = $value;
        }

        $sql = "INSERT INTO " . $this->getTableName() . " ".
                "(" . implode(",", $columns) . ") ".
                "VALUES (" . implode(",", $values) . ")";

        $dao = new SOY2DAO();

        try{
            $dao->executeQuery($sql, $binds);
        }catch(Exception $e){
            $this->update($id, $columns, $values, $binds, $langId);
        }
    }

    function update($id, $columns, $values, $binds, $langId){
        $sql = "UPDATE " . $this->getTableName() . " SET ";
        $first = true;
        foreach($columns as $i => $column){
            if($column == "item_id" || $column == "category_id") continue;
            if(!$first) $sql .= ", ";
            $first = false;
            $sql .= $column . " = " . $values[$i];
        }
        switch($this->getMode()){
          case self::MODE_CATEGORY:
            $sql .= " WHERE category_id = " . $id;
            break;
          default:
            $sql .= " WHERE item_id = " . $id;
        }
        $sql .= " AND lang = " . $langId;
        $dao = new SOY2DAO();
        try{
            $dao->executeUpdateQuery($sql, $binds);
        }catch(Exception $e){
            //
        }
    }

    function delete($id){
        $dao = new SOY2DAO();

        $sql = "DELETE FROM " . $this->getTableName();
        switch($this->getMode()){
          case self::MODE_CATEGORY:
            $sql .= " WHERE item_id = :id";
            break;
          default:
            $sql .= " WHERE category_id = :id";
        }

        try{
            $dao->executeQuery($sql, array(":id" => $id));
        }catch(Exception $e){
            //
        }
    }

    /**
     * @ToDo カテゴリカスタムサーチフィールド対応
     */
    function migrate(){
        $dao = new SOY2DAO();

        //すべての商品番号を取得する
        try{
            $results = $dao->executeQuery("SELECT id FROM soyshop_item");
        }catch(Exception $e){
            $results = array();
        }

        if(!count($results)) return false;
        $attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
        $configs = SOYShop_ItemAttributeConfig::load(true);
        $keys = array_keys($configs);

        $keysTmp = array();
        foreach($keys as $key){
            if($configs[$key]->getType() == "image" || $configs[$key]->getType() == "file" || $configs[$key]->getType() == "link"){
                //何もしない
            }else{
                $keysTmp[] = $key;
            }
        }
        $keys = $keysTmp;
        unset($configs);
        unset($keysTmp);

        if(!count($keys)) return false;

        //カスタムサーチフィールドの方のフィールドID
        $skeys = array_keys(CustomSearchFieldUtil::getConfig());

        foreach($results as $v){
            if(isset($v["id"]) && is_numeric($v["id"])){
                $itemId = (int)$v["id"];
                try{
                    $attrs = $attrDao->getByItemId($itemId);
                }catch(Exception $e){
                    continue;
                }

                if(!count($attrs)) continue;


                $sets = array();
                foreach($attrs as $key => $attr){
                    if(array_search($key, $skeys) === false) continue;    //0を含むためにfalseにした
                    if(array_search($key, $keys) !== false && strlen($attr->getValue())) $sets[$key] = $attr->getValue();
                }

                $this->insert($itemId, $sets);
            }
        }

        return true;
    }

    function getByItemId($itemId, $lang=null){
        $dao = new SOY2DAO();

        if(is_null($lang)) $lang = SOYSHOP_PUBLISH_LANGUAGE;

        try{
            $res = $dao->executeQuery("SELECT * FROM " . $this->getTableName() . " WHERE item_id = :item_id AND lang = :lang LIMIT 1", array(":item_id" => $itemId, ":lang" => UtilMultiLanguageUtil::getLanguageId($lang)));
        }catch(Exception $e){
            return array();
        }

        return (isset($res[0])) ? $res[0] : array();
    }

    function getByCategoryId($categoryId, $lang=null){
        $dao = new SOY2DAO();

        if(is_null($lang)) $lang = SOYSHOP_PUBLISH_LANGUAGE;

        try{
            $res = $dao->executeQuery("SELECT * FROM " . $this->getTableName() . " WHERE category_id = :category_id AND lang = :lang LIMIT 1", array(":category_id" => $categoryId, ":lang" => UtilMultiLanguageUtil::getLanguageId($lang)));
        }catch(Exception $e){
            return array();
        }

        return (isset($res[0])) ? $res[0] : array();
    }

    function getMode(){
      return $this->mode;
    }

    function setMode($mode){
      $this->mode = $mode;
    }
}
