<?php
class ExportPage extends WebPage{

    var $logic;

    function __construct() {
        parent::__construct();
        self::buildForm();

		//前にチェックした項目 jqueryで制御
		$this->addLabel("check_js", array(
			"html" => SOY2Logic::createInstance("logic.csv.ItemCheckListLogic")->buildJSCode("item")
		));
    }

    private function buildForm(){
        $this->addForm("export_form");

        //多言語化
        $this->createAdd("multi_language_item_name_list", "_common.Item.MultiLanguageItemNameListComponent", array(
            "list" => self::getLanguageList()
        ));

        //特別価格プラグイン周りの項目を表示する
        $this->createAdd("special_price_list","_common.Item.SpecialPriceExportListComponent", array(
            "list" => self::getSpecialPriceList()
        ));

        //カスタムフィールドリストを表示する
        $this->createAdd("customfield_list","_common.Item.CustomFieldImExportListComponent", array(
            "list" => self::getCustomFieldList()
        ));

        //カスタムサーチフィールドリストを表示する
        if(!defined("ITEM_CSV_IMEXPORT_MODE")) define("ITEM_CSV_IMEXPORT_MODE", "export");
        $this->createAdd("custom_search_field_list", "_common.Item.CustomSearchFieldImExportListComponent", array(
            "list" => self::getCustomSearchFieldList(),
            "languages" => self::getLanguageList()
        ));

        //商品オプションリストを表示する
        $this->createAdd("item_option_list", "_common.Item.ItemOptionImExportListComponent", array(
            "list" => self::getItemOptionList()
        ));

        SOYShopPlugin::load("soyshop.item.csv");
        $delegate = SOYShopPlugin::invoke("soyshop.item.csv");

        $this->createAdd("plugin_list", "_common.Item.PluginCSVListComponent", array(
            "list" => $delegate->getModules()
        ));

        //カテゴリ
        $this->createAdd("category_tree", "_base.MyTreeComponent", array(
            "list" => soyshop_get_category_objects(),
        ));

        $this->addModel("retry", array("visible" => (isset($_GET["retry"]))));

    }

    function getLabels(){
        return array(
            "id" => "id",

            "isOpen" => "公開状態",

            "name" => "商品名",
            "alias" => "URL",
            "code" => "商品コード",

            "config[list_price]" => "定価",
            "price" => "通常価格",
            "salePrice" => "セール価格",
            "saleFlag" => "セール中",

            "stock" => "在庫",
			"unit" => "単位",
            "category" => "カテゴリ",
            "type" => "商品タイプ",
            "detailPageId" => "商品詳細ページID",
            "config[keywords]" => "キーワード",
            "config[description]" => "説明",
            "config[image_small]" => "商品画像（小）",
            "config[image_large]" => "商品画像（大）",

            "orderPeriodStart" => "販売開始日",
            "orderPeriodEnd" => "販売終了日",
            "openPeriodStart" => "公開開始日",
            "openPeriodEnd" => "公開終了日",
        );
    }

    private function getLanguageList(){
        static $list;

        if(is_null($list)){
            SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
            if(SOYShopPluginUtil::checkIsActive("util_multi_language")){
                $list = UtilMultiLanguageUtil::allowLanguages();
            }else{
                $list[UtilMultiLanguageUtil::LANGUAGE_JP] = "日本語";
            }
        }

        return $list;
    }

    private function getSpecialPriceList(){
        SOY2::import("util.SOYShopPluginUtil");
        if(!SOYShopPluginUtil::checkIsActive("member_special_price")) return array();

        SOY2::import("module.plugins.member_special_price.util.MemberSpecialPriceUtil");
        return MemberSpecialPriceUtil::getConfig();
    }

    private function getCustomFieldList($flag = false){
        $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
        return SOYShop_ItemAttributeConfig::load($flag);
    }

    private function getCustomSearchFieldList(){
        SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
        return CustomSearchFieldUtil::getConfig();
    }

    private function getItemOptionList(){
		SOY2::import("module.plugins.common_item_option.util.ItemOptionUtil");
        return ItemOptionUtil::getOptions();
    }

