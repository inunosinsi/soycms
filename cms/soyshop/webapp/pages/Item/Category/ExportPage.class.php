<?php
class ExportPage extends WebPage{

    private $logic;

    function __construct() {
        parent::__construct();

        DisplayPlugin::toggle("retry", isset($_GET["retry"]));

        self::buildForm();
    }

    private function buildForm(){
        $this->addForm("export_form");

        //多言語化
        $this->createAdd("multi_language_category_name_list", "_common.Category.MultiLanguageCategoryNameListComponent", array(
            "list" => self::getLanguageList()
        ));

        //カスタムフィールドリストを表示する
        $this->createAdd("customfield_list","_common.Item.CustomFieldImExportListComponent", array(
            "list" => self::getCustomFieldList()
        ));
    }

    function getLabels(){
        return array(
            "id" => "id",

            "name" => "カテゴリ名",
            "alias" => "カテゴリID",

            "order" => "表示順",

        );
    }

    private function getLanguageList(){
        if(!SOYShopPluginUtil::checkIsActive("util_multi_language")) return array();
        SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
        return UtilMultiLanguageUtil::allowLanguages();
    }

    function getCustomFieldList($flag = false){
        $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
        return SOYShop_CategoryAttributeConfig::load($flag);
    }

    function doPost(){
        if(!soy2_check_token()){
            SOY2PageController::jump("Item.Category.Export?retry");
            exit;
        }

        set_time_limit(0);

        $logic = SOY2Logic::createInstance("logic.shop.category.ExImportLogic");
        $this->logic = $logic;

        $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");

        $format = $_POST["format"];
        $item = $_POST["item"];

        $displayLabel = @$format["label"];
        $logic->setSeparator(@$format["separator"]);
        $logic->setQuote(@$format["quote"]);
        $logic->setCharset(@$format["charset"]);

        //出力する項目にセット
        $logic->setItems($item);
        $logic->setLabels($this->getLabels());
        $logic->setCustomFields(self::getCustomFieldList(true));
        $logic->setLanguageItems(self::getLanguageList());

        //DAO: 2000ずつ取得
        $limit = 2000;//16MB弱を消費（商品データの場合）
        $step = 0;
        $dao->setLimit($limit);

        do{
            if(connection_aborted())exit;

            $dao->setOffset($step * $limit);
            $step++;

            //データ取得
            try{
                $categories = $dao->get();
            }catch(Exception $e){
                $categories = array();
            }

            foreach($categories as $category){
                //CSV(TSV)に変換
                $lines[] = $logic->export($category);
            }

            //出力
            self::outputFile($lines, $displayLabel);

        }while(count($categories) >= $limit);

        exit;
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
            header("Content-Disposition: attachment; filename=soyshop_categories-".date("Ymd").".csv");
            header("Content-Type: text/csv; charset=" . $this->logic->getCharset() . ";");

            //ラベル：logic->export()の後で呼び出さないとカスタムフィールドのタイトルが入らない
            if($displayLabel){
                echo $this->logic->getHeader();
                echo "\r\n";
            }
        }
        self::outputData($lines);
    }

    /**
     * データ出力：改行コードはCRLF
     */
    private function outputData($lines){
        if(count($lines) > 0){
            echo implode("\r\n", $lines);
            echo "\r\n";
        }
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("カテゴリCSVエクスポート", array("Item" => "商品管理", "Item.Category" => "カテゴリ管理"));
	}

	function getFooterMenu(){
		try{
			return SOY2HTMLFactory::createInstance("Item.FooterMenu.CategoryFooterMenuPage")->getObject();
		}catch(Exception $e){
			//
			return null;
		}
	}
}
