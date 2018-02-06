<?php
SOY2::imports("module.plugins.download_assistant.domain.*");
SOYShopPlugin::load("soyshop.item.option");
class DetailPage extends MainMyPagePageBase{

	const CHANGE_STOCK_MODE_CANCEL = "cancel";	//キャンセルにした場合
	const CHANGE_STOCK_MODE_RETURN = "return";	//キャンセルから他のステータスに戻した場合

    private $orderId;
    private $userId;
    private $itemDao;

    function doPost(){
		if(isset($_POST["cancel"])) self::cancel();
	}

    function __construct($args){
		$this->checkIsLoggedIn(); //ログインチェック

        //orderIdがない場合はorderトップへ戻す
        if(!isset($args[0])) $this->jump("order");

		//編集中の情報を削除
		if(isset($_GET["edit"]) && $_GET["edit"] == "reset"){
			$mypage = $this->getMyPage();
			$mypage->clearAttribute("order_edit_item_orders");
			$mypage->clearAttribute("order_edit_on_mypage");	//編集モードを念のために解除しておく
			$mypage->clearAttribute("order_edit_is_edit");	//編集モードを念のために解除しておく
			$mypage->save();
		}

		$this->orderId = (int)$args[0];
        $this->userId = (int)$this->getUser()->getId();

		//キャンセル
		if(isset($_GET["cancel"])) self::cancel();

        parent::__construct();

		DisplayPlugin::toggle("updated", isset($_GET["updated"]));

        self::buildOrder();
    }

    private function buildOrder(){
		$order = $this->getOrderByIdAndUserId($this->orderId, $this->userId);
        if(!$order->isOrderDisplay()) $this->jump("order");

        //注文番号
        $this->addLabel("order_number", array(
            "text" => $order->getTrackingNumber()
        ));

        //注文日時
        $this->addLabel("order_date", array(
            "text" => date("Y年m月d日 H時i分s秒", $order->getOrderDate())
        ));

        //合計金額
        $this->addLabel("order_price", array(
            "text" => number_format($order->getPrice())
        ));

        $attributes = $order->getAttributeList();

        //備考、支払方法、配送方法、配達時間
        $this->createAdd("attribute_list", "_common.order.AttributeListComponent", array(
            "list" => $order->getAttributeList()
        ));

        $this->addLabel("payment_status", array(
            "text" => $order->getPaymentStatusText()
        ));

        //オーダーカスタムフィールド
        $this->createAdd("customfield_list", "_common.order.OrderCustomfieldListComponent", array(
            "list" => self::getCustomfield()
        ));

        //備考
        $this->addLabel("order_memo", array(
            "html" => (isset($attributes["memo"]["value"])) ? nl2br(htmlspecialchars($attributes["memo"]["value"], ENT_QUOTES, "UTF-8")) : ""
        ));

        //送付先と請求先のsoy:idを生成する
        self::getAddressList($order, "send");
        self::getAddressList($order, "claimed");

        //注文の内訳
        $itemOrders = $this->getItemOrdersByOrderId($order->getId());
        $this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
        $this->createAdd("item_list", "_common.order.ItemOrderListComponent", array(
            "list" => $itemOrders,
            "itemDao" => $this->itemDao
        ));

        //ダウンロード商品関連
        $files = (SOYShopPluginUtil::checkIsActive("download_assistant")) ? self::getDownloadFiles($itemOrders) : array();

        //ボーナス
        $bonuses = (SOYShopPluginUtil::checkIsActive("bonus_download")) ? self::getBonusFiles($order) : array();

        $this->addModel("is_download_files", array(
            "visible" => (count($files) > 0 || count($bonuses))
        ));

        $this->createAdd("download_list", "_common.order.DownloadListComponent", array(
            "list" => $files,
            "order" => $order
        ));

        $this->createAdd("bonus_list", "_common.order.BonusListComponent", array(
            "list" => $bonuses
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
            "text" => number_format($order->getPrice())
        ));

        $this->addLink("top_link", array(
            "link" => soyshop_get_mypage_top_url()
        ));

        SOYShopPlugin::load("soyshop.order.createadd");
        $delegate = SOYShopPlugin::invoke("soyshop.order.createadd", array(
            "order" => $order,
            "orders" => $itemOrders,
            "page" => $this
        ));

		//注文詳細を変更するボタンを表示する
		$isEditPlugin = (SOYShopPluginUtil::checkIsActive("order_edit_on_mypage") && $this->checkUnDeliveried($this->orderId, $this->userId));
		DisplayPlugin::toggle("order_edit", $isEditPlugin);
		$this->addLink("edit_link", array(
			"link" => soyshop_get_mypage_url() . "/order/edit/item/" . $this->orderId
		));

		//配送時間帯等の編集
		DisplayPlugin::toggle("order_module", ($isEditPlugin && $this->checkUsedDeliveryModule($this->orderId, $this->userId)));
		$this->addLink("module_link", array(
			"link" => soyshop_get_mypage_url() . "/order/edit/module/" . $this->orderId
		));

		//お届け先情報の編集
		DisplayPlugin::toggle("order_send_address", $isEditPlugin);
		$this->addLink("send_address_link", array(
			"link" => soyshop_get_mypage_url() . "/order/edit/address/send/" . $this->orderId
		));

		//請求先情報の編集
		DisplayPlugin::toggle("order_claimed_address", $isEditPlugin);
		$this->addLink("claimed_address_link", array(
			"link" => soyshop_get_mypage_url() . "/order/edit/address/claimed/" . $this->orderId
		));

		//注文をキャンセル
		DisplayPlugin::toggle("order_cancel", $isEditPlugin);
		$this->addActionLink("cancel_link", array(
			"link" => soyshop_get_mypage_url() . "/order/detail/" . $this->orderId . "?cancel",
			"onclick" => "return confirm('注文をキャンセルしますか？');"
		));
    }

