<?php

class InvoicePage extends HTMLTemplatePage{

  private $orderId;

  function build_note(){
    SOY2::imports("module.plugins.order_invoice_with_note.component.*");
    $this->createAdd("continuous_print", "NoteListComponent", array(
			"list" => array($this->orderId),
		));
  }

  function getOrderId(){
    return $this->orderId;
  }

  function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}
