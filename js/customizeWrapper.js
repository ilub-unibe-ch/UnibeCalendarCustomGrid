var il = il || {};
il.Unibe = il.Unibe || {};

il.Unibe.customizeWrapper = function(uploadId){
    setTimeout(function(){
        //Copied From src/UI/templates/js/Dropzone/File/uploader.js
        var $uploadFileLists = $('.il-upload-file-list');
        $uploadFileLists.find('span.toggle .glyphicon-triangle-bottom').hide();

        $uploadFileLists.on('click', '.glyphicon-triangle-right',function () {
            $(this).parents('.il-upload-file-item').find('.glyphicon-triangle-right').hide();
            $(this).parents('.il-upload-file-item').find('.glyphicon-triangle-bottom').show();
            $(this).parents('.il-upload-file-item').find('.metadata').show();
            return false;
        });
        $uploadFileLists.on('click', '.glyphicon-triangle-bottom',function () {
            $(this).parents('.il-upload-file-item').find('.glyphicon-triangle-bottom').hide();
            $(this).parents('.il-upload-file-item').find('.glyphicon-triangle-right').show();
            $(this).parents('.il-upload-file-item').find('.metadata').hide();
            return false;
        });
        $uploadFileLists.on('click', 'span.remove button.close', function () {
            var dz = $(this).closest('.il-dropzone-base').find(".il-dropzone");
            var uploadId = dz.data('upload-id');
            var fileId = parseInt($(this).parents('.il-upload-file-item').data('file-id'));
            il.UI.uploader.removeFile(uploadId, fileId);
            return false;
        });
        //End Copy

        il.UI.uploader.onAllUploadCompleted($(uploadId).attr('id'), function(){
            location.reload();
        });
    }, 3000);

}