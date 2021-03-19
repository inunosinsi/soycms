<?php

class ImportPage extends CMSWebPageBase {

	private $dao;

	function __construct(){
		parent::__construct();
	}

	function main(){
		$this->addForm("import_form", array(
             "ENCTYPE" => "multipart/form-data"
        ));

		$this->createAdd("custom_field_list", "_component.Entry.CustomFieldImExportListComponent", array(
            "list" => self::getCustomFieldList()
        ));

		$this->createAdd("custom_search_field_list", "_component.Entry.CustomSearchFieldImExportListComponent", array(
            "list" => self::getCustomSearchFieldList()
        ));

		$this->createAdd("plugin_list", "_component.Entry.PluginCSVListComponent", array(
            "list" => self::_getPlugins()
        ));

		//前にチェックした項目 jqueryで制御
		$this->addLabel("check_js", array(
			"html" => SOY2Logic::createInstance("logic.csv.ItemCheckListLogic")->buildJSCode("entry")
		));
	}

    function doPost(){

        //check token
        if(!soy2_check_token()){
			$this->jump("Entry.Import?fail");
            exit;
        }

        set_time_limit(0);

        $file  = $_FILES["import_file"];

        $logic = SOY2Logic::createInstance("logic.site.Entry.ExImportLogic");
        $format = $_POST["format"];
        $item = $_POST["item"];

		//今回チェックした内容を保持する
		SOY2Logic::createInstance("logic.csv.ItemCheckListLogic")->save($item, "entry");

		//$displayLabel = (isset($format["label"])) ? $format["label"] : null;
        if(isset($format["separator"])) $logic->setSeparator($format["separator"]);
        if(isset($format["quote"])) $logic->setQuote($format["quote"]);
        if(isset($format["charset"])) $logic->setCharset($format["charset"]);
        $logic->setItems($item);

        //$logic->setCustomFields($this->getCustomFieldList(true));

        if(!$logic->checkUploadedFile($file)){
            $this->jump("Entry.Import?fail");
            exit;
        }
        // if(!$logic->checkFileContent($file)){
		// 	$this->jump("Entry.Import?invalid");
        //     exit;
        // }

        //ファイル読み込み・削除
        $fileContent = file_get_contents($file["tmp_name"]);
        unlink($file["tmp_name"]);

        //データを行単位にばらす
        $lines = $logic->GET_CSV_LINES($fileContent);    //fix multiple lines

        //先頭行削除
        if(isset($format["label"])) array_shift($lines);

        //DAO
        $this->dao = SOY2DAOFactory::create("cms.EntryDAO");

		$attrDao = SOY2DAOFactory::create("cms.EntryAttributeDAO");

		//カスタムサーチフィールド
        $customSearchFieldDBLogic = SOY2Logic::createInstance("site_include.plugin.CustomSearchField.logic.DataBaseLogic");

        $this->dao->begin();

        //データ更新
        foreach($lines as $line){
            if(empty($line)) continue;

            list($obj, $attributes, $customSearchFields, $plugins) = $logic->import($line);
			$deleted = ($obj["id"] == "delete");

            $entry = self::import($obj);

            if(strlen($entry->getAlias()) > 0){

                if($deleted){
                    self::deleteItem($entry);
                }else{
                    $id = self::insertOrUpdate($entry);

					//カスタムフィールドアドバンスド
					foreach($attributes as $fieldId => $value){
						try{
							$attrDao->delete($id, $fieldId);
						}catch(Exception $e){
							//
						}

                        $attr = new EntryAttribute();
                        $attr->setEntryId($id);
                        $attr->setFieldId($fieldId);
                        $attr->setValue($value);
						try{
							$attrDao->insert($attr);
						}catch(Exception $e){
							//
						}
                    }

					//カスタムサーチフィールド
					if(is_array($customSearchFields) && count($customSearchFields)){
                        $customSearchFieldDBLogic->save($id, $customSearchFields);
                    }

					//プラグイン
					if(is_array($plugins) && count($plugins)){
						foreach($plugins as $pluginId => $value){
							CMSPlugin::callLocalPluginEventFunc('onEntryCSVImport', $pluginId, array("entryId" => $id, "value" => $value));
						}
					}
                }
            }
        }

        $this->dao->commit();

        $this->jump("Entry.Import?updated");
    }

    /**
     * CSV, TSVの一行からEntryを作り、返す
     *
     * idでチェックを行う
     *
     * @param String $line
     * @param Array $properties
     * @return Entry
     */
    private function import($obj){

        if(isset($obj["id"])) unset($obj["id"]);
        $entry = SOY2::cast("Entry", (object)$obj);

        try{
            $entry = $this->dao->getByAlias($entry->getAlias());
            SOY2::cast($entry, (object)$obj);
        }catch(Exception $e){
            //
        }

		//エイリアスがない場合はタイトルをエイリアスを入れる
		if(!strlen($entry->getAlias())) $entry->setAlias($entry->getTitle());

        return $entry;
    }

    /**
     * 商品データの更新または挿入を実行する
     * 同じメールアドレスのユーザがすでに登録されている場合に更新を行う
     * @param Entry
     * @return id
     */
    function insertOrUpdate(Entry $entry){
        if(strlen($entry->getId())){
            self::update($entry);
            return $entry->getId();
        }else{
            return self::insert($entry);
        }
    }

    /**
     * 商品データの挿入を実行する
     * @param Entry
     */
    private function insert(Entry $entry){
        try{
            return $this->dao->insert($entry);
        }catch(Exception $e){
            return null;
        }
    }

    /**
     * 商品データの更新を実行する
     * @param Entry
     */
    private function update(Entry $entry){

        try{
            $this->dao->update($entry);
        }catch(Exception $e){
			//
        }
    }

    private function deleteItem(Entry $entry){
        try{
            $this->dao->deleteByAlias($entry>getAlias());
        }catch(Exception $e){
			//
        }
    }

	private function getCustomFieldList($flag = false){
		$fname = UserInfoUtil::getSiteDirectory() . ".plugin/CustomFieldAdvanced.config";
		if(!file_exists($fname)) return array();

		include_once(SOY2::RootDir() . "site_include/plugin/CustomFieldAdvanced/CustomFieldAdvanced.php");
		$obj = unserialize(file_get_contents($fname));
		return $obj->customFields;
    }

	private function getCustomSearchFieldList(){
        SOY2::import("site_include.plugin.CustomSearchField.util.CustomSearchFieldUtil");
        return CustomSearchFieldUtil::getConfig();
    }

	private function _getPlugins(){
		$onLoads = CMSPlugin::getEvent('onEntryCSVExImport');
		if(!is_array($onLoads) || !count($onLoads)) return array();

		$plugins = array();
		foreach($onLoads as $pluginId => $plugin){
			$func = $plugin[0];
			if(!isset($func[0])) continue;
			$res = call_user_func($func, array());
			if(!is_string($res) || !strlen($res)) continue;
			$plugins[$pluginId] = $res;
		}
		return $plugins;
	}
}
