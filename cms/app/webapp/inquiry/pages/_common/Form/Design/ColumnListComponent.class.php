<?php

class ColumnListComponent extends HTMLList{
	private $isLinkageSOYMail;
	private $isLinkageSOYShop;
	private $formDesign;

	protected function populateItem($entity){

		$this->addLabel("column_hash", array(
			"name" => "column_" . $entity->getId(),
			"text" => $entity->getColumnId()
		));

		$this->addInput("label", array(
			"name" => "Column[label]",
			"value" => $entity->getLabel()
		));

		$this->addLabel("column_type", array(
			"text" => $entity->getTypeText()
		));

		$this->addLink("configure_link", array(
			"onclick" => '$(\'#configure_wrapper_' . $entity->getId() . '\').toggle();',
			"link" => "javascript:void(0);"
		));

		$this->addModel("configure_wrapper", array(
			"id" => "configure_wrapper_" . $entity->getId()
		));

		$this->addModel("require_cell", array(
			"onclick" => 'changeColor(this,$(\''."#column_require_" . $entity->getId().'\'));changeRepalce($(\''."#column_replace_" . $entity->getId().'\'),$(\''."#column_require_" . $entity->getId().'\'));',
			"style" => ($entity->getRequire()) ? "background-color:#FF8888;" : "",
			"checkColor" => "#FF8888"
		));

		$this->addInput("not_require", array(
			"type" => "hidden",
			"name" => "Column[require]",
			"value" => 0,
		));

		$this->addCheckBox("require", array(
			"elementId" => "column_require_" . $entity->getId(),
			"name" => "Column[require]",
			"selected" => $entity->getRequire(),
			"value" => 1,
			"onmouseup" => 'changeColor(this.parentNode,this);changeRepalce($(\''."#column_replace_" . $entity->getId().'\'),$(\''."#column_require_" . $entity->getId().'\'));'
		));

		$column = $entity->getColumn(new SOYInquiry_Form());
		$this->addLabel("configure", array(
			"html" => $column->getConfigForm()
		));

		$this->addForm("column_form", array(
			"action" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Design.UpdateColumn." . $entity->getId())
		));

		$this->addSelect("linkage_soymail", array(
			"name" => "Column[config][SOYMailTo]",
			"options" => (is_array($column->getLinkagesSOYMailTo())) ? $column->getLinkagesSOYMailTo() : array(),
			"selected" => $column->getSOYMailTo(),
			"visible" => $this->isLinkageSOYMail && count($column->getLinkagesSOYMailTo())>1
		));

		$this->addSelect("linkage_soyshop", array(
			"name" => "Column[config][SOYShopFrom]",
			"options" => (is_array($column->getLinkagesSOYShopFrom())) ? $column->getLinkagesSOYShopFrom() : array(),
			"selected" => $column->getSOYShopFrom(),
			"visible" => $this->isLinkageSOYShop && count($column->getLinkagesSOYShopFrom())>1
		));

		$this->addInput("replace", array(
			"value" => $column->getReplacement(),
			"name" => "Column[config][replacement]",
		));

		$this->addModel("replace_wrapper", array(
			"id" => "column_replace_".$entity->getId(),
			"style" => ($column->getIsRequire()) ? "" : "visibility:hidden"
		));

		$this->addInput("annotation", array(
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
