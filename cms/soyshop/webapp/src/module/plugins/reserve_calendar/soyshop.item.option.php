<?php

class ReserveCalendarOption extends SOYShopItemOptionBase{

	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
	}

    function clear(int $index, CartLogic $cart){
        $itemOrders = $cart->getItems();
        if(!isset($itemOrders[$index])) return;

        $cart->clearAttribute(ReserveCalendarUtil::getCartAttributeId("schedule_id", $index, $itemOrders[$index]->getItemId()));
    }

    function compare(array $postedOptions, CartLogic $cart){
        $checkOptionId = null;

        $itemOrders = $cart->getItems();

        //比較用の配列を作成する
        $attrs = array();
        foreach($itemOrders as $index => $itemOrder){
            $attrs[$index]["schedule_id"] = $cart->getAttribute(ReserveCalendarUtil::getCartAttributeId("schedule_id", $index, $itemOrder->getItemId()));

            $currentOptions = array_diff($attrs[$index], array(null));

            if($postedOptions == $currentOptions){
                $checkOptionId = $index;
                break;
            }
        }

        return $checkOptionId;
    }

    function doPost(int $index, CartLogic $cart){
        if(isset($_REQUEST["item_option"]["schedule_id"])){
            $itemOrders = $cart->getItems();
			if(isset($itemOrders[$index])){
                $obj = ReserveCalendarUtil::getCartAttributeId("schedule_id", $index, $itemOrders[$index]->getItemId());
				$cart->setAttribute($obj, $_REQUEST["item_option"]["schedule_id"]);
            }else{	//存在していない時

			}
        }
    }

    /**
     * 商品情報の下に表示される情報
     * @param htmlObj, integer index
     * @return string html
     */
    function onOutput($htmlObj, int $index){
        $cart = CartLogic::getCart();

        $items = $cart->getItems();
        if(!isset($items[$index])){
            return "";
        }

        $itemId = $items[$index]->getItemId();

        $schId = $cart->getAttribute(ReserveCalendarUtil::getCartAttributeId("schedule_id", $index, $itemId));
        $list = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelList($itemId);

        $sch = self::getScheduleById($schId);
        if(isset($list[$sch->getLabelId()])){
            return $sch->getYear() . "-" . $sch->getMonth() . "-"  . $sch->getDay() . " " . $list[$sch->getLabelId()];
        }

        return "";
    }

    function order(int $index){
        $cart = CartLogic::getCart();

        $items = $cart->getItems();
        if(!isset($items[$index])){
            return null;
        }

        $itemId = $items[$index]->getItemId();
		$itemCount = $items[$index]->getItemCount();
		if(!strlen($itemCount) || (int)$itemCount === 0) $itemCount = 1;

        $obj = ReserveCalendarUtil::getCartAttributeId("schedule_id", $index, $itemId);
        $schId = $cart->getAttribute($obj);

        //予約として登録
        $resDao = self::resDao();
        $res = new SOYShopReserveCalendar_Reserve();
        $res->setScheduleId($schId);
        $res->setOrderId($cart->getAttribute("order_id"));
		$res->setSeat($itemCount);
        $res->setTemp(SOYShopReserveCalendar_Reserve::NO_TEMP);
        $res->setReserveDate(time());

		//仮登録
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
		$config = ReserveCalendarUtil::getConfig();
		if(isset($config["tmp"]) && $config["tmp"] == ReserveCalendarUtil::IS_TMP){
			$res->setTemp(ReserveCalendarUtil::IS_TMP);
			$res->setTempDate(time());
			$res->setToken(substr(md5($res->getScheduleId() . $res->getOrderId() . $res->getTempDate()), 0, 25));
		}

        try{
            $resId = $resDao->insert($res);
        }catch(Exception $e){
            //
        }

        //注文属性にも入れておく
        return soy2_serialize(array("reserve_id" => $resId));
    }

    function display(SOYShop_ItemOrder $itemOrder){
        $attributes = $itemOrder->getAttributeList();
        if(isset($attributes["reserve_id"]) && is_numeric($attributes["reserve_id"])){
            $sch = self::schDao()->getScheduleByReserveId((int)$attributes["reserve_id"]);

            $list = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelList($itemOrder->getItemId());

            if(isset($list[$sch->getLabelId()])){
                return $sch->getYear() . "-" . $sch->getMonth() . "-"  . $sch->getDay() . " " . $list[$sch->getLabelId()];
            }
        }
    }

    private function getScheduleById(int $schId){
        try{
            return self::schDao()->getById($schId);
        }catch(Exception $e){
            return new SOYShopReserveCalendar_Schedule();
        }
    }

    private function schDao(){
        static $dao;
        if(is_null($dao)){
            SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ScheduleDAO");
            $dao = SOY2DAOFactory::create("SOYShopReserveCalendar_ScheduleDAO");
        }
        return $dao;
    }

    private function resDao(){
        static $dao;
        if(is_null($dao)){
            SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ReserveDAO");
            $dao = SOY2DAOFactory::create("SOYShopReserveCalendar_ReserveDAO");
        }
        return $dao;
    }
}

SOYShopPlugin::extension("soyshop.item.option", "reserve_calendar", "ReserveCalendarOption");
