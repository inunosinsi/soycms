<?php
SOY2::imports("module.plugins.item_review.domain.*");
SOY2::imports("module.plugins.item_review.logic.*");
class IndexPage extends WebPage{

	//表示件数
	private $limit = 15;

	function doPost(){

		if(!soy2_check_token()) SOY2PageController::jump("Review");

		$reviews = $_POST["reviews"];

		if(isset($_POST["do_change_publish"])){
			$publish = $_POST["do_change_publish"];
			SOY2Logic::createInstance("logic.review.ReviewLogic")->changeOpen($reviews, $publish);

			SOY2PageController::jump("Review?updated");
			exit;
		}

		if(isset($_POST["do_remove"])){
			SOY2Logic::createInstance("logic.review.ReviewLogic")->delete($reviews);
			SOY2PageController::jump("Review?deleted");
			exit;
		}
	}

    function __construct($args) {
    	if(!self::isReview()) SOY2PageController::jump("");

    	parent::__construct();

    	if(isset($_GET["reset"])){
			$this->setParameter("page", 1);
			$this->setParameter("sort", null);
		}

		/*引数など取得*/
		$limit = $this->limit;
		$page = (isset($args[0])) ? (int)$args[0] : $this->getParameter("page");
		if(array_key_exists("page", $_GET)) $page = $_GET["page"];
		if(array_key_exists("sort", $_GET) OR array_key_exists("search", $_GET)) $page = 1;
		$page = max(1, $page);

		$offset = ($page - 1) * $limit;

		//表示順
		$sort = $this->getParameter("sort");
		$this->setParameter("page", $page);

		/*データ*/
		$searchLogic = SOY2Logic::createInstance("logic.review.SearchReviewLogic");
		$searchLogic->setLimit($limit);
		$searchLogic->setOffset($offset);
		$searchLogic->setOrder($sort);

		//データ取得
		$total = (int)$searchLogic->getTotalCount();
		$reviews = ($total > 0) ? $searchLogic->getReviews() : array();

		DisplayPlugin::toggle("has_review", $total > 0);
		DisplayPlugin::toggle("no_review", $total === 0);

		/*表示*/

		//表示順リンク
		$this->buildSortLink($searchLogic, $sort);

		//ページャー
		$start = $offset + 1;
		$end = $offset + count($reviews);
		if($end > 0 && $start == 0) $start = 1;

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPageURL("Review");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);

		$pager->buildPager($this);

    	$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

    	$this->createAdd("review_list", "_common.Review.ReviewListComponent", array(
    		"list" => $reviews,
    	));

    	//操作周り
		$this->addForm("review_form");

		$this->addLink("reset_link", array(
			"link" => SOY2PageController::createLink("Review") . "?reset",
			"visible" => ($sort)
		));
    }

    function getParameter($key){
		if(array_key_exists($key, $_GET)){
			$value = $_GET[$key];
			$this->setParameter($key, $value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("Review.Search:" . $key);
		}
		return $value;
	}
	function setParameter($key, $value){
		SOY2ActionSession::getUserSession()->setAttribute("Review.Search:" . $key, $value);
	}

	function buildSortLink($logic, $sort){

		$link = SOY2PageController::createLink("Review");
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

    /**
     * レビュープラグインがアクティブかどうか
     */
    private function isReview(){
    	return (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("item_review")));
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("レビュー");
	}

	function getFooterMenu(){
		try{
			return SOY2HTMLFactory::createInstance("Review.FooterMenu.ReviewFooterMenuPage", array(
				"arguments" => array(null)
			))->getObject();
		}catch(Exception $e){
			//
			return null;
		}
	}
}
