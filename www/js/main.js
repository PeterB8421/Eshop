$(function(){
    setTimeout(function(){
        $(".alert").hide(200);
    },5000);
    
    $.nette.init();
    $(".delete").click(function(){
       $(".modal-title").html("Opravdu chcete smazat produkt?")
       $("#modal-confirm").attr("n:href","delete! "+$(".delete").attr("id"));
       $("#deleteDialog").modal('show'); 
    });
});
