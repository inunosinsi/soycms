<?php

class SearchLogic extends SOY2LogicBase{

    private $where = array();
    private $binds = array();
    private $itemDao;

    function __construct(){
        SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
        $this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
        SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
    }

    /**
     * @params int current:現在のページ, int limit:一ページで表示する商品
     * @return array<SOYShop_Item>
     */
    function search($obj, $current, $limit){
        self::setCondition();

        $sql = "SELECT DISTINCT s.item_id, s.*, i.* " .
                "FROM soyshop_item i ".
                "INNER JOIN soyshop_custom_search s ".
                "ON i.id = s.item_id ";
        $sql .= self::buildWhere();    //カウントの時と共通の処理は切り分ける
        $sort = self::buildOrderBySQLOnSearchPage($obj->getPageObject());
        if(isset($sort)) $sql .= $sort;

        //表示件数
        $sql .= " LIMIT " . (int)$limit;

        //OFFSET
        $offset = $limit * ($current - 1);
        if($offset > 0) $sql .= " OFFSET " . $offset;

        try{
            $res = $this->itemDao->executeQuery($sql, $this->binds);
        }catch(Exception $e){
            return array();
        }

        if(!count($res)) return array();

        $items = array();
        foreach($res as $v){
            $items[] = $this->itemDao->getObject($v);
        }

        return $items;
    }

    function getTotal(){
        self::setCondition();

        $sql = "SELECT COUNT(id) AS total " .
                "FROM soyshop_item i ".
                "INNER JOIN soyshop_custom_search s ".
                "ON i.id = s.item_id ";
        $sql .= self::buildWhere();    //カウントの時と共通の処理は切り分ける

        try{
            $res = $this->itemDao->executeQuery($sql, $this->binds);
        }catch(Exception $e){
            return 0;
        }

        return (isset($res[0]["total"])) ? (int)$res[0]["total"] : 0;
    }

    private function buildWhere(){
        $config = CustomSearchFieldUtil::getSearchConfig();

        $where = "WHERE i.open_period_start < :now ".
                "AND i.open_period_end > :now ".
                "AND i.item_is_open = 1 ".
                "AND i.is_disabled != 1 ";

        $item_where = array();

        //通常商品を表示
        if(isset($config["search"]["single"]) && (int)$config["search"]["single"] === 1){
            $item_where[] = "i.item_type = \"" . SOYShop_Item::TYPE_SINGLE . "\"";
        }

        //親商品を表示
        if(isset($config["search"]["parent"]) && (int)$config["search"]["parent"] === 1){
            $item_where[] = "i.item_type = \"" . SOYShop_Item::TYPE_GROUP . "\"";
        }

        //子商品を表示
        if(isset($config["search"]["child"]) && (int)$config["search"]["child"] === 1){
            $item_where[] = "(i.item_type != \"" . SOYShop_Item::TYPE_SINGLE . "\" AND i.item_type != \"" . SOYShop_Item::TYPE_GROUP . "\" AND i.item_type != \"" . SOYShop_Item::TYPE_DOWNLOAD . "\") ";

            //SQLiteでREGEXPを使用できないサーバがあるみたい
            if(SOY2DAOConfig::type() == "mysql"){
                $item_where[] = "i.item_type REGEXP '^[0-9]+$'";
            }
        }

        //ダウンロード商品を表示
        if(isset($config["search"]["download"]) && (int)$config["search"]["download"] === 1){
            $item_where[] = "i.item_type = \"" . SOYShop_Item::TYPE_DOWNLOAD . "\"";
        }

        if(count($item_where)){
            $where .= "AND (" .implode(" OR ", $item_where) .") ";
        }

        foreach($this->where as $key => $w){
            if(!strlen($w)) continue;
            $where .= "AND " . $w . " ";
        }
        return $where;
    }

