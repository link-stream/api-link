"use strict";

$(document).ready(function () {

    Dropzone.options.singleFileUpload = {
        uploadMultiple: false,
        maxFiles: 1,
        maxFilesize: 2, // MB
        dictFileTooBig: "That image is too large. We only allow 2MB or smaller.",
        acceptedFiles: ".jpeg, .jpg",
        init: function () {
            this.on("success", function (file, response) {
                $('.uploaded-photos').append('<div><img src="' + response.link + '"height="150" class="photo" id="' + response.id + '"/><span class="delete-photo">Delete</span></div>');
                $('.uploaded-photos').justifiedGallery('norewind')
                this.removeFile(file);

                var grabNewCount = parseInt($('.recordcount').text(), 10)
                $('.recordcount span').text(grabNewCount += 1);
            });
        }
    };


//  Dropzone.options.singleFileUpload = {
//    paramName: "file",
//    // The name that will be used to transfer the file
//    maxFiles: 1,
//    maxFilesize: 1,//MB
//    init: function init() {
//      this.on("maxfilesexceeded", function (file) {
//        this.removeAllFiles();
//        this.addFile(file);
//      });
//    }
//  }; 

//    Dropzone.options.singleFileUpload = {
//        maxFiles: 1,
//        accept: function (file, done) {
//            console.log("uploaded");
//            done();
//        },
//        init: function () {
//            this.on("maxfilesexceeded", function (file) {
//                alert("No more files please!");
//            });
//        }
//    };
//
//    var myDropzone = new Dropzone("#single-file-upload");
//    myDropzone.on("maxfilesexceeded", function (file) {
//        myDropzone.removeFile(file);
//    });


    // multiple files

//  Dropzone.options.multipleFileUpload = {
//    paramName: "file",
//    // The name that will be used to transfer the file
//    maxFilesize: 1,
//    // MB
//    clickable: true
//  }; // button slect
//
//  new Dropzone(document.body, {
//    // Make the whole body a dropzone
//    url: "#",
//    // Set the url
//    previewsContainer: "#button-select-upload",
//    // Define the container to display the previews
//    clickable: "#button-select" // Define the element that should be used as click trigger to select files.
//
//  }); // file limit
//
//  Dropzone.options.fileLimitUpload = {
//    paramName: "file",
//    // The name that will be used to transfer the file
//    maxFilesize: 1,
//    // MB
//    maxFiles: 4,
//    maxThumbnailFilesize: 1 // MB
//
//  }; // accepted files
//
//  Dropzone.options.fileAcceptedUpload = {
//    paramName: "file",
//    // The name that will be used to transfer the file
//    maxFilesize: 1,
//    // MB
//    acceptedFiles: 'image/*'
//  }; // remove thumbnail
//
//  Dropzone.options.fileRemoveThumbnailUpload = {
//    paramName: "file",
//    // The name that will be used to transfer the file
//    maxFilesize: 1,
//    // MB
//    addRemoveLinks: true,
//    dictRemoveFile: " Trash"
//  };
});