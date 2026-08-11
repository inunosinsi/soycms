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
					console.log(arr);
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
				var isPush = false;
				for(var i = 0; i < 4; i++){
					if(v[i] != null && v[i].match(re)){
						isPush = true;
						break;
					}
				}
				if(isPush) list.push(v[0]);
            });
            rsp(list);
        }
    });
}
