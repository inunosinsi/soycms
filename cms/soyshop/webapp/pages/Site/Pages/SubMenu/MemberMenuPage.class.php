<?php
/**
 * @class Site.Pages.SubMenu.FreePage
 * @date 2009-11-19T22:37:03+09:00
 * @author SOY2HTMLFactory
 */
class MemberMenuPage extends HTMLPage{

	var $id;

	function __construct($arg = array()){
		$this->id = $arg[0];
		parent::__construct();
	}
}
