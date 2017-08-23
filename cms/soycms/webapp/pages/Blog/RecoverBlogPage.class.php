<?php

include_once(dirname(dirname(__FILE__))."/Page/RecoverPage.class.php");

class RecoverBlogPage extends RecoverPage{
	protected $pageToGoBack = "Blog.List";
}
