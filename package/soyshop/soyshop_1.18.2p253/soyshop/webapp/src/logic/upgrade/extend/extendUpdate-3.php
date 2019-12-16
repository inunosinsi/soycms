<?php

if($this->checkInitMail(true)){
	//not yet init config
	$this->initMailText(true);
}else{
}

if($this->checkInitMail(false)){
	//not yet init config
	$this->initMailText(false);
}else{
	//already initi
}
?>