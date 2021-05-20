<?php
SOY2::import("logic.PagerLogic");

class IndexPage extends WebPage{

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
		$start = (isset($_GET["start"]) && strlen($_GET["start"]) && $_GET["start"] != "投稿日時（始）") ? $_GET["start"] : null;
		$end = (isset($_GET["end"]) && strlen($_GET["end"]) && $_GET["end"] != "投稿日時（終）") ? $_GET["end"] : null;
		if(strlen($end) && strtotime($end) === strtotime(date("Y-m-d", strtotime($end)))){
			$end = date("Y-m-d", strtotime($end));
		}

		$trackId = (isset($_GET["trackId"]) && strlen($_GET["trackId"]) && $_GET["trackId"] != "受付番号") ? $_GET["trackId"] : null;
		$flag = (isset($_GET["flag"]) && strlen($_GET["flag"])) ? $_GET["flag"] : null;
		$commentFlag = (isset($_GET["comment_flag"]) && strlen($_GET["comment_flag"])) ? (int)$_GET["comment_flag"] : null;

		//ページャー
		$limit = 20;
		$page = (isset($args[0])) ? (int)$args[0] : 1;
		if(array_key_exists("page", $_GET)) $page = $_GET["page"];
		$offset = ($page - 1) * $limit;

    	$dao = SOY2DAOFactory::create("SOYInquiry_InquiryDAO");
    	$dao->setLimit($limit);
    	$dao->setOffset($offset);

    	$inquiries = $dao->search($this->formId, strtotime($start), strtotime($end), $trackId, $flag, $commentFlag);

		$this->forms = SOY2DAOFactory::create("SOYInquiry_FormDAO")->get();


    	//問い合わせ一覧
    	$this->buildList($inquiries);

    	//簡易検索フォーム、削除フォーム
    	$this->buildForm($start, $end, $trackId, $flag, $commentFlag);



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

    function buildList($inquiries){
    	/* 問い合わせ */
    	$this->createAdd("inquiry_list","InquiryList",array(
    		"forms" => $this->forms,
    		"formId" => $this->formId,
			"list" => $inquiries
    	));

    	//問い合わせがないとき
		DisplayPlugin::toggle("no_inquiry", !count($inquiries));
    	$this->createAdd("no_inquiry_text","HTMLModel",array(
    		"colspan" => ( is_null($this->formId) AND count($this->forms) >= 2 ) ? "6" : "5"
    	));

    	//フォームが一つしかないときとフォームが指定されているときはフォーム名は表示しない
    	$this->createAdd("form_name_th", "HTMLModel", array(
    		"visible" => ( is_null($this->formId) AND count($this->forms) >= 2 ),
    	));
    	$this->createAdd("bulk_modify_buttons", "HTMLModel", array(
    		"colspan" => ( is_null($this->formId) AND count($this->forms) >= 2 ) ? "5" :"4"
    	));
    	$this->createAdd("pager_col","HTMLModel",array(
    		"colspan" => ( is_null($this->formId) AND count($this->forms) >= 2 ) ? "6" :"5"
		));
    }

