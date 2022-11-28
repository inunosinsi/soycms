<?php
SOY2::import("logic.site.page.PageLogic");

class PageCreateLogic extends PageLogic{

	private $errors = array();

	/**
	 * @final
	 */
	function validate(SOYShop_Page $obj){
		$errors = array();

		if(strlen($obj->getName()) < 1){
			$errors["name"] = MessageManager::get("ERROR_REQUIRE");
		}

		if(strlen($obj->getUri()) < 1){
			$errors["uri"] = MessageManager::get("ERROR_REQUIRE");
		}else if(!preg_match('/^[a-zA-Z0-9\.\/\_-]+$/', $obj->getUri())){
			$errors["uri"] = Message::ERROR_INVALID;
		}

		if(strlen($obj->getType()) < 1){
			$errors["type"] = MessageManager::get("ERROR_REQUIRE");
		}

		//unique check
		if(true == SOY2DAOFactory::create("site.SOYShop_PageDAO")->checkUri($obj->getUri())){
			$errors["uri"] = MessageManager::get("ERROR_INVALID");
		}

		$this->setErrors($errors);

		return (empty($errors));
	}

	function create(SOYShop_Page $obj){
		//canonical url
		$cnf = $obj->getConfigObject();
		$cnf["canonical_format"] = "%PERMALINK%";
		$obj->setConfig($cnf);

		$id = SOY2DAOFactory::create("site.SOYShop_PageDAO")->insert($obj);

		$obj->setId($id);
		$this->onUpdate($obj);

		return $id;
	}

	/**
	 * ページの初期化。ファイルのチェックのみ。ファイルがあればinitPageが処理を行う
	 */
	function initPageByIniFile(){
		$ini = SOY2::RootDir() . "logic/init/page/ini.csv";

		//念の為にiniファイルがあるか確認しておく
		if(!file_exists($ini)) return;

		$this->initPage($ini);
	}

	function initPage($ini){
		$logic = SOY2Logic::createInstance("logic.csv.ExImportLogicBase");

		//データを行単位にばらす
		$lines = $logic->GET_CSV_LINES(file_get_contents($ini));	//fix multiple lines
		//先頭行削除
		array_shift($lines);

		//ページはなければ追加形式
		$dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
		foreach($lines as $line){
			//0.ページ名, 1.URL, 2.タイプ, 3.テンプレート, 4.カテゴリ(商品一覧のみ), 5.コンテンツ(フリーのみ), 6.モジュール(検索のみ)
			$values = $logic->explodeLine($line);

			if((!isset($values[0]) || strlen($values[0]) === 0) || (!isset($values[1]) || strlen($values[1]) === 0)) continue;

			$page = new SOYShop_Page();
			$page->setName(trim($values[0]));
			$page->setUri(trim($values[1]));
			$page->setType(trim($values[2]));

			$page->setTemplate((isset($values[3])) ? trim($values[3]) : "default.html");

			//canonicalURL
			$pageCnf = $page->getConfigObject();
			$pageCnf["canonical_format"] = "%PERMALINK%";
			$page->setConfig($pageCnf);

			try{
				$id = $this->create($page);
			}catch(Exception $e){
				continue;	//urlの重複で弾かれるので、ページのチェックは行わない
			}

			$isContinue = false;
			switch($values[2]){
				case SOYShop_Page::TYPE_LIST:
					if(isset($values[4]) && $values[4] == "category"){
						$page->setId($id);
						$listPage = $page->getPageObject();
						$listPage->setType("category");
						$listPage->setDefaultCategory(1);
						$page->setPageObject($listPage);
						try{
							$this->updatePageObject($page);
						}catch(Exeception $e){
							//
						}
					}else{
						/**
						 * @ToDo カテゴリ以外の設定
						 */
					}
					break;
				case SOYShop_Page::TYPE_FREE:
					if(isset($values[5]) && strlen($values[5])){
						$contentsFile = SOY2::RootDir() . "logic/init/free/" . SOYSHOP_TEMPLATE_ID . "/" . $values[5];
						if(file_exists($contentsFile)){
							$page->setId($id);
							$freePage = $page->getPageObject();
							$freePage->setTitle($values[0]);
							$freePage->setContent(file_get_contents($contentsFile));
							$page->setPageObject($freePage);
							try{
								$this->updatePageObject($page);
							}catch(Exeception $e){
								//
							}
						}
					}
					break;
				case SOYShop_Page::TYPE_SEARCH:
					if(isset($values[6]) && strlen($values[6])){
						$page->setId($id);
						$searchPage = $page->getPageObject();
						$searchPage->setModule($values[6]);
						$searchPage->setDisplayCount(10);
						$page->setPageObject($searchPage);
						try{
							$this->updatePageObject($page);
						}catch(Exeception $e){
							//
						}
					}
					break;
				default:
					//
			}
		}
	}

	function getErrors() {
		return $this->errors;
	}
	function setErrors($errors) {
		$this->errors = $errors;
	}
}
