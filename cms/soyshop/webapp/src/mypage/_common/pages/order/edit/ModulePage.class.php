<?php

class ModulePage extends MainMyPagePageBase{

	private $orderId;
	private $userId;
	private $moduleId;	//配送モジュールの選択

	function doPost(){
		if(soy2_check_token() && soy2_check_referer()){

			$order = $this->getOrderByIdAndUserId($this->orderId, $this->userId);
			$changes = SOYShopPlugin::invoke("soyshop.delivery", array(
				"mode" => "update",
				"order" => $order
			))->getChanges();

			$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");
			$author = "顧客:" . $this->getUser()->getName();

			$histories = array();
			foreach($changes as $moduleId => $values){
				if(!count($values)) continue;
				foreach($values as $v){
					$historyText = $this->getHistoryText($v["label"], $v["old"], $v["new"]);
					$orderLogic->addHistory($this->orderId, $historyText, null, $author);
					$histories[] = $historyText;
				}
			}

			//エラーチェック
			$error = SOYShopPlugin::invoke("soyshop.order.customfield", array(
				"mode" => "error",
				"mypage" => $this->getMyPage(),
				"orderId" => $order->getId()
			))->hasError();

			//エラーがある場合
			if($error){
				$this->jump("order/edit/module/" . $this->orderId . "?error");
			}

			$list = SOYShopPlugin::invoke("soyshop.order.customfield", array(
				"mode" => "config",
				"orderId" => $order->getId()
			))->getList();

			if(count($list)){
				//扱いやすい配列に変える
				$array = array();
				foreach($list as $obj){
					if(is_array($obj)){
						foreach($obj as $key => $value){
							$array[$key] = $value;
						}
					}
				}
				$newCustomfields = $_POST["Customfield"];

				$dao = SOY2DAOFactory::create("order.SOYShop_OrderAttributeDAO");
				$dateDao = SOY2DAOFactory::create("order.SOYShop_OrderDateAttributeDAO");
			   	foreach($array as $key => $obj){
			   		$newValue1 = null;
					$newValue2 = null;

					switch($obj["type"]){
						case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_INPUT:
						case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_TEXTAREA:
						case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_SELECT:
							$newValue1 = (isset($newCustomfields[$key])) ? $newCustomfields[$key] : null;
							break;
						case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_CHECKBOX:
							$newValue1 = (isset($newCustomfields[$key])) ? implode(",", $newCustomfields[$key]) : null;
							break;
						case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RADIO:
							$newValue1 = (isset($newCustomfields[$key])) ? $newCustomfields[$key] : null;

							//その他を選んだとき
							if(isset($obj["value1"]) && $newCustomfields[$key] == trim($obj["value1"])){
								$newValue2 = (isset($newCustomfields[$key . "_other_text"])) ? $newCustomfields[$key . "_other_text"] : null;
							}
							break;
						case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_DATE:
							$newValue1 = (isset($newCustomfields[$key]["date"])) ? self::convertDate($newCustomfields[$key]["date"]) : null;
							$newValue2 = null;
							break;
						case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_PERIOD:
							$newValue1 = (isset($newCustomfields[$key]["start"])) ? self::convertDate($newCustomfields[$key]["start"]) : null;
							$newValue2 = (isset($newCustomfields[$key]["end"])) ? self::convertDate($newCustomfields[$key]["end"]) : null;
							break;
					}

					switch($obj["type"]){
						case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_INPUT:
						case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_TEXTAREA:
						case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_CHECKBOX:
						case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RADIO:
						case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_SELECT:
							if($newValue1 != $obj["value1"]){
								$historyText = $this->getHistoryText($obj["label"], $obj["value1"], $newValue1);
								$orderLogic->addHistory($this->orderId, $historyText, null, $author);
								$histories[] = $historyText;
							}
							if(isset($newValue2) && $newValue2 != $obj["value2"]){
								$historyText = $this->getHistoryText($obj["label"], $obj["value2"], $newValue2);
								$orderLogic->addHistory($this->orderId, $historyText, null, $author);
								$histories[] = $historyText;
							}
							//ここで配列を入れてしまう。
							try{
								$orderAttr = $dao->get($order->getId(), $key);
							}catch(Exception $e){
								$orderAttr = new SOYShop_OrderAttribute();
								$orderAttr->setOrderId($order->getId());
								$orderAttr->setFieldId($key);
							}

							$orderAttr->setValue1($newValue1);
							$orderAttr->setValue2($newValue2);


							try{
								$dao->insert($orderAttr);
							}catch(Exception $e){
								try{
									$dao->update($orderAttr);
								}catch(Exception $e){
									//
								}
							}
							break;
						case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_DATE:
						case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_PERIOD:
							//value2に値がない場合 dateとか
							if(is_null($newValue2)){
								if($newValue1 !== (int)$obj["value1"]){
									$historyText = $this->getHistoryText($obj["label"], $this->convertDateText($obj["value1"]), $this->convertDateText($newValue1));
									$orderLogic->addHistory($this->orderId, $historyText, null, $author);
									$histories[] = $historyText;
								}

							//value2に値がある場合 periodとか
							}else{
								if($newValue1 !== (int)$obj["value1"] || $newValue2 !== (int)$obj["value2"]){
									$historyText = $this->getHistoryText($obj["label"], $this->convertDateText($obj["value1"]) . " ～ " . $this->convertDateText($obj["value1"]), $this->convertDateText($newValue1) . " ～ " . $this->convertDateText($newValue2));
									$orderLogic->addHistory($this->orderId, $historyText, null, $author);
									$histories[] = $historyText;
								}
							}

							try{
								$orderDateAttr = $dateDao->get($order->getId(), $key);
							}catch(Exception $e){
								$orderDateAttr = new SOYShop_OrderDateAttribute();
								$orderDateAttr->setOrderId($order->getId());
								$orderDateAttr->setFieldId($key);
							}

							$orderDateAttr->setValue1($newValue1);
							$orderDateAttr->setValue2($newValue2);

							try{
								$dateDao->insert($orderDateAttr);
							}catch(Exception $e){
								try{
									$dateDao->update($orderDateAttr);
								}catch(Exception $e){
									//
								}
							}

							break;
						default:
							break;
					}
				}
			}

			if(count($histories)){
				$mailLogic = SOY2Logic::createInstance("module.plugins.order_edit_on_mypage.logic.NoticeSendMailLogic", array("order" => $order, "user" => $this->getUser()));
				$mailLogic->send(implode("\n", $histories));
			}

			//キャッシュの削除
			SOY2Logic::createInstance("module.plugins.order_edit_on_mypage.logic.HistoryIdCacheLogic")->removeCache();

			$this->jump("order/edit/module/" . $this->orderId . "?updated");
		}
		$this->jump("order/edit/module/" . $this->orderId . "?failed");
	}

