<?php
SOY2::import("logic.PagerLogic");

class IndexPage extends WebPage{

	const SEARCH_CONDITION_STAET = 0;
	const SEARCH_CONDITION_END = 1;

	private $formId;
	private $forms;

	function doPost(){
		if(!isset($_POST["bulk_modify"]) OR !isset($_POST["bulk_modify"]["flag"]) OR !isset($_POST["bulk_modify"]["inquiry"])){
			return;
		}
		if( !is_array($_POST["bulk_modify"]["flag"]) OR !is_array($_POST["bulk_modify"]["inquiry"])){
			return;
		}

		$logic = SOY2Logic::createInstance("logic.InquiryLogic");
		$keys = array_keys($_POST["bulk_modify"]["flag"]);
		$flag_text = array_shift($keys);
		$inquiry_ids = $_POST["bulk_modify"]["inquiry"];

		if($flag_text == "delete_completely"){

			$logic->bulk_delete($inquiry_ids);

		}else{

			switch($flag_text){
				case "new" :
					$flag = SOYInquiry_Inquiry::FLAG_NEW;
					break;
				case "read" :
					$flag = SOYInquiry_Inquiry::FLAG_READ;
					break;
				case "delete" :
					$flag = SOYInquiry_Inquiry::FLAG_DELETED;
					break;
				default:
					return;
			}

			$logic->bulk_update_flag($inquiry_ids, $flag);
		}

		CMSApplication::jump("Inquiry");
	}

    function __construct($args) {
		SOY2DAOFactory::importEntity("SOYInquiry_Inquiry");

    	parent::__construct();

    	/* 検索 */
		$this->formId = (isset($_GET["formId"]) && strlen($_GET["formId"])>0) ? $_GET["formId"] : null;
		$start = (isset($_GET["start"]) && strlen($_GET["start"]) && $_GET["start"] != "投稿日時（始）") ? $_GET["start"] : "";
		$end = (isset($_GET["end"]) && strlen($_GET["end"]) && $_GET["end"] != "投稿日時（終）") ? $_GET["end"] : "";
		if(strlen($end) && strtotime($end) === strtotime(date("Y-m-d", strtotime($end)))) $end = date("Y-m-d", strtotime($end));
		
		$trackId = (isset($_GET["trackId"]) && strlen($_GET["trackId"]) && $_GET["trackId"] != "受付番号") ? $_GET["trackId"] : "";
		$flag = (isset($_GET["flag"]) && strlen($_GET["flag"])) ? (int)$_GET["flag"] : -1;
		$commentFlag = (isset($_GET["comment_flag"]) && strlen($_GET["comment_flag"])) ? (int)$_GET["comment_flag"] : null;

		//ページャー
		$limit = (isset($_GET["display_count"]) && is_numeric($_GET["display_count"])) ? (int)$_GET["display_count"] : 20;
		$page = (isset($args[0])) ? (int)$args[0] : 1;
		if(array_key_exists("page", $_GET)) $page = $_GET["page"];
		$offset = ($page - 1) * $limit;

    	$dao = SOY2DAOFactory::create("SOYInquiry_InquiryDAO");
    	$dao->setLimit($limit);
    	$dao->setOffset($offset);

    	$inquiries = $dao->search((string)$this->formId, strtotime(self::_getSearchConditionStrtotime($start)), strtotime(self::_getSearchConditionStrtotime($end, self::SEARCH_CONDITION_END)), $trackId, $flag, $commentFlag);

		$this->forms = SOY2DAOFactory::create("SOYInquiry_FormDAO")->get();


    	//問い合わせ一覧
    	self::_buildInquiryList($inquiries);

    	//簡易検索フォーム、削除フォーム
    	self::_buildForm($start, $end, $trackId, $flag, $limit, $commentFlag);



		//ページャー
		$start = $offset;
		$end = $start + count($inquiries);
		if($end > 0 && $start == 0)$start = 1;

		//TODO http_build_query($_GET)の追加

		$pager = new PagerLogic();
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($dao->getRowCount());
		$pager->setLimit($limit);

		$pager->setPageURL("inquiry.Inquiry");
		if(isset($_GET["soy2_token"])) unset($_GET["soy2_token"]);
		$pager->setQuery($_GET);

		$this->buildPager($pager);
    }

	/**
	 * 投稿日時が空の場合は初めてのお問い合わせの日時
	 * @param string, int
	 * @return string
	 */
	private function _getSearchConditionStrtotime(string $str, int $mode=self::SEARCH_CONDITION_STAET){
		if(strlen($str)) return $str;

		switch($mode){
			case self::SEARCH_CONDITION_STAET:
				$dao = new SOY2DAO();
				try{
					$res = $dao->executeQuery("SELECT create_date FROM soyinquiry_inquiry ORDER BY create_date ASC LIMIT 1;");
				}catch(Exception $e){
					$res = array();
				}
				return (isset($res[0]["create_date"])) ? date("Y-m-d", $res[0]["create_date"]) : date("Y-m-d");
			case self::SEARCH_CONDITION_END:
				return date("Y-m-d", strtotime("+1day"));
		}
	}

