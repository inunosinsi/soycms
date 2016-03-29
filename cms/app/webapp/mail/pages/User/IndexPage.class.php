<?php
SOY2HTMLFactory::importWebPage("_common.CommonPartsPage");
SOY2::import("logic.PagerLogic");

class IndexPage extends CommonPartsPage{

	function IndexPage($args) {
		WebPage::WebPage();
		
		$this->createTag();

		/*引数など取得*/
		//表示件数
		$limit = 15;
		$page = (isset($args[0])) ? (int)$args[0] : 1;
		if(array_key_exists("page", $_GET)) $page = $_GET["page"]; 
		if(array_key_exists("sort", $_GET) OR array_key_exists("search", $_GET)) $page = 1; 
		$offset = ($page - 1) * $limit;
		
		//表示順
		$sort = $this->getParameter("sort");
		//検索
		$search = $this->getParameter("search");
		if(!$search)$search = array();
		
		/*データ*/
		//DAO
		$SearchUsers = SOY2Logic::createInstance("logic.user.SearchUsersLogic");
		$SearchUsers->setLimit($limit);
		$SearchUsers->setOffset($offset);
		$SearchUsers->setSortOrder($sort);
		$SearchUsers->setSearchCondition($search);

		//データ取得
		$total = $SearchUsers->getTotalCount();
		$users = $SearchUsers->getUsers();
		
		/*表示*/
		
		//表示順リンク
		$this->buildSortLink($sort);
		//絞込みフォーム
		$this->buildSearchForm($search);
		//詳細検索フォーム
		$this->buildAdvancedSearchForm($search);
		
		//ユーザ一覧
		$this->createAdd("user_list","_common.UserListComponent",array(
			"list" => $users
		));

		//ページャー
		$start = $offset;
		$end = $start + count($users);
		if($end > 0 && $start == 0)$start = 1;

		$pager = new PagerLogic();
		$pager->setPageURL("mail.User");
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
			SOY2ActionSession::getUserSession()->setAttribute("mail.User:"."search", $value);
		}
		if(array_key_exists("reset", $_POST)){
			SOY2ActionSession::getUserSession()->setAttribute("mail.User:"."search", array());
		}
		CMSApplication::jump("User");
	}
	
	function getParameter($key){
		if(array_key_exists($key, $_GET)){
			$value = $_GET[$key];
			SOY2ActionSession::getUserSession()->setAttribute("mail.User:".$key, $value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("mail.User:".$key);
		}
		return $value;
	}
	
	function buildSortLink($sort){
		$this->createAdd("sort_id","HTMLLink",array(
			"text" => "▲",
			"link" => "?sort=".SearchUsersLogic::SORT_ID,
			"title" => "昇順",
			"class" => ($sort === SearchUsersLogic::SORT_ID) ? "pager_disable" : ""
		));
		$this->createAdd("sort_id_desc","HTMLLink",array(
			"text" => "▼",
			"link" => "?sort=".SearchUsersLogic::SORT_ID_DESC,
			"title" => "降順",
			"class" => ($sort === SearchUsersLogic::SORT_ID_DESC) ? "pager_disable" : ""
		));
		$this->createAdd("sort_name","HTMLLink",array(
			"text" => "▲",
			"link" => "?sort=".SearchUsersLogic::SORT_READING,
			"title" => "昇順",
			"class" => ($sort === SearchUsersLogic::SORT_READING) ? "pager_disable" : ""
		));
		$this->createAdd("sort_name_desc","HTMLLink",array(
			"text" => "▼",
			"link" => "?sort=".SearchUsersLogic::SORT_READING_DESC,
			"title" => "降順",
			"class" => ($sort === SearchUsersLogic::SORT_READING_DESC) ? "pager_disable" : ""
		));
		$this->createAdd("sort_mail_address","HTMLLink",array(
			"text" => "▲",
			"link" => "?sort=".SearchUsersLogic::SORT_MAIL_ADDRESS,
			"title" => "昇順",
			"class" => ($sort === SearchUsersLogic::SORT_MAIL_ADDRESS) ? "pager_disable" : ""
		));
		$this->createAdd("sort_mail_address_desc","HTMLLink",array(
			"text" => "▼",
			"link" => "?sort=".SearchUsersLogic::SORT_MAIL_ADDRESS_DESC,
			"title" => "降順",
			"class" => ($sort === SearchUsersLogic::SORT_MAIL_ADDRESS_DESC) ? "pager_disable" : ""
		));
	}
	
	function buildSearchForm($search){
    	$this->createAdd("search_form", "HTMLModel", array(
    		"method" => "GET",
    		"onsubmit" => "if(this.elements['search[id]'].value == 'ID'){this.elements['search[id]'].value = '';}"
    		             ."if(this.elements['search[name]'].value == '姓名'){this.elements['search[name]'].value = '';}"
    		             ."if(this.elements['search[mail_address]'].value == 'メールアドレス'){this.elements['search[mail_address]'].value = '';}"
    		             ."if(this.elements['search[attribute1]'].value == '属性１'){this.elements['search[attribute1]'].value = '';}"
    		             ."if(this.elements['search[attribute2]'].value == '属性２'){this.elements['search[attribute2]'].value = '';}"
    		             ."if(this.elements['search[attribute3]'].value == '属性３'){this.elements['search[attribute3]'].value = '';}"
    	));
    	
    	$this->createAdd("soy2_token","HTMLInput",array(
    		"name" => "soy2_token",
    		"value" => soy2_get_token()
    	));

		$this->createAdd("search_id","HTMLInput",array(
			"name" => "search[id]",
    		"value" => (strlen(@$search["id"]) >0) ? @$search["id"] : "ID",
    		"style" => (strlen(@$search["id"]) >0) ? "width:90%;" : "width:90%;color: grey;",
    		"onfocus" => "if(this.value == 'ID'){ this.value = ''; this.style.color = '';}else{ this.select(); }",
    		"onblur"  => "if(this.value.length == 0){ this.value='ID'; this.style.color = 'grey'}"
		));
		$this->createAdd("search_name","HTMLInput",array(
			"name" => "search[name]",
    		"value" => (strlen(@$search["name"]) >0) ? @$search["name"] : "姓名",
    		"style" => (strlen(@$search["name"]) >0) ? "width:90%;" : "width:90%;color: grey;",
    		"onfocus" => "if(this.value == '姓名'){ this.value = ''; this.style.color = '';}else{ this.select(); }",
    		"onblur"  => "if(this.value.length == 0){ this.value='姓名'; this.style.color = 'grey'}"
		));
		$this->createAdd("search_mail_address","HTMLInput",array(
			"name" => "search[mail_address]",
    		"value" => (strlen(@$search["mail_address"]) >0) ? @$search["mail_address"] : "メールアドレス",
    		"style" => (strlen(@$search["mail_address"]) >0) ? "width:90%;" : "width:90%;color: grey;",
    		"onfocus" => "if(this.value == 'メールアドレス'){ this.value = ''; this.style.color = '';}else{ this.select(); }",
    		"onblur"  => "if(this.value.length == 0){ this.value='メールアドレス'; this.style.color = 'grey'}"
		));
		$this->createAdd("search_attribute1","HTMLInput",array(
			"name" => "search[attribute1]",
    		"value" => (strlen(@$search["attribute1"]) >0) ? @$search["attribute1"] : "属性１",
    		"style" => (strlen(@$search["attribute1"]) >0) ? "width:90%;" : "width:90%;color: grey;",
    		"onfocus" => "if(this.value == '属性１'){ this.value = ''; this.style.color = '';}else{ this.select(); }",
    		"onblur"  => "if(this.value.length == 0){ this.value='属性１'; this.style.color = 'grey'}"
		));
		$this->createAdd("search_attribute2","HTMLInput",array(
			"name" => "search[attribute2]",
    		"value" => (strlen(@$search["attribute2"]) >0) ? @$search["attribute2"] : "属性２",
    		"style" => (strlen(@$search["attribute2"]) >0) ? "width:90%;" : "width:90%;color: grey;",
    		"onfocus" => "if(this.value == '属性２'){ this.value = ''; this.style.color = '';}else{ this.select(); }",
    		"onblur"  => "if(this.value.length == 0){ this.value='属性２'; this.style.color = 'grey'}"
		));
		$this->createAdd("search_attribute3","HTMLInput",array(
			"name" => "search[attribute3]",
    		"value" => (strlen(@$search["attribute3"]) >0) ? @$search["attribute3"] : "属性３",
    		"style" => (strlen(@$search["attribute3"]) >0) ? "width:90%;" : "width:90%;color: grey;",
    		"onfocus" => "if(this.value == '属性３'){ this.value = ''; this.style.color = '';}else{ this.select(); }",
    		"onblur"  => "if(this.value.length == 0){ this.value='属性３'; this.style.color = 'grey'}"
		));
		$this->createAdd("advanced_search", "HTMLLink", array(
			"onclick" => "advanced_search_form();return false;" 
		));
		
	}
	
	function buildAdvancedSearchForm($search){
		$this->createAdd("advanced_search_form","HTMLForm");
		
		$this->createAdd("advanced_search_id","HTMLInput",array(
			"name" => "search[id]",
			"value" => @$search["id"],
		));

		$this->createAdd("advanced_search_mail_address","HTMLInput",array(
			"name" => "search[mail_address]",
			"value" => @$search["mail_address"],
			"style" => "width:100%",
		));

		$this->createAdd("advanced_search_name","HTMLInput",array(
			"name" => "search[name]",
			"value" => @$search["name"],
			"style" => "width:100%",
		));
		
		$this->createAdd("advanced_search_furigana","HTMLInput",array(
			"name" => "search[reading]",
			"value" => @$search["reading"],
			"style" => "width:100%",
		));

		$this->createAdd("advanced_search_gender_male","HTMLCheckbox", array(
			"name" => "search[gender][male]",
			"selected" => ( array_key_exists("gender", $search) AND array_key_exists("male", $search["gender"]) ) ? true : false ,
			"value" => 1,
			"elementId" => "checkbox_gender_male"
		));
		
		$this->createAdd("advanced_search_gender_female","HTMLCheckbox", array(
			"name" => "search[gender][female]",
			"selected" => ( array_key_exists("gender", $search) AND array_key_exists("female", $search["gender"]) ) ? true : false ,
			"value" => 1,
			"elementId" => "checkbox_gender_female"
		));
		$this->createAdd("advanced_search_gender_other","HTMLCheckbox", array(
			"name" => "search[gender][other]",
			"selected" => ( array_key_exists("gender", $search) AND array_key_exists("other", $search["gender"]) ) ? true : false ,
			"value" => 1,
			"elementId" => "checkbox_gender_other"
		));
		
		$this->createAdd("advanced_search_birth_date_start_year","HTMLInput",array(
			"name" => "search[birthday][start][year]",
			"value" => @$search["birthday"]["start"]["year"],
			"size" => "5",
		));
		$this->createAdd("advanced_search_birth_date_start_month","HTMLInput",array(
			"name" => "search[birthday][start][month]",
			"value" => @$search["birthday"]["start"]["month"],
			"size" => "3",
		));
		$this->createAdd("advanced_search_birth_date_start_day","HTMLInput",array(
			"name" => "search[birthday][start][day]",
			"value" => @$search["birthday"]["start"]["day"],
			"size" => "3",
		));
		$this->createAdd("advanced_search_birth_date_end_year","HTMLInput",array(
			"name" => "search[birthday][end][year]",
			"value" => @$search["birthday"]["end"]["year"],
			"size" => "5",
		));
		$this->createAdd("advanced_search_birth_date_end_month","HTMLInput",array(
			"name" => "search[birthday][end][month]",
			"value" => @$search["birthday"]["end"]["month"],
			"size" => "3",
		));
		$this->createAdd("advanced_search_birth_date_end_day","HTMLInput",array(
			"name" => "search[birthday][end][day]",
			"value" => @$search["birthday"]["end"]["day"],
			"size" => "3",
		));
		
		$this->createAdd("advanced_search_post_number","HTMLInput",array(
			"name" => "search[zip_code]",
			"value" => @$search["zip_code"],
			"style" => "width:100%",
		));
		
		$this->createAdd("advanced_search_area","HTMLSelect",array(
			"name" => "search[area]",
			"selected" => @$search["area"],
			"options" => Area::getAreas(),
		));
		
		$this->createAdd("advanced_search_address1","HTMLInput",array(
			"name" => "search[address1]",
			"value" => @$search["address1"],
			"style" => "width:100%",
		));
		
		$this->createAdd("advanced_search_address2","HTMLInput",array(
			"name" => "search[address2]",
			"value" => @$search["address2"],
			"style" => "width:100%",
		));

		$this->createAdd("advanced_search_tel_number","HTMLInput",array(
			"name" => "search[telephone_number]",
			"value" => @$search["telephone_number"],
			"style" => "width:100%",
		));
		
		$this->createAdd("advanced_search_fax_number","HTMLInput",array(
			"name" => "search[fax_number]",
			"value" => @$search["fax_number"],
			"style" => "width:100%",
		));

		$this->createAdd("advanced_search_ketai_number","HTMLInput",array(
			"name" => "search[cellphone_number]",
			"value" => @$search["cellphone_number"],
			"style" => "width:100%",
		));

		$this->createAdd("advanced_search_office","HTMLInput",array(
			"name" => "search[job_name]",
			"value" => @$search["job_name"],
			"style" => "width:100%",
		));
		$this->createAdd("advanced_search_office_post_number","HTMLInput",array(
			"name" => "search[job_zip_code]",
			"value" => @$search["job_zip_code"],
			"style" => "width:100%",
		));
		$this->createAdd("advanced_search_jobArea","HTMLSelect",array(
			"name" => "search[job_area]",
			"selected" => @$search["job_area"],
			"options" => Area::getAreas(),
		));
		$this->createAdd("advanced_search_jobAddress1","HTMLInput",array(
			"name" => "search[job_address1]",
			"value" => @$search["job_address1"],
			"style" => "width:100%",
		));
		$this->createAdd("advanced_search_jobAddress2","HTMLInput",array(
			"name" => "search[job_address2]",
			"value" => @$search["job_address2"],
			"style" => "width:100%",
		));
		$this->createAdd("advanced_search_office_tel_number","HTMLInput",array(
			"name" => "search[job_telephone_number]",
			"value" => @$search["job_telephone_number"],
			"style" => "width:100%",
		));
		$this->createAdd("advanced_search_office_fax_number","HTMLInput",array(
			"name" => "search[job_fax_number]",
			"value" => @$search["job_fax_number"],
			"style" => "width:100%",
		));

		
		$this->createAdd("advanced_search_memo","HTMLTextArea",array(
			"name" => "search[memo]",
			"value" => @$search["memo"],
			"style" => "width:100%; padding: 2px; margin: 0;",
		));
		
		$this->createAdd("advanced_search_attribute1","HTMLInput",array(
			"name" => "search[attribute1]",
			"value" => @$search["attribute1"],
			"style" => "width:100%",
		));

		$this->createAdd("advanced_search_attribute2","HTMLInput",array(
			"name" => "search[attribute2]",
			"value" => @$search["attribute2"],
			"style" => "width:100%",
		));
		
		$this->createAdd("advanced_search_attribute3","HTMLInput",array(
			"name" => "search[attribute3]",
			"value" => @$search["attribute3"],
			"style" => "width:100%",
		));
		
		$this->createAdd("advanced_search_mail_send_true","HTMLCheckbox",array(
			"type" => "radio",
			"name" => "search[is_disabled]",
			"value" => 0,
			"selected" => (@$search["is_disabled"] === "0") ? true : false ,
			"elementId" => "checkbox_send_email_true"
		));
		$this->createAdd("advanced_search_mail_send_false","HTMLCheckbox",array(
			"type" => "radio",
			"name" => "search[is_disabled]",
			"value" => 1,
			"selected" => (@$search["is_disabled"] === "1") ? true : false ,
			"elementId" => "checkbox_send_email_false"
		));

		$this->createAdd("advanced_search_mail_error_count","HTMLInput",array(
			"name" => "search[mail_error_count]",
			"value" => @$search["mail_error_count"],
		));

		$this->createAdd("advanced_search_register_date_start_year","HTMLInput",array(
			"name" => "search[register_date][start][year]",
			"value" => @$search["register_date"]["start"]["year"],
			"size" => "5"
		));
		$this->createAdd("advanced_search_register_date_start_month","HTMLInput",array(
			"name" => "search[register_date][start][month]",
			"value" => @$search["register_date"]["start"]["month"],
			"size" => "3"
		));
		$this->createAdd("advanced_search_register_date_start_day","HTMLInput",array(
			"name" => "search[register_date][start][day]",
			"value" => @$search["register_date"]["start"]["day"],
			"size" => "3"
		));
		$this->createAdd("advanced_search_register_date_end_year","HTMLInput",array(
			"name" => "search[register_date][end][year]",
			"value" => @$search["register_date"]["end"]["year"],
			"size" => "5"
		));
		$this->createAdd("advanced_search_register_date_end_month","HTMLInput",array(
			"name" => "search[register_date][end][month]",
			"value" => @$search["register_date"]["end"]["month"],
			"size" => "3"
		));
		$this->createAdd("advanced_search_register_date_end_day","HTMLInput",array(
			"name" => "search[register_date][end][day]",
			"value" => @$search["register_date"]["end"]["day"],
			"size" => "3"
		));

		$this->createAdd("advanced_search_update_date_start_year","HTMLInput",array(
			"name" => "search[update_date][start][year]",
			"value" => @$search["update_date"]["start"]["year"],
			"size" => "5"
		));
		$this->createAdd("advanced_search_update_date_start_month","HTMLInput",array(
			"name" => "search[update_date][start][month]",
			"value" => @$search["update_date"]["start"]["month"],
			"size" => "3"
		));
		$this->createAdd("advanced_search_update_date_start_day","HTMLInput",array(
			"name" => "search[update_date][start][day]",
			"value" => @$search["update_date"]["start"]["day"],
			"size" => "3"
		));
		$this->createAdd("advanced_search_update_date_end_year","HTMLInput",array(
			"name" => "search[update_date][end][year]",
			"value" => @$search["update_date"]["end"]["year"],
			"size" => "5"
		));
		$this->createAdd("advanced_search_update_date_end_month","HTMLInput",array(
			"name" => "search[update_date][end][month]",
			"value" => @$search["update_date"]["end"]["month"],
			"size" => "3"
		));
		$this->createAdd("advanced_search_update_date_end_day","HTMLInput",array(
			"name" => "search[update_date][end][day]",
			"value" => @$search["update_date"]["end"]["day"],
			"size" => "3"
		));
	}
	
	function buildPager(PagerLogic $pager){

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
		$this->createAdd("pager_list","_common.SimplePagerComponent",$pager->getPagerParam());
		
		//ページへジャンプ
		$this->createAdd("pager_jump","HTMLForm",array(
			"method" => "get",
			"action" => $pager->getPageURL()."/"
		));
		$this->createAdd("pager_select","HTMLSelect",array(
			"name" => "page",
			"options" => $pager->getSelectArray(),
			"selected" => $pager->getPage(),
			"onchange" => "location.href=this.parentNode.action+this.options[this.selectedIndex].value"
		));
		
	}
}
?>