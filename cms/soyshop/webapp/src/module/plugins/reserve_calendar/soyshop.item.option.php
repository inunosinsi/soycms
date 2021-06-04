<?php

class ReserveCalendarOption extends SOYShopItemOptionBase{

	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
	}

    function clear($index, CartLogic $cart){
        $items = $cart->getItems();
        if(isset($items[$index])){
            $itemId = $items[$index]->getItemId();

            $obj = ReserveCalendarUtil::getCartAttributeId("schedule_id", $index, $itemId);
            $cart->clearAttribute($obj);
        }
    }

    function compare($postedOption, CartLogic $cart){
        $checkOptionId = null;

        $items = $cart->getItems();

        //比較用の配列を作成する
        $attributes = array();
        foreach($items as $index => $item){
            $obj = ReserveCalendarUtil::getCartAttributeId("schedule_id", $index, $item->getItemId());
            $attributes[$index]["schedule_id"] = $cart->getAttribute($obj);

            $currentOptions = array_diff($attributes[$index], array(null));

            if($postedOption == $currentOptions){
                $checkOptionId = $index;
                break;
            }
        }

        return $checkOptionId;
    }

    function doPost($index, CartLogic $cart){
        if(isset($_REQUEST["item_option"]["schedule_id"])){
            $items = $cart->getItems();
			if(isset($items[$index])){
                $itemId = $items[$index]->getItemId();

                $obj = ReserveCalendarUtil::getCartAttributeId("schedule_id", $index, $itemId);
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
    function onOutput($htmlObj, $index){
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

    function order($index){
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

    function edit($key){}

    private function getScheduleById($schId){
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
