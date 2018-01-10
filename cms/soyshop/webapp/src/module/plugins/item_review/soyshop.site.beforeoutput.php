<?php
SOY2::import("module.plugins.item_review.common.ItemReviewCommon");
class ItemReviewBeforeOutput extends SOYShopSiteBeforeOutputAction{

	private $reviewLogic;
	private $config;
	private $userDao;
	private $itemDao;

	private $review = array();

	function doPost($page){

		$obj = $page->getPageObject();

		//詳細ページ以外では読み込まない
		if(!is_object($obj) || get_class($obj) != "SOYShop_Page" || $obj->getType() != SOYShop_Page::TYPE_DETAIL) return;

		if(soy2_check_token()){

			self::prepare();

			$this->review = $_POST["Review"];

			//レビューの本文に入力がない場合
			if(!isset($this->review["content"]) || strlen($this->review["content"]) === 0) return;

			//文字認証:フォームが存在していた時に認証を行う
			if(isset($_POST["Review"]["captcha"]) && (isset($this->config["captcha"]) && strlen($this->config["captcha"]))){
				if($this->config["captcha"] !== $_POST["Review"]["captcha"]) return;
			}

			//画像認証のモード
			if(isset($this->config["captcha_img"]) && $this->config["captcha_img"] == 1){

				//画像の削除:念の為
				@unlink(SOY2HTMLConfig::CacheDir() . $_POST["soy2_token"] . ".jpg");

				$captcha_value = SOY2ActionSession::getUserSession()->getAttribute("review_" . SOYSHOP_ID);
				SOY2ActionSession::getUserSession()->setAttribute("review_" . SOYSHOP_ID, null);

				//フォームが無い時
				if(!isset($_POST["Review"]["captcha"])) return;

				//入力した値に誤りがあるとき
				if(trim($_POST["Review"]["captcha"]) != $captcha_value) return;
			}

			$this->reviewLogic->setPage($page);
			$res = $this->reviewLogic->registerReview($this->review);
			if($res){
				//入力内容をクリアする
				$this->review = array();
			}
		}
	}

	function beforeOutput($page){

		$pageObj = $page->getPageObject();

		//カートページとマイページでは読み込まない
		if(!is_object($pageObj) || get_class($pageObj) != "SOYShop_Page" || $pageObj->getType() != SOYShop_Page::TYPE_DETAIL) return;

		self::prepare();

		$obj = $pageObj->getObject();
		$current = $obj->getCurrentItem();

		$page->addLabel("review_item_name", array(
			"soy2prefix" =>SOYSHOP_SITE_PREFIX,
			"text" => $current->getName()
		));

		$this->reviewLogic->setPage($page);
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

		$reviews = $this->reviewLogic->getReviews();

		$page->addModel("has_reviews", array(
			"visible" => (count($reviews) > 0),
			"soy2prefix" => "block"
		));

		//レビューが投稿されていない場合
		$page->addModel("no_reviews", array(
			"visible" => (count($reviews) == 0),
			"soy2prefix" => "block"
		));

		SOY2::import("module.plugins.item_review.component.ReviewsListComponent");
		$page->createAdd("review_list", "ReviewsListComponent", array(
			"soy2prefix" => "block",
			"list" => $reviews,
			"item" => $current,
			"mypage" => MyPageLogic::getMyPage(),
			"config" => $this->config
		));

		self::buildForm($page);
	}

	private function isLoggedIn(){
		if(!isset($this->config["login"]) || $this->config["login"] != 1){
			return true;
		}else{
			return $this->reviewLogic->isLoggedIn();
		}
	}

	/**
	 * レビューフォーム
	 * 投稿されたレビュー一覧
	 * ログインモード
	 */
	private function buildForm($page){

		$user = $this->reviewLogic->getUser();
		$nickname = (!is_null($user->getNickname())) ? $user->getNickname() : $user->getName();

		$page->addForm("review_form", array(
			"soy2prefix" => "block"
		));

		$page->addModel("review_error", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => (!isset($this->review["title"]))
		));

		$page->addInput("nickname", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"name" => "Review[nickname]",
			"value" => $nickname
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

		$page->addInput("captcha_input", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"name" => "Review[captcha]",
			"value" => ""
		));

		$page->addLabel("captcha_code", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => (isset($this->config["captcha"])) ? $this->config["captcha"] : null
		));

		//CAPTCHAの生成
		if((isset($this->config["captcha_img"])) && $this->config["captcha_img"] == 1){
			$captcha_filename = str_replace(array(".", "/", "\\"), "", soy2_get_token());
			$captcha_value = self::getRandomString(5);
			self::generateCaptchaImage($captcha_value, $captcha_filename);

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

	function getItemName($itemId){

		try{
			$item = $this->itemDao->getById($itemId);
		}catch(Exception $e){
			$item = new SOYShop_Item();
		}

		return $item->getName();
	}

	/**
	 * ランダムな文字列を取得
	 */
	private function getRandomString($length){

		$alpha = range(ord('A'), ord('Z'));

		$res = "";
		for($i = 0; $i < $length; $i++){
			$res .= chr($alpha[array_rand($alpha)]);
		}

		return $res;
	}

	/**
	 * Captcha用の画像を生成してファイルに保存する
	 * 要GD（imagejpeg）
	 */
	private function generateCaptchaImage($captcha_value, $captcha_filename){
		SOY2::import("module.plugins.item_review.logic.SimpleCaptchaGenerator");
		$gen = SimpleCaptchaGenerator::getInstance();
		if(DIRECTORY_SEPARATOR == '\\'){
			//Windowsの場合：GDFONTPATHが効かないようだ
			$gen->setFonts(array(SOY2::RootDir() . "module/plugins/item_review/fonts/tuffy.ttf"));
		}else{
			putenv("GDFONTPATH=".str_replace("\\", "/", SOY2::RootDir() . "module/plugins/item_review/fonts/"));
			$gen->setFonts(array("tuffy.ttf"));
		}
		$gen->setBgRange(255, 255);
		$gen->setFgRange(0, 0);
		$gen->setBorderRange(0, 0);
		$gen->setMaxLineWidth(1);
		imagejpeg($gen->generate($captcha_value), SOY2HTMLConfig::CacheDir() . $captcha_filename . ".jpg");
	}

	private function prepare(){
		$this->reviewLogic = SOY2Logic::createInstance("module.plugins.item_review.logic.ItemReviewLogic");
		$this->config = ItemReviewCommon::getConfig();
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	}
}
SOYShopPlugin::extension("soyshop.site.beforeoutput", "item_review", "ItemReviewBeforeOutput");
