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
 * Contains client side functions for createing good looking dialogs
 */
Dialog = {
    
    /**
     * Shows a confirm dialog with a message, a Yes and a No button. The
     * callback function is called when the user clicks a button.
     * 
     * @param {String} question Question to show as message in the dialog.
     * @param {function} callback Function which is called when the user quits
     * the confirm dialog. The only parameter is boolean. When the user clicks
     * on "Yes", the parameter is true, otherwise it is false.
     */
    confirm : function(question, callback) {
        var dialog = document.createElement("div");
        dialog.classList.add("Dialog");
        dialog.classList.add("Confirm");
        var message = document.createElement("div");
        message.classList.add("Message");
        message.innerHTML = question;
        dialog.appendChild(message);
        var donebutton = document.createElement("button");
        donebutton.classList.add("OK");
        donebutton.addEventListener("click", function() {
            document.body.removeChild(dialog);
            document.body.classList.remove("DialogOpen");
			if (typeof callback !== "undefined") {
				callback(true);
			}
        });
        donebutton.innerHTML = '++##Yes##--';
        dialog.appendChild(donebutton);
        var cancelbutton = document.createElement("button");
        cancelbutton.classList.add("Cancel");
        cancelbutton.addEventListener("click", function() {
            document.body.removeChild(dialog);
            document.body.classList.remove("DialogOpen");
			if (typeof callback !== "undefined") {
				callback(false);
			}
        });
        cancelbutton.innerHTML = '++##No##--';
        dialog.appendChild(cancelbutton);
        document.body.appendChild(dialog);
        document.body.classList.add("DialogOpen");
    },
    /**
     * Shows a progress dialog with a progress bar and a cancel button.
     * The cancelcallback function is called, when the user clicks on the
     * cancel button. The dialog contains the function setProgress() for
     * updating the progress and the function close() to close the dialog.
     * 
     * @param {string} message Message to be shown initially in the dialog.
     * @param {function} cancelcallback Function is called when the user clicks
     * on the cancel button.
     * @returns {Element|Dialog.progress.dialog} The dialog itself is returned
     * so that the caller has access to its setProgress() and close() functions.
     */
    progress : function(message, cancelcallback) {
        var dialog = document.createElement("div");
        dialog.classList.add("Dialog");
        dialog.classList.add("Progress");
        /**
         * Updates the progress of the dialog.
         * 
         * @param {int} percent Percentage of the progress bar between 0 and
         * 100.
         * @param {string} message Message to show in the dialog.
         */
        dialog.setProgress = function(percent, message) {
            this.progressdiv.style.width = percent + "%";
            this.messagediv.innerHTML = message;
        };
        /**
         * Closes the progress dialog.
         */
        dialog.close = function() {
            document.body.removeChild(this);
            document.body.classList.remove("DialogOpen");
        };
        dialog.messagediv = document.createElement("div");
        dialog.messagediv.classList.add("Message");
        dialog.messagediv.innerHTML = message;
        dialog.appendChild(dialog.messagediv);
        var progressbar = document.createElement("div");
        progressbar.classList.add("UploadProgress");
        dialog.appendChild(progressbar);
        dialog.progressdiv = document.createElement("div");
        dialog.progressdiv.classList.add("ProgressCompletion");
        dialog.progressdiv.style.width = "0%";
        progressbar.appendChild(dialog.progressdiv);
        var cancelbutton = document.createElement("button");
        cancelbutton.classList.add("Cancel");
        cancelbutton.addEventListener("click", function() {
			if (typeof cancelcallback !== "undefined") {
				cancelcallback();
			}
            dialog.close();
        });
        cancelbutton.innerHTML = '++##Cancel##--';
        dialog.appendChild(cancelbutton);
        document.body.appendChild(dialog);
        document.body.classList.add("DialogOpen");
        return dialog;
    },
	/**
	 * Opens a modal dialog for selecting something. Shows two buttons "Cancel" 
	 * and "Select".
	 * 
	 * @param {object} content Content DOM node to insert.
	 * @param {function} doneCallback Callback to be called ahen the dialog
	 * was closed with Done
	 * @param {function} cancelCallback Callback to be called ahen the dialog
	 * was closed with Cancel
	 */
    select : function(content, doneCallback, cancelCallback) {
        var dialog = document.createElement("div");
        dialog.classList.add("Dialog");
        dialog.classList.add("Select");
        content.classList.add("DialogContent");
        dialog.appendChild(content);
        var donebutton = document.createElement("button");
        donebutton.classList.add("Done");
        donebutton.addEventListener("click", function() {
            document.body.removeChild(dialog);
            document.body.classList.remove("DialogOpen");
			if (typeof doneCallback !== "undefined") {
				doneCallback();
			}
        });
        donebutton.innerHTML = '++##Done##--';
        dialog.appendChild(donebutton);
        var cancelbutton = document.createElement("button");
        cancelbutton.classList.add("Cancel");
        cancelbutton.addEventListener("click", function() {
            document.body.removeChild(dialog);
            document.body.classList.remove("DialogOpen");
			if (typeof cancelCallback !== "undefined") {
				cancelCallback();
			}
        });
        cancelbutton.innerHTML = '++##Cancel##--';
        dialog.appendChild(cancelbutton);
        document.body.appendChild(dialog);
        document.body.classList.add("DialogOpen");
    },
    info : function(message, callback) {
        var dialog = document.createElement("div");
        dialog.classList.add("Dialog");
        dialog.classList.add("Info");
        var messagediv = document.createElement("div");
        messagediv.classList.add("Message");
        messagediv.innerHTML = message;
        dialog.appendChild(messagediv);
        var okbutton = document.createElement("button");
        okbutton.classList.add("OK");
        okbutton.addEventListener("click", function() {
            document.body.removeChild(dialog);
            document.body.classList.remove("DialogOpen");
			if (typeof callback !== "undefined") {
				callback();
			}
        });
        okbutton.innerHTML = '++##OK##--';
        dialog.appendChild(okbutton);
        document.body.appendChild(dialog);
        document.body.classList.add("DialogOpen");
    }

};