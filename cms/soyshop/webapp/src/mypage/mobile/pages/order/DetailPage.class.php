<?php
class DetailPage extends MobileMyPagePageBase{

	function doPost(){

	}

	private $id;

	function __construct($args){
		parent::__construct();

		$mypage = MyPageLogic::getMyPage();
		if(!$mypage->getIsLoggedin())$this->jump("login");//ログインチェック

		if(!isset($args[0]))$this->jump("order");
		$this->id = $args[0];

		$this->addLink("return_link", array(
			"link" => soyshop_get_mypage_url() . "/top"
		));

		$this->buildOrder();
	}

	function buildOrder(){

		$orderDAO = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		$order = array();
		try{
			$order = $orderDAO->getForOrderDisplay($this->id,$this->getUserId());
			$logic = SOY2Logic::createInstance("logic.order.OrderLogic");

			if(!$order->isOrderDisplay())throw new Exception;

		}catch(Exception $e){
			$this->jump("order");
		}

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

		//備考、支払方法、配送方法、配達時間
    	$this->createAdd("attribute_list","AttributeList", array(
    		"list" => $order->getAttributeList()
    	));

		//送付先
    	$address = $order->getAddressArray();

    	$customerHTML = "";
    	$customerHTML.= $address["name"] . "\n";
    	$customerHTML.= $address["zipCode"]. "\n";
    	$customerHTML.= SOYShop_Area::getAreaText($address["area"]) .$address["address1"].$address["address2"] . "\n";
    	$customerHTML.= $address["telephoneNumber"] . "\n";

    	$this->addLabel("order_address", array(
    		"html" => nl2br(htmlspecialchars($customerHTML, ENT_QUOTES, "UTF-8"))
    	));

		//備考
    	$this->addLabel("order_memo", array(
    		"html" => nl2br(htmlspecialchars($customerHTML, ENT_QUOTES, "UTF-8"))
    	));

		//注文の内訳
    	$this->createAdd("item_list","OrderItemList", array(
    		"list" => soyshop_get_item_orders($this->id)
    	));

    	$this->createAdd("module_list", "ModuleList", array(
    		"list" => $order->getModuleList()
    	));

    	//送料も含めたトータルの金額
    	$this->createAdd("order_total_price","HTMLLabel", array(
    		"text" => number_format($order->getPrice())
    	));

	}

}

class AttributeList extends HTMLList {

	protected function populateItem($item) {

		$this->createAdd("attribute_title","HTMLLabel", array(
			"text" => $item["name"]
		));

		$this->createAdd("attribute_value","HTMLLabel", array(
			"html" => nl2br(htmlspecialchars($item["value"], ENT_QUOTES, "UTF-8"))
		));

	}

}

class OrderItemList extends HTMLList{

	private $itemDAO;

	protected function populateItem($itemOrder) {

		$item = $this->getItem($itemOrder->getItemId());


		$urls = SOYShop_DataSets::get("site.url_mapping", array());
		$url = "";
		if(isset($urls[$item->getDetailPageId()])){
			$url = $urls[$item->getDetailPageId()]["uri"];
		}else{
			foreach($urls as $array){
				if($array["type"] == "detail"){
					$url = $array["uri"];
					break;
				}
			}
		}

		$this->createAdd("item_code","HTMLLink", array(
			"text" => (strlen($item->getCode()) > 0) ? $item->getCode() : "deleted item " . $itemOrder->getItemId(),
			"link" => soyshop_get_page_url($url,$item->getAlias()),
		));

		$this->createAdd("item_name","HTMLLabel", array(
			"text" => $itemOrder->getItemName()
		));

		$this->createAdd("item_price","HTMLLabel", array(
			"text" => number_format($itemOrder->getItemPrice())
		));

		$this->createAdd("item_count","HTMLLabel", array(
			"text" => number_format($itemOrder->getItemCount())
		));

		$this->createAdd("item_total_price","HTMLLabel", array(
			"text" => number_format($itemOrder->getTotalPrice())
		));

	}

	/**
	 * @return object#SOYShop_Item
	 * @param itemId
	 */
	function getItem($itemId){
		if(!$this->itemDAO)$this->itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

		try{
			return $this->itemDAO->getById($itemId);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}

}

class ModuleList extends HTMLList {

	protected function populateItem($item) {

		$this->createAdd("module_name","HTMLLabel", array(
			"text" => $item->getName()
		));

		$this->createAdd("module_price","HTMLLabel", array(
			"text" => number_format($item->getPrice())
		));

		return $item->isVisible();

	}

}
?>
