<?php
SOY2HTMLFactory::importWebPage("User.IndexPage");

/**
 * @class User.SearchPage
 * @date 2009-12-09T14:20:53+09:00
 * @author SOY2HTMLFactory
 */
class SearchPage extends WebPage{

	function doPost(){
		if(array_key_exists("search", $_POST)){
			$value = $_POST["search"];
			SOY2ActionSession::getUserSession()->setAttribute("User.Search:search", $value);
		}
		if(array_key_exists("reset", $_POST)){
			SOY2ActionSession::getUserSession()->setAttribute("User.Search:search", array());
		}
		SOY2PageController::jump("User.Search");
	}

	function SearchPage(){
		WebPage::WebPage();

		$this->createAdd("advanced_search_form","HTMLForm");
		$this->createAdd("reset_form","HTMLForm");

		/*引数など取得*/
		//表示件数
		$limit = 15;
		$page = (isset($args[0])) ? (int)$args[0] : 1;
		if(array_key_exists("page", $_GET)) $page = $_GET["page"];
		if(array_key_exists("sort", $_GET) OR array_key_exists("search", $_GET)) $page = 1;
		$page = max(1, $page);

		$offset = ($page - 1) * $limit;

		//表示順
		$sort = $this->getParameter("sort");
		//検索
		$search = $this->getParameter("search");
		if(!$search)$search = array();

		SOY2::import("domain.config.SOYShop_Area");
		$this->buildAdvancedSearchForm($search);

		/*データ*/
		$searchLogic = SOY2Logic::createInstance("logic.user.SearchUserLogic");
		$searchLogic->setLimit($limit);
		$searchLogic->setOffset($offset);
		$searchLogic->setOrder($sort);
		$searchLogic->setSearchCondition($search);

		//データ取得
		$total = $searchLogic->getTotalCount();
		$users = $searchLogic->getUsers();

		/*表示*/

		//表示順リンク
		$this->buildSortLink($sort);

		//ユーザ一覧
		$this->createAdd("user_list","_common.User.UserListComponent", array(
			"list" => $users
		));
		$this->createAdd("has_result","HTMLModel", array(
			"visible" => (count($users) > 0)
		));

		$this->createAdd("no_result_error","HTMLModel", array(
			"visible" => (count($users) == 0 && !empty($search))
 		));


		//ページャー
		$start = $offset + 1;
		$end = $offset + count($users);
		if($end > 0 && $start == 0)$start = 1;

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPageURL("User");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);

