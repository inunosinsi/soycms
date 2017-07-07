<?php
class OrderAttributeListComponent extends HTMLList{

	protected function populateItem($entity, $key){
		$this->addLabel("attribute_title", array(
			"text" => $entity["name"],
		));

		$this->addLabel("attribute_value", array(
			"text" => $entity["value"],
		));

		//hiddenなら表示しない
		if(isset($entity["hidden"]) && $entity["hidden"]){
			//ただし、オーダカスタムフィールドの値は除く
			if(strpos($key, "order_customfield_") === 0 || strpos($key, "order_date_customfield_") === 0){
				//何もしない
			}else{
				return false;
			}
		}
	}
}
