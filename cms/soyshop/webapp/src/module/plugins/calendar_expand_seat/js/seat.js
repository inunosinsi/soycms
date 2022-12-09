var $adultSeat = $("#adult_seat");
var $childSeat = $("#child_seat");
var $totalSeat = $("#total_seat");

function insert_schedule_form(ele, schId){
	var $btnArea = $("#reserve_button");
	if(!isNaN(schId) && schId > 0){
		var $schIdForm = $("#schedule_id").val(schId);

		//ボタンの色を変える
		$(".schedule_button").each(function(){
			$(this).prop("class", "btn btn-primary schedule_button");
		});

		$(ele).prop("class", "btn btn-warning schedule_button");

		if(unsold_seat_list[schId]){
			var unsoldSeat = unsold_seat_list[schId];
			var adult = parseInt($adultSeat.val());
			var child = parseInt($childSeat.val());
			$adultSeat.prop("max", unsoldSeat);
			$childSeat.prop("max", (unsoldSeat - adult));
			$totalSeat.prop("max", unsoldSeat);

			$totalSeat.val(adult + child);
		}

		$btnArea.show();
	}else{
		$btnArea.hide();
	}
}

$adultSeat.on("blur", auto_sum);
$childSeat.on("blur", auto_sum);

function auto_sum(){
	var sum = 0;
	if(!isNaN($adultSeat.val())){
		var adult = parseInt($adultSeat.val());
		sum += adult;
		//子供の残席数を減らす
		var total = parseInt($totalSeat.prop("max"));
		$childSeat.prop("max", (total - adult));
	}

	if(!isNaN($childSeat.val())){
		sum += parseInt($childSeat.val());
	}

	$totalSeat.val(sum);
}
