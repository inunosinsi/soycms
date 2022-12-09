<?php

class ContinuousPage extends HTMLTemplatePage{

	private $orders;

	function setOrders($orders){
		$this->orders = $orders;
	}

	function build_print(){
		SOY2::imports("module.plugins.order_invoice_with_note.component.*");
		$this->createAdd("continuous_print", "NoteListComponent", array(
			"list" => self::getOrderIds(),
		));
	}

	private function getOrderIds(){
		if(!count($this->orders)) return array();

		$list = array();
		foreach($this->orders as $order){
			if(!is_null($order->getId()) && is_numeric($order->getId())){
				$list[] = $order->getId();
			}
		}

		return $list;
	}
}