    private function setCondition(){
        if(!count($this->where)){
            //SOYShop_Itemの値
            if(isset($_GET["c_search"]["item_name"]) && strlen($_GET["c_search"]["item_name"])) {
                //日本語検索
                if(SOYSHOP_PUBLISH_LANGUAGE == "jp"){
                    $this->where["item_name"] = "i.item_name LIKE :item_name";
                    $this->binds[":item_name"] = "%" . trim($_GET["c_search"]["item_name"]) . "%";
                //多言語検索
                }else{
                    $this->where["item_name"] = "id IN (SELECT item_id FROM soyshop_item_attribute WHERE item_field_id = 'item_name_" . SOYSHOP_PUBLISH_LANGUAGE . "' AND item_value LIKE :item_name)";
                    $this->binds[":item_name"] = "%" . trim($_GET["c_search"]["item_name"]) . "%";
                }
            }

            if(isset($_GET["c_search"]["item_code"]) && strlen($_GET["c_search"]["item_code"])) {
                $this->where["item_code"] = "i.item_code LIKE :item_code";
                $this->binds[":item_code"] = "%" . trim($_GET["c_search"]["item_code"]) . "%";
            }

            //カテゴリー
            if(isset($_GET["c_search"]["item_category"]) && is_numeric($_GET["c_search"]["item_category"])){
				//小カテゴリの商品も引っ張ってこれる様にする
                $maps = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->getMapping();
                $catId = (int)trim($_GET["c_search"]["item_category"]);
                if(isset($maps[$catId])){
                    $this->where["item_category"] = " i.item_category IN (" . implode(",", $maps[$catId]) . ")";
                }
            }

            $pmin = "";$pmax = "";
            if(isset($_GET["c_search"]["item_price_min"]) && strlen($_GET["c_search"]["item_price_min"]) && is_numeric($_GET["c_search"]["item_price_min"])) {
                $pmin = "i.item_price >= :item_price_min";
                $this->binds[":item_price_min"] = (int)$_GET["c_search"]["item_price_min"];
            }

            if(isset($_GET["c_search"]["item_price_max"]) && strlen($_GET["c_search"]["item_price_max"]) && is_numeric($_GET["c_search"]["item_price_max"])) {
                $pmax = "i.item_price <= :item_price_max";
                $this->binds[":item_price_max"] = (int)$_GET["c_search"]["item_price_max"];
            }

            if(strlen($pmin) && strlen($pmax)){
                $this->where["item_price"] = "(" . $pmin . " AND " . $pmax . ")";
            }else{
                $this->where["item_price"] = $pmin . $pmax;
            }

            foreach(CustomSearchFieldUtil::getConfig() as $key => $field){

                //まずは各タイプのfield SQLでkeyを指定する場合、s.を付けること。soyshop_custom_searchのaliasがs
                switch($field["type"]){
                    //文字列の場合
                    case CustomSearchFieldUtil::TYPE_STRING:
                    case CustomSearchFieldUtil::TYPE_TEXTAREA:
                    case CustomSearchFieldUtil::TYPE_RICHTEXT:
						if(isset($_GET["c_search"][$key])){
							//文字列として検索
							if(is_string($_GET["c_search"][$key]) && strlen($_GET["c_search"][$key])){
								//否定として検索
								if(isset($field["denial"]) && $field["denial"] == 1){
									$this->where[$key] = "s." . $key . " != :" . $key;
									$this->binds[":" . $key] = trim($_GET["c_search"][$key]);
								}else{
									$this->where[$key] = "s." . $key . " LIKE :" . $key;
									$this->binds[":" . $key] = "%" . trim($_GET["c_search"][$key]) . "%";
								}
							//配列として検索
							}else if(is_array($_GET["c_search"][$key]) && count($_GET["c_search"][$key])){
								$w = array();
	                            foreach($_GET["c_search"][$key] as $i => $v){
	                                if(!strlen($v)) continue;
	                                $w[] = "s." . $key . " LIKE :" . $key . $i;
	                                $this->binds[":" . $key . $i] = "%" . trim($v) . "%";
	                            }
	                            if(count($w)) $this->where[$key] = "(" . implode(" OR ", $w) . ")";
							}
						}
                        break;

                    //範囲の場合
                    case CustomSearchFieldUtil::TYPE_RANGE:
						//配列で渡す
						if(isset($_GET["c_search"][$key]) && is_array($_GET["c_search"][$key])){
							$cnt = 0;
							$rWhere = array();
							$cnds = $_GET["c_search"][$key];
							for($i = 0; $i < count($cnds); $i++){
								$ws = "";
								$we = "";
								if(isset($cnds[$i]["start"][0]) && is_numeric($cnds[$i]["start"][0])){
									$symbol = (isset($cnds[$i]["start"][1]) && $cnds[$i]["start"][1]) ? ">=" : ">";
									$ws = "s." . $key . " " . $symbol . " :" . $key . "_start_" . $i;
		                            $this->binds[":" . $key . "_start_" . $i] = (int)$cnds[$i]["start"][0];
								}

								if(isset($cnds[$i]["end"][0]) && is_numeric($cnds[$i]["end"][0])){
									$symbol = (isset($cnds[$i]["end"][1]) && $cnds[$i]["end"][1]) ? "<=" : "<";
									$we = "s." . $key .  " " . $symbol . " :" . $key . "_end_" . $i;
									$this->binds[":" . $key . "_end_" . $i] = (int)$cnds[$i]["end"][0];
								}

								if(strlen($ws) && strlen($we)){
		                            $rWhere[] = "(" . $ws . " AND " . $we . ")";
		                        }else if(strlen($ws) || strlen($we)){
		                            $rWhere[] = $ws . $we;
		                        }
							}
							if(count($rWhere)) $this->where[$key] = "(" . implode(" OR ", $rWhere) . ")";

						//通常の検索 @ToDo >= or <=の対応を考える
						}else{
							$ws = "";$we = "";    //whereのスタートとエンド
	                        if(isset($_GET["c_search"][$key . "_start"]) && strlen($_GET["c_search"][$key . "_start"]) && is_numeric($_GET["c_search"][$key . "_start"])){
	                            $ws = "s." . $key . " >= :" . $key . "_start";
	                            $this->binds[":" . $key . "_start"] = (int)$_GET["c_search"][$key . "_start"];
	                        }
	                        if(isset($_GET["c_search"][$key . "_end"]) && strlen($_GET["c_search"][$key . "_end"]) && is_numeric($_GET["c_search"][$key . "_end"])){
	                            $we = "s." . $key .  " <= :" . $key . "_end";
	                            $this->binds[":" . $key . "_end"] = (int)$_GET["c_search"][$key . "_end"];
	                        }
	                        if(strlen($ws) && strlen($we)){
	                            $this->where[$key] = "(" . $ws . " AND " . $we . ")";
	                        }else if(strlen($ws) || strlen($we)){
	                            $this->where[$key] = $ws . $we;
	                        }
						}

                        break;

                    //チェックボックスの場合
                    case CustomSearchFieldUtil::TYPE_CHECKBOX:
                        if(isset($_GET["c_search"][$key]) && count($_GET["c_search"][$key])){
                            $w = array();
                            foreach($_GET["c_search"][$key] as $i => $v){
                                if(!strlen($v)) continue;
                                $w[] = "s." . $key . " LIKE :" . $key . $i;
                                $this->binds[":" . $key . $i] = "%" . trim($v) . "%";
                            }
                            if(count($w)) $this->where[$key] = "(" . implode(" OR ", $w) . ")";
                        }
                        break;

                    //数字、ラジオボタン、セレクトボックス
                    default:
                        if(isset($_GET["c_search"][$key]) && strlen($_GET["c_search"][$key])){
                            $this->where[$key] = "s." . $key . " = :" . $key;
                            $this->binds[":" . $key] = $_GET["c_search"][$key];
                        }
                }
            }

            //カテゴリカスタムフィールド
            if(isset($_GET["cat_search"]) && count($_GET["cat_search"])){
              $catWhere = array();
              foreach(CustomSearchFieldUtil::getCategoryConfig() as $key => $field){
                //まずは各タイプのfield SQLでkeyを指定する場合、s.を付けること。soyshop_custom_searchのaliasがs
                switch($field["type"]){
                    //文字列の場合
                    case CustomSearchFieldUtil::TYPE_STRING:
                    case CustomSearchFieldUtil::TYPE_TEXTAREA:
                    case CustomSearchFieldUtil::TYPE_RICHTEXT:
                        if(isset($_GET["cat_search"][$key]) && strlen($_GET["cat_search"][$key])){
                            $catWhere["c_" . $key] = $key . " LIKE :c_" . $key;
                            $this->binds[":c_" . $key] = "%" . trim($_GET["cat_search"][$key]) . "%";
                        }
                        break;

                    //範囲の場合
                    case CustomSearchFieldUtil::TYPE_RANGE:
                        $ws = "";$we = "";    //whereのスタートとエンド
                        if(isset($_GET["cat_search"][$key . "_start"]) && strlen($_GET["cat_search"][$key . "_start"]) && is_numeric($_GET["cat_search"][$key . "_start"])){
                            $ws = $key . " >= :c_" . $key . "_start";
                            $this->binds[":c_" . $key . "_start"] = (int)$_GET["cat_search"][$key . "_start"];
                        }
                        if(isset($_GET["cat_search"][$key . "_end"]) && strlen($_GET["cat_search"][$key . "_end"]) && is_numeric($_GET["cat_search"][$key . "_end"])){
                            $we = $key .  " <= :c_" . $key . "_end";
                            $this->binds[":c_" . $key . "_end"] = (int)$_GET["cat_search"][$key . "_end"];
                        }
                        if(strlen($ws) && strlen($we)){
                            $catWhere[$key] = "(" . $ws . " AND " . $we . ")";
                        }else if(strlen($ws) || strlen($we)){
                            $catWhere[$key] = $ws . $we;
                        }
                        break;

                    //チェックボックスの場合
                    case CustomSearchFieldUtil::TYPE_CHECKBOX:
                        if(isset($_GET["cat_search"][$key]) && count($_GET["cat_search"][$key])){
                            $w = array();
                            foreach($_GET["cat_search"][$key] as $i => $v){
                                if(!strlen($v)) continue;
                                $w[] = $key . " LIKE :c_" . $key . $i;
                                $this->binds[":c_" . $key . $i] = "%" . trim($v) . "%";
                            }
                            if(count($w)) $catWhere[$key] = "(" . implode(" OR ", $w) . ")";
                        }
                        break;

                    //数字、ラジオボタン、セレクトボックス
                    default:
                        if(isset($_GET["cat_search"][$key]) && strlen($_GET["cat_search"][$key])){
                            $catWhere[$key] = "s." . $key . " = :c_" . $key;
                            $this->binds[":c_" . $key] = $_GET["cat_search"][$key];
                        }
                }
              }

              if(count($catWhere)){
                $this->where["category_custom_search"] = "i.item_category IN (SELECT category_id FROM soyshop_category_custom_search WHERE " . implode(" AND ", $catWhere) . " AND lang = " . UtilMultiLanguageUtil::getLanguageId(SOYSHOP_PUBLISH_LANGUAGE) . ")";
              }
            }

            //多言語化
            $this->where["lang"] = "s.lang = " . UtilMultiLanguageUtil::getLanguageId(SOYSHOP_PUBLISH_LANGUAGE);

            $this->binds[":now"] = time();
        }
    }

