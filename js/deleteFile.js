var il = il || {};
il.Unibe = il.Unibe || {};

il.Unibe.deleteFile = function(element, url){
    $.ajax(url)
        .done(function(data) {
            console.log(element);
            console.log(data);
            if($(".modal .il-unibe-file").length===1){
                $(element).parents(".il_calevent").find(".il-downloader").remove();
                $(element).parents(".ilInfoScreenSec").remove();
            }else{
                $(element).parents(".il-unibe-file").remove();
            }

        })
        .fail(function(data) {
            console.log(data);
        });
};