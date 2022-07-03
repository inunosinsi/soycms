var $postArea = $("#soycms_preview_url_postfix");
var $prevArea = $("#soycms_preview_url_area");
$postArea.css("display", "none");
$prevArea.css("display", "none");

var $prevChk = $("#soycms_preview_check");
if($prevChk.prop("checked")){
	$postArea.css("display", "block");
    $prevArea.css("display", "block");
}

$prevChk.on("click", function(){
    if($(this).prop("checked")){
		$postArea.css("display", "block");
		$prevArea.css("display", "block");
    }else{
		$postArea.css("display", "none");
        $prevArea.css("display", "none");
    }
});