    private function buildListWhere(){

        return "WHERE i.open_period_start < :now ".
                "AND i.open_period_end > :now ".
                "AND i.item_is_open = 1 ".
                "AND i.is_disabled != 1 ".
                "AND i.item_type IN (\"" . SOYShop_Item::TYPE_SINGLE . "\",\"" . SOYShop_Item::TYPE_GROUP . "\",\"" . SOYShop_Item::TYPE_DOWNLOAD . "\") ".
                "AND s.lang = " . UtilMultiLanguageUtil::getLanguageId(SOYSHOP_PUBLISH_LANGUAGE) . " ";
    }

    /** 商品一覧ページ用 **/
    function getItemList($obj, $key, $value, $current, $offset, $limit){

        $confs = CustomSearchFieldUtil::getConfig();
        if(!isset($confs[$key])) return array();

        $binds = array(":now" => time());

        $sql = "SELECT i.* " .
                "FROM soyshop_item i ".
                "INNER JOIN soyshop_custom_search s ".
                "ON i.id = s.item_id ";
        $sql .= self::buildListWhere();    //カウントの時と共通の処理は切り分ける
        switch($confs[$key]["type"]){
            case CustomSearchFieldUtil::TYPE_CHECKBOX:
                $sql .= "AND s." . $key . " LIKE :" . $key;
                $binds[":" . $key] = "%" . trim($value) . "%";
                break;
            default:
                $sql .= "AND s." . $key . " = :" . $key;
                $binds[":" . $key] = trim($value);
        }

        $sql .= self::buildOrderBySQLOnListPage($obj);
        $sql .= " LIMIT " . $limit;

        //OFFSET
        $offset = $limit * ($current - 1);
        if($offset > 0) $sql .= " OFFSET " . $offset;

        try{
            $res = $this->itemDao->executeQuery($sql, $binds);
        }catch(Exception $e){
            var_dump($e);
            return array();
        }

        if(count($res) === 0) return array();

        $items = array();
        foreach($res as $obj){
            if(!isset($obj["id"])) continue;
            $items[] = $this->itemDao->getObject($obj);
        }

        return $items;
    }

