(function(){
	var old = ""; //前回打ち込んだ文字を記録しておく

	set_auto_completion_tag([]);

	$("#auto_completion").on("keyup", function(){
		if(old != $(this).val()){
			old = $(this).val();

			$.ajax({
				type: "POST",
				url: $("#auto_completion_url").val(),
				data: "q=" + old,
				success: function(data){
					var arr = JSON.parse(data);
					set_auto_completion_tag(arr);
				}
			});
		}
	});
}());

function set_auto_completion_tag(opts){
	$("#auto_completion").autocomplete({
        source : function(req, rsp) {
            var re = new RegExp('(' + req.term + ')');
            var list = [];

            $.each(opts, function(i, v) {
                if(v[0].match(re) || v[1].match(re) || v[2].match(re)){
                    list.push(v[0]);
                }
            });
            rsp(list);
        }
    });
}
