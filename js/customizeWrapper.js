il = il || {};
il.Unibe = il.Unibe || {};

il.Unibe.customizeWrapper = function(uploadId){
    setTimeout(function(){
        il.UI.uploader.onAllUploadCompleted($(uploadId).attr('id'), function(){
            location.reload();
        });
    }, 3000);

};

il.Unibe.loadMap = function(wrapper_id){
    setTimeout(function() {
        let openLayer = ServiceOpenLayers.create(ol, jQuery, ilOLInvalidAddress, ilOLMapData, ilOLUserMarkers);

        ilLookupAddress = function (id, address) {
            return openLayer.jumpToAddress(id, address);
        };

        ilUpdateMap = function (id) {
            return openLayer.updateMap(id);
        };

        ilShowUserMarker = function (id, counter) {
            return openLayer.moveToUserMarkerAndOpen(id, counter);
        };

        openLayer.forceResize(jQuery);
        openLayer.init();
    }, 500);
};


//Init logic to be loaded after model is opened
$(document).ready(function () {
    $('#il_center_col .btn-link').on('click', function (e) {
        setTimeout(function(){
            //See Report: 1377, prevent modals from opening twice and not be closable
            var nr_modals = $(".il-modal-roundtrip.in").length;
            if($(".il-modal-roundtrip.in").length > 1){
                $(".il-modal-roundtrip.in").slice(1,nr_modals).remove();
                $(".modal-backdrop.in").slice(0,nr_modals).remove();
            }


        }, 3000);
    });
});



