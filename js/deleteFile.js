var il = il || {};
il.Unibe = il.Unibe || {};

il.Unibe.deleteFile = function(element, url){

    $.ajax(url)
        .done(function(data) {
            data = JSON.parse(data);
            var is_listing = $(element).parents(".il_calevent").length == 0;
            var was_last_file = $(".modal .il-unibe-file").length===1;
            var $listing_file_property = null;
            if(is_listing){
                $listing_file_property = $(".il-item .btn:contains("+data.session_title+")")
                    .parents('.il-item').find(".il-item-property-value a:contains("+data.file_title+")").parent();
            }

            if(was_last_file){
                if(is_listing){
                    $listing_file_property.html('-');
                }else{
                    $(element).parents(".il_calevent").find(".il-downloader").remove();
                }
                $(element).parents(".ilInfoScreenSec").remove();
            }else{
                if(is_listing){
                    $listing_file_property.find(".btn-link:contains("+data.file_title+")").remove();
                    var link_list_html = $listing_file_property.html();
                    link_list_html = link_list_html.replace(", ,",", ");
                    link_list_html = link_list_html.replace(/^,|,$/g,'');
                    $listing_file_property.html(link_list_html);

                }
                $(element).parents(".il-unibe-file").remove();
            }

        })
        .fail(function(data) {
            console.log(data);
        });
};