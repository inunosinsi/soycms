<?php

class RegisteredItemListComponent extends HTMLList{
	
	protected function populateItem($entity, $idx) {
		
		$this->addLabel("label", array(
			"text" => (isset($entity["label"])) ? $entity["label"] : ""
		));

		$a = (isset($entity["attribute"])) ? (int)$entity["attribute"] : 1;
		$f = ($a > 3 && isset($entity["field_id"])) ? $entity["field_id"] : "";
		switch($a){
			case 4:
				$label = MemberSpecialPriceUtil::getFieldLabelById($f);
				break;
			default:
				$label = "顧客属性".$a;
		}
		$this->addLabel("attribute", array(
			"text" => $label
		));
		
		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=member_special_price&index=" . $idx),
			"onclick" => "return confirm('削除しますか？');"
		));
	}
}
