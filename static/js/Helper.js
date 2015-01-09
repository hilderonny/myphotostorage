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
 * Contains useful helper functions
 */
Helper = {

    /**
     * Performs an asynchronous ajax request to the ajax.php page.
     * 
     * @param {string} action Action to perform on the server
     * @param {array} postdata Associative array of post data to send to the server. Provide null if not used.
     * @param {function} completecallback Callback which is called, when the request is completed. Contains the response of the request.
     * @param {function} progresscallback Callback for file uploads which is called multiply with upload progress information. Provide null if not used.
     */
    doRequest : function(action, postdata, completecallback, progresscallback) {
        var self = this;
        self.xhr = new XMLHttpRequest();
        var formdata = new FormData();
        self.xhr.open("POST", "ajax.php", true);
        if (typeof progresscallback === 'function') {
            self.xhr.upload.addEventListener("progress", progresscallback, false);
        }
        if (typeof completecallback === 'function') {
            self.xhr.onreadystatechange = function() {
                if (self.xhr.readyState === 4 && self.xhr.status === 200) {
                    completecallback(self.xhr.responseText);
                }
            };
        }
        formdata.append("action", action);
        if (postdata !== null) {
            for (var key in postdata) {
                formdata.append(key, postdata[key]);
            }
        }
        self.xhr.send(formdata);
    }
};