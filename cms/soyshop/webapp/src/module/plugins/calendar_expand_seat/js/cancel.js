(function(){
	var ipts = $('input[name="prev"]');
	var $prev = $(ipts[0]);
	$prev.prop("type", "button");
	$prev.on("click", function(){
		var forms = $("form");
		forms[0].novalidate = true;
		var $form = $(forms[0]);
		$('<input>').attr({
                'type': 'hidden',
                'name': 'prev',
                'value': '戻る'
            }).appendTo($form);
		$form.submit();
	});
}());