    function countItemList($key, $value){
        $confs = CustomSearchFieldUtil::getConfig();
        if(!isset($confs[$key])) return 0;

        $binds = array(":now" => time());

        $sql = "SELECT COUNT(i.id) AS TOTAL " .
                "FROM soyshop_item i ".
                "INNER JOIN soyshop_custom_search s ".
                "ON i.id = s.item_id ";
        $sql .= self::buildListWhere();    //カウントの時と共通の処理は切り分ける
        switch($confs[$key]["type"]){
            case CustomSearchFieldUtil::TYPE_CHECKBOX:
                $sql .= "AND s." . $key . " LIKE :" . $key;
                $binds[":" . $key] = "%" . trim($value) . "%";
                break;
            default:
                $sql .= "AND s." . $key . " = :" . $key;
                $binds[":" . $key] = trim($value);
        }

        try{
            $res = $this->itemDao->executeQuery($sql, $binds);
        }catch(Exception $e){
            return 0;
        }

        return (isset($res[0]["TOTAL"])) ? (int)$res[0]["TOTAL"] : 0;
    }

    private function buildOrderBySQLOnSearchPage(SOYShop_SearchPage $obj){
        return self::buildOrderBySQLCommon($obj->getPage()->getId());
    }

    private function buildOrderBySQLOnListPage(SOYShop_ListPage $obj){
        $orderSql = self::buildOrderBySQLCommon($obj->getPage()->getId());
        if(is_null($orderSql)){
            $sort = SOY2Logic::createInstance("logic.shop.item.SearchItemUtil", array("sort" => $obj))->getSortQuery();
            $orderSql = " ORDER BY i." . $sort . " ";
        }
        return $orderSql;
    }

