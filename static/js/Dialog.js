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
	
    confirm : function(question, callback) {
        var dialog = document.createElement("div");
        dialog.classList.add("Dialog");
        dialog.classList.add("Confirm");
        var message = document.createElement("div");
        message.classList.add("Message");
        message.innerHTML = question;
        dialog.appendChild(message);
        var okbutton = document.createElement("button");
        okbutton.classList.add("OK");
        okbutton.addEventListener("click", function() {
            document.body.removeChild(dialog);
            document.body.classList.remove("DialogOpen");
            callback(true);
        });
        okbutton.innerHTML = '++##Yes##--';
        dialog.appendChild(okbutton);
        var cancelbutton = document.createElement("button");
        cancelbutton.classList.add("Cancel");
        cancelbutton.addEventListener("click", function() {
            document.body.removeChild(dialog);
            document.body.classList.remove("DialogOpen");
            callback(false);
        });
        cancelbutton.innerHTML = '++##No##--';
        dialog.appendChild(cancelbutton);
        document.body.appendChild(dialog);
        document.body.classList.add("DialogOpen");
    },
    
    progress : function(message, cancelcallback) {
        var dialog = document.createElement("div");
        dialog.classList.add("Dialog");
        dialog.classList.add("Progress");
        dialog.setProgress = function(percent, message) {
            this.progressdiv.style.width = percent + "%";
            this.messagediv.innerHTML = message;
        };
        dialog.close = function() {
            document.body.removeChild(dialog);
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
            cancelcallback();
            dialog.close();
        });
        cancelbutton.innerHTML = '++##Cancel##--';
        dialog.appendChild(cancelbutton);
        document.body.appendChild(dialog);
        document.body.classList.add("DialogOpen");
        return dialog;
    }

};