    private function getAddressList(SOYShop_Order $order, $mode = "send"){

        //送付先の場合
        if($mode == "send"){
            $prefix = "user";
            $address = $order->getAddressArray();
        //請求先の場合
        }else{
            $prefix = "claimed";
            $address = $order->getClaimedAddressArray();
        }

		foreach(array("name", "reading", "office", "zipCode", "area", "address1", "address2", "tel", "telephoneNumber") as $t){
			$this->addModel($prefix . "_" . strtolower($t) . "_show", array(
				"visible" => (isset($address[$t]) && strlen($address[$t]))
			));

			switch($t){
				case "area":
					$this->addLabel($prefix . "_" . $t, array(
			            "text" => (isset($address[$t])) ? SOYShop_Area::getAreaText($address[$t]) : ""
			        ));
					break;
				case "tel":
					$this->addLabel($prefix . "_" . $t, array(
						"text" => (isset($address["telephoneNumber"])) ? $address["telephoneNumber"] : ""
					));
					break;
				default:
					$this->addLabel($prefix . "_" . strtolower($t), array(
						"text" => (isset($address[$t])) ? $address[$t] : ""
					));
			}
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

    private function getCustomfield(){
        SOYShopPlugin::load("soyshop.order.customfield");
        $delegate = SOYShopPlugin::invoke("soyshop.order.customfield", array(
            "mode" => "admin",
            "orderId" => $this->orderId
        ));

        $array = array();
        foreach($delegate->getDisplay() as $obj){
            if(is_array($obj)){
                foreach($obj as $value){
                    $array[] = $value;
                }
            }
        }

        return $array;
    }

    private function getDownloadFiles($itemOrders){
        $files = array();
        $items = array();
		$commonLogic = SOY2Logic::createInstance("module.plugins.download_assistant.logic.DownloadCommonLogic");
        foreach($itemOrders as $itemOrder){
            try{
                $item = $this->itemDao->getById($itemOrder->getItemId());
            }catch(Exception $e){
                continue;
            }

            if($commonLogic->checkItemType($item)) $items[] = $item;
        }

        if(count($items) > 0){
            $downloadDao = SOY2DAOFactory::create("SOYShop_DownloadDAO");

            foreach($items as $item){
                try{
                    $array = $downloadDao->getFilesByOrderIdAndItemIdAndUserId($this->orderId, $item->getId(), $this->userId);
                }catch(Exception $e){
                    continue;
                }
                if(count($array) > 0){
                    foreach($array as $file){
                        $files[] = $file;
                    }
                }
            }
        }

        return $files;
    }

    /**
     * ボーナスダウンロードリストを作成
     * @param object SOYShop_Order
     * @return array
     */
    private function getBonusFiles(SOYShop_Order $order){
        $paymentFlag = (int)$order->getPaymentStatus();

        $attributes = $order->getAttributeList();

        $nameList = array();
        $timelimitList = array();
        $urlList = array();

        foreach($attributes as $key => $array){
            if(strpos($key, "bonus_download.filename.") === 0){
                $nameList[] = $array["value"];
            }
            if(strpos($key, "bonus_download.timelimit.") === 0){
                $timelimitList[] = $array["value"];
            }
            if(strpos($key, "bonus_download.url_list") === 0){
                $urlList = explode("\n", $array["value"]);
            }
        }

        $list = array();
        if(count($nameList) > 0 && count($urlList) > 0){
            for($i = 0; $i < count($urlList); $i++){
                $array = array();
                $array["filename"] = $nameList[$i];
                $array["timelimit"] = $timelimitList[$i];
                $array["url"] = $urlList[$i];
                $array["payment"] = $paymentFlag;
                $list[] = $array;
            }
        }

        return $list;
    }

	private function cancel(){
		if(!soy2_check_token()) $this->jump("order");
		$order = $this->getOrderByIdAndUserId($this->orderId, $this->userId);
		$order->setStatus(SOYShop_Order::ORDER_STATUS_CANCELED);

		$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		$orderDao->begin();

		try{
			$orderDao->update($order);
			self::changeItemStock($order->getId(), self::CHANGE_STOCK_MODE_CANCEL);
		}catch(Exception $e){
			var_dump($e);
		}

		//メールを送信する
		$change = "注文番号『" . $order->getTrackingNumber() . "』の注文をキャンセルしました。";
		$this->insertHistory($this->orderId, $change);

		//変更履歴のメールを送信する
		$mailLogic = SOY2Logic::createInstance("module.plugins.order_edit_on_mypage.logic.NoticeSendMailLogic", array("order" => $order, "user" => $this->getUser()));
		$mailLogic->send($change);

		$orderDao->commit();
		$this->jump("order?canceled");
	}

	private function changeItemStock($orderId, $mode){
		$itemOrderDao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		try{
			$itemOrders = $itemOrderDao->getByOrderId($orderId);
		}catch(Exception $e){
			return false;
		}

		if(!count($itemOrders)) return false;

		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		foreach($itemOrders as $itemOrder){
			try{
				$item = $itemDao->getById($itemOrder->getItemId());
			}catch(Exception $e){
				var_dump($e);
				continue;
			}

			//在庫数を戻す
			if($mode == self::CHANGE_STOCK_MODE_CANCEL){
				$item->setStock((int)$item->getStock() + (int)$itemOrder->getItemCount());
			//在庫数を減らす
			}else if($mode == self::CHANGE_STOCK_MODE_RETURN){
				$item->setStock((int)$item->getStock() - (int)$itemOrder->getItemCount());
			}else{
				//何もしない
			}

			try{
				$itemDao->update($item);
			}catch(Exception $e){
				var_dump($e);
			}
		}
	}
}
