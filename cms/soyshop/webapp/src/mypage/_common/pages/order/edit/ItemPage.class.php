<?php

SOYShopPlugin::load("soyshop.item.option");
class ItemPage extends MainMyPagePageBase{

	private $orderId;
	private $userId;

	function doPost(){
		if(soy2_check_token() && soy2_check_referer()){

			if(isset($_POST["reset"])){
				$mypage = $this->getMyPage();
				$mypage->clearAttribute("order_edit_item_orders");
				$mypage->clearAttribute("order_edit_is_edit");
				$mypage->save();

				$this->jump("order/edit/item/" . $this->orderId . "?reset");
			}

			//リセット以外のボタンを押した時、各商品の個数は必ず変更しておきたい
			$newItemOrders = self::getItemOrders();

			if(isset($_POST["add_mode"])){
				$mypage = $this->getMyPage();
				$mypage->setAttribute("order_edit_on_mypage", true);
				$mypage->save();
				header("Location:" . soyshop_get_site_url());
				exit;
			}

			if(isset($_POST["update"])){
				//登録
				$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
				$itemOrderDao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
				$itemOrderDao->begin();
				try{
					$oldItemOrders = $itemOrderDao->getByOrderId($this->orderId);
				}catch(Exception $e){
					$oldItemOrders = array();
				}

				$change = array();
				$itemChange = array();

				foreach($oldItemOrders as $itemOrderId => $oldItemOrder){
					$doUpdate = false;	//新しい商品一覧に商品情報があり、数量の変更を行っていれば変更
					$doRemove = true;	//新しい商品一覧に商品情報がなければ削除
					foreach($newItemOrders as $newItemOrder){
						if(!is_null($newItemOrder->getId()) && $itemOrderId == $newItemOrder->getId()) {
							$doRemove = false;	//削除は行わない
							if($oldItemOrder->getItemCount() != $newItemOrder->getItemCount()){
								$doUpdate = true;	//数量が異なる事を確認してはじめて更新を行う
								$itemChange[] = $this->getHistoryText($oldItemOrder->getItemName() . "の個数", $oldItemOrder->getItemCount(), $newItemOrder->getItemCount());
								$itemDao->orderItem($newItemOrder->getItemId(), ($newItemOrder->getItemCount() - $oldItemOrder->getItemCount()));
								$oldItemOrder = $newItemOrder;
							}
						}
					}

					//商品オプション
					$optChanges = SOYShopPlugin::invoke("soyshop.item.option", array(
						"mode" => "history",
						"newItemOrder" => $newItemOrders[$itemOrderId],
						"oldItemOrder" => $oldItemOrders[$itemOrderId]
					))->getChanges();

					if(count($optChanges)){
						foreach($optChanges as $optChange){
							if(is_null($optChange) || !count($optChange)) continue;
							$itemChange = array_merge($itemChange, $optChange);
							$oldItemOrder->setAttributes($newItemOrders[$itemOrderId]->getAttributeList());
							$doUpdate = true;
						}
					}

					//情報の更新
					if($doUpdate){
						try{
							//在庫数の変更はdoUpdateのチェックの際に行っている
							$itemOrderDao->update($oldItemOrder);
						}catch(Exception $e){
							var_dump($e);
						}
					}

					//情報の削除
					if($doRemove){
						try{
							$itemDao->orderItem($oldItemOrder->getItemId(), -$oldItemOrder->getItemCount());
							$itemOrderDao->delete($oldItemOrder);
							$itemChange[] = $oldItemOrder->getItemName() . "（" . $this->getItemCodeByItemId($oldItemOrder->getItemId())." " . $oldItemOrder->getItemPrice() . "円×" . $oldItemOrder->getItemCount() . "点）を削除しました。";
						}catch(Exception $e){
							var_dump($e);
						}
					}
				}

				foreach($newItemOrders as $newItemOrder){
					//IDがnullであれば新規登録
					if(is_null($newItemOrder->getId())){
						try{
							$itemDao->orderItem($newItemOrder->getItemId(), $newItemOrder->getItemCount());
							$itemOrderDao->insert($newItemOrder);
							$itemChange[] = $newItemOrder->getItemName() . "（" . $this->getItemCodeByItemId($newItemOrder->getItemId()) . " " . $newItemOrder->getItemPrice() . "円×" . $newItemOrder->getItemCount() . "点）を追加しました。";
						}catch(Exception $e){
							var_dump($e);
						}
					}
				}

				if(count($itemChange)){
					//金額の変更
					$order = $this->getOrderByIdAndUserId($this->orderId, $this->getUser()->getId());
					$oldPrice = $order->getPrice();
					$newPrice = self::getTotalPrice($newItemOrders, $order->getModuleList());
					$order->setPrice($newPrice);

					$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
					try{
						$orderDao->update($order);
						if($oldPrice != $newPrice) $change[] = $this->getHistoryText("注文合計", $oldPrice . "円", $newPrice . "円");
					}catch(Exception $e){
						var_dump($e);
					}

					//注文履歴の編集
					$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");
					$author = "顧客:" . $this->getUser()->getName();
					$orderLogic->addHistory($this->orderId, implode("\n", $itemChange), null, $author);
					if(count($change)) $orderLogic->addHistory($this->orderId, implode("\n", $change), null, $author);

					//変更履歴のメールを送信する
					$mailLogic = SOY2Logic::createInstance("module.plugins.order_edit_on_mypage.logic.NoticeSendMailLogic", array("order" => $order, "user" => $this->getUser()));
					$mailLogic->send(implode("\n", $change) ."\n" . implode("\n", $itemChange));
				}

				$itemOrderDao->commit();

				$mypage = $this->getMyPage();
				$mypage->clearAttribute("order_edit_item_orders");
				$mypage->clearAttribute("order_edit_is_edit");
				$mypage->save();

				//キャッシュの削除
				SOY2Logic::createInstance("module.plugins.order_edit_on_mypage.logic.HistoryIdCacheLogic")->removeCache();

				$this->jump("order/detail/" . $this->orderId . "?updated");
			}

			//数量の変更の場合のみは何もせずにジャンプで良い
			if(isset($_POST["change"]) || isset($_POST["option"])){
				$mypage = $this->getMyPage();

				//商品オプション等
				if(isset($_POST["option"])){
					SOYShopPlugin::load("soyshop.item.option");
					SOYShopPlugin::invoke("soyshop.item.option", array(
						"mode" => "change",
						"itemOrders" => $newItemOrders,
					));
					$mypage->setAttribute("order_edit_item_orders", $newItemOrders);
				}

				$mypage->setAttribute("order_edit_is_edit", true);
				$mypage->save();
				$this->jump("order/edit/item/" . $this->orderId);
			}

			$this->jump("order/edit/item/" . $this->orderId . "?failed");
		}
	}

