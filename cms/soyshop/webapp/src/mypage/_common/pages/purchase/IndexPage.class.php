<?php

class IndexPage extends MainMyPagePageBase{

	//表示件数
    private $limit = 15;

	function doPost(){}

	function __construct($args){
		SOY2Logic::createInstance("module.plugins.purchase_manager.logic.SOYInquiryLogic")->exec();
		$this->checkIsLoggedIn(); //ログインチェック

		//買取プラグインがアクティブでない場合はトップページに飛ばす
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("purchase_manager")) $this->jumpToTop();

		parent::__construct();

		$user = $this->getUser();

        $this->addLabel("user_name", array(
            "text" => $user->getName()
        ));

        /*引数など取得*/
        //表示件数
        $limit = $this->limit;
        $page = (isset($args[0])) ? (int)$args[0] : $this->getParameter("page");
        if(array_key_exists("page", $_GET)){
            $page = $_GET["page"];
        }
        $page = max(1, $page);
        $offset = ($page - 1) * $limit;

        $searchLogic = SOY2Logic::createInstance("module.plugins.purchase_manager.logic.SearchPurchaseLogic");
		$searchLogic->setMode("mypage");

        //検索条件の投入と検索実行
        $searchLogic->setLimit($limit);
        $searchLogic->setOffset($offset);
        $purchases = $searchLogic->search();
		$total = $searchLogic->getTotal();

        //ページャーの作成
        $start = $offset + 1;
        $end = $offset + count($purchases);

        $pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
        $pager->setPublishPageUrl("purchase");
        $pager->setPage($page);
        $pager->setStart($start);
        $pager->setEnd($end);
        $pager->setTotal($total);
        $pager->setLimit($limit);
        //$pager->setQuery(array("search" => $search));

        $pager->buildPager($this);

        $this->createAdd("purchase_list", "_common.purchase.PurchaseListComponent", array(
            "list" => $purchases,
			"userId" => $this->getUser()->getId()
        ));

		DisplayPlugin::toggle("has_purchase", $total > 0);
		DisplayPlugin::toggle("no_purchase", $total === 0);

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_top_url()
		));
	}
}
