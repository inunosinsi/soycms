<?php

class DataBaseLogic extends SOY2LogicBase{

    const TABLE_NAME = "EntryCustomSearch";

    function __construct(){
        SOY2::import("site_include.plugin.CustomSearchField.util.CustomSearchFieldUtil");
    }

    function getTableName(){
    	return "EntryCustomSearch";
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
    function save($id, $values){

        $sets = array();
        foreach(CustomSearchFieldUtil::getConfig() as $key => $field){
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
						//空の値を消す
						for($i = 0; $i < count($values[$key]); $i++){
							if(!strlen($values[$key][$i])){
								unset($values[$key][$i]);
							}
						}
						if(count($values[$key])){
							$sets[$key] = implode(",", $values[$key]);
						}else {
							$sets[$key] = null;
						}

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
        self::insert($id, $sets);
    }

    private function insert($id, $sets){
        $columns = array();
        $values = array();
        $binds = array();

        $columns[] = "entry_id";
        $values[] = (int)$id;

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
			self::update($id, $columns, $values, $binds);
        }
    }

    private function update($id, $columns, $values, $binds){
        $sql = "UPDATE " . $this->getTableName() . " SET ";
        $first = true;
        foreach($columns as $i => $column){
            if($column == "item_id" || $column == "category_id") continue;
            if(!$first) $sql .= ", ";
            $first = false;
            $sql .= $column . " = " . $values[$i];
        }
        $sql .= " WHERE entry_id = " . $id;
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
        $sql .= " WHERE entry_id = :id";

        try{
            $dao->executeQuery($sql, array(":id" => $id));
        }catch(Exception $e){
            //
        }
    }

    /**
     *
     */
    function migrate(){
        $dao = new SOY2DAO();

        //すべての記事番号を取得する
        try{
            $results = $dao->executeQuery("SELECT id FROM EntryCustomSearch");
        }catch(Exception $e){
            $results = array();
        }

        if(!count($results)) return false;
        $attrDao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
        $configs = EntryAttribute::load(true);
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
                $entryId = (int)$v["id"];
                try{
                    $attrs = $attrDao->getByEntryId($entryId);
                }catch(Exception $e){
                    continue;
                }

                if(!count($attrs)) continue;


                $sets = array();
                foreach($attrs as $key => $attr){
                    if(array_search($key, $skeys) === false) continue;    //0を含むためにfalseにした
                    if(array_search($key, $keys) !== false && strlen($attr->getValue())) $sets[$key] = $attr->getValue();
                }

                self::insert($entryId, $sets);
            }
        }

        return true;
    }

    function getByEntryId($entryId){
        $dao = new SOY2DAO();

        try{
            $res = $dao->executeQuery("SELECT * FROM " . $this->getTableName() . " WHERE entry_id = :entry_id LIMIT 1", array(":entry_id" => $entryId));
        }catch(Exception $e){
            return array();
        }

        return (isset($res[0])) ? $res[0] : array();
    }
}
