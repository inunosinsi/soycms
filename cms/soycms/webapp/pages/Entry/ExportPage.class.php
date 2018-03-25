<?php

class ExportPage extends CMSWebPageBase {

	private $logic;

	function __construct(){
		parent::__construct();
	}

	function main(){
		$this->addForm("export_form");
	}

	private function getLabels(){
		return array(
			"id" => "id",
			"title" => "タイトル",
			"alias" => "エイリアス",
			"content" => "本文",
			"more" => "追記"
		);
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

        $displayLabel = (isset($format["label"])) ? $format["label"] : null;
        if(isset($format["separator"])) $logic->setSeparator($format["separator"]);
        if(isset($format["quote"])) $logic->setQuote($format["quote"]);
        if(isset($format["charset"])) $logic->setCharset($format["charset"]);

        //出力する項目にセット
        $logic->setItems($item);
        $logic->setLabels(self::getLabels());
		// $logic->setCustomFields(self::getCustomFieldList(true));

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

            //CSV(TSV)に変換
            $lines = self::itemToCSV($entries);

            //出力
            self::outputFile($lines, $displayLabel);

        }while(count($entries) >= $limit);

        exit;
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