		$this->buildPager($pager);
	}

	function buildAdvancedSearchForm($search){


		$this->createAdd("advanced_search_id","HTMLInput", array(
			"name" => "search[id]",
			"value" => @$search["id"],
		));

		$this->createAdd("advanced_search_mail_address","HTMLInput", array(
			"name" => "search[mail_address]",
			"value" => @$search["mail_address"],
		));
		$this->createAdd("advanced_search_mail_send_true","HTMLCheckBox", array(
			"name" => "search[]",
			"value" => 1,
			"elementId" => "checkbox_send_email_true"
		));

		$this->createAdd("advanced_search_name","HTMLInput", array(
			"name" => "search[name]",
			"value" => @$search["name"],
		));

		$this->createAdd("advanced_search_furigana","HTMLInput", array(
			"name" => "search[reading]",
			"value" => @$search["reading"],
		));

		$this->createAdd("advanced_search_gender_male","HTMLCheckbox", array(
			"name" => "search[gender][male]",
			"selected" => ( array_key_exists("gender", $search) AND array_key_exists("male", $search["gender"]) ) ? true : false ,
			"value" => 0,
			"elementId" => "checkbox_gender_male"
		));

		$this->createAdd("advanced_search_gender_female","HTMLCheckbox", array(
			"name" => "search[gender][female]",
			"selected" => ( array_key_exists("gender", $search) AND array_key_exists("female", $search["gender"]) ) ? true : false ,
			"value" => 1,
			"elementId" => "checkbox_gender_female"
		));
/**
		$this->createAdd("advanced_search_gender_other","HTMLCheckbox", array(
			"name" => "search[gender][other]",
			"selected" => ( array_key_exists("gender", $search) AND array_key_exists("other", $search["gender"]) ) ? true : false ,
			"value" => 1,
			"elementId" => "checkbox_gender_other"
		));
**/
		$this->createAdd("advanced_search_birth_date_start_year","HTMLInput", array(
			"name" => "search[birthday][start][year]",
			"value" => @$search["birthday"]["start"]["year"],
			"size" => "5",
		));
		$this->createAdd("advanced_search_birth_date_start_month","HTMLInput", array(
			"name" => "search[birthday][start][month]",
			"value" => @$search["birthday"]["start"]["month"],
			"size" => "3",
		));
		$this->createAdd("advanced_search_birth_date_start_day","HTMLInput", array(
			"name" => "search[birthday][start][day]",
			"value" => @$search["birthday"]["start"]["day"],
			"size" => "3",
		));
		$this->createAdd("advanced_search_birth_date_end_year","HTMLInput", array(
			"name" => "search[birthday][end][year]",
			"value" => @$search["birthday"]["end"]["year"],
			"size" => "5",
		));
		$this->createAdd("advanced_search_birth_date_end_month","HTMLInput", array(
			"name" => "search[birthday][end][month]",
			"value" => @$search["birthday"]["end"]["month"],
			"size" => "3",
		));
		$this->createAdd("advanced_search_birth_date_end_day","HTMLInput", array(
			"name" => "search[birthday][end][day]",
			"value" => @$search["birthday"]["end"]["day"],
			"size" => "3",
		));

		$this->createAdd("advanced_search_post_number","HTMLInput", array(
			"name" => "search[zip_code]",
			"value" => @$search["zip_code"],

		));

		$this->createAdd("advanced_search_area","HTMLSelect", array(
			"name" => "search[area]",
			"selected" => @$search["area"],
			"options" => SOYShop_Area::getAreas(),
		));

		$this->createAdd("advanced_search_address1","HTMLInput", array(
			"name" => "search[address1]",
			"value" => @$search["address1"],

		));

		$this->createAdd("advanced_search_address2","HTMLInput", array(
			"name" => "search[address2]",
			"value" => @$search["address2"],

		));

		$this->createAdd("advanced_search_tel_number","HTMLInput", array(
			"name" => "search[telephone_number]",
			"value" => @$search["telephone_number"],

		));

		$this->createAdd("advanced_search_fax_number","HTMLInput", array(
			"name" => "search[fax_number]",
			"value" => @$search["fax_number"],

		));

		$this->createAdd("advanced_search_ketai_number","HTMLInput", array(
			"name" => "search[cellphone_number]",
			"value" => @$search["cellphone_number"],

		));

		$this->createAdd("advanced_search_office","HTMLInput", array(
			"name" => "search[job_name]",
			"value" => @$search["job_name"],

		));
		$this->createAdd("advanced_search_office_post_number","HTMLInput", array(
			"name" => "search[job_zip_code]",
			"value" => @$search["job_zip_code"],

		));
		$this->createAdd("advanced_search_jobArea","HTMLSelect", array(
			"name" => "search[job_area]",
			"selected" => @$search["job_area"],
			"options" => SOYShop_Area::getAreas(),
		));
		$this->createAdd("advanced_search_jobAddress1","HTMLInput", array(
			"name" => "search[job_address1]",
			"value" => @$search["job_address1"],
		));
		$this->createAdd("advanced_search_jobAddress2","HTMLInput", array(
			"name" => "search[job_address2]",
			"value" => @$search["job_address2"],
		));
		$this->createAdd("advanced_search_office_tel_number","HTMLInput", array(
			"name" => "search[job_telephone_number]",
			"value" => @$search["job_telephone_number"],
		));
		$this->createAdd("advanced_search_office_fax_number","HTMLInput", array(
			"name" => "search[job_fax_number]",
			"value" => @$search["job_fax_number"],
		));


		$this->createAdd("advanced_search_memo","HTMLTextArea", array(
			"name" => "search[memo]",
			"value" => @$search["memo"],
		));

		$this->createAdd("advanced_search_attribute1","HTMLInput", array(
			"name" => "search[attribute1]",
			"value" => @$search["attribute1"],
		));

		$this->createAdd("advanced_search_attribute2","HTMLInput", array(
			"name" => "search[attribute2]",
			"value" => @$search["attribute2"],
		));

		$this->createAdd("advanced_search_attribute3","HTMLInput", array(
			"name" => "search[attribute3]",
			"value" => @$search["attribute3"],
		));

		$this->createAdd("advanced_search_shop_send_true","HTMLCheckbox", array(
			"type" => "radio",
			"name" => "search[is_disabled]",
			"value" => 0,
			"selected" => (@$search["is_disabled"] === "0") ? true : false ,
			"elementId" => "checkbox_send_eshop_true"
		));
		$this->createAdd("advanced_search_shop_send_false","HTMLCheckbox", array(
			"type" => "radio",
			"name" => "search[is_disabled]",
			"value" => 1,
			"selected" => (@$search["is_disabled"] === "1") ? true : false ,
			"elementId" => "checkbox_send_eshop_false"
		));

		$this->createAdd("advanced_search_shop_error_count","HTMLInput", array(
			"name" => "search[shop_error_count]",
			"value" => @$search["shop_error_count"],
		));

		$this->createAdd("advanced_search_register_date_start_year","HTMLInput", array(
			"name" => "search[register_date][start][year]",
			"value" => @$search["register_date"]["start"]["year"],
			"size" => "5"
		));
		$this->createAdd("advanced_search_register_date_start_month","HTMLInput", array(
			"name" => "search[register_date][start][month]",
			"value" => @$search["register_date"]["start"]["month"],
			"size" => "3"
		));
		$this->createAdd("advanced_search_register_date_start_day","HTMLInput", array(
			"name" => "search[register_date][start][day]",
			"value" => @$search["register_date"]["start"]["day"],
			"size" => "3"
		));
		$this->createAdd("advanced_search_register_date_end_year","HTMLInput", array(
			"name" => "search[register_date][end][year]",
			"value" => @$search["register_date"]["end"]["year"],
			"size" => "5"
		));
		$this->createAdd("advanced_search_register_date_end_month","HTMLInput", array(
			"name" => "search[register_date][end][month]",
			"value" => @$search["register_date"]["end"]["month"],
			"size" => "3"
		));
		$this->createAdd("advanced_search_register_date_end_day","HTMLInput", array(
			"name" => "search[register_date][end][day]",
			"value" => @$search["register_date"]["end"]["day"],
			"size" => "3"
		));

		$this->createAdd("advanced_search_update_date_start_year","HTMLInput", array(
			"name" => "search[update_date][start][year]",
			"value" => @$search["update_date"]["start"]["year"],
			"size" => "5"
		));
		$this->createAdd("advanced_search_update_date_start_month","HTMLInput", array(
			"name" => "search[update_date][start][month]",
			"value" => @$search["update_date"]["start"]["month"],
			"size" => "3"
		));
		$this->createAdd("advanced_search_update_date_start_day","HTMLInput", array(
			"name" => "search[update_date][start][day]",
			"value" => @$search["update_date"]["start"]["day"],
			"size" => "3"
		));
		$this->createAdd("advanced_search_update_date_end_year","HTMLInput", array(
			"name" => "search[update_date][end][year]",
			"value" => @$search["update_date"]["end"]["year"],
			"size" => "5"
		));
		$this->createAdd("advanced_search_update_date_end_month","HTMLInput", array(
			"name" => "search[update_date][end][month]",
			"value" => @$search["update_date"]["end"]["month"],
			"size" => "3"
		));
		$this->createAdd("advanced_search_update_date_end_day","HTMLInput", array(
			"name" => "search[update_date][end][day]",
			"value" => @$search["update_date"]["end"]["day"],
			"size" => "3"
		));
	}

	function buildSortLink($sort){

		$link = SOY2PageController::createLink("User");

		$this->createAdd("sort_id","HTMLLink", array(
			"text" => "▲",
			"link" => $link . "?sort=".SearchUserLogic::SORT_ID,
			"title" => "昇順",
			"class" => ($sort === SearchUserLogic::SORT_ID) ? "pager_disable" : ""
		));
		$this->createAdd("sort_id_desc","HTMLLink", array(
			"text" => "▼",
			"link" => $link . "?sort=".SearchUserLogic::SORT_ID_DESC,
			"title" => "降順",
			"class" => ($sort === SearchUserLogic::SORT_ID_DESC) ? "pager_disable" : ""
		));
		$this->createAdd("sort_name","HTMLLink", array(
			"text" => "▲",
			"link" => $link . "?sort=".SearchUserLogic::SORT_READING,
			"title" => "昇順",
			"class" => ($sort === SearchUserLogic::SORT_READING) ? "pager_disable" : ""
		));
		$this->createAdd("sort_name_desc","HTMLLink", array(
			"text" => "▼",
			"link" => $link . "?sort=".SearchUserLogic::SORT_READING_DESC,
			"title" => "降順",
			"class" => ($sort === SearchUserLogic::SORT_READING_DESC) ? "pager_disable" : ""
		));
		$this->createAdd("sort_mail_address","HTMLLink", array(
			"text" => "▲",
			"link" => $link . "?sort=".SearchUserLogic::SORT_MAIL_ADDRESS,
			"title" => "昇順",
			"class" => ($sort === SearchUserLogic::SORT_MAIL_ADDRESS) ? "pager_disable" : ""
		));
		$this->createAdd("sort_mail_address_desc","HTMLLink", array(
			"text" => "▼",
			"link" => $link . "?sort=".SearchUserLogic::SORT_MAIL_ADDRESS_DESC,
			"title" => "降順",
			"class" => ($sort === SearchUserLogic::SORT_MAIL_ADDRESS_DESC) ? "pager_disable" : ""
		));
	}

	function buildPager(PagerLogic $pager){

		//件数情報表示
		$this->createAdd("count_start","HTMLLabel", array(
			"text" => $pager->getStart()
		));
		$this->createAdd("count_end","HTMLLabel", array(
			"text" => $pager->getEnd()
		));
		$this->createAdd("count_max","HTMLLabel", array(
			"text" => $pager->getTotal()
		));

		//ページへのリンク
		$this->createAdd("next_pager","HTMLLink",$pager->getNextParam());
		$this->createAdd("prev_pager","HTMLLink",$pager->getPrevParam());
		$this->createAdd("pager_list","SimplePager",$pager->getPagerParam());

		//ページへジャンプ
		$this->createAdd("pager_jump","HTMLForm", array(
			"method" => "get",
			"action" => $pager->getPageURL()
		));
		$this->createAdd("pager_select","HTMLSelect", array(
			"name" => "page",
			"options" => $pager->getSelectArray(),
			"selected" => $pager->getPage(),
			"onchange" => "location.href=this.parentNode.action+this.options[this.selectedIndex].value"
		));

	}

	function getParameter($key){
		if(array_key_exists($key, $_GET)){
			$value = $_GET[$key];
			SOY2ActionSession::getUserSession()->setAttribute("User.Search:" . $key, $value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("User.Search:" . $key);
		}
		return $value;
	}
}


?>