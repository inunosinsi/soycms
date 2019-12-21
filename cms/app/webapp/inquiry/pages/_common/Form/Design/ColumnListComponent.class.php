<?php

class ColumnListComponent extends HTMLList{
	private $isLinkageSOYMail;
	private $isLinkageSOYShop;
	private $formDesign;

	protected function populateItem($entity){

		$this->createAdd("column_hash","HTMLLabel",array(
			"name" => "column_" . $entity->getId(),
			"text" => $entity->getColumnId()
		));

		$this->createAdd("label","HTMLInput",array(
			"name" => "Column[label]",
			"value" => $entity->getLabel()
		));

		$this->createAdd("column_type","HTMLLabel",array(
			"text" => $entity->getTypeText()
		));

		$this->createAdd("configure_link","HTMLLink",array(
			"onclick" => '$(\'#configure_wrapper_' . $entity->getId() . '\').toggle();',
			"link" => "javascript:void(0);"
		));

		$this->createAdd("configure_wrapper","HTMLModel",array(
			"id" => "configure_wrapper_" . $entity->getId()
		));


		$this->createAdd("require_cell","HTMLModel",array(
			"onclick" => 'changeColor(this,$(\''."#column_require_" . $entity->getId().'\'));changeRepalce($(\''."#column_replace_" . $entity->getId().'\'),$(\''."#column_require_" . $entity->getId().'\'));',
			"style" => ($entity->getRequire()) ? "background-color:#FF8888;" : "",
			"checkColor" => "#FF8888"
		));

		$this->createAdd("not_require","HTMLInput",array(
			"type" => "hidden",
			"name" => "Column[require]",
			"value" => 0,
		));

		$this->createAdd("require","HTMLCheckbox",array(
			"elementId" => "column_require_" . $entity->getId(),
			"name" => "Column[require]",
			"selected" => $entity->getRequire(),
			"value" => 1,
			"onmouseup" => 'changeColor(this.parentNode,this);changeRepalce($(\''."#column_replace_" . $entity->getId().'\'),$(\''."#column_require_" . $entity->getId().'\'));'
		));

		$column = $entity->getColumn();
		$this->createAdd("configure","HTMLLabel",array(
			"html" => $column->getConfigForm()
		));

		$this->createAdd("column_form","HTMLForm",array(
			"action" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Design.UpdateColumn." . $entity->getId())
		));

		$this->createAdd("linkage_soymail","HTMLSelect",array(
			"name" => "Column[config][SOYMailTo]",
			"options" => (is_array($column->getLinkagesSOYMailTo())) ? $column->getLinkagesSOYMailTo() : array(),
			"selected" => $column->getSOYMailTo(),
			"visible" => $this->isLinkageSOYMail && count($column->getLinkagesSOYMailTo())>1
		));

		$this->createAdd("linkage_soyshop","HTMLSelect",array(
			"name" => "Column[config][SOYShopFrom]",
			"options" => (is_array($column->getLinkagesSOYShopFrom())) ? $column->getLinkagesSOYShopFrom() : array(),
			"selected" => $column->getSOYShopFrom(),
			"visible" => $this->isLinkageSOYShop && count($column->getLinkagesSOYShopFrom())>1
		));

		$this->createAdd("replace","HTMLInput",array(
			"value" => $column->getReplacement(),
			"name" => "Column[config][replacement]",
		));

		$this->createAdd("replace_wrapper","HTMLModel",array(
			"id" => "column_replace_".$entity->getId(),
			"style" => ($column->getIsRequire()) ? "" : "visibility:hidden"
		));

		$this->createAdd("annotation","HTMLInput",array(
			"value" => $column->getAnnotation(),
			"name" => "Column[config][annotation]"
		));

		//デザインがdefaultの時のみ属性(tr)のフォームを出力する
		$this->addModel("is_tr_property", array(
			"visible" => SOYInquiryUtil::checkUsabledTrProperty($this->formDesign)
		));

		$this->addInput("tr_property", array(
			"name" => "Column[config][trProperty]",
			"value" => $column->getTrProperty()
		));
	}


	/**#@+
	 *
	 * @access public
	 */
	function getIsLinkageSOYMail() {
		return $this->isLinkageSOYMail;
	}
	function setIsLinkageSOYMail($isLinkageSOYMail) {
		$this->isLinkageSOYMail = $isLinkageSOYMail;
	}

	/**#@+
	 *
	 * @access public
	 */
	function getIsLinkageSOYShop() {
		return $this->isLinkageSOYShop;
	}
	function setIsLinkageSOYShop($isLinkageSOYShop) {
		$this->isLinkageSOYShop = $isLinkageSOYShop;
	}

	function getFormDesign(){
		return $this->formDesign;
	}
	function setFormDesign($formDesign){
		$this->formDesign = $formDesign;
	}

	/**#@-*/

}
