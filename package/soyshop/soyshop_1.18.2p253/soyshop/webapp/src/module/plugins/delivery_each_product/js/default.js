function return_default_price(){
  var res = confirm("標準の送料設定に戻しますか？");
  if(res){
    for (var i = 1; i <= 48; i++) {
      $("#price_input_" + i).val($("#default_price_" + i).val());
    }
  }
}
