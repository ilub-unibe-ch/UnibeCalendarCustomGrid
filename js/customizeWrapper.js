var il = il || {};
il.Unibe = il.Unibe || {};

il.Unibe.customizeWrapper = function(uploadId){
    setTimeout(function(){
        il.UI.uploader.onAllUploadCompleted($(uploadId).attr('id'), function(){
            location.reload();
        });
    }, 3000);

}