<?php

class Print_IndexPage extends HTMLTemplatePage{

	private $orders;
	private $logic;

	function setOrders($orders){
		$this->orders = $orders;
	}

	function build_print(){
		$orders = $this->orders;

		$this->createAdd("continuous_print","ContinuousPrint", array(
			"list" => $orders
		));
	}

}

class ContinuousPrint extends HTMLList{

	private $orderDAO;

	protected function populateItem($entity){

		$id = $entity->getId();

		$this->createAdd("order_id","HTMLLabel", array(
			"text" => $entity->getTrackingNumber()
		));

		$this->createAdd("order_date","HTMLLabel", array(
			"text" => date('Y-m-d', $entity->getOrderDate())
		));

		$this->createAdd("create_date","HTMLLabel", array(
			"text" => date('Y-m-d', time())
		));

		$this->createAdd("create_date","HTMLLabel", array(
			"text" => date('Y-m-d', time())
		));

		$this->createAdd("subtotal_price","HTMLLabel", array(
			"text" => number_format($this->getTotalPrice($id))
		));


		$this->createAdd("order_total_price","HTMLLabel", array(
			"text" => number_format($entity->getPrice($id))
		));

    	$this->createAdd("module_list","Invoice_ModuleList", array(
    		"list" => $entity->getModuleList()
    	));

        /*** お届け先 ***/
        // customer_xx

        $customer = $this->getCustomer($entity->getUserId());

		$address = $entity->getAddressArray();

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
			"text" => @$address["office"]
		));

        //お届け先の人名
		$this->createAdd("customer_name","HTMLLabel", array(
			"text" => $address["name"]
		));

        /*** 顧客情報 ***/

		//注文者住所の郵便番号
		$this->createAdd("zip_code","HTMLLabel", array(
			"text" => $customer->getZipCode()
		));

		//注文者住所の都道府県
		$this->createAdd("area","HTMLLabel", array(
			"text" => SOYShop_Area::getAreaText($customer->getArea())
		));

		//注文者の住所1
		$this->createAdd("address1","HTMLLabel", array(
			"text" => $customer->getAddress1()
		));

		//注文者の住所2
		$this->createAdd("address2","HTMLLabel", array(
			"text" => $customer->getAddress2()
		));

		//注文者の法人名
		$this->createAdd("office","HTMLLabel", array(
			"text" => $customer->getJobName()
		));

		//注文者の名前
		$this->createAdd("name","HTMLLabel", array(
			"text" => $customer->getName()
		));


        /*** 注文商品 ***/
		SOY2::imports("module.plugins.order_invoice.component.*");
	   	$this->createAdd("item_detail","InvoiceItemListComponent", array(
    		"list" => $entity->getItems()
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

	/**
	 * @return object#SOYShop_ItemOrder
	 * @param orderId
	 */
	function getTotalPrice($orderId){
		if(!$this->orderDAO)$this->orderDAO = SOY2DAOFactory::create("shop.SOYShop_ItemOrderDAO");

		try{
			return $this->orderDAO->getTotalPriceByOrderId($orderId);
		}catch(Exception $e){
			return new SOYShop_ItemOrder();
		}
	}
}

class Invoice_ItemList extends HTMLList {

	private $itemDAO;

	protected function populateItem($itemOrder) {


		$item = $this->getItem($itemOrder->getItemId());


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
