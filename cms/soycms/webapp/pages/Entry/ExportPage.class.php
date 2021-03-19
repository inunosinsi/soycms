<?php

class ExportPage extends CMSWebPageBase {

	private $logic;

	function __construct(){
		parent::__construct();
	}

	function main(){
		$this->addForm("export_form");

		$this->createAdd("label_list", "_component.Label.LabelListComponent", array(
			"list" => self::getLabelLists(false)
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

	/**
	 *  ラベルオブジェクトのリストのリストを返す
	 * @param Boolean $classified ラベルを分けるかどうか
	 */
	private function getLabelLists($classified = true){
		$action = SOY2ActionFactory::createInstance("Label.CategorizedLabelListAction");
		$result = $action->run();

		if($result->success()){
			$labels = $result->getAttribute("list");
			return (isset($labels[""])) ? $labels[""] : array();
		}else{
			return array();
		}
	}

	private function getLabels(){
		return array(
			"id" => "id",
			"title" => "タイトル",
			"alias" => "エイリアス",
			"content" => "本文",
			"more" => "追記",
			"cdate" => "作成日時"
		);
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

	function doPost(){
		if(!soy2_check_token()){
			$this->jump("Entry.Export?retry");
            exit;
        }

        set_time_limit(0);

        //準備
        $logic = SOY2Logic::createInstance("logic.site.Entry.ExImportLogic");
		$this->logic = $logic;

        $dao = SOY2DAOFactory::create("cms.EntryDAO");

        $format = $_POST["format"];
        $item = $_POST["item"];

		//今回チェックした内容を保持する
		SOY2Logic::createInstance("logic.csv.ItemCheckListLogic")->save($item, "entry");

        $displayLabel = (isset($format["label"])) ? $format["label"] : null;
        if(isset($format["separator"])) $logic->setSeparator($format["separator"]);
        if(isset($format["quote"])) $logic->setQuote($format["quote"]);
        if(isset($format["charset"])) $logic->setCharset($format["charset"]);

        //出力する項目にセット
        $logic->setItems($item);
        $logic->setLabels(self::getLabels());
		$logic->setCustomFields(self::getCustomFieldList());
		$logic->setCustomSearchFields(self::getCustomSearchFieldList());
		$logic->setPlugins(self::_getPlugins());

        //DAO: 2000ずつ取得
        $limit = 2000;//16MB弱を消費
        $step = 0;
        $dao->setLimit($limit);

        do{
            if(connection_aborted())exit;

            $dao->setOffset($step * $limit);
            $step++;

            //データ取得
            try{
				$entries = $dao->get();
            }catch(Exception $e){
                $entries = array();
            }

			//ラベルはここで精査する
			if(count($entries) && isset($_POST["Label"]) && count($_POST["Label"])) $entries = self::refineByEntryLabels($entries);

            //CSV(TSV)に変換
            $lines = self::itemToCSV($entries);

            //出力
            self::outputFile($lines, $displayLabel);

        }while(count($entries) >= $limit);

        exit;
	}

	private function refineByEntryLabels($entries){
		static $dao, $labelCount;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryLabelDAO");
		if(is_null($labelCount)) $labelCount = count($_POST["Label"]);

		$list = array();
		foreach($entries as $entry){
			try{
				$labels = $dao->getByEntryId($entry->getId());
			}catch(Exception $e){
				continue;
			}

			if(!count($labels)) continue;

			//指定のラベルをすべて含むか？指定したラベルの個数とヒットの件数が一致するか？で調べる
			$cnt = 0;
			foreach($labels as $labelId => $label){
				$res = array_search($labelId, $_POST["Label"]);
				if(isset($res) && is_numeric($res)) $cnt++;
			}

			if($cnt === $labelCount) $list[] = $entry;
		}

		return $list;
	}

	/**
     * 商品データをCSVに変換する
     * カテゴリーは">"でつないだ文字列にする。
     */
    private function itemToCSV($entries){

        $lines = array();
        foreach($entries as $entry){
			/** 作成日等の表示の変更をここで行う **/

            //CSVに変換
            $lines[] = $this->logic->export($entry);
        }

        return $lines;
    }

	/**
     * ファイル出力：改行コードはCRLF
     */
    private function outputFile($lines, $displayLabel){
        static $headerSent = false;
        if(!$headerSent){
            $headerSent = true;
            header("Cache-Control: no-cache");
            header("Pragma: no-cache");
            header("Content-Disposition: attachment; filename=soycms_entries-".date("Ymd").".csv");
            header("Content-Type: text/csv; charset=" . $this->logic->getCharset() . ";");

            //ラベル：logic->export()の後で呼び出さないとカスタムフィールドのタイトルが入らない
            if($displayLabel){
                echo $this->logic->getHeader() . "\r\n";
            }
        }

        echo implode("\r\n", $lines) . "\r\n";
    }
}
