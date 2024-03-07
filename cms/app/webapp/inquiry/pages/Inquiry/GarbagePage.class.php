<?php
SOY2::import("logic.PagerLogic");

class GarbagePage extends WebPage{

	function doPost(){
		if(soy2_check_token() && isset($_POST["delete_modify"])){
			$ids = array();
			if(isset($_POST["bulk_modify"]["inquiry"])){
				foreach($_POST["bulk_modify"]["inquiry"] as $id){
					$ids[] = (int)$id;
				}
			}
			if(count($ids)){
				SOY2DAOFactory::importEntity("SOYInquiry_Inquiry");
				$logic = SOY2Logic::createInstance("logic.InquiryLogic");
				switch(key($_POST["delete_modify"])){
					case "logical_delete_cancel":
						$logic->bulk_update_flag($ids, SOYInquiry_Inquiry::FLAG_NEW);
						break;
					case "physical_delete":
						$logic->bulk_delete($ids);
						break;
					default:
						// 何もしない
				}
			}
			
			CMSApplication::jump("Inquiry.Garbage");
		}
	}

    function __construct($args) {
		SOY2DAOFactory::importEntity("SOYInquiry_Inquiry");
		
    	parent::__construct();


		$this->addLabel("physical_delete_days", array(
			"text" => SOYInquiryUtil::SOYINQUIRY_PHYSICAL_DELETE_DAYS
		));

		//ページャー
		$limit = 20;
		$page = (isset($args[0])) ? (int)$args[0] : 1;
		if(array_key_exists("page", $_GET)) $page = $_GET["page"];
		$offset = ($page - 1) * $limit;

    	$dao = SOY2DAOFactory::create("SOYInquiry_InquiryDAO");
    	$dao->setLimit($limit);
    	$dao->setOffset($offset);

    	try{
			$inquiries = $dao->getByFlag(SOYInquiry_Inquiry::FLAG_DELETED);
		}catch(Exception $e){
			$inquiries = array();
		}
		

		$this->forms = SOY2DAOFactory::create("SOYInquiry_FormDAO")->get();


    	//問い合わせ一覧
    	self::_buildInquiryList($inquiries);

		//ページャー
		$start = $offset;
		$end = $start + count($inquiries);
		if($end > 0 && $start == 0) $start = 1;

		//TODO http_build_query($_GET)の追加

		$pager = new PagerLogic();
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($dao->getRowCount());
		$pager->setLimit($limit);

		$pager->setPageURL("inquiry.Inquiry.Garbage");
		if(isset($_GET["soy2_token"])) unset($_GET["soy2_token"]);
		$pager->setQuery($_GET);

		$this->buildPager($pager);
    }

    private function _buildInquiryList(array $inquiries){
    	/* 問い合わせ */
    	$this->createAdd("inquiry_list", "_common.Inquiry.InquiryListComponent", array(
    		"forms" => $this->forms,
    		"formId" => null,
			"list" => $inquiries
    	));

    	//問い合わせがないとき
		DisplayPlugin::toggle("no_inquiry", !count($inquiries));
    	$this->addModel("no_inquiry_text", array(
    		"colspan" => (count($this->forms) >= 2 ) ? "6" : "5"
    	));

    	//フォームが一つしかないときとフォームが指定されているときはフォーム名は表示しない
    	$this->addModel("form_name_th", array(
    		"visible" => (count($this->forms) >= 2 ),
    	));
    	$this->addModel("bulk_modify_buttons", array(
    		"colspan" => (count($this->forms) >= 2 ) ? "5" :"4"
    	));
    	$this->addModel("pager_col", array(
    		"colspan" => (count($this->forms) >= 2 ) ? "6" :"5"
		));

		$this->addForm("bulk_modify_form");
		
		$this->addInput("logical_delete_cancel", array(
    		"name"  => "delete_modify[logical_delete_cancel]",
    		"value" => "論理削除を取り消す"
    	));
    	$this->addInput("physical_delete", array(
    		"name"  => "delete_modify[physical_delete]",
    		"value" => "物理削除",
			"onclick" => "return confirm('物理削除を行ってもよろしいでしょうか？');"
    	));
    }

	function buildPager(PagerLogic $pager){

		$this->addModel("pager_row", array(
			"visible" => $pager->getTotal() > $pager->getLimit()
		));

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
		$this->addSelect("pager_select", array(
			"name" => "page",
			"options" => $pager->getSelectArray(),
			"selected" => $pager->getPageURL()."/".$pager->getPage().$pager->getQueryString(),
			"onchange" => "location.href=this.options[this.selectedIndex].value"
		));
	}
}