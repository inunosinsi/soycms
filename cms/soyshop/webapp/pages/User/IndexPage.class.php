<?php
class IndexPage extends WebPage{

	function IndexPage($args) {

		WebPage::WebPage();

    	DisplayPlugin::toggle("registered", (isset($_GET["registered"])));

		/*引数など取得*/
		//表示件数
		$limit = 15;
		$page = (isset($args[0])) ? (int)$args[0] : $this->getParameter("page");
		if(array_key_exists("page", $_GET)) $page = $_GET["page"];
		if(array_key_exists("sort", $_GET) || array_key_exists("search", $_GET)) $page = 1;
		$page = max(1, $page);

		$offset = ($page - 1) * $limit;

		//表示順
		$sort = $this->getParameter("sort");
		//検索
		$search = $this->getParameter("search");
		if(!$search)$search = array();

		/*データ*/
		$searchLogic = SOY2Logic::createInstance("logic.user.SearchUserLogic");
		$searchLogic->setLimit($limit);
		$searchLogic->setOffset($offset);
		$searchLogic->setOrder($sort);
		$searchLogic->setSearchCondition($search);

		//データ取得
		$total = $searchLogic->getTotalCount();
		$users = $searchLogic->getUsers();

		/*表示*/

		//表示順リンク
		$this->buildSortLink($sort);
		//絞込みフォーム
		$this->buildSearchForm($search);
		//リセットボタン
		$this->addForm("reset_form");
		$this->addModel("reset_button", array(
			"visible" => (!empty($search))
		));

		//ユーザ一覧
		$this->createAdd("user_list", "_common.User.UserListComponent", array(
			"list" => $users
		));
		$this->addModel("no_user", array(
			"visible" => (count($users) < 1)
		));

		//ページャー
		$start = $offset + 1;
		$end = $offset + count($users);
		if($end > 0 && $start == 0) $start = 1;

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPageURL("User");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);

		$this->buildPager($pager);


		//管理制限の権限を取得し、権限がない場合は表示しない
		$session = SOY2ActionSession::getUserSession();
		$this->addModel("app_limit_function", array(
			"visible" => $session->getAttribute("app_shop_auth_limit")
		));

		$this->addModel("is_custom_plugin", array(
			"visible" => class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_user_customfield"))
		));

		//user.function
		$this->createAdd("function_list", "_common.User.FunctionListComponent", array(
			"list" => $this->getFunctionList()
		));
		
