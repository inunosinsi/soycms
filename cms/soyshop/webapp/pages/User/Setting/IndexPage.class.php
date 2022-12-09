<?php
class IndexPage extends WebPage{

	private $pageNumber;

	function doPost(){
		if(!AUTH_OPERATE) return;	//操作権限がないアカウントの場合は以後のすべての動作を封じる

		if(soy2_check_token()){
			if(isset($_POST["search_btn"])){
				if(array_key_exists("search", $_POST)){
					$value = $_POST["search"];
					SOY2ActionSession::getUserSession()->setAttribute("User.Search:"."search", $value);
				}
			}else if(isset($_POST["register_btn"]) || isset($_POST["remove_btn"])){
				//一括設定
				if(isset($_POST["users"]) && count($_POST["users"])){
					$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
					foreach($_POST["users"] as $userId){
						try{
							$user = $userDao->getById($userId);
						}catch(Exception $e){
							continue;
						}

						//一括登録
						if(isset($_POST["register_btn"])){
							if(strlen($_POST["input"]["attribute1"])) $user->setAttribute1($_POST["input"]["attribute1"]);
							if(strlen($_POST["input"]["attribute2"])) $user->setAttribute2($_POST["input"]["attribute2"]);
							if(strlen($_POST["input"]["attribute3"])) $user->setAttribute3($_POST["input"]["attribute3"]);

						//一括削除
						}else if(isset($_POST["remove_btn"])){
							if(isset($_POST["remove"]["attribute1"])) $user->setAttribute1(null);
							if(isset($_POST["remove"]["attribute2"])) $user->setAttribute2(null);
							if(isset($_POST["remove"]["attribute3"])) $user->setAttribute3(null);
						}

						try{
							$userDao->update($user);
						}catch(Exception $e){
							continue;
						}
					}
				}

				if(isset($this->pageNumber)){
					SOY2PageController::jump("User.Setting.".$this->pageNumber."?updated");
				}else{
					SOY2PageController::jump("User.Setting?updated");
				}

			}
		}

		if(array_key_exists("reset", $_POST)){
			SOY2ActionSession::getUserSession()->setAttribute("User.Search:"."search", array());
		}
		SOY2PageController::jump("User.Setting");
	}

	function __construct($args) {

		$this->pageNumber = (isset($args[0])) ? (int)$args[0] : null;

		parent::__construct();

    	DisplayPlugin::toggle("registered", (isset($_GET["registered"])));

		/*引数など取得*/
		//表示件数
		$limit = 30;
		$page = (isset($args[0])) ? (int)$args[0] : $this->getParameter("page");
		if(array_key_exists("page", $_GET)) $page = $_GET["page"];
		if(array_key_exists("sort", $_GET) || array_key_exists("search", $_GET)) $page = 1;
		$page = max(1, $page);

		$offset = ($page - 1) * $limit;

		//検索
		$search = $this->getParameter("search");
		if(!$search)$search = array();

		/*データ*/
		$searchLogic = SOY2Logic::createInstance("logic.user.SearchUserLogic");
		$searchLogic->setLimit($limit);
		$searchLogic->setOffset($offset);
		$searchLogic->setOrder("id desc");
		$searchLogic->setSearchCondition($search);

		//データ取得
		$total = $searchLogic->getTotalCount();
		$users = $searchLogic->getUsers();

		/*表示*/

		//絞込みフォーム
		self::buildSearchForm($search);
		//リセットボタン
		$this->addForm("reset_form");
		$this->addModel("reset_button", array(
			"visible" => (!empty($search))
		));

		$this->addForm("form");

		//ユーザ一覧
		DisplayPlugin::toggle("no_user", (count($users) < 1));
		$this->createAdd("user_list", "_common.User.UserListComponent", array(
			"list" => $users
		));

		//ページャー
		$start = $offset + 1;
		$end = $offset + count($users);
		if($end > 0 && $start == 0) $start = 1;

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPageURL("User.Setting");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);

		$this->buildPager($pager);

		self::buildInputForm();
		self::buildRemoveForm();
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

	private function buildSearchForm($search){

		foreach(range(1, 3) as $key){
			$this->addCheckbox("search_no_attribute" . $key, array(
				"name" => "search[no][attribute" . $key . "]",
				"value" => 1,
				"selected" => (isset($search["no"]["attribute" . $key])),
				"label" => "属性" . $key
			));
		}

		foreach(array("id", "name", "mail_address", "attribute1", "attribute2", "attribute3") as $key){
			$this->addInput("search_" . $key, array(
				"name" => "search[" . $key . "]",
				"value" => (isset($search[$key])) ? $search[$key] : "",
				"style" => "width:90%;",
				"onclick" => "this.select()"
			));
		}
	}

	private function buildInputForm(){
		foreach(array("attribute1", "attribute2", "attribute3") as $key){
			$this->addInput("input_" . $key, array(
				"name" => "input[" . $key . "]",
				"value" => "",
				"style" => "width:15%"
			));
		}
	}

	private function buildRemoveForm(){
		foreach(range(1, 3) as $key){
			$this->addCheckBox("remove_attribute" . $key, array(
				"name" => "remove[attribute" . $key . "]",
				"value" => 1,
				"label" => "属性" . $key
			));
		}
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

	function getBreadcrumb(){
		return BreadcrumbComponent::build(SHOP_USER_LABEL . "属性の一括設定", array("User" => SHOP_USER_LABEL . "管理"));
	}

	function getFooterMenu(){
		try{
			return SOY2HTMLFactory::createInstance("User.FooterMenu.UserFooterMenuPage")->getObject();
		}catch(Exception $e){
			//
			return null;
		}
	}
}