	function __construct($args){
		$this->checkIsLoggedIn(); //ログインチェック

        //orderIdがない場合はorderトップへ戻す
        if(!isset($args[0]) || !SOYShopPluginUtil::checkIsActive("order_edit_on_mypage")) $this->jump("order");
		$this->orderId = (int)$args[0];
		$this->userId = (int)$this->getUser()->getId();

		//すでに発送してしまった場合は表示しない
		if(!$this->checkUnDeliveried($this->orderId, $this->userId)) $this->jump("order");

		//商品一覧から商品を削除
		if(isset($_GET["index"]) && is_numeric($_GET["index"])) self::remove();

        parent::__construct();

		self::buildOrderTable();

		DisplayPlugin::toggle("update_notice", !is_null($this->getMyPage()->getAttribute("order_edit_is_edit")));
		DisplayPlugin::toggle("update_button", !is_null($this->getMyPage()->getAttribute("order_edit_is_edit")));
		$this->addForm("form");

		$this->addLink("back_link", array(
			"link" => soyshop_get_mypage_url() . "/order/detail/" . $this->orderId . "?edit=reset"
		));
	}

	private function buildOrderTable(){
		$order = $this->getOrderByIdAndUserId($this->orderId, $this->userId);
        if(!$order->isOrderDisplay()) $this->jump("order");

		$this->addLabel("order_number", array(
			"text" => $order->getTrackingNumber()
		));

		$itemOrders = self::getItemOrders();
		$this->createAdd("item_list", "_common.order.ItemOrderListComponent", array(
            "list" => $itemOrders,
			"itemCount" => count($itemOrders)
        ));

		//商品追加ボタン
		$this->addLink("add_item_link", array(
			"link" => soyshop_get_site_url() . "?func=order_edit_on_mypage"
		));

		DisplayPlugin::toggle("subtotal", self::checkTaxModule($order->getModuleList()));
        $this->addLabel("subtotal_item_count", array(
            "text" => self::getSubtotalItemCount($itemOrders)
        ));

        $this->addLabel("subtotal_price", array(
            "text" => self::getSubtotalPrice($itemOrders)
        ));

        $this->createAdd("module_list", "_common.order.ModuleListComponent", array(
            "list" => $order->getModuleList()
        ));

        //送料も含めたトータルの金額
        $this->addLabel("order_total_price", array(
            "text" => number_format(self::getTotalPrice($itemOrders, $order->getModuleList()))
        ));
	}

