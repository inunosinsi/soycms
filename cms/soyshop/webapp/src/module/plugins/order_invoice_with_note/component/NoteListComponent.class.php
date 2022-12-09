<?php

class NoteListComponent extends HTMLList {

	protected function populateItem($entity) {

    	$this->createAdd("note_print", "InvoiceListComponent", array(
      		"list" => array("", ""),
      		"orderId" => $entity
		));
	}
}
