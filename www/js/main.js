$(function(){
    setTimeout(function(){
        $(".alert").hide(200);
    },5000);
    
    $(".delete").click(function(){
       $(".modal-title").html("Opravdu chcete smazat produkt <b>"+$(this).attr("data-name")+"</b>?")
       $("#modal-confirm").attr("href","/?id="+$(this).attr("data-id")+"&do=delete");
       $("#deleteDialog").modal('show'); 
    });
    
    $.nette.init();
});