		//user.info
		$this->createAdd("info_list", "_common.User.InfoListComponent", array(
			"list" => $this->getInfoList()
		));
		
	}

	function getFunctionList(){
		SOYShopPlugin::load("soyshop.user.function");
		$delegate = SOYShopPlugin::invoke("soyshop.user.function", array(
			"mode" => "list"
		));

		return $delegate->getList();
	}
	
	function getInfoList(){
		SOYShopPlugin::load("soyshop.user.info");
		$delegate = SOYShopPlugin::invoke("soyshop.user.info");
		return $delegate->getList();
	}

	function doPost(){
		if(array_key_exists("search", $_POST)){
			$value = $_POST["search"];
			SOY2ActionSession::getUserSession()->setAttribute("User.Search:"."search", $value);
		}
		if(array_key_exists("reset", $_POST)){
			SOY2ActionSession::getUserSession()->setAttribute("User.Search:"."search", array());
		}
		SOY2PageController::jump("User");
	}

	function getParameter($key){
		if(array_key_exists($key, $_GET)){
			$value = $_GET[$key];
			$this->setParameter($key, $value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("User.Search:" . $key);
		}
		return $value;
	}
	function setParameter($key,$value){
		SOY2ActionSession::getUserSession()->setAttribute("User.Search:" . $key, $value);
	}

	function buildSortLink($sort){

		$link = SOY2PageController::createLink("User");

		$this->addLink("sort_id", array(
			"text" => "▲",
			"link" => $link . "?sort=".SearchUserLogic::SORT_ID,
			"title" => "昇順",
			"class" => ($sort === SearchUserLogic::SORT_ID) ? "pager_disable" : ""
		));
		$this->addLink("sort_id_desc", array(
			"text" => "▼",
			"link" => $link . "?sort=".SearchUserLogic::SORT_ID_DESC,
			"title" => "降順",
			"class" => ($sort === SearchUserLogic::SORT_ID_DESC) ? "pager_disable" : ""
		));
		$this->addLink("sort_name", array(
			"text" => "▲",
			"link" => $link . "?sort=".SearchUserLogic::SORT_READING,
			"title" => "昇順",
			"class" => ($sort === SearchUserLogic::SORT_READING) ? "pager_disable" : ""
		));
		$this->addLink("sort_name_desc", array(
			"text" => "▼",
			"link" => $link . "?sort=".SearchUserLogic::SORT_READING_DESC,
			"title" => "降順",
			"class" => ($sort === SearchUserLogic::SORT_READING_DESC) ? "pager_disable" : ""
		));
		$this->addLink("sort_mail_address", array(
			"text" => "▲",
			"link" => $link . "?sort=".SearchUserLogic::SORT_MAIL_ADDRESS,
			"title" => "昇順",
			"class" => ($sort === SearchUserLogic::SORT_MAIL_ADDRESS) ? "pager_disable" : ""
		));
		$this->addLink("sort_mail_address_desc", array(
			"text" => "▼",
			"link" => $link . "?sort=".SearchUserLogic::SORT_MAIL_ADDRESS_DESC,
			"title" => "降順",
			"class" => ($sort === SearchUserLogic::SORT_MAIL_ADDRESS_DESC) ? "pager_disable" : ""
		));
	}

	function buildSearchForm($search){
		$this->addInput("search_id", array(
			"name" => "search[id]",
			"value" => (isset($search["id"])) ? $search["id"] : "",
			"style" => "width:90%;",
			"onclick" => "this.select()"
		));
		$this->addInput("search_name", array(
			"name" => "search[name]",
			"value" => (isset($search["name"])) ? $search["name"] : "",
			"style" => "width:90%;",
			"onclick" => "this.select()"
		));
		$this->addInput("search_mail_address", array(
			"name" => "search[mail_address]",
			"value" => (isset($search["mail_address"])) ? $search["mail_address"] : "",
			"style" => "width:90%;",
			"onclick" => "this.select()"
		));
		$this->addInput("search_attribute1", array(
			"name" => "search[attribute1]",
			"value" => (isset($search["attribute1"])) ? $search["attribute1"] : "",
			"style" => "width:90%;",
			"onclick" => "this.select()"
		));
		$this->addInput("search_attribute2", array(
			"name" => "search[attribute2]",
			"value" => (isset($search["attribute2"])) ? $search["attribute2"] : "",
			"style" => "width:90%;",
			"onclick" => "this.select()"
		));
		$this->addInput("search_attribute3", array(
			"name" => "search[attribute3]",
			"value" => (isset($search["attribute3"])) ? $search["attribute3"] : "",
			"style" => "width:90%;",
			"onclick" => "this.select()"
		));
	}

	function buildPager(PagerLogic $pager){

		//件数情報表示
		$this->addLabel("count_start", array(
			"text" => $pager->getStart()
		));
		$this->addLabel("count_end", array(
			"text" => $pager->getEnd()
		));
		$this->addLabel("count_max", array(
			"text" => $pager->getTotal()
		));

		//ページへのリンク
		$this->addLink("next_pager", $pager->getNextParam());
		$this->addLink("prev_pager", $pager->getPrevParam());
		$this->createAdd("pager_list","SimplePager",$pager->getPagerParam());

		//ページへジャンプ
		$this->addForm("pager_jump", array(
			"method" => "get",
			"action" => $pager->getPageURL() . "/"
		));
		$this->addSelect("pager_select", array(
			"name" => "page",
			"options" => $pager->getSelectArray(),
			"selected" => $pager->getPage(),
			"onchange" => "location.href=this.parentNode.action+this.options[this.selectedIndex].value"
		));
	}

/*	function getSubMenu(){
		try{
			$subMenuPage = SOY2HTMLFactory::createInstance("User.SubMenu.UserSubMenu", array(
				//"arguments" => array($this->id,$this->page)
			));
			return $subMenuPage->getObject();
		}catch(Exception $e){
			var_dump($e);
			exit;
			return null;
		}
	}*/
}
?>