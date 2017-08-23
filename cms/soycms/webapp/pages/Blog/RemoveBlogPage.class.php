<?php
include_once(dirname(dirname(__FILE__))."/Page/RemovePage.class.php");

class RemoveBlogPage extends RemovePage{
	protected $pageToGoBack = "Blog.List";
}