	private function getItemOrders(){
		//注文の内訳をセッションに入れておく
		$mypage = $this->getMyPage();
		$itemOrders = $mypage->getAttribute("order_edit_item_orders"); //注文の内訳

		//セッションに入っている商品が注文IDと一致するか？
		$checkItemSession = false;
		if(!is_null($itemOrders) && count($itemOrders)){
			foreach($itemOrders as $itemOrder){
				$checkItemSession = ($itemOrder->getOrderId() == $this->orderId);
				break;
			}
			//もしなければ、編集モードを解除する
			if(!$checkItemSession) $mypage->clearAttribute("order_edit_on_mypage");
		}
		if(!$checkItemSession){
			$itemOrders = $this->getItemOrdersByOrderId($this->orderId);
			$mypage->setAttribute("order_edit_item_orders", $itemOrders);
			$mypage->clearAttribute("order_edit_on_mypage");	//商品追加モードを念のために解除しておく
			$mypage->clearAttribute("order_edit_is_edit");		//編集モードを念のために解除しておく
			$mypage->save();
		}

		//個数の変更
		if(isset($_POST["item_count"]) && count($_POST["item_count"])){
			$isChange = false;
			foreach($_POST["item_count"] as $idx => $cnt){
				if(isset($itemOrders[$idx]) && $itemOrders[$idx]->getItemCount() != $cnt){
					$isChange = true;
					if($cnt > 0){
						$itemOrders[$idx]->setItemCount($cnt);
						$itemOrders[$idx]->setTotalPrice($itemOrders[$idx]->getItemPrice() * $itemOrders[$idx]->getItemCount());
					//指定する個数が0の場合はリストから削除
					}else{
						unset($itemOrders[$idx]);
					}
				}
			}
			if($isChange){
				$mypage->setAttribute("order_edit_item_orders", $itemOrders);
				$mypage->save();
			}
		}

		return $itemOrders;
	}

	private function remove(){
		if(soy2_check_token()){
			$idx = (int)$_GET["index"];
			$itemOrders = self::getItemOrders();
			if(count($itemOrders) > 1){	//削除ボタンは商品が２つ以上でないと表示されないため
				if(isset($itemOrders[$idx])) unset($itemOrders[$idx]);
				$mypage = $this->getMyPage();
				$mypage->setAttribute("order_edit_item_orders", $itemOrders);
				$mypage->clearAttribute("order_edit_on_mypage");	//商品追加モードを念のために解除しておく
				$mypage->setAttribute("order_edit_is_edit", true);	//編集モードを念のために起動しておく
				$mypage->save();
			}

			$this->jump("order/edit/item/" . $this->orderId);
		}
	}

	//taxモジュールが登録されているか？をチェックする
    private function checkTaxModule($modules){

        if(count($modules) === 0) return false;

        $res = false;
        foreach($modules as $module){
            if($module->getType() == SOYShop_ItemModule::TYPE_TAX){
                $res = true;
                break;
            }
        }

        return $res;
    }

    //小計時のアイテムの総個数
    private function getSubtotalItemCount($itemOrders){
        $total = 0;

        if(count($itemOrders) === 0) return $total;

        foreach($itemOrders as $itemOrder){
            $total += $itemOrder->getItemCount();
        }

        return $total;
    }

    private function getSubtotalPrice($itemOrders){
        $total = 0;

        if(count($itemOrders) === 0) return $total;

        foreach($itemOrders as $itemOrder){
            $total += $itemOrder->getTotalPrice();
        }

        return $total;
    }

	private function getTotalPrice($itemOrders, $modules){
		$total = 0;
		if(count($itemOrders)){
			foreach($itemOrders as $itemOrder){
				$total += $itemOrder->getItemCount() * $itemOrder->getItemPrice();
			}
		}

		/** @ToDo 商品合計によってモジュールの値を変更できる仕組みを作りたい **/
		if(count($modules)){
			foreach($modules as $module){
				$total += $module->getPrice();
			}
		}

		return $total;
	}
}
