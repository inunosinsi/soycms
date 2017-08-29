var entryAlertFlag = 0;
$(window).on("beforeunload", function(e) {
	if (entryAlertFlag)
		return "Do you want to move this page ?";
});

$("input#title").on("blur", function(e) {
	if ($(this).val()) {
		entryAlertFlag = 1;
	}
});

/** @ToDo 本文や追記にも対応したい **/

//beforeunloadの解除
$("form").on('submit', function() {
	$(window).off('beforeunload');
});
