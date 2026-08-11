$(function(){
	displayEvaluateStar();
});

function displayEvaluateStar(){
	var evaluateStar = $("#evaluate_star");
	var evaluateValue = parseInt($("#evaluate_value").val());
	var evaluateColor = $("#evaluate_color").val();
	evaluateStar.html("");

	for ( var i = 0; i < 5; i++ ) {
		var $span = $("<span>");
		$span.prop("id", "star_" + (i + 1));
		if(i < evaluateValue){
			$span.text("★").css("color", "#" + evaluateColor);
		}else{
			$span.text("☆");
		}

		$span.css("cursor", "pointer");

		//イベントの登録
		$span.on("click", function(){
			var evaluate = parseInt($(this).prop("id").replace("star_", ""));
			$("#evaluate_value").val(evaluate);
			displayEvaluateStar();
		});

		evaluateStar.append($span);
	}
}
