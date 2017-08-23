<?php

include_once(dirname(dirname(__FILE__))."/Page/PutTrashPage.class.php");

class PutTrashBlogPage extends PutTrashPage{
	protected $pageToGoBack = "Blog.List";
}
