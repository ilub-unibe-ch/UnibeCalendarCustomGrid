var il = il || {};
il.Unibe = il.Unibe || {};

il.Unibe.customizeWrapper = function(uploadId){
    setTimeout(function(){
        $(".il-modal-roundtrip").css('z-index', '2000');
        $(".modal-backdrop").css('z-index', '1900');

        //Copied From src/UI/templates/js/Dropzone/File/uploader.js
        var $uploadFileLists = $('.il-upload-file-list');
        $uploadFileLists.find('span.toggle .glyph:first').hide();
        $uploadFileLists.find('span.toggle .glyph:visible').on('click', function () {
            console.log("clicke");
            $(this).parent().find(".glyph").each(function () {
                $(this).toggle();
            });
            $(this).parents('.il-upload-file-item').find('.metadata').toggle();
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