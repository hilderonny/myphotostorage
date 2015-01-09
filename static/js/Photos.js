/* 
 * The MIT License
 *
 * Copyright 2014 Ronny Hildebrandt <ronny.hildebrandt@avorium.de>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Contains client side functions for handling photos. Designed as class Photos
 */
Photos = {

    /**
     * Constructs the list of all photos of the user.
     * 
     * @param {string} listNodeId ID of the DOM element where to put the photos
     * list into.
	 * @param {function} selectListener Function is called when the number of
	 * selected elements changes. The only parameter is the number of currently
	 * selected elements. Useful for menu buttons which are only available when
	 * at least one photo is selected.
     */
    getList : function(listNodeId, selectListener) {
        var self = this;
        this.listNode = document.getElementById(listNodeId);
        this.listNode.selectedImageCount = 0;
        Helper.doRequest("getPhotoList", null, function(response) {
            var photoIdsList = JSON.parse(response);
            var monthnames = {"01" : "++##January##--", "02" : "++##February##--", "03" : "++##March##--", "04" : "++##April##--", "05" : "++##May##--", "06" : "++##June##--", "07" : "++##July##--", "08" : "++##August##--", "09" : "++##September##--", "10" : "++##October##--", "11" : "++##November##--", "12" : "++##December##--" };
            for (var yearmonth in photoIdsList) {
                yearmontharray = yearmonth.split("-");
                var monthnode = document.createElement("div");
                self.listNode.appendChild(monthnode);
                var input = document.createElement("input");
                input.setAttribute("type", "checkbox");
                input.setAttribute("checked", "checked");
                monthnode.appendChild(input);
                var label = document.createElement("label");
                label.innerHTML = monthnames[yearmontharray[1]] + " " + yearmontharray[0] + "<span>" + photoIdsList[yearmonth].length + " ++##Photos##--</span>";
                label.input = input;
                label.addEventListener("click", function() {
                        this.input.click();
                });
                monthnode.appendChild(label);
                var containerdiv = document.createElement("div");
                monthnode.appendChild(containerdiv);
                for (var i = 0; i < photoIdsList[yearmonth].length; i++) {
                    var id = photoIdsList[yearmonth][i];
                    var container = document.createElement("div");
                    var image = document.createElement("img");
                    image.photoId = id;
                    image.addEventListener("click", function() {
                        if (self.listNode.isInSelectionMode) {
                            if (this.isSelected) {
                                this.isSelected = false;
                                this.parentNode.classList.remove("Selected");
                                self.listNode.selectedImageCount--;
                            } else {
                                this.isSelected = true;
                                this.parentNode.classList.add("Selected");
                                self.listNode.selectedImageCount++;
                            }
                            selectListener(self.listNode.selectedImageCount);
                        } else {
							self.showPreview(this);
                        }
                    });
                    image.src = "images.php?type=thumb&id=" + id;
                    container.appendChild(image);
                    containerdiv.appendChild(container);
                }
                isfirst = false;
            }
        });
    },
    /**
     * Handles uploading of files recursively file by file. The function takes
     * the first element of the given file array, uploads it and calls itself.
     * 
     * @param {array} files Array of files to be uploaded.
     * @param {int} index Index of the file to be uploaded.
     * @param {function} fileuploadedcallback Callback function which is called
     * each time a file is uploaded. As parameter the index of the uploaded
     * file is given.
     * @param {function} fileprogresscallback Function which is called for
     * upload progress with percentage of upload of the current file.
     */
    uploadFiles : function(files, index, fileuploadedcallback, fileprogresscallback) {
        var self = this;
        if (!self.uploading || files.length <= index) {
            return;
        }
        Helper.doRequest("uploadPhoto", {file : files[index]}, function(response) {
            fileprogresscallback(100);
            fileuploadedcallback(index);
            self.uploadFiles(files, index + 1, fileuploadedcallback, fileprogresscallback);
        }, function(evt) {
            if (evt.lengthComputable) {
                var percentage = Math.round((evt.loaded * 100) / evt.total);
                fileprogresscallback(percentage);
            }
        });
    },
    /**
     * Handles the upload of the files given in the fileinput DOM node. This
     * function is called after the user has selected the files to be uploaded.
     * 
     * @param {Node} fileinput File input node which contains information
     * about the files to be uploaded.
     * @param {string} progressdomnodeid ID of the DOM node which represents
     * the progress bar. The width of the bar is set in percent depending on
     * the status of the full upload.
     * @param {string} statusdomnodeid ID of the DOM node for status texts.
     * This node will contain the HTML for the current upload status (Uploading
     * file X of Y).
     * @param {function} completioncallback Function is called when the upload
     * is complete.
     */
    processUpload : function(fileinput, progressdomnodeid, statusdomnodeid, completioncallback) {
        var self = this;
        self.uploading = true;
        var progressdomnode = document.getElementById(progressdomnodeid);
        var statusdomnode = document.getElementById(statusdomnodeid);
        self.filecount = fileinput.files.length;
        self.uploadedfiles = 0;
        var formatstring = "++##Uploading file {0} of {1}##--";
        progressdomnode.style.width = "0%";
        statusdomnode.innerHTML = formatstring.replace(/\{0\}/g, 1).replace(/\{1\}/g, self.filecount);
        self.uploadFiles(fileinput.files, 0, function(index) {
            self.uploadedfiles = index + 1;
            if ((self.uploadedfiles) < self.filecount) {
                statusdomnode.innerHTML = formatstring.replace(/\{0\}/g, self.uploadedfiles + 1).replace(/\{1\}/g, self.filecount);
            } else {
                statusdomnode.innerHTML = "++##Upload complete.##--";
                completioncallback();
            }
        }, function(progress) {
            var filepercent = 100 / self.filecount;
            progressdomnode.style.width = (self.uploadedfiles * filepercent + progress * filepercent / 100) + "%";
        });
    },
    /**
     * Cancels the current upload process.
     */
    cancelUpload : function() {
        this.uploading = false;
        this.xhr.abort();
    },
    /**
     * Handles zooming the photo list when moving the range input.
     * The zoom is performed by dynamically changing the CSS stylesheet
     * for the photo images.
     * 
     * @param {int} value Zoom factor between 0 and 100.
     */
    zoom : function(value) {
        var stylesheet = document.styleSheets[0];
        if (stylesheet.selectableborderrule) {
            stylesheet.deleteRule(stylesheet.selectableborderrule);
        }
        if (stylesheet.zoomrule) {
            stylesheet.deleteRule(stylesheet.zoomrule);
        }
        var lastindex = stylesheet.cssRules.length;
        var size = 80 + 2.4 * value;
        var margin = value * .1;
        stylesheet.insertRule("div.PhotoList > div > div > div > img { width:" + size + "px;height:" + size + "px;margin:" + margin + "px;box-shadow: 0px " + (value * .05) + "px " + (value * .1) + "px 0px rgba(0,0,0,0.5); }", lastindex);
        stylesheet.zoomrule = lastindex;
        stylesheet.insertRule("div.PhotoList.Selectable > div > div > div:after { margin: " + margin + "px;}", lastindex + 1);
        stylesheet.selectableborderrule = lastindex + 1;
    },
    /**
     * Triggered, when the user clicks on a photo and handles the selection
     * of the photo or the details view.
     * 
     * @param {Node} selectButton Button used for toggling the selection mode.
     */
    handleSelect : function(selectButton) {
        if (selectButton.isInSelectionMode) {
            // Select the photo
            selectButton.isInSelectionMode = false;
            selectButton.innerHTML = "++##Select##--";
            if (this.listNode) {
                this.listNode.isInSelectionMode = false;
                this.listNode.classList.remove("Selectable");
                var images = this.listNode.getElementsByTagName("img");
                for (var i in images) {
                    var image = images[i];
                    if (image.isSelected) {
                        image.isSelected = false;
                        image.parentNode.classList.remove("Selected");
                    }
                }
            }
        } else {
            // Show photo details
            selectButton.isInSelectionMode = true;
            selectButton.innerHTML = "++##Cancel##--";
            if (this.listNode) {
                this.listNode.isInSelectionMode = true;
                this.listNode.classList.add("Selectable");
            }
        }
    },
    /**
     * Handles the deletion of the currently selected photos. Shows a progress
     * dialog for the deletion with a cancel button where the user can cancel
     * the deletion after each file.
     * 
     * @param {function} completioncallback Function is called when the
     * deletion progress is complete or was cancelled. The only parameter
     * is true when the deletion was completed for all files or false when the
     * deletion was cancelled and there are still files to be uploaded.
     */
    deleteSelectedPhotos : function(completioncallback) {
        var self = this;
        self.messagetemplate = '++##Deleting photo {0} of {1}##--';
        self.canceldeletion = false;
        /**
         * Recursive function for performing a file deletion. Takes the first
         * element of the given array, performs its deletion, removes it from
         * the array and calls itself with the remaining array.
         * 
         * @param {array} imagenodes Array of image nodes containing the photos
         * to be deleted.
         */
        var doDeleteRequest = function(imagenodes) {
            if (self.canceldeletion || imagenodes.length < 1) {
                self.progressdialog.close();
                completioncallback(!self.canceldeletion && imagenodes.length < 1);
                return;
            }
            var processedfilecount = self.filecount - imagenodes.length;
            var percent = processedfilecount * 100 / self.filecount;
            self.progressdialog.setProgress(percent, self.messagetemplate.replace("{0}", processedfilecount + 1).replace("{1}", self.filecount));
            var imagenode = imagenodes.shift();
            Helper.doRequest('deletePhoto', { id : imagenode.photoId }, function(response) {
                var monthlistnode = imagenode.parentNode.parentNode;
                monthlistnode.removeChild(imagenode.parentNode);
                if (monthlistnode.childNodes.length < 1) {
                    monthlistnode.parentNode.parentNode.removeChild(monthlistnode.parentNode);
                }
                self.listNode.selectedImageCount--;
                doDeleteRequest(imagenodes);
            }, null);
        };
        var images = this.listNode.getElementsByTagName('img');
        var imagenodes = [];
        for (var i in images) {
            var image = images[i];
            if (image.isSelected) {
                imagenodes.push(image);
            }
        }
        self.filecount = imagenodes.length;
        self.progressdialog = Dialog.progress(self.messagetemplate.replace("{0}", 1).replace("{1}", self.filecount), function() {
            self.canceldeletion = true;
        });
        doDeleteRequest(imagenodes);
    },
	/**
	 * Shows a fullscreen preview of an image with a given ID.
	 * Contains a close button which closes the preview on click.
	 * Also contains two pr buttons to switch to previous and next
	 * images in the list without leaving the preview mode.
	 * 
	 * @param {Node} thumbimagenode DOM node of the thumb image where the user
	 * clicked on.
	 */
	showPreview : function(thumbimagenode) {
		var self = this;
		self.thumbimagenode = thumbimagenode;
		self.previewresizehandler = function() {
			self.previewcontainer.style.height = window.innerHeight + "px";
		};
		window.addEventListener("resize", self.previewresizehandler);
		self.previewcontainer = document.createElement("div");
		self.previewcontainer.classList.add("PhotoPreview");
		self.previewcontainer.style.height = window.innerHeight + "px";
		document.body.appendChild(self.previewcontainer);
		self.previewimage = document.createElement("img");
		self.previewimage.src = "images.php?type=preview&id=" + self.thumbimagenode.photoId;
		self.previewcontainer.appendChild(self.previewimage);
		var closebutton = document.createElement("button");
		closebutton.classList.add("Close");
		closebutton.addEventListener("click", function() {
			document.body.removeChild(self.previewcontainer);
			document.body.classList.remove("PhotoPreview");
			window.removeEventListener("resize", self.previewresizehandler);
		});
		self.previewcontainer.appendChild(closebutton);
		var previousbutton = document.createElement("button");
		previousbutton.classList.add("Previous");
		previousbutton.addEventListener("click", function() {
			var listnode = self.thumbimagenode.parentNode;
			var previouslistnode = null;
			if (listnode.previousSibling === null) {
				var previousmonthnode = listnode.parentNode.parentNode.previousSibling !== null ? listnode.parentNode.parentNode.previousSibling : listnode.parentNode.parentNode.parentNode.lastChild;
				previouslistnode = previousmonthnode.lastChild.lastChild;
			} else {
				previouslistnode = listnode.previousSibling;
			}
			var previousthumbimagenode = previouslistnode.firstChild;
			self.previewimage.src = "images.php?type=preview&id=" + previousthumbimagenode.photoId;
			self.thumbimagenode = previousthumbimagenode;
		});
		self.previewcontainer.appendChild(previousbutton);
		var nextbutton = document.createElement("button");
		nextbutton.classList.add("Next");
		nextbutton.addEventListener("click", function() {
			var listnode = self.thumbimagenode.parentNode;
			var nextlistnode = null;
			if (listnode.nextSibling === null) {
				var nextmonthnode = listnode.parentNode.parentNode.nextSibling !== null ? listnode.parentNode.parentNode.nextSibling : listnode.parentNode.parentNode.parentNode.firstChild;
				nextlistnode = nextmonthnode.lastChild.firstChild;
			} else {
				nextlistnode = listnode.nextSibling;
			}
			var nextthumbimagenode = nextlistnode.firstChild;
			self.previewimage.src = "images.php?type=preview&id=" + nextthumbimagenode.photoId;
			self.thumbimagenode = nextthumbimagenode;
		});
		self.previewcontainer.appendChild(nextbutton);
		document.body.classList.add("PhotoPreview");
	},
	/**
	 * Shows up a dialog where the user can select one photo from his photo
	 * list. Used in calendars.
	 * 
	 * @param {function} selectListener Callback to be called when the user
	 * has selected a photo. The only parameter id can be false when the user
	 * cancels the selection, null when the user selects nothing and presses
	 * Done or the id of the selected photo.
	 */
    selectPhoto : function(selectListener) {
		var self = this;
		self.selectedImage = null;
        Helper.doRequest("getPhotoList", null, function(response) {
			var content = document.createElement("div");
            var photoIdsList = JSON.parse(response);
            for (var yearmonth in photoIdsList) {
                for (var i = 0; i < photoIdsList[yearmonth].length; i++) {
                    var id = photoIdsList[yearmonth][i];
                    var container = document.createElement("div");
                    var image = document.createElement("img");
                    image.photoId = id;
                    image.addEventListener("click", function() {
						if (self.selectedImage) {
							self.selectedImage.parentNode.classList.remove("Selected");
						}
						if (self.selectedImage !== this) {
							this.parentNode.classList.add("Selected");
							self.selectedImage = this;
						} else {
							self.selectedImage = null;
						}
                    });
                    image.src = "images.php?type=thumb&id=" + id;
                    container.appendChild(image);
                    content.appendChild(container);
                }
            }
			Dialog.select(content, function() {
				selectListener(self.selectedImage ? self.selectedImage.photoId : null);
			}, function() {
				selectListener(false);
			});
        });
    }
};