var il = il || {};
il.Unibe = il.Unibe || {};

il.Unibe.customizeWrapper = function(uploadId,target){
    setTimeout(function(){
            il.UI.uploader.onAllUploadCompleted($(uploadId).attr('id'), function(){
                window.location.replace(target);
            });
    }, 3000);

}