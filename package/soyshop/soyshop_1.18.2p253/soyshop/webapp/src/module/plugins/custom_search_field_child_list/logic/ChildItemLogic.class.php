<?php

class ChildItemLogic extends SOY2LogicBase{

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
        $sort = SOY2Logic::createInstance("logic.shop.item.SearchItemUtil", array("sort" => $obj))->getSortQuery();
		if(isset($sort)) $sql .= " ORDER BY i." . $sort . " ";

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
        foreach($res as $obj){
            $items[] = $this->itemDao->getObject($obj);
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
        $where = "WHERE i.open_period_start < :now ".
                "AND i.open_period_end > :now ".
                "AND i.item_is_open = 1 ".
                "AND i.is_disabled != 1 ";

        $item_where = array();

        //検索対象を子商品のみとする
        $item_where[] = "(i.item_type != \"" . SOYShop_Item::TYPE_SINGLE . "\" AND i.item_type != \"" . SOYShop_Item::TYPE_GROUP . "\" AND i.item_type != \"" . SOYShop_Item::TYPE_DOWNLOAD . "\")";

        //SQLiteでREGEXPを使用できないサーバがあるみたい
        if(SOY2DAOConfig::type() == "mysql"){
            $item_where[] = "i.item_type REGEXP '^[0-9]+$' ";
        }

        if(count($item_where)){
            $where .= "AND (" .implode(" AND ", $item_where) .") ";
        }

        foreach($this->where as $key => $w){
            if(!strlen($w)) continue;
            $where .= "AND " . $w . " ";
        }
        return $where;
    }