    private function _buildInquiryList(array $inquiries){
    	/* 問い合わせ */
    	$this->createAdd("inquiry_list", "_common.Inquiry.InquiryListComponent", array(
    		"forms" => $this->forms,
    		"formId" => $this->formId,
			"list" => $inquiries
    	));

    	//問い合わせがないとき
		DisplayPlugin::toggle("no_inquiry", !count($inquiries));
    	$this->addModel("no_inquiry_text", array(
    		"colspan" => ( is_null($this->formId) && count($this->forms) >= 2 ) ? "6" : "5"
    	));

    	//フォームが一つしかないときとフォームが指定されているときはフォーム名は表示しない
    	$this->addModel("form_name_th", array(
    		"visible" => ( is_null($this->formId) && count($this->forms) >= 2 ),
    	));
    	$this->addModel("bulk_modify_buttons", array(
    		"colspan" => ( is_null($this->formId) && count($this->forms) >= 2 ) ? "5" :"4"
    	));
    	$this->addModel("pager_col", array(
    		"colspan" => ( is_null($this->formId) && count($this->forms) >= 2 ) ? "6" :"5"
		));
    }

    private function _buildForm(string $start, string $end, string $trackId, int $flag, int $limit=20, $commentFlag = null){
    	/* 絞り込むフォームの作成 */
    	$this->addModel("search_form", array(
    		"method" => "GET",
    		"action" => SOY2PageController::createLink("inquiry.Inquiry"),
    		"onsubmit" => "if(this.start.value == '投稿日時（始）'){this.start.value = '';}"
    		             ."if(this.end.value == '投稿日時（終）'){this.end.value = '';}"
    		             ."if(this.trackId.value == '受付番号'){this.trackId.value = '';}"
    	));

		DisplayPlugin::toggle("multi_form", (count($this->forms) > 1));

    	//フォームが一つしかないときはフォーム名は表示しない
    	$this->addSelect("forms", array(
    		"name" => "formId",
			"options" => $this->forms,
    		"property" => "name",
    		"selected" => $this->formId
    	));

    	$this->addInput("start", array(
    		"name" => "start",
    		"value" => (strlen($start) >0) ? $start : "投稿日時（始）",
    		"style" => (strlen($start) >0) ? "" : "color: grey;width:120px;",
    		"onfocus" => "if(this.value == '投稿日時（始）'){ this.value = ''; this.style.color = '';}else{ this.select(); }",
    		"onblur"  => "if(this.value.length == 0){ this.value='投稿日時（始）'; this.style.color = 'grey'}",
    		"readonly" => true
    	));

    	$this->addInput("end", array(
    		"name" => "end",
    		"value" => (strlen($end) >0) ? $end : "投稿日時（終）",
    		"style" => (strlen($end) >0) ? "" : "color: grey;width:120px",
    		"onfocus" => "if(this.value == '投稿日時（終）'){ this.value = ''; this.style.color = '';}else{ this.select(); }",
    		"onblur"  => "if(this.value.length == 0){ this.value='投稿日時（終）'; this.style.color = 'grey'}",
    		"readonly" => true
    	));
    	$this->addInput("trackId", array(
    		"name"  => "trackId",
    		"value" => (strlen($trackId) >0) ? $trackId : "受付番号",
    		"style" => (strlen($trackId) >0) ? "" : "color: grey;",
    		"onfocus" => "if(this.value == '受付番号'){ this.value = ''; this.style.color = '';}else{ this.select(); }",
    		"onblur"  => "if(this.value.length == 0){ this.value='受付番号'; this.style.color = 'grey'}"
    	));

    	$flags = array(
    		"" => "全て",
    		SOYInquiry_Inquiry::FLAG_NEW => "未読のみ",
    		SOYInquiry_Inquiry::FLAG_READ => "既読のみ",
    		//SOYInquiry_Inquiry::FLAG_DELETED => "削除済"	//GarbagePageに移設
    	);

    	$this->addSelect("flag", array(
    		"name" => "flag",
    		"options" => $flags,
    		"indexOrder" => true,
    		"selected" => $flag
    	));

    	$comemnts = array(
    		"" => "全て",
    		SOYInquiry_Inquiry::COMMENT_HAS => "メモ有り",
    		SOYInquiry_Inquiry::COMMENT_NONE => "メモ無し",
    	);

    	$this->addSelect("comment_flag", array(
    		"name" => "comment_flag",
    		"options" => $comemnts,
    		"indexOrder" => true,
    		"selected" => $commentFlag
    	));

		$this->addInput("display_count", array(
			"name" => "display_count",
			"value" => $limit
		));

    	/* 削除用フォーム */
    	$this->addForm("bulk_modify_form", array(
    		"method" => "POST",
			"action" => SOY2PageController::createLink(APPLICATION_ID . ".Inquiry")
    	));
    	$this->addInput("bulk_delete", array(
    		"name"  => ($flag == SOYInquiry_Inquiry::FLAG_DELETED) ? "bulk_modify[flag][delete_completely]" : "bulk_modify[flag][delete]",
    		"value" => ($flag == SOYInquiry_Inquiry::FLAG_DELETED) ? "完全に削除する" : "削除する",
    		"onclick" => ($flag == SOYInquiry_Inquiry::FLAG_DELETED) ? "confirm('チェックの付いた問い合わせを完全に削除する場合はOKを押してください。')" : "",
    	));
    	$this->addInput("bulk_read", array(
    		"visible" => $flag != SOYInquiry_Inquiry::FLAG_READ,
    		"name"  => "bulk_modify[flag][read]",
    		"value" => "既読にする"
    	));
    	$this->addInput("bulk_new", array(
    		"visible" => $flag != SOYInquiry_Inquiry::FLAG_NEW,
    		"name"  => "bulk_modify[flag][new]",
    		"value" => "未読にする"
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