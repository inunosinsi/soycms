<?php
class ItemReviewBeforeOutput extends SOYShopSiteBeforeOutputAction{

	private $review = array();

	function doPost($page){

		$obj = $page->getPageObject();

		//詳細ページ以外では読み込まない
		if(!is_object($obj) || get_class($obj) != "SOYShop_Page" || $obj->getType() != SOYShop_Page::TYPE_DETAIL) return;

		if(soy2_check_token()){

			$config = self::_config();
			$this->review = $_POST["Review"];

			//レビューの本文に入力がない場合
			if(!isset($this->review["content"]) || strlen($this->review["content"]) === 0) return;

			//文字認証:フォームが存在していた時に認証を行う
			if(isset($_POST["Review"]["captcha"]) && (isset($config["captcha"]) && strlen($config["captcha"]))){
				if($config["captcha"] !== $_POST["Review"]["captcha"]) return;
			}

			//画像認証のモード
			if(isset($config["captcha_img"]) && $config["captcha_img"] == 1){

				//画像の削除:念の為
				@unlink(SOY2HTMLConfig::CacheDir() . $_POST["soy2_token"] . ".jpg");

				$captcha_value = SOY2ActionSession::getUserSession()->getAttribute("review_" . SOYSHOP_ID);
				SOY2ActionSession::getUserSession()->setAttribute("review_" . SOYSHOP_ID, null);

				//フォームが無い時
				if(!isset($_POST["Review"]["captcha"])) return;

				//入力した値に誤りがあるとき
				if(trim($_POST["Review"]["captcha"]) != $captcha_value) return;
			}

			self::_logic()->setPage($page);

			//入力内容をクリアする
			if(self::_logic()->registerReview($this->review)) $this->review = array();
		}
	}

	function beforeOutput($page){

		$pageObj = $page->getPageObject();

		//カートページとマイページでは読み込まない
		if(!is_object($pageObj) || get_class($pageObj) != "SOYShop_Page" || $pageObj->getType() != SOYShop_Page::TYPE_DETAIL) return;

		$obj = $pageObj->getObject();
		$current = $obj->getCurrentItem();

		$page->addLabel("review_item_name", array(
			"soy2prefix" =>SOYSHOP_SITE_PREFIX,
			"text" => $current->getOpenItemName()
		));

		self::_logic()->setPage($page);
		$isLoggedIn = self::isLoggedIn();

		//ログイン時に表示する箇所
		$page->addModel("is_logged_in", array(
			"visible" => ($isLoggedIn),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		//ログアウト時に表示する箇所
		$page->addModel("no_logged_in", array(
			"visible" => ($isLoggedIn == false),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$page->addLink("review_login_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => soyshop_get_mypage_url() . "/login?r=" . rawurldecode($_SERVER["REQUEST_URI"])
		));

		$config = ItemReviewUtil::getConfig();
		$cnt = (isset($config["review_count"]) && is_numeric($config["review_count"])) ? (int)$config["review_count"] : null;

		$reviews = (is_numeric($cnt)) ? self::_logic()->getReviews($cnt + 1) : self::_logic()->getReviews();
		$revCnt = count($reviews);

		if(is_numeric($cnt) && $revCnt > $cnt){
			$reviews = array_slice($reviews, 0, $cnt);
			$isLink = (isset($config["active_other_page"]) && $config["active_other_page"] == 1 && isset($config["review_page_id"]) && is_numeric($config["review_page_id"]));
		}else{
			$isLink = false;
		}

		$page->addModel("has_reviews", array(
			"visible" => ($revCnt > 0),
			"soy2prefix" => "block"
		));

		//レビューが投稿されていない場合
		$page->addModel("no_reviews", array(
			"visible" => ($revCnt == 0),
			"soy2prefix" => "block"
		));

		SOY2::import("module.plugins.item_review.component.ReviewsListComponent");
		$page->createAdd("review_list", "ReviewsListComponent", array(
			"soy2prefix" => "block",
			"list" => $reviews,
			"itemId" => $current->getId(),
		));

		//レビュー一覧ページへのリンクを出力
		$page->addModel("is_review_page", array(
			"visible" => $isLink,
			"soy2prefix" => "block"
		));

		$page->addLink("review_page_link", array(
			"link" => ($isLink) ? rtrim(soyshop_get_page_url(soyshop_get_page_object($config["review_page_id"])->getUri()), "/") . "/" . $current->getId() . "/page-2.html" : "",
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		self::_buildForm($page);
	}

	private function isLoggedIn(){
		//ログイン不要の設定の場合は無条件でtrue
		$config = self::_config();
		if(!isset($config["login"]) || $config["login"] != 1) return true;
		return self::_logic()->isLoggedIn();
	}

	/**
	 * レビューフォーム
	 * 投稿されたレビュー一覧
	 * ログインモード
	 */
	private function _buildForm($page){

		$user = self::_logic()->getUser();
		$nickname = (!is_null($user->getNickname()) && strlen($user->getNickname())) ? $user->getNickname() : $user->getName();

		$page->addForm("review_form", array(
			"soy2prefix" => "block"
		));

		$page->addModel("review_error", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => (count($this->review) && !isset($this->review["title"]))
		));

		$page->addInput("nickname", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"name" => "Review[nickname]",
			"value" => $nickname,
			"readonly" => (strlen($nickname))
		));

		$page->addInput("title", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"name" => "Review[title]",
			"value" => (isset($this->review["title"])) ? $this->review["title"] : ""
		));

		$page->addTextArea("content", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"name" => "Review[content]",
			"value" => (isset($this->review["content"])) ? $this->review["content"] : ""
		));

		$page->addSelect("evaluation", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"name" => "Review[evaluation]",
			"options" => range(5,1),
			"selected" => (isset($this->review["evaluation"])) ? $this->review["evaluation"] : null
		));

		//Amazon形式の星をクリックで評価
		SOY2::import("module.plugins.item_review.component.EvaluationStarComponent");
		$page->addLabel("evaluation_star", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => EvaluationStarComponent::buildEvaluateArea(5)
		));

		$page->addInput("captcha_input", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"name" => "Review[captcha]",
			"value" => "",
			"attr:required" => "required",
			"attr:placeholder" => "表示されているアルファベットを入力して下さい。"
		));

		$config = self::_config();
		$page->addLabel("captcha_code", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => (isset($config["captcha"])) ? $config["captcha"] : null
		));

		//CAPTCHAの生成
		if((isset($config["captcha_img"])) && $config["captcha_img"] == 1){
			$captcha_filename = str_replace(array(".", "/", "\\"), "", soy2_get_token());
			$captcha_value = ItemReviewUtil::getRandomString(5);
			ItemReviewUtil::generateCaptchaImage($captcha_value, $captcha_filename);

			//captchaをセッションに入れておく
			SOY2ActionSession::getUserSession()->setAttribute("review_" . SOYSHOP_ID, $captcha_value);
		}else{
			$captcha_filename = null;
		}

		//画像ファイル生成
		$page->addImage("captcha_img_url", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"src" => "/" . SOYSHOP_ID . "?captcha=" . $captcha_filename
		));
	}

	private function _logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.item_review.logic.ItemReviewLogic");
		return $logic;
	}

	private function _config(){
		static $cnf;
		if(is_null($cnf)){
			SOY2::import("module.plugins.item_review.util.ItemReviewUtil");
			$cnf = ItemReviewUtil::getConfig();
		}
		return $cnf;
	}
}
SOYShopPlugin::extension("soyshop.site.beforeoutput", "item_review", "ItemReviewBeforeOutput");