    private function setCondition(){
		$params = CustomSearchFieldUtil::getParameter("c_search");

        if(!count($this->where)){
            //SOYShop_Itemの値
            if(isset($params["item_name"]) && strlen($params["item_name"])) {
                //日本語検索
                if(SOYSHOP_PUBLISH_LANGUAGE == "jp"){
					$this->where["item_name"] = "(i.item_name LIKE :item_name OR i.item_subtitle LIKE :item_subtitle)";
                    $this->binds[":item_name"] = "%" . trim($params["item_name"]) . "%";
					$this->binds[":item_subtitle"] = $this->binds[":item_name"];
                //多言語検索
                }else{
                    $this->where["item_name"] = "id IN (SELECT item_id FROM soyshop_item_attribute WHERE item_field_id = 'item_name_" . SOYSHOP_PUBLISH_LANGUAGE . "' AND item_value LIKE :item_name)";
                    $this->binds[":item_name"] = "%" . trim($params["item_name"]) . "%";
                }
            }

            if(isset($params["item_code"]) && strlen($params["item_code"])) {
                $this->where["item_code"] = "i.item_code LIKE :item_code";
                $this->binds[":item_code"] = "%" . trim($params["item_code"]) . "%";
            }

            //カテゴリー
            if(isset($params["item_category"]) && is_numeric($params["item_category"])){
                //小カテゴリの商品も引っ張ってこれる様にする
                $maps = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->getMapping();
                $catId = (int)trim($params["item_category"]);
                if(isset($maps[$catId])){
                    $this->where["item_category"] = "i.item_category IN (" . implode(",", $maps[$catId]) . ")";
                }
            }

            //サブクエリ内でも子商品のみの指定を行う
            $this->where["item_type"] = "(i.item_type != \"" . SOYShop_Item::TYPE_SINGLE . "\" AND i.item_type != \"" . SOYShop_Item::TYPE_GROUP . "\" AND i.item_type != \"" . SOYShop_Item::TYPE_DOWNLOAD . "\")";

            $pmin = "";$pmax = "";
            if(isset($params["item_price_min"]) && strlen($params["item_price_min"]) && is_numeric($params["item_price_min"])) {
                $pmin = "i.item_price >= :item_price_min";
                $this->binds[":item_price_min"] = (int)$params["item_price_min"];
            }

            if(isset($params["item_price_max"]) && strlen($params["item_price_max"]) && is_numeric($params["item_price_max"])) {
                $pmax = "i.item_price <= :item_price_max";
                $this->binds[":item_price_max"] = (int)$params["item_price_max"];
            }

            if(strlen($pmin) && strlen($pmax)){
                $this->where["item_price"] = "(" . $pmin . " AND " . $pmax . ")";
            }else{
                $this->where["item_price"] = $pmin . $pmax;
            }

            //$s_where = array();

            foreach(CustomSearchFieldUtil::getConfig() as $key => $field){

                //まずは各タイプのfield SQLでkeyを指定する場合、s.を付けること。soyshop_custom_searchのaliasがs
                switch($field["type"]){
                    //文字列の場合
                    case CustomSearchFieldUtil::TYPE_STRING:
                    case CustomSearchFieldUtil::TYPE_TEXTAREA:
                    case CustomSearchFieldUtil::TYPE_RICHTEXT:
                        if(isset($params[$key]) && strlen($params[$key])){
                            $this->where[$key] = "s." . $key . " LIKE :" . $key;
                            $this->binds[":" . $key] = "%" . trim($params[$key]) . "%";
                        }
                        break;

                    //範囲の場合
                    case CustomSearchFieldUtil::TYPE_RANGE:
                        $ws = "";$we = "";    //whereのスタートとエンド
                        if(isset($params[$key . "_start"]) && strlen($params[$key . "_start"]) && is_numeric($params[$key . "_start"])){
                            $ws = "s." . $key . " >= :" . $key . "_start";
                            $this->binds[":" . $key . "_start"] = (int)$params[$key . "_start"];
                        }
                        if(isset($params[$key . "_end"]) && strlen($params[$key . "_end"]) && is_numeric($params[$key . "_end"])){
                            $we = "s." . $key .  " <= :" . $key . "_end";
                            $this->binds[":" . $key . "_end"] = (int)$params[$key . "_end"];
                        }
                        if(strlen($ws) && strlen($we)){
                            $this->where[$key] = "(" . $ws . " AND " . $we . ")";
                        }else if(strlen($ws) || strlen($we)){
                            $this->where[$key] = $ws . $we;
                        }
                        break;

                    //チェックボックスの場合
                    case CustomSearchFieldUtil::TYPE_CHECKBOX:
                        if(isset($params[$key]) && count($params[$key])){
                            $w = array();
                            foreach($params[$key] as $i => $v){
                                if(!strlen($v)) continue;
                                $w[] = "s." . $key . " LIKE :" . $key . $i;
                                $this->binds[":" . $key . $i] = "%" . trim($v) . "%";
                            }
                            if(count($w)) $this->where[$key] = "(" . implode(" OR ", $w) . ")";
                        }
                        break;
					case "csf_free_word":	//フリーワード検索は何もしない
						break;

                    //数字、ラジオボタン、セレクトボックス
                    default:
                        if(isset($params[$key]) && strlen($params[$key])){
                            $this->where[$key] = "s." . $key . " = :" . $key;
                            $this->binds[":" . $key] = $params[$key];
                        }
                }
            }

			//フリーワード検索
			if(isset($params["csf_free_word"]) && strlen($params["csf_free_word"])){
				$v = htmlspecialchars($params["csf_free_word"], ENT_QUOTES, "UTF-8");
				$v = str_replace("　", " ", $v);
				$words = explode(" ", $v);
				$freeQueries = array();
				for($i = 0; $i < count($words); $i++){
					$word = trim($words[$i]);
					if(!strlen($word)) continue;
					$freeSubQueries = array();
					foreach(CustomSearchFieldUtil::getConfig() as $key => $field){
						$freeSubQueries[] = "s." . $key . " LIKE :csffree" . $key . $i;
						$this->binds[":csffree" . $key . $i] = "%" . $word . "%";
					}

					//商品名等
					foreach(array("item_name", "item_code") as $key){
						$freeSubQueries[] = "i." . $key . " LIKE :csffree" . $key . $i;
						$this->binds[":csffree" . $key . $i] = "%" . $word . "%";
					}

					if(count($freeSubQueries)){
						$freeQueries[] = "(" . implode(" OR ", $freeSubQueries) . ")";
					}
				}

				if(count($freeQueries)){
					$this->where["csf_free_word"] = "(" . implode(" AND ", $freeQueries) . ")";
				}
			}

            $this->binds[":now"] = time();
        }
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

        $sql .= self::buildOrderBySQL($obj);
        $sql .= " LIMIT " . $limit;

        //OFFSET
        $offset = $limit * ($current - 1);
        if($offset > 0) $sql .= " OFFSET " . $offset;

        try{
            $res = $this->itemDao->executeQuery($sql, $binds);
        }catch(Exception $e){
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

    private function buildListWhere(){

        $sql = "WHERE i.open_period_start < :now ".
                "AND i.open_period_end > :now ".
                "AND i.item_is_open = 1 ".
                "AND i.is_disabled != 1 ".
                "AND s.lang = " . UtilMultiLanguageUtil::getLanguageId(SOYSHOP_PUBLISH_LANGUAGE) . " ";

        //子商品のみ表示
        $sql .= "AND (i.item_type != \"" . SOYShop_Item::TYPE_SINGLE . "\" AND i.item_type != \"" . SOYShop_Item::TYPE_GROUP . "\" AND i.item_type != \"" . SOYShop_Item::TYPE_DOWNLOAD . "\") ";

        //SQLiteでREGEXPを使用できないサーバがあるみたい
        if(SOY2DAOConfig::type() == "mysql"){
            $sql .= "AND i.item_type REGEXP '^[0-9]+$' ";
        }

        return $sql;
    }

    private function buildOrderBySQL(SOYShop_ListPage $obj){

        $pageId = $obj->getPage()->getId();

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
        }else{
            $sort = SOY2Logic::createInstance("logic.shop.item.SearchItemUtil", array("sort" => $obj))->getSortQuery();
            return " ORDER BY i." . $sort . " ";
        }
    }
}
