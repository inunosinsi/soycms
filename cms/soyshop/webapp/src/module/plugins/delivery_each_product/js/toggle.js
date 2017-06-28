function toggle_check(all){
    $(".price_input_check").each(function(){
        var ele = $(this);
        if(all.checked){
            ele.attr("checked","checked");
        }else{
            ele.removeAttr("checked");
        }

    });

}

function toggle_all(){
    var price = $("#toggle_price").val();
    $(".price_input_check").each(function(){
        var ele = $(this);

        if(ele.attr("checked")){
            $("#" + ele.attr("targetId")).val(price);
        }
    });
}