	function __construct($args){
		$this->checkIsLoggedIn(); //ログインチェック

        //orderIdがない場合はorderトップへ戻す
        if(!isset($args[0]) || !SOYShopPluginUtil::checkIsActive("order_edit_on_mypage")) $this->jump("order");
		$this->orderId = (int)$args[0];
		$this->userId = (int)$this->getUser()->getId();

		//すでに発送してしまった場合は表示しない
		if(!$this->checkUnDeliveried($this->orderId, $this->userId) && !$this->checkUsedDeliveryModule($this->orderId, $this->userId)) $this->jump("order");

		//この注文が指定した顧客のものであるか？
		$order = $this->getOrderByIdAndUserId($this->orderId, $this->userId);
		if(!$order->isOrderDisplay()) $this->jump("order");

		//カートに表示している配送方法のフォームを出力する
		$module = $this->getModuleByOrderIdAndUserId($this->orderId, $this->userId);
		SOYShopPlugin::load("soyshop.delivery", $module);
		SOYShopPlugin::load("soyshop.order.customfield");

		parent::__construct();

		DisplayPlugin::toggle("updated", isset($_GET["updated"]));
		DisplayPlugin::toggle("failed", isset($_GET["failed"]));
		DisplayPlugin::toggle("error", isset($_GET["error"]));

		$errMsg = (isset($_GET["error"])) ? SOYShopPlugin::invoke("soyshop.order.customfield", array("mode" => "error_message", "mypage" => $this->getMyPage()))->getErrorMessage() : null;

		$this->addLabel("error_message", array(
			"text" => (strlen($errMsg)) ? $errMsg : "失敗しました"
		));

		$this->addLabel("order_number", array(
			"text" => $order->getTrackingNumber()
		));

		$this->addForm("form");

		$forms = array();

		$list = SOYShopPlugin::invoke("soyshop.delivery", array(
			"mode" => "mypage",
			"order" => $order
		))->getList();

		if(count($list)){
			foreach($list as $moduleId => $values){
				if(!is_array($values) || !count($values)) continue;
				foreach($values as $v){
					if(!strlen($v["form"])) continue;
					$forms[] = $v;
				}
			}
		}

		$labels = SOYShopPlugin::invoke("soyshop.order.customfield", array(
			"mode" => "edit",
			"orderId" => $this->orderId
		))->getLabel();

		if(count($labels)){
			foreach($labels as $fieldId => $values){
				if($fieldId == "slip_number" || !is_array($values) || !count($values)) continue;	//伝票番号プラグインはここでは編集させない
				foreach($values as $v){
					$forms[] = $v;
				}
			}
		}

		$this->createAdd("form_list", "_common.order.CustomFormListComponent", array(
			"list" => $forms
		));

		$this->addLink("back_link", array(
			"link" => soyshop_get_mypage_url() . "/order/detail/" . $this->orderId . "?edit=reset"
		));
	}
}
