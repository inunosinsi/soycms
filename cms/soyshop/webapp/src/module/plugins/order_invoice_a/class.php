<?php
class Invoice_IndexPage extends HTMLTemplatePage{

	protected $id;
	protected $logic;

	function setOrderId($id){
		$this->id = $id;
	}

	function build_invoice(){
		$this->logic = SOY2Logic::createInstance("logic.order.OrderLogic");

        /*** 注文情報 ***/
        $order = $this->getOrder();

    	$this->createAdd("order_id","HTMLLabel", array(
			"text" => $order->getTrackingNumber()
		));

		$this->createAdd("order_date","HTMLLabel", array(
			"text" => date('Y-m-d', $order->getOrderDate())
		));

		$this->createAdd("create_date","HTMLLabel", array(
			"text" => date('Y-m-d', time())
		));

		$this->createAdd("subtotal_price","HTMLLabel", array(
			"text" => number_format($this->logic->getTotalPrice($this->id))
		));


		$this->createAdd("subtotal_price","HTMLLabel", array(
			"text" => number_format($this->logic->getTotalPrice($this->id))
		));

		$this->createAdd("order_total_price","HTMLLabel", array(
			"text" => number_format($order->getPrice($this->id))
		));



    	$this->createAdd("module_list","Invoice_ModuleList", array(
    		"list" => $order->getModuleList()
    	));



        /*** お届け先 ***/
        // customer_xx

		$address = $order->getAddressArray();

        //お届け先の郵便番号
		$this->createAdd("customer_zip_code","HTMLLabel", array(
			"text" => $address["zipCode"]
		));

        //お届け先の都道府県
		$this->createAdd("customer_area","HTMLLabel", array(
			"text" => SOYShop_Area::getAreaText($address["area"])
		));

        //お届け先の住所1
		$this->createAdd("customer_address1","HTMLLabel", array(
			"text" => $address["address1"]
		));

        //お届け先の住所2
		$this->createAdd("customer_address2","HTMLLabel", array(
			"text" => $address["address2"]
		));

        //お届け先の法人名
		$this->createAdd("customer_office","HTMLLabel", array(
			"text" => $address["office"]
		));

        //お届け先の人名
		$this->createAdd("customer_name","HTMLLabel", array(
			"text" => $address["name"]
		));

        /*** 顧客情報 ***/

		$claimedAddress = $order->getClaimedAddressArray();

		//注文者住所の郵便番号
		$this->createAdd("zip_code","HTMLLabel", array(
			"text" => $claimedAddress["zipCode"]
		));

		//注文者住所の都道府県
		$this->createAdd("area","HTMLLabel", array(
			"text" => SOYShop_Area::getAreaText($claimedAddress["area"])
		));

		//注文者の住所1
		$this->createAdd("address1","HTMLLabel", array(
			"text" => $claimedAddress["address1"]
		));

		//注文者の住所2
		$this->createAdd("address2","HTMLLabel", array(
			"text" => $claimedAddress["address2"]
		));

		//注文者の法人名
		$this->createAdd("office","HTMLLabel", array(
			"text" => $claimedAddress["office"]
		));

		//注文者の名前
		$this->createAdd("name","HTMLLabel", array(
			"text" => $claimedAddress["name"]
		));



        /*** 注文商品 ***/
    	$this->createAdd("item_detail","Invoice_ItemList", array(
    		"list" => $this->getItems()
    	));

		/*** ショップ情報 ***/
		// shop_xx
	   	$config = SOYShop_ShopConfig::load();
    	$company = $config->getCompanyInformation();

    	$this->createAdd("shop_name", "HTMLLabel", array(
    		"text" => $config->getShopName()
    	));

    	$this->createAdd("shop_url", "HTMLLabel", array(
    		"text" => soyshop_get_site_url(true)
    	));

    	$this->createAdd("company_name", "HTMLLabel", array(
    		"text" => $company["name"]
    	));

    	$this->createAdd("company_area", "HTMLLabel", array(
    		"text" => ""
    	));

    	$this->createAdd("company_zip_code", "HTMLLabel", array(
    		"text" => $company["address1"]
    	));

    	$this->createAdd("company_address1", "HTMLLabel", array(
    		"text" => ""
    	));

    	$this->createAdd("company_address2", "HTMLLabel", array(
    		"text" => $company["address2"]
    	));

    	$this->createAdd("company_telephone", "HTMLLabel", array(
    		"text" => $company["telephone"]
    	));

    	$this->createAdd("company_fax", "HTMLLabel", array(
    		"text" => $company["fax"]
    	));

    	$this->createAdd("company_mailaddress", "HTMLLabel", array(
    		"text" => $company["mailaddress"]
    	));



    }

    protected function getOrder(){
    	return soyshop_get_order_object($this->id);
    }

    protected function getItems(){
    	return soyshop_get_item_orders($this->id);
    }

    protected function getCustomer($id){
        SOY2DAOFactory::importEntity("user.SOYShop_User");
        SOY2DAOFactory::importEntity("config.SOYShop_Area");

		try{
    		$customer = SOY2DAOFactory::create("user.SOYShop_UserDAO")->getById($id);
		}catch(Exception $e){
			$customer = new SOYShop_User();
			$customer->setName("[deleted]");
		}

		return $customer;
    }

}

class Invoice_ItemList extends HTMLList {

	private $itemDAO;

	protected function populateItem($itemOrder) {


		$item = soyshop_get_item_object($itemOrder->getItemId());


		$this->createAdd("item_id","HTMLLink", array(
			"text" => (strlen($item->getCode()) > 0) ? $item->getCode() : "deleted item " . $itemOrder->getItemId(),
			"link" => SOY2PageController::createLink("Item.Detail." . $itemOrder->getItemId())
		));

		$this->createAdd("item_code","HTMLLabel", array(
			"text" => $item->getCode()
		));

		$this->createAdd("item_name","HTMLLabel", array(
			"text" => $itemOrder->getItemName()
		));

		SOYShopPlugin::load("soyshop.item.option");
		$delegate = SOYShopPlugin::invoke("soyshop.item.option", array(
			"mode" => "display",
			"item" => $itemOrder,
		));

		$this->createAdd("item_option","HTMLLabel", array(
			"text" => $delegate->getHtmls()
		));

		$this->createAdd("item_count","HTMLLabel", array(
			"text" => $itemOrder->getItemCount()
		));

		$this->createAdd("item_price","HTMLLabel", array(
			"text" => number_format($itemOrder->getItemPrice())
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


class Invoice_ModuleList extends HTMLList {

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
