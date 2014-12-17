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
	 * Performs an asynchronous ajax request to the ajax.php page.
	 * 
	 * @param {string} action Action to perform on the server
	 * @param {array} postdata Associative array of post data to send to the server. Provide null if not used.
	 * @param {function} completecallback Callback which is called, when the request is completed. Contains the response of the request.
	 * @param {function} progresscallback Callback for file uploads which is called multiply with upload progress information. Provide null if not used.
	 */
	doRequest : function(action, postdata, completecallback, progresscallback) {
		var xhr = new XMLHttpRequest();
		var formdata = new FormData();
		xhr.open("POST", "ajax.php", true);
		if (typeof progresscallback === 'function') {
			xhr.upload.addEventListener("progress", progresscallback, false);
		}
		if (typeof completecallback === 'function') {
			xhr.onreadystatechange = function() {
				if (xhr.readyState === 4 && xhr.status === 200) {
					completecallback(xhr.responseText);
				}
			};
		}
		formdata.append("action", action);
		if (postdata !== null) {
			for (var key in postdata) {
				formdata.append(key, postdata[key]);
			}
		}
		xhr.send(formdata);
	},
	
	/**
	 * Constructs the list of all photos of the user.
	 * 
	 * @param {string} listNodeId ID of the DOM element where to put the photos
	 * list into.
	 */
	getList : function(listNodeId) {
		var self = this;
		var listNode = document.getElementById(listNodeId);
		this.doRequest("getPhotoList", null, function(response) {
			var photoIdsList = JSON.parse(response);
			for (var i = 0; i < photoIdsList.length; i++) {
				var container = document.createElement("div");
				var image = document.createElement("img");
				// TODO: Löschen-Funktion nur temporär
				image.photoId = photoIdsList[i];
				image.addEventListener("click", function() {
					window.open("images.php?type=preview&id=" + this.photoId);
				});
				image.src = "images.php?type=thumb&id=" + photoIdsList[i];
				container.appendChild(image);
				listNode.appendChild(container);
			}
		});
	},
	
	uploadFiles : function(files, index, fileuploadedcallback, fileprogresscallback) {
		var self = this;
		if (files.length <= index) {
			return;
		}
		this.doRequest("uploadPhoto", {file : files[index]}, function() {
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
	
	processUpload : function(fileinput, allprogressdomnodeid, fileprogressdomnodeid, statusdomnodeid) {
		var self = this;
		var allprogressdomnode = document.getElementById(allprogressdomnodeid);
		var fileprogressdomnode = document.getElementById(fileprogressdomnodeid);
		var statusdomnode = document.getElementById(statusdomnodeid);
		var filecount = fileinput.files.length;
		var formatstring = "++##Uploading file {0} of {1}##--";
		allprogressdomnode.style.width = "0%";
		fileprogressdomnode.style.width = "0%";
		statusdomnode.innerHTML = formatstring.replace(/\{0\}/g, 1).replace(/\{1\}/g, filecount);
		self.uploadFiles(fileinput.files, 0, function(index) {
			var progress = Math.round(((index + 1) * 100) / filecount);
			if ((index + 1) < filecount) {
				statusdomnode.innerHTML = formatstring.replace(/\{0\}/g, index + 2).replace(/\{1\}/g, filecount);
			} else {
				statusdomnode.innerHTML = "++##Upload complete##--";
			}
			allprogressdomnode.style.width = progress + "%";
		}, function(progress) {
			fileprogressdomnode.style.width = progress + "%";
		});
	}
};