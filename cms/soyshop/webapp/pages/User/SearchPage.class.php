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
		if(array_key_exists("u_search", $_POST)){
			$value = $_POST["u_search"];
			SOY2ActionSession::getUserSession()->setAttribute("User.Search:u_search", $value);
		}
		if(array_key_exists("reset", $_POST)){
			SOY2ActionSession::getUserSession()->setAttribute("User.Search:search", array());
			SOY2ActionSession::getUserSession()->setAttribute("User.Search:u_search", array());
		}
		SOY2PageController::jump("User.Search");
	}

	function __construct($args){
		SOY2::import("domain.config.SOYShop_ShopConfig");

		parent::__construct();

		//一覧でログインIDを表示するか？
		$cnf = SOYShop_ShopConfig::load();
		$adminCnf = $cnf->getCustomerAdminConfig();
		define("SHOW_ACCOUNT_ID_ITEM", ($adminCnf["accountId"] && $cnf->getAllowLoginIdLogin()));

		$this->addForm("advanced_search_form");
		$this->addForm("reset_form");

		/*引数など取得*/
		//表示件数
		$limit = 15;
		$page = (isset($args[0])) ? (int)$args[0] : 1;
		if(array_key_exists("page", $_GET)) $page = $_GET["page"];
		if(array_key_exists("sort", $_GET) OR array_key_exists("search", $_GET)) $page = 1;
		$page = max(1, $page);

		$offset = ($page - 1) * $limit;

		//表示順
		$sort = self::getParameter("sort");
		//検索
		$search = self::getParameter("search");
		if(!$search)$search = array();

		//カスタムサーチフィールド
		$customSearch = self::getParameter("u_search");
		if(!$customSearch) $customSearch = array();

		SOY2::import("domain.config.SOYShop_Area");
		self::buildAdvancedSearchForm($search);
		self::buildCustomSearchForm($search, $customSearch);

		/*データ*/
		$searchLogic = SOY2Logic::createInstance("logic.user.SearchUserLogic");
		$searchLogic->setLimit($limit);
		$searchLogic->setOffset($offset);
		$searchLogic->setOrder($sort);
		$searchLogic->setSearchCondition($search, $customSearch);

		//データ取得
		$total = $searchLogic->getTotalCount();
		$users = $searchLogic->getUsers();

		/*表示*/

		//表示順リンク
		self::buildSortLink($searchLogic, $sort);

		$this->addModel("show_account_id", array(
			"visible" => SHOW_ACCOUNT_ID_ITEM
		));

		$this->addModel("colspan", array(
			"attr:colspan" => (SHOW_ACCOUNT_ID_ITEM) ? 8 : 7
		));

		//ログインIDの名称変更
		$this->addLabel("account_id_item_name", array(
			"text" => (SHOW_ACCOUNT_ID_ITEM) ? $cnf->getAccountIdItemName() : ""
		));

		//ユーザ一覧
		$this->createAdd("user_list","_common.User.UserListComponent", array(
			"list" => $users
		));
		DisplayPlugin::toggle("has_result", (count($users) > 0));
		DisplayPlugin::toggle("no_result_error", (count($users) == 0 && !empty($search)));

		//ページャー
		$start = $offset + 1;
		$end = $offset + count($users);
		if($end > 0 && $start == 0)$start = 1;

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPageURL("User.Search");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);

		self::buildPager($pager);

		/* 出力用 */
		$moduleList = self::getExportModuleList();
		$this->createAdd("module_list", "_common.User.ExportModuleList", array(
			"list" => $moduleList
		));

		$this->addForm("export_form", array(
			"action" => SOY2PageController::createLink("User.Plugin.Export")
		));

		$this->addInput("query", array(
			"name" => "search",
			"value" => (isset($search)) ? http_build_query($search) : ""
		));
	}

	private function getExportModuleList(){
		SOYShopPlugin::load("soyshop.user.export");

		$delegate = SOYShopPlugin::invoke("soyshop.user.export", array(
			"mode" => "list"
		));

		$list = $delegate->getList();
		DisplayPlugin::toggle("export_module_menu", (count($list) > 0));

		return $list;
	}

	private function buildAdvancedSearchForm($search){
		SOY2::import("domain.user.SOYShop_User");
		$config = SOYShop_ShopConfig::load();

		$this->addInput("advanced_search_id", array(
			"name" => "search[id]",
			"value" => (isset($search["id"])) ? $search["id"] : ""
		));

		$this->addInput("advanced_search_mail_address", array(
			"name" => "search[mail_address]",
			"value" => (isset($search["mail_address"])) ? $search["mail_address"] : "",
		));

		$this->addInput("advanced_search_account_id", array(
			"name" => "search[account_id]",
			"value" => (isset($search["account_id"])) ? $search["account_id"] : "",
		));

		DisplayPlugin::toggle("userCode", $config->getUseUserCode());
		$this->addInput("advanced_search_user_code", array(
			"name" => "search[user_code]",
			"value" => (isset($search["user_code"])) ? $search["user_code"] : ""
		));

		$this->addCheckBox("advanced_search_user_type_register", array(
			"name" => "search[user_type][]",
			"value" => SOYShop_User::USERTYPE_REGISTER,
			"selected" => (!count($search) || (isset($search["user_type"]) && is_array($search["user_type"]) && in_array(SOYShop_User::USERTYPE_REGISTER, $search["user_type"]))),
			"label" => "本登録"
		));

		$this->addCheckBox("advanced_search_user_type_tmp", array(
			"name" => "search[user_type][]",
			"value" => SOYShop_User::USERTYPE_TMP,
			"selected" => (!count($search) || (isset($search["user_type"]) && is_array($search["user_type"]) && in_array(SOYShop_User::USERTYPE_TMP, $search["user_type"]))),
			"label" => "仮登録"
		));

		SOY2::import("util.SOYShopPluginUtil");
		DisplayPlugin::toggle("user_custom_search_field", SOYShopPluginUtil::checkIsActive("user_custom_search_field"));

		$this->addCheckBox("advanced_search_is_publish", array(
			"name" => "search[is_publish][]",
			"value" => SOYShop_User::USER_IS_PUBLISH,
			"selected" => (!count($search) || (isset($search["is_publish"]) && is_array($search["is_publish"]) && in_array(SOYShop_User::USER_IS_PUBLISH, $search["is_publish"]))),
			"label" => "公開"
		));

		$this->addCheckBox("advanced_search_no_publish", array(
			"name" => "search[is_publish][]",
			"value" => SOYShop_User::USER_NO_PUBLISH,
			"selected" => (isset($search["is_publish"]) && is_array($search["is_publish"]) && in_array(SOYShop_User::USER_NO_PUBLISH, $search["is_publish"])),
			"label" => "非公開"
		));

		DisplayPlugin::toggle("active_soymail_connector", SOYShopPluginUtil::checkIsActive("soymail_connector"));

		$this->addCheckBox("advanced_search_mail_send_true", array(
			"name" => "search[]",
			"value" => 1,
			"elementId" => "checkbox_send_email_true"
		));

		$this->addInput("advanced_search_name", array(
			"name" => "search[name]",
			"value" => (isset($search["name"])) ? $search["name"] : "",
		));

		$this->addInput("advanced_search_furigana", array(
			"name" => "search[reading]",
			"value" => (isset($search["reading"])) ? $search["reading"] : "",
		));

		$this->addCheckBox("advanced_search_gender_male", array(
			"name" => "search[gender][male]",
			"selected" => ( array_key_exists("gender", $search) && array_key_exists("male", $search["gender"]) ),
			"value" => 0,
			"elementId" => "checkbox_gender_male"
		));

		$this->addCheckBox("advanced_search_gender_female", array(
			"name" => "search[gender][female]",
			"selected" => ( array_key_exists("gender", $search) && array_key_exists("female", $search["gender"]) ),
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
		$this->addInput("advanced_search_birth_date_year", array(
			"name" => "search[birthday][year]",
			"value" => (isset($search["birthday"]["year"])) ? $search["birthday"]["year"] : "",
			"size" => "5",
		));
		$this->addInput("advanced_search_birth_date_month", array(
			"name" => "search[birthday][month]",
			"value" => (isset($search["birthday"]["month"])) ? $search["birthday"]["month"] : "",
			"size" => "3",
		));
		$this->addInput("advanced_search_birth_date_day", array(
			"name" => "search[birthday][day]",
			"value" => (isset($search["birthday"]["day"])) ? $search["birthday"]["day"] : "",
			"size" => "3",
		));
/**
		$this->addInput("advanced_search_birth_date_start_year", array(
			"name" => "search[birthday][start][year]",
			"value" => (isset($search["birthday"]["start"]["year"])) ? $search["birthday"]["start"]["year"] : "",
			"size" => "5",
		));
		$this->addInput("advanced_search_birth_date_start_month", array(
			"name" => "search[birthday][start][month]",
			"value" => (isset($search["birthday"]["start"]["month"])) ? $search["birthday"]["start"]["month"] : "",
			"size" => "3",
		));
		$this->addInput("advanced_search_birth_date_start_day", array(
			"name" => "search[birthday][start][day]",
			"value" => (isset($search["birthday"]["start"]["day"])) ? $search["birthday"]["start"]["day"] : "",
			"size" => "3",
		));
		$this->addInput("advanced_search_birth_date_end_year", array(
			"name" => "search[birthday][end][year]",
			"value" => (isset($search["birthday"]["end"]["year"])) ? $search["birthday"]["end"]["year"] : "",
			"size" => "5",
		));
		$this->addInput("advanced_search_birth_date_end_month", array(
			"name" => "search[birthday][end][month]",
			"value" => (isset($search["birthday"]["end"]["month"])) ? $search["birthday"]["end"]["month"] : "",
			"size" => "3",
		));
		$this->addInput("advanced_search_birth_date_end_day", array(
			"name" => "search[birthday][end][day]",
			"value" => (isset($search["birthday"]["end"]["day"])) ? $search["birthday"]["end"]["day"] : "",
			"size" => "3",
		));
**/
		$this->addInput("advanced_search_post_number", array(
			"name" => "search[zip_code]",
			"value" => (isset($search["zip_code"])) ? $search["zip_code"] : "",
		));

		$this->addSelect("advanced_search_area", array(
			"name" => "search[area]",
			"selected" => (isset($search["area"])) ? $search["area"] : null,
			"options" => SOYShop_Area::getAreas(),
		));

		$this->addInput("advanced_search_address1", array(
			"name" => "search[address1]",
			"value" => (isset($search["address1"])) ? $search["address1"] : "",
		));

		$this->addInput("advanced_search_address2", array(
			"name" => "search[address2]",
			"value" => (isset($search["address2"])) ? $search["address2"] : "",
		));

		$this->addInput("advanced_search_tel_number", array(
			"name" => "search[telephone_number]",
			"value" => (isset($search["telephone_number"])) ? $search["telephone_number"] : "",
		));

		$this->addInput("advanced_search_fax_number", array(
			"name" => "search[fax_number]",
			"value" => (isset($search["fax_number"])) ? $search["fax_number"] : "",
		));

		$this->addInput("advanced_search_ketai_number", array(
			"name" => "search[cellphone_number]",
			"value" => (isset($search["cellphone_number"])) ? $search["cellphone_number"] : "",
		));

		/** 勤務先 **/
		SOY2::import("domain.config.SOYShop_ShopConfig");
		DisplayPlugin::toggle("office_items", SOYShop_ShopConfig::load()->getDisplayUserOfficeItems());

		$this->addInput("advanced_search_office", array(
			"name" => "search[job_name]",
			"value" => (isset($search["job_name"])) ? $search["job_name"] : "",

		));
		$this->addInput("advanced_search_office_post_number", array(
			"name" => "search[job_zip_code]",
			"value" => (isset($search["job_zip_code"])) ? $search["job_zip_code"] : "",

		));
		$this->addSelect("advanced_search_jobArea", array(
			"name" => "search[job_area]",
			"selected" => (isset($search["job_area"])) ? $search["job_area"] : null,
			"options" => SOYShop_Area::getAreas(),
		));
		$this->addInput("advanced_search_jobAddress1", array(
			"name" => "search[job_address1]",
			"value" => (isset($search["job_address1"])) ? $search["job_address1"] : "",
		));
		$this->addInput("advanced_search_jobAddress2", array(
			"name" => "search[job_address2]",
			"value" => (isset($search["job_address2"])) ? $search["job_address2"] : "",
		));
		$this->addInput("advanced_search_office_tel_number", array(
			"name" => "search[job_telephone_number]",
			"value" => (isset($search["job_telephone_number"])) ? $search["job_telephone_number"] : "",
		));
		$this->addInput("advanced_search_office_fax_number", array(
			"name" => "search[job_fax_number]",
			"value" => (isset($search["job_fax_number"])) ? $search["job_fax_number"] : "",
		));


		$this->addTextArea("advanced_search_memo", array(
			"name" => "search[memo]",
			"value" => (isset($search["memo"])) ? $search["memo"] : "",
		));

		$this->addInput("advanced_search_attribute1", array(
			"name" => "search[attribute1]",
			"value" => (isset($search["attribute1"])) ? $search["attribute1"] : "",
		));

		$this->addInput("advanced_search_attribute2", array(
			"name" => "search[attribute2]",
			"value" => (isset($search["attribute2"])) ? $search["attribute2"] : "",
		));

		$this->addInput("advanced_search_attribute3", array(
			"name" => "search[attribute3]",
			"value" => (isset($search["attribute3"])) ? $search["attribute3"] : "",
		));

		$this->addCheckBox("advanced_search_is_send", array(
			"name" => "search[not_send][]",
			"value" => 0,
			"selected" => (isset($search["not_send"]) && in_array("0", $search["not_send"])),
			"label" => "配信する"
		));

		$this->addCheckBox("advanced_search_not_send", array(
			"name" => "search[not_send][]",
			"value" => 1,
			"selected" => (isset($search["not_send"]) && in_array("1", $search["not_send"])),
			"label" => "配信しない"
		));

		$this->addCheckBox("advanced_search_shop_send_true", array(
			"type" => "radio",
			"name" => "search[is_disabled]",
			"value" => 0,
			"selected" => (isset($search["is_disabled"]) && $search["is_disabled"] === "0"),
			"elementId" => "checkbox_send_eshop_true"
		));
		$this->addCheckBox("advanced_search_shop_send_false", array(
			"type" => "radio",
			"name" => "search[is_disabled]",
			"value" => 1,
			"selected" => (isset($search["is_disabled"]) && $search["is_disabled"] === "1"),
			"elementId" => "checkbox_send_eshop_false"
		));

		$this->addInput("advanced_search_shop_error_count", array(
			"name" => "search[shop_error_count]",
			"value" => (isset($search["shop_error_count"])) ? $search["shop_error_count"] : "",
		));

		$this->addInput("advanced_search_register_date_start_year", array(
			"name" => "search[register_date][start][year]",
			"value" => (isset($search["register_date"]["start"]["year"])) ? $search["register_date"]["start"]["year"] : "",
			"size" => "4"
		));
		$this->addInput("advanced_search_register_date_start_month", array(
			"name" => "search[register_date][start][month]",
			"value" => (isset($search["register_date"]["start"]["month"])) ? $search["register_date"]["start"]["month"] : "",
			"size" => "3"
		));
		$this->addInput("advanced_search_register_date_start_day", array(
			"name" => "search[register_date][start][day]",
			"value" => (isset($search["register_date"]["start"]["day"])) ? $search["register_date"]["start"]["day"] : "",
			"size" => "3"
		));
		$this->addInput("advanced_search_register_date_end_year", array(
			"name" => "search[register_date][end][year]",
			"value" => (isset($search["register_date"]["end"]["year"])) ? $search["register_date"]["end"]["year"] : "",
			"size" => "4"
		));
		$this->addInput("advanced_search_register_date_end_month", array(
			"name" => "search[register_date][end][month]",
			"value" => (isset($search["register_date"]["end"]["month"])) ? $search["register_date"]["end"]["month"] : "",
			"size" => "3"
		));
		$this->addInput("advanced_search_register_date_end_day", array(
			"name" => "search[register_date][end][day]",
			"value" => (isset($search["register_date"]["end"]["day"])) ? $search["register_date"]["end"]["day"] : "",
			"size" => "3"
		));

		$this->addInput("advanced_search_update_date_start_year", array(
			"name" => "search[update_date][start][year]",
			"value" => (isset($search["update_date"]["start"]["year"])) ? $search["update_date"]["start"]["year"] : "",
			"size" => "4"
		));
		$this->addInput("advanced_search_update_date_start_month", array(
			"name" => "search[update_date][start][month]",
			"value" => (isset($search["update_date"]["start"]["month"])) ? $search["update_date"]["start"]["month"] : "",
			"size" => "3"
		));
		$this->addInput("advanced_search_update_date_start_day", array(
			"name" => "search[update_date][start][day]",
			"value" => (isset($search["update_date"]["start"]["day"])) ? $search["update_date"]["start"]["day"] : "",
			"size" => "3"
		));
		$this->addInput("advanced_search_update_date_end_year", array(
			"name" => "search[update_date][end][year]",
			"value" => (isset($search["update_date"]["end"]["year"])) ? $search["update_date"]["end"]["year"] : "",
			"size" => "4"
		));
		$this->addInput("advanced_search_update_date_end_month", array(
			"name" => "search[update_date][end][month]",
			"value" => (isset($search["update_date"]["end"]["month"])) ? $search["update_date"]["end"]["month"] : "",
			"size" => "3"
		));
		$this->addInput("advanced_search_update_date_end_day", array(
			"name" => "search[update_date][end][day]",
			"value" => (isset($search["update_date"]["end"]["day"])) ? $search["update_date"]["end"]["day"] : "",
			"size" => "3"
		));

		// ショップサイトで注文可の場合(cartIDがnoneでない場合)は下記の項目を出力
		DisplayPlugin::toggle("thing_about_order", !SOYSHOP_ADMIN_MODE);

		$this->addInput("advanced_search_total_price_min", array(
			"name" => "search[order_price][min]",
			"value" => (isset($search["order_price"]["min"])) ? $search["order_price"]["min"] : "",
		));
		$this->addInput("advanced_search_total_price_max", array(
			"name" => "search[order_price][max]",
			"value" => (isset($search["order_price"]["max"])) ? $search["order_price"]["max"] : "",
		));
		$this->addInput("advanced_search_purchase_count_min", array(
			"name" => "search[purchase_count][min]",
			"value" => (isset($search["purchase_count"]["min"])) ? $search["purchase_count"]["min"] : "",
		));
		$this->addInput("advanced_search_purchase_count_max", array(
			"name" => "search[purchase_count][max]",
			"value" => (isset($search["purchase_count"]["max"])) ? $search["purchase_count"]["max"] : "",
		));

		//項目の非表示用タグ
		foreach($config->getCustomerAdminConfig() as $key => $bool){
			if($key == "accountId" && $bool){
				//ログインIDのみ、マイページでログインIDを使用する時だけtrueにする
				$bool = (SOYShop_ShopConfig::load()->getAllowLoginIdLogin() != 0);
			}
			DisplayPlugin::toggle($key, $bool);
		}
	}

	private function buildCustomSearchForm($search, $custom){
		$searchItemList = self::getCustomSearchItems($search);
		$isUcsf = SOYShopPluginUtil::checkIsActive("user_custom_search_field");
		DisplayPlugin::toggle("installed_user_custom_search_field", ($searchItemList || $isUcsf));

		$html = array();

		if($isUcsf){
			SOY2::import("module.plugins.user_custom_search_field.util.UserCustomSearchFieldUtil");
			$cnfs = UserCustomSearchFieldUtil::getConfig();
			if(count($cnfs)){
				SOY2::import("module.plugins.user_custom_search_field.component.FieldFormComponent");
				foreach($cnfs as $key => $field){
					$html[] = "<tr>";
					$html[] = "<th>" . htmlspecialchars($field["label"], ENT_QUOTES, "UTF-8") . "</th>";
					$html[] = "<td>" . FieldFormComponent::buildSearchConditionForm($key, $field, $custom) . "</td>";
					$html[] = "</tr>";
				}
			}
		}

		$this->addLabel("user_custom_search_field_form_area", array(
			"html" => implode("\n", $html)
		));

		// Itemの方のコンポーネントを使い回す
		$this->createAdd("custom_search_item_list", "_common.Item.CustomSearchItemListComponent", array(
			"list" => $searchItemList
		));
	}

	private function getCustomSearchItems($cnds){
		//検索フォームの拡張ポイント
		SOYShopPlugin::load("soyshop.user.search");
		$items = SOYShopPlugin::invoke("soyshop.user.search", array(
			"mode" => "form",
			"params" => (isset($cnds["customs"])) ? $cnds["customs"] : array()
		))->getSearchItems();

		//再配列
		$list = array();
		foreach($items as $item){
			if(is_null($item)) continue;
			$key = key($item);
			if($key == "label"){
				$list[] = $item;
			//複数の項目が入っている
			}else{
				foreach($item as $v){
					$list[] = $v;
				}
			}
		}

		return $list;
	}

	private function buildSortLink(SearchUserLogic $logic, $sort){

		$link = SOY2PageController::createLink("User.Search");

		foreach($logic->getSorts() as $key => $value){

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

	private function buildPager(PagerLogic $pager){

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
		$this->addForm("pager_jump", array(
			"method" => "get",
			"action" => $pager->getPageURL() . "/"
		));
		$this->addSelect("pager_select", array(
			"name" => "page",
			"options" => $pager->getSelectArray(),
			"selected" => $pager->getPage(),
			"onchange" => "location.href=this.parentNode.action+this.options[this.selectedIndex].value"
		));

	}

	private function getParameter($key){
		if(array_key_exists($key, $_GET)){
			$value = $_GET[$key];
			SOY2ActionSession::getUserSession()->setAttribute("User.Search:" . $key, $value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("User.Search:" . $key);
		}
		return $value;
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("顧客検索", array("User" => "顧客管理"));
	}

	function getScripts(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			//$root . "tools/soy2_date_picker.pack.js"
			$root . "tools/datepicker-ja.js",
			$root . "tools/datepicker.js"
		);
	}

	// function getCSS(){
	// 	//$root = SOY2PageController::createRelativeLink("./js/");
	// 	return array(
	// 		"./css/admin/user_detail.css",
	// 		//$root . "tools/soy2_date_picker.css"
	//
	// 	);
	// }
}