    function buildForm($start, $end, $trackId, $flag, $commentFlag = null){
    	/* 絞り込むフォームの作成 */
    	$this->createAdd("search_form", "HTMLModel", array(
    		"method" => "GET",
    		"action" => SOY2PageController::createLink("inquiry.Inquiry"),
    		"onsubmit" => "if(this.start.value == '投稿日時（始）'){this.start.value = '';}"
    		             ."if(this.end.value == '投稿日時（終）'){this.end.value = '';}"
    		             ."if(this.trackId.value == '受付番号'){this.trackId.value = '';}"
    	));

		DisplayPlugin::toggle("multi_form", (count($this->forms) > 1));

    	//フォームが一つしかないときはフォーム名は表示しない
    	$this->createAdd("forms","HTMLSelect",array(
    		"name" => "formId",
			"options" => $this->forms,
    		"property" => "name",
    		"selected" => $this->formId
    	));

    	$this->createAdd("start", "HTMLInput",array(
    		"name" => "start",
    		"value" => (strlen($start) >0) ? $start : "投稿日時（始）",
    		"style" => (strlen($start) >0) ? "" : "color: grey;width:120px;",
    		"onfocus" => "if(this.value == '投稿日時（始）'){ this.value = ''; this.style.color = '';}else{ this.select(); }",
    		"onblur"  => "if(this.value.length == 0){ this.value='投稿日時（始）'; this.style.color = 'grey'}",
    		"readonly" => true
    	));

    	$this->createAdd("end", "HTMLInput",array(
    		"name" => "end",
    		"value" => (strlen($end) >0) ? $end : "投稿日時（終）",
    		"style" => (strlen($end) >0) ? "" : "color: grey;width:120px",
    		"onfocus" => "if(this.value == '投稿日時（終）'){ this.value = ''; this.style.color = '';}else{ this.select(); }",
    		"onblur"  => "if(this.value.length == 0){ this.value='投稿日時（終）'; this.style.color = 'grey'}",
    		"readonly" => true
    	));
    	$this->createAdd("trackId", "HTMLInput",array(
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
    		SOYInquiry_Inquiry::FLAG_DELETED => "削除済"
    	);

    	$this->createAdd("flag","HTMLSelect",array(
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

    	/* 削除用フォーム */
    	$this->createAdd("bulk_modify_form","HTMLForm",array(
    		"method" => "POST",
			"action" => SOY2PageController::createLink(APPLICATION_ID . ".Inquiry")
    	));
    	$this->createAdd("bulk_delete", "HTMLInput",array(
    		"name"  => ($flag == SOYInquiry_Inquiry::FLAG_DELETED) ? "bulk_modify[flag][delete_completely]" : "bulk_modify[flag][delete]",
    		"value" => ($flag == SOYInquiry_Inquiry::FLAG_DELETED) ? "完全に削除する" : "削除する",
    		"onclick" => ($flag == SOYInquiry_Inquiry::FLAG_DELETED) ? "confirm('チェックの付いた問い合わせを完全に削除する場合はOKを押してください。')" : "",
    	));
    	$this->createAdd("bulk_read", "HTMLInput",array(
    		"visible" => $flag != SOYInquiry_Inquiry::FLAG_READ,
    		"name"  => "bulk_modify[flag][read]",
    		"value" => "既読にする"
    	));
    	$this->createAdd("bulk_new", "HTMLInput",array(
    		"visible" => strlen($flag) == 0 OR $flag != SOYInquiry_Inquiry::FLAG_NEW,
    		"name"  => "bulk_modify[flag][new]",
    		"value" => "未読にする"
    	));

    }

	function buildPager(PagerLogic $pager){

		$this->createAdd("pager_row","HTMLModel",array(
			"visible" => $pager->getTotal() > $pager->getLimit()
		));

		//件数情報表示
		$this->createAdd("count_start","HTMLLabel",array(
			"text" => $pager->getStart()
		));
		$this->createAdd("count_end","HTMLLabel",array(
			"text" => $pager->getEnd()
		));
		$this->createAdd("count_max","HTMLLabel",array(
			"text" => $pager->getTotal()
		));

		//ページへのリンク
		$this->createAdd("next_pager","HTMLLink",$pager->getNextParam());
		$this->createAdd("prev_pager","HTMLLink",$pager->getPrevParam());
		$this->createAdd("pager_list","SimplePager",$pager->getPagerParam());

		//ページへジャンプ
		$this->createAdd("pager_select","HTMLSelect",array(
			"name" => "page",
			"options" => $pager->getSelectArray(),
			"selected" => $pager->getPageURL()."/".$pager->getPage().$pager->getQueryString(),
			"onchange" => "location.href=this.options[this.selectedIndex].value"
		));

	}
}

class InquiryList extends HTMLList{

	private $forms;
	private $formId;

	protected function populateItem($entity){

		$formId = (is_string($entity->getFormId()) || is_numeric($entity->getFormId())) ? $entity->getFormId() : "";
		$detailLink = SOY2PageController::createLink(APPLICATION_ID . ".Inquiry.Detail." . $entity->getId());
		$formLink = SOY2PageController::createLink(APPLICATION_ID . ".Inquiry?formId=" . $formId);

		$this->addCheckBox("inquiry_check", array(
			"type"=>"checkbox",
			"name"=>"bulk_modify[inquiry][]",
			"value"=>$entity->getId(),
			//"label" => $entity->getId(),
		));

    	//フォームが一つしかないときとフォームが指定されているときはフォーム名は表示しない
		$this->addModel("form_name_td", array(
			"style"   => "cursor:pointer;". (($entity->getFlag() == SOYInquiry_Inquiry::FLAG_NEW) ? "color:black;font-weight: bold;" : ""),
    		"visible" => (is_null($this->formId) && count($this->forms) >= 2),
    		"onclick" => "location.href='{$detailLink}'"
		));
		$this->addLink("form_name", array(
			"text" => ( (isset($this->forms[$formId])) ? $this->forms[$formId]->getName() : "" ),
			//"link" => $formLink,
			"title" => ( (isset($this->forms[$formId])) ? $this->forms[$formId]->getName() : "" ),
		));

		$this->addLink("traking_number", array(
			"text" => $entity->getTrackingNumber(),
			"link" => $detailLink,
			"style" => ($entity->getFlag() == SOYInquiry_Inquiry::FLAG_NEW) ? "color:black;font-weight: bold;" : ""
		));

		//getContentの中身はhtmlspecialcharsがかかっている
		$this->addLabel("content", array(
			"html"  => (mb_strlen($entity->getContent()) >= 80) ? mb_substr($entity->getContent(), 0, 80) . "..." : $entity->getContent(),
			"style" => "cursor:pointer;". ( ($entity->getFlag() == SOYInquiry_Inquiry::FLAG_NEW) ? "color:black;font-weight: bold;" : "" ),
			"title" => $entity->getContent(),
			"onclick" => "location.href='{$detailLink}'"
		));

		$this->addLabel("create_date", array(
			"text" => (is_numeric($entity->getCreateDate())) ? date("Y-m-d H:i:s",$entity->getCreateDate()) : "",
			"style" => "cursor:pointer;".( ($entity->getFlag() == SOYInquiry_Inquiry::FLAG_NEW) ? "color:black;font-weight: bold;" : "" ),
			"onclick" => "location.href='{$detailLink}'"
		));

		$this->addLink("flag", array(
			"text" => $entity->getFlagText(),
			"link" => $detailLink,
			"style" => ($entity->getFlag() == SOYInquiry_Inquiry::FLAG_NEW) ? "font-weight: bold;" : ""
		));

		$this->addModel("traking_number_td", array("onclick" => "location.href='{$detailLink}'","style" => "cursor:pointer;"));
		$this->addModel("create_date_td", array("onclick" => "location.href='{$detailLink}'","style" => "cursor:pointer;"));
		$this->addModel("flag_td", array("onclick" => "location.href='{$detailLink}'","style" => "cursor:pointer;"));
	}

	function getForms() {
		return $this->forms;
	}
	function setForms($forms) {
		$this->forms = $forms;
	}
	function setFormId($formId) {
		$this->formId = $formId;
	}
}
