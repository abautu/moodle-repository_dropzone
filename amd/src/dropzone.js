import $ from 'jquery';
import dropzone from 'repository_dropzone/dropzone-amd-module';

export const init = (chunkSize, maxFilesize, fallbackUploadUUID) => {
    let filename = $('.fp-content form .fp-file input', parent.document);
    filename.val(fallbackUploadUUID);

    dropzone.autoDiscover = false;
    new dropzone("#fileupload", {
        chunkSize: chunkSize,
        maxFilesize: maxFilesize,
        chunking: true,
        retryChunks: true,
        maxFiles: 1,
        parallelChunkUploads: false,
        forceChunking: true,
        chunksUploaded: function(file, done) {
            filename.val(file.upload.uuid);
            done();
        }
    }).on("maxfilesexceeded", function(file) {
        this.removeAllFiles(true);
        this.addFile(file);
    });
};
