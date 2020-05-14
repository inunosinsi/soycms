<?php
class IndexPage extends WebPage{

	function __construct($args) {
		parent::__construct();

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
		$total = (int)$searchLogic->getTotalCount();
		$users = ($total > 0) ? $searchLogic->getUsers() : array();

		/*表示*/

		//表示順リンク
		$this->buildSortLink($searchLogic, $sort);
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
		DisplayPlugin::toggle("no_user", (count($users) < 1));

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

	function buildSortLink(SearchUserLogic $logic, $sort){

		$link = SOY2PageController::createLink("User");

		$sorts = $logic->getSorts();

		foreach($sorts as $key => $value){

			$text = (!strpos($key,"_desc")) ? "▲" : "▼";
			$title = (!strpos($key,"_desc")) ? "昇順" : "降順";

			$this->addLink("sort_${key}", array(
				"text" => $text,
				"link" => $link . "?sort=" . $key,
				"title" => $title,
				"class" => ($sort === $key) ? "sorter_selected" : "sorter"
			));
		}
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
			"action" => $pager->getPageURL()
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

	function getFooterMenu(){
		try{
			return SOY2HTMLFactory::createInstance("User.FooterMenu.UserFooterMenuPage")->getObject();
		}catch(Exception $e){
			//
			return null;
		}
	}
}
