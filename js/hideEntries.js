var il = il || {};
il.Unibe = il.Unibe || {};

il.Unibe.hideEntries = function(){
    $(".ilCalendarEntryInvisible").parents(".calevent").addClass("calempty").css('background-color', 'white').css('border-top','none');
    item_groups = $(".il-panel-listing-std-container .il-item-group");
    if(item_groups.length){
        item_groups.each(function(id,item) {
            console.log(item);
            if(!$(item).find(".ilCalendarEntryVisible").length){
                $(item).hide();
            }
        });
    }


};