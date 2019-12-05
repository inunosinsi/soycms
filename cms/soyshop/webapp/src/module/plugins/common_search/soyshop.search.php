<?php
class SOYShopCommonSearchModule extends SOYShopSearchModule{

	/**
	 * title text
	 */
	function getTitle(){
		return "Common Search Module";
	}

	/**
	 * @return html
	 */
	function getForm(){
		$query = (isset($_REQUEST["q"])) ? $_REQUEST["q"] : "";
		$query = mb_convert_encoding($query, "UTF-8", "auto");
		$query = htmlspecialchars($query, ENT_QUOTES, "UTF-8");
		return stripslashes("<input name='q' value='$query' />");
	}

	private $count = 0;

	/**
	 * @return array<soyshop_item>
	 */
	function getItems($current,$limit){

		$query = (isset($_REQUEST["q"])) ? $_REQUEST["q"] : "";
		$query = mb_convert_encoding($query, "UTF-8", "auto");
		$type = $_REQUEST["type"];
		if(isset($query) && isset($type)){

			switch($type){
				case "name":
					$items = $this->searchByName($current, $limit, $query);
					$this->count = $this->totalCountByName($query);
					break;
			}

			return $items;
		}

		//typeが使われてなかったらどっかに飛ばす
		$uri = SOYShop_DataSets::get("sample.search.onError", SOYSHOP_SITE_URL);
		header("Location: $uri");
		exit;
	}

	/**
	 * @return number
	 */
	function getTotal(){ return $this->count; }


	/**
	 * @return int totalPageCount
	 * @param string nameQuery
	 */
	function totalCountByName($name){
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$sql = new SOY2DAO_Query();
		$binds = array();

		$sql->prefix = "select";
		$sql->table = "soyshop_item";
		$sql->sql = "count(id)";

		$where = array();
		$queries = explode(" ", $name);
		foreach($queries as $key => $word){
			$where[] = "(soyshop_item.item_name LIKE :query" . $key . " OR soyshop_item.item_name LIKE :s_query" . $key . ")";
			$binds[":query" . $key] = "%" . $word . "%";
			$binds[":s_query" . $key] = $binds[":query" . $key];
		}
		$sql->where .= " (".implode(") AND (",$where).")";
		$sql->where .= " AND item_type in (" . $this->getItemType() . ") ";

		$result = $dao->executeOpenItemQuery($sql, $binds);
		$count = (isset($result[0]["count(id)"])) ? (int)$result[0]["count(id)"] : 0;

		return $count;
	}

	/**
	 * @return array<soyshop_item>
	 * @param string nameQuery
	 * 商品名の検索
	 */
	function searchByName($current, $limit, $name){

		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$sql = new SOY2DAO_Query();
		$binds = array();
		$sql->prefix = "select";
		$sql->table = "soyshop_item";
		$sql->distinct = true;
		$sql->order = $this->getSortQuery();//"update_date desc";
		$sql->sql = "id, item_name"; //MySQL5.7対策。ORDER BYでitem_nameを指定してるので、item_nameの値も取得しておく

		$where = array();
		$queries = explode(" ", $name);
		foreach($queries as $key => $word){
			$where[] = "soyshop_item.item_name LIKE :query" . $key;
			$binds[":query" . $key] = "%" . $word . "%";
		}
		$sql->where .= " (".implode(") AND (",$where).")";
		$sql->where .= " AND item_type in (" . $this->getItemType() . ") ";

		$dao->setLimit($limit);
		$offset = ($current -1) * $limit;
		$dao->setOffset($offset);

		try{
			$result = $dao->executeOpenItemQuery($sql, $binds);
		}catch(Exception $e){
			return array();
		}

		$items = array();

		try{
			$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

			foreach($result as $key => $item){
				$id = $item["id"];
				$items[] = $itemDAO->getById($id);
			}
		}catch(Exception $e){
			//
		}
		return $items;
	}

	/**
	 * ソート文を作成
	 *
	 * $_GET["r"]		0 降順 1 昇順
	 * $_GET["sort"]	ソート種別
	 * 		・id
	 * 		・name
	 * 		・code
	 * 	 	・stock
	 * 		・cdate
	 * 		・udate
	 * $_GET["csort"]	カスタムフィールドでソート
	 *
	 */
	function getSortQuery(){

		$session = SOY2ActionSession::getUserSession();
		$pageId = $this->getPage()->getId();

		$sort = $session->getAttribute("soyshop_" . SOYSHOP_ID . "_sort" . $pageId);
		$csort = $session->getAttribute("soyshop_" . SOYSHOP_ID . "_csort" . $pageId);
		$suffix = $session->getAttribute("soyshop_" . SOYSHOP_ID . "_suffix" . $pageId);

		if(isset($_GET["sort"])){
			$sort = $_GET["sort"];
			$session->setAttribute("soyshop_" . SOYSHOP_ID . "_sort" . $pageId, $sort);
		}

		//未実装
		if(isset($_GET["csort"])){
			$csort = $_GET["csort"];
			$session->setAttribute("soyshop_" . SOYSHOP_ID . "_csort" . $pageId, $csort);
		}

		if(isset($_GET["r"])){
			$suffix = ($_GET["r"] == 1) ? " desc" : "";
			$session->setAttribute("soyshop_" . SOYSHOP_ID . "_suffix" . $pageId, $suffix);
		}

		//default
		if(!$sort && !$csort){
			$obj = $this->getPage()->getObject();
			$defaultSort = $obj->getDefaultSort();
			if(!$suffix) $suffix = ($obj->getIsReverse()) ? " desc" : "";
			if($defaultSort != "_custom") $sort = $defaultSort;
			$csort = $obj->getCustomSort();
		}

		if($sort){
			switch($sort){
				case "id":
					return "id" . $suffix;
					break;
				case "name":
				case "code":
				case "stock":
					return "item_" .$sort. $suffix;
					break;
				case "price":
					return "item_selling_price" . $suffix;
					break;
				case "cdate":
					return "create_date" . $suffix;
					break;
				case "udate":
					return "update_date" . $suffix;
					break;
			}
		}

		if($csort){
			SOY2::import("domain.shop.SOYShop_ItemAttribute");
			$fields = SOYShop_ItemAttributeConfig::getIndexFields();
			if(!in_array($csort, $fields)){
				$csort = $obj->getCustomSort();
			}

			return SOYShop_ItemDAO::getSortColumnName($csort) . $suffix;
		}

		return "update_date desc";
	}

	function getItemType(){
		$array = SOYShop_Item::getItemTypes();
		$obj = array();
		foreach($array as $value){
			$obj[] = "'" . $value."'";
		}
		return implode(",", $obj);
	}
}
SOYShopPlugin::extension("soyshop.search", "common_search", "SOYShopCommonSearchModule");
