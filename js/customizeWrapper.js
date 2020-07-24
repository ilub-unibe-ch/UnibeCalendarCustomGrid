var il = il || {};
il.Unibe = il.Unibe || {};

il.Unibe.customizeWrapper = function(uploadId){
    setTimeout(function(){
        il.UI.uploader.onAllUploadCompleted($(uploadId).attr('id'), function(){
            lslocation.reload();
        });
    }, 3000);

};

//See Report: 1377, prevent modals from opening twice and not be closable
$(document).ready(function () {
    $('#il_center_col .btn-link').on('click', function (e) {
        setTimeout(function(){
            var nr_modals = $(".il-modal-roundtrip.in").length;
            if($(".il-modal-roundtrip.in").length > 1){
                $(".il-modal-roundtrip.in").slice(1,nr_modals).remove();
                $(".modal-backdrop.in").slice(0,nr_modals).remove();
            }
        }, 3000);

    });
});