    private function buildOrderBySQLCommon($pageId){
        $session = SOY2ActionSession::getUserSession();
        if(isset($_GET["sort"]) || isset($_GET["csort"])){
            $custom_search_sort = null;
        }else{
            $custom_search_sort = $session->getAttribute("soyshop_" . SOYSHOP_ID . "_custom_search" . $pageId);
        }

        //カスタムソート
        if(isset($_GET["custom_search_sort"])){
            $custom_search_sort = ($_GET["custom_search_sort"] != "reset") ? htmlspecialchars($_GET["custom_search_sort"], ENT_QUOTES, "UTF-8") : null;
            //存在するフィールドか調べる
            $dao = new SOY2DAO();
            try{
                $dao->executeQuery("SELECT item_id FROM soyshop_custom_search WHERE " . $custom_search_sort . "= '' LIMIT 1");
            }catch(Exception $e){
                $custom_search_sort = null;
            }
            $session->setAttribute("soyshop_" . SOYSHOP_ID . "_custom_search" . $pageId, $custom_search_sort);
        }

        if(isset($custom_search_sort)){
            $suffix = $session->getAttribute("soyshop_" . SOYSHOP_ID . "_suffix" . $pageId);
            if(isset($_GET["r"])){
                $suffix = ($_GET["r"] == 1) ? " DESC" : " ASC";
                $session->setAttribute("soyshop_" . SOYSHOP_ID . "_suffix" . $pageId, $suffix);
            }

            return " ORDER BY s." . $custom_search_sort . " IS NULL ASC, s." . $custom_search_sort . $suffix;
        }

        return null;
    }
}