    function doPost(){
        if(!soy2_check_token()){
            SOY2PageController::jump("Item.Export?retry");
            exit;
        }

        set_time_limit(0);

        //準備
        $logic = SOY2Logic::createInstance("logic.shop.item.ExImportLogic");
        $this->logic = $logic;

        $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

        //パラメータ
        $category_id = $_POST["category"];

        $format = $_POST["format"];
        $item = (isset($_POST["item"])) ? $_POST["item"] : array();

		//今回チェックした内容を保持する
		SOY2Logic::createInstance("logic.csv.ItemCheckListLogic")->save($item, "item");

        $displayLabel = @$format["label"];
        $logic->setSeparator(@$format["separator"]);
        $logic->setQuote(@$format["quote"]);
        $logic->setCharset(@$format["charset"]);

        //出力する項目にセット
        $logic->setItems($item);
        $logic->setLabels($this->getLabels());
        $logic->setCustomFields(self::getCustomFieldList(true));
        $logic->setLanguageItems(self::getLanguageList());
        $logic->setSpecialPrices(self::getSpecialPriceList());
        $logic->setCustomSearchFields(self::getCustomSearchFieldList());
        $logic->setItemOptions(self::getItemOptionList());

        //Plugin soyshop.item.csv
        SOYShopPlugin::load("soyshop.item.csv");
        $delegate = SOYShopPlugin::invoke("soyshop.item.csv", array("mode" => "export"));
        $logic->setModules($delegate->getModules());

        //カテゴリの親子取得
        $mappings = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->getMapping();

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
                if(strlen($category_id) > 0 && isset($mappings[$category_id])){
                    $items = $dao->getByCategories($mappings[$category_id]);
                }else{
                    $items = $dao->get();
                }
            }catch(Exception $e){
                $items = array();
            }

            //CSV(TSV)に変換
            $lines = self::itemToCSV($items);

            //出力
            self::outputFile($lines, $displayLabel);

        }while(count($items) >= $limit);

        exit;
    }

    /**
     * 商品データをCSVに変換する
     * カテゴリーは">"でつないだ文字列にする。
     */
    private function itemToCSV($items){
        $categories = soyshop_get_category_objects();

        $lines = array();
        foreach($items as $item){
            //CSVにはカテゴリは文字列で出力
            $category = $item->getCategory();

            if(strlen($category) > 0){
                $categoryChain = (isset($categories[$category])) ? $categories[$category]->getCategoryChain() : "";
                $item->setCategory($categoryChain);
            }

            //販売日の変換
            $item->setOrderPeriodStart(soyshop_convert_date_string($item->getOrderPeriodStart()));
            $item->setOrderPeriodEnd(soyshop_convert_date_string($item->getOrderPeriodEnd()));

            //公開日
            $item->setOpenPeriodStart(soyshop_convert_date_string($item->getOpenPeriodStart()));
            $item->setOpenPeriodEnd(soyshop_convert_date_string($item->getOpenPeriodEnd()));

            //CSVに変換
            $lines[] = $this->logic->export($item);
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
            header("Content-Disposition: attachment; filename=soyshop_items-".date("Ymd").".csv");
            header("Content-Type: text/csv; charset=" . $this->logic->getCharset() . ";");

            //ラベル：logic->export()の後で呼び出さないとカスタムフィールドのタイトルが入らない
            if($displayLabel){
                echo $this->logic->getHeader() . "\r\n";
            }
        }

        echo implode("\r\n", $lines) . "\r\n";
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("商品情報CSVエクスポート", array("Item" => "商品管理"));
	}

	function getFooterMenu(){
		try{
			return SOY2HTMLFactory::createInstance("Item.FooterMenu.ItemFooterMenuPage", array(
				"arguments" => array(null)
			))->getObject();
		}catch(Exception $e){
			//
			return null;
		}
	}

    function getScripts(){
        $root = SOY2PageController::createRelativeLink("./js/");
        return array(
            $root . "jquery/treeview/jquery.treeview.pack.js",
        );
    }

    function getCSS(){
        $root = SOY2PageController::createRelativeLink("./js/");
        return array(
            $root . "jquery/treeview/jquery.treeview.css",
            $root . "tree.css",
        );
    }
}
