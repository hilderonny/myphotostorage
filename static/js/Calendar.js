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
 * Contains client side functions for handling calendars.
 * Designed as class Calendar.
 */
Calendar = {
	/**
	 * @type Boolean Defines whether the calendar was changed since open or last save.
	 */
	ischanged : false,
	/**
	 * Initializes the calendar with new empty data and the year set to the 
	 * current year. Used when making a new calendar.
	 * Also renders the new calendar into the given DIV.
	 * 
	 * @param {string} contentdivid ID of the DIV to render the content into.
	 */
	init : function(contentdivid) {
		this.id = null;
		this.months = [];
		for (var i = 0; i < 13; i++) { // In 0 is the cover image
			this.months.push({
				image : {
					id : null, // ID of the image to show
					x : 0, // X-Distance of the center of the image to the center of the placeholder relative to the placeholder width (-0.5 means that the center of the image is on the left border of the placeholder)
					y : 0, // Y-Distance of the center of the image to the center of the placeholder relative to the placeholder width (yes, not relative to the height!)
					scale : 1 } // Scale factor of the image relative to the placeholder width, 1 = the image has the width of the placeholder
			});
		}
		this.year = new Date().getFullYear();
		this.currentmonth = 0; // 0 means that the cover is selected, else the month starting with 1 is selected
		document.body.classList.add("New");
		this.renderCalendarPage(contentdivid);
	},
	/**
	 * Updates thew calendar to show the content of the current month (name,
	 * days and image). Also handles showing the cover page and loads the image.
	 */
	showCurrentMonth : function() {
		if (this.currentmonth > 0) {
			var monthnames = ["++##January##--", "++##February##--", "++##March##--", "++##April##--", "++##May##--", "++##June##--", "++##July##--", "++##August##--", "++##September##--", "++##October##--", "++##November##--", "++##December##--" ];
			this.monthnamediv.innerHTML = monthnames[this.currentmonth - 1];
			var daysinmonth = new Date(this.year, this.currentmonth, 0).getDate();
			var offset = (new Date(this.year, this.currentmonth - 1, 1).getDay() + 6) % 7;
			var tds = this.monthtbody.getElementsByTagName("td");
			for (var i = 0; i < tds.length; i++) {
				tds[i].innerHTML = (i >= offset && i < daysinmonth + offset) ? i - offset + 1 : "";
			}			
			this.calendardiv.classList.remove("Cover");
		} else {
			this.calendardiv.classList.add("Cover");
			this.titlediv.innerHTML = this.year;
		}
		this.previousbutton.style.display = this.currentmonth > 0 ? "inherit" : "none";
		this.nextbutton.style.display = this.currentmonth < 12 ? "inherit" : "none";
		this.showCurrentMonthImage();
	},
	/**
	 * Shows the image of the currently selected month in the placeholder and
	 * updates it position and scale.
	 */
	showCurrentMonthImage : function() {
		var image = this.months[this.currentmonth].image;
		this.upperdiv.style.backgroundImage = image.id === null ? "none" : "url(images.php?type=preview&id=" + image.id + ")";
		this.updateImage();
	},
	/**
	 * Updates the display of the current month image depending on the settings
	 * in the image model.
	 */
	updateImage : function() {
		var currentimage = this.months[this.currentmonth].image;
		this.upperdiv.style.backgroundSize = (currentimage.scale * 100) + "%";
		var pbounds = this.upperdiv.getBoundingClientRect();
		var pw = pbounds.width;
		var is = currentimage.scale;
		var icpx = currentimage.x;
		var ix = pw * ( icpx + (1 - is) / 2 );
		var icpy = currentimage.y;
		var iy = pw * ( icpy + (1 - is) / 2 );
		this.upperdiv.style.backgroundPosition = ix + "px " + iy + "px";
	},
	/**
	 * Creates a DOM structure for the current calendar within the Node with
	 * the given ID.
	 * 
	 * @param {string} contentdivid ID of the node where to put the calendar into.
	 */
	renderCalendarPage : function(contentdivid) {
		var self = this;
		self.contentdiv = document.getElementById(contentdivid);
		self.calendardiv = document.createElement("div");
		self.calendardiv.classList.add("Calendar");
		self.contentdiv.appendChild(self.calendardiv);
		self.upperdiv = document.createElement("div");
		self.upperdiv.classList.add("Upper");
		self.upperdiv.addEventListener("mousewheel", function(e) {
			self.scaleAt(e.offsetX, e.offsetY, self.months[self.currentmonth].image.scale * (e.wheelDelta > 0 ? 1.02 : 0.98));
		}, false);
		self.upperdiv.addEventListener("DOMMouseScroll", function(e) { // For Firefox
			self.scaleAt(e.layerX, e.layerY, self.months[self.currentmonth].image.scale * (e.detail < 0 ? 1.02 : 0.98));
		}, false);
		if (typeof MSGesture !== "undefined") { // Zooming for mobile IE
			self.iegesture = new MSGesture();
			self.iegesture.target = self.upperdiv;
			self.upperdiv.addEventListener("pointerdown", function(e) { // For mobile IE
				self.iegesture.addPointer(e.pointerId);
			});
			self.upperdiv.addEventListener("MSGestureChange", function(e) { // For mobile IE
				if (e.scale !== 1) {
					self.scaleAt(e.offsetX, e.offsetY, self.months[self.currentmonth].image.scale * e.scale);
				}
			}, false);
		}
		self.upperdiv.addEventListener("mousedown", function(e) {
			this.movestartx = e.clientX;
			this.movestarty = e.clientY;
			this.mousedown = true;
		}, false);
		self.upperdiv.addEventListener("mouseup", function() {
			this.mousedown = false;
		}, false);
		self.upperdiv.addEventListener("mousemove", function(e) {
			if (this.mousedown) {
				self.move(e.clientX - this.movestartx, e.clientY - this.movestarty);
				this.movestartx = e.clientX;
				this.movestarty = e.clientY;
			}
		}, false);
		self.upperdiv.addEventListener("touchstart", function(e) {
			if(e.touches.length === 1) {
				this.touch1startx = e.touches[0].pageX;
				this.touch1starty = e.touches[0].pageY;
				this.touchmode = 'move';
			} else if(e.touches.length === 2) {
				this.touch2startx = e.touches[1].pageX;
				this.touch2starty = e.touches[1].pageY;
				this.currentscale = self.months[self.currentmonth].image.scale;
				this.touchmode = 'scale';
			}
			e.preventDefault();
		}, false);
		self.upperdiv.addEventListener("touchmove", function(e) {
			if (e.touches.length === 1 && this.touchmode === 'move') {
				self.move(e.touches[0].pageX - this.touch1startx, e.touches[0].pageY - this.touch1starty);
				this.touch1startx = e.touches[0].pageX;
				this.touch1starty = e.touches[0].pageY;
			} else if (e.touches.length === 2) {
				var olddiffx = this.touch2startx - this.touch1startx;
				var olddiffy = this.touch2starty - this.touch1starty;
				var olddiff = Math.sqrt(olddiffx * olddiffx + olddiffy * olddiffy);
				var newdiffx = e.touches[0].pageX - e.touches[1].pageX;
				var newdiffy = e.touches[0].pageY - e.touches[1].pageY;
				var newdiff = Math.sqrt(newdiffx * newdiffx + newdiffy * newdiffy);
				var factor = this.currentscale * Math.abs(newdiff / olddiff);
				var rect = this.getBoundingClientRect();
				var centerx = (e.touches[0].pageX + e.touches[1].pageX - 2 * rect.left) / 2;
				var centery = (e.touches[0].pageY + e.touches[1].pageY - 2 * rect.top) / 2;
				self.scaleAt(centerx, centery, factor);
			}
			e.preventDefault();
		}, false);
		self.calendardiv.appendChild(self.upperdiv);
		self.ringsimg = document.createElement("img");
		self.ringsimg.src = "../static/img/calendar/a4-rings.svg";
		self.calendardiv.appendChild(self.ringsimg);
		var selectbutton = document.createElement("button");
		selectbutton.classList.add("Select");
		selectbutton.addEventListener("click", function() {
			Photos.selectPhoto(function(id) {
				if (id) {
					var currentimage = self.months[self.currentmonth].image;
					currentimage.id = id;
					currentimage.x = 0;
					currentimage.y = 0;
					currentimage.scale = 1;
					self.showCurrentMonthImage();
				}
			});
		});
		self.calendardiv.appendChild(selectbutton);
		self.lowerdiv = document.createElement("div");
		self.lowerdiv.classList.add("Lower");
		self.calendardiv.appendChild(self.lowerdiv);
		self.monthnamediv = document.createElement("div");
		self.monthnamediv.classList.add("Month");
		self.lowerdiv.appendChild(self.monthnamediv);
		var table = document.createElement("table");
		self.lowerdiv.appendChild(table);
		self.monththead = document.createElement("thead");
		table.appendChild(self.monththead);
		var trh = document.createElement("tr");
		self.monththead.appendChild(trh);
		var daynames = ["++##Monday##--", "++##Tuesday##--", "++##Wednesday##--", "++##Thursday##--", "++##Friday##--", "++##Saturday##--", "++##Sunday##--"];
		for (var i = 0; i < 7; i++) {
			var th = document.createElement("th");
			trh.appendChild(th);
			th.innerHTML = daynames[i];
		}
		self.monthtbody = document.createElement("tbody");
		table.appendChild(self.monthtbody);
		for (var i = 0; i < 6; i++) {
			var trb = document.createElement("tr");
			self.monthtbody.appendChild(trb);
			for (var j = 0; j < 7; j++) {
				var td = document.createElement("td");
				trb.appendChild(td);
			}
		}
		self.previousbutton = document.createElement("button");
		self.previousbutton.classList.add("Previous");
		self.previousbutton.addEventListener("click", function() {
			self.currentmonth--;
			self.showCurrentMonth();
		});
		self.calendardiv.appendChild(self.previousbutton);
		self.nextbutton = document.createElement("button");
		self.nextbutton.classList.add("Next");
		self.nextbutton.addEventListener("click", function() {
			self.currentmonth++;
			self.showCurrentMonth();
		});
		self.calendardiv.appendChild(self.nextbutton);
		self.titlediv = document.createElement("div");
		self.titlediv.classList.add("Title");
		self.titlediv.innerHTML = self.year;
		self.calendardiv.appendChild(self.titlediv);
		window.addEventListener("resize", function() { self.handleResize(); });
		self.handleResize();
		self.showCurrentMonth();
	},
	/**
	 * Moves the image depending on the given X and Y differences. The given
	 * differences are transformed to relative movements for the image model.
	 * The image is then redrawn.
	 * 
	 * @param {int} dx Number of pixels to move the image in X direction
	 * @param {int} dy Number of pixels to move the image in Y direction
	 */
	move : function(dx, dy) {
		this.ischanged = true;
		var currentimage = this.months[this.currentmonth].image;
		var pbounds = this.upperdiv.getBoundingClientRect();
		var diffx = dx / pbounds.width;
		var diffy = dy / pbounds.width;
		currentimage.x += diffx;
		currentimage.y += diffy;
		this.updateImage();
	},
	/**
	 * Scales the model of the image. The center of the scale is defined with px
	 * and py. The current scale factor is replaced with the given one and the
	 * image position is recalculated and updated.
	 * 
	 * @param {int} mx X coordinate of the scaling center relative to the placeholder.
	 * @param {int} my Y coordinate of the scaling center relative to the placeholder.
	 * @param {float} nis Absolute factor to scale the image.
	 */
	scaleAt : function(mx, my, nis) {
		this.ischanged = true;
		var currentimage = this.months[this.currentmonth].image;
		var pbounds = this.upperdiv.getBoundingClientRect();
		var pw = pbounds.width;
		var is = currentimage.scale;
		var icpx = currentimage.x;
		var mxr = mx / pw - 0.5;
		var nicpx = nis / is * (icpx - mxr) + mxr;
		var icpy = currentimage.y;
		var myr = my / pw - 0.5;
		var nicpy = nis / is * (icpy - myr) + myr;
		currentimage.scale = nis;
		currentimage.x = nicpx;
		currentimage.y = nicpy;
		this.updateImage();
	},
	/**
	 * Handles the resizing of the page and resizes the calendar keeping
	 * its aspect ratio. Also redraws the image for the new size.
	 */
	handleResize : function() {
		var self = this;
		var contentwidth = self.contentdiv.offsetWidth;
		var contentheight = self.contentdiv.offsetHeight;
		var ratiowithrings = (297 + 10) / 210; // A4 + 10mm height for rings
		if ((contentwidth * ratiowithrings) < contentheight) {
			self.calendardiv.style.width = contentwidth + "px";
			self.calendardiv.style.height = ((contentwidth * ratiowithrings) | 0) + "px";
		} else {
			self.calendardiv.style.width = ((contentheight / ratiowithrings) | 0) + "px";
			self.calendardiv.style.height = contentheight + "px";
		}
		var calendarwidth = self.calendardiv.offsetWidth;
		self.calendardiv.style.paddingTop = (calendarwidth / 21) + "px";
		self.monthnamediv.style.fontSize = (calendarwidth * 0.04) + "px";
		self.titlediv.style.fontSize = (calendarwidth * 0.15) + "px";
		var ths = self.monththead.getElementsByTagName("th");
		for (var i = 0; i < ths.length; i++) {
			ths[i].style.fontSize = (calendarwidth * 0.02) + "px";
		}
		var tds = self.monthtbody.getElementsByTagName("td");
		for (var i = 0; i < tds.length; i++) {
			tds[i].style.fontSize = (calendarwidth * 0.015) + "px";
		}
		self.updateImage();
	},
	/**
	 * Saves the current calendar to the server by transferring it as JSON via
	 * POST. When storing a new calendar, the new id is returned and stored in this class.
	 * 
	 * @param {function} callback Called when the transfer was successful.
	 */
	save : function(callback) {
		var self = this;
		var data = {
			id : this.id,
			year : this.year,
			months : this.months
		};
		Helper.doRequest("saveCalendar", {calendar : JSON.stringify(data)}, function(response) {
			self.id = response;
			self.ischanged = false;
			Dialog.info("++##Calendar was saved successfully.##--");
			document.body.classList.remove("New");
			if (typeof callback !== "undefined") {
				callback();
			}
		});
	},
	/**
	 * Retrieve the list of calendars of the current user and renders it into 
	 * the DIV with the given ID.
	 */
    getList : function(listNodeId) {
        var self = this;
        self.listNode = document.getElementById(listNodeId);
        Helper.doRequest("getCalendarList", null, function(response) {
			var calendars = JSON.parse(response);
			for (var i = 0; i < calendars.length; i++) {
				var calendar = calendars[i];
				var calendardiv = document.createElement("div");
				calendardiv.calendarId = calendar.id;
				calendardiv.addEventListener("click", function() {
					window.location.href = "calendar-edit.php?id=" + this.calendarId;
				});
				self.listNode.appendChild(calendardiv);
				calendardiv.style.backgroundImage = "url(images.php?type=preview&id=" + calendar.image.id + ")";
				calendardiv.style.backgroundSize = (calendar.image.scale * 100) + "%";
				var pbounds = calendardiv.getBoundingClientRect();
				var pw = pbounds.width;
				var is = calendar.image.scale;
				var icpx = calendar.image.x;
				var ix = pw * ( icpx + (1 - is) / 2 );
				var icpy = calendar.image.y;
				var iy = pw * ( icpy + (1 - is) / 2 );
				calendardiv.style.backgroundPosition = ix + "px " + iy + "px";
				var titlediv = document.createElement("div");
				titlediv.classList.add("Title");
				console.log(calendar);
				titlediv.innerHTML = calendar.year;
				calendardiv.appendChild(titlediv);
				titlediv.style.fontSize = (pw * 0.15) + "px";
			}
        });
	},
	/**
	 * Loads the calendar with the given ID and renders it into the given DIV.
	 * 
	 * @param {string} id ID of the calendar to load
	 * @param {string} contentdivid ID of the DIV where to render the calendar into
	 */
	load : function(id, contentdivid) {
		var self = this;
        Helper.doRequest("getCalendar", {id : id}, function(response) {
			var calendar = JSON.parse(response);
			self.id = calendar.id;
			self.months = calendar.months;
			self.year = calendar.year;
			self.currentmonth = 0;
			self.renderCalendarPage(contentdivid);
		});
    },
	/**
	 * Deletes the currently loaded calendar from the database. New calendars
	 * which were not saved before, are simply deleted locally.
	 * 
	 * @param {function} callback Called when the deletion process was performed.
	 */
	delete : function(callback) {
		if (this.id) {
			console.log('DEL');
	        Helper.doRequest("deleteCalendar", {id : this.id}, function() {
				if (typeof callback !== "undefined") {
					callback();
				}
			});
		}
	},
	/**
	 * Shows the settings dialog for the current calender and handles settings
	 * changes.
	 */
	showSettings : function() {
		var self = this;
		var contentdiv = document.createElement("div");
		var label = document.createElement("label");
		label.innerHTML = "++##Year##--";
		contentdiv.appendChild(label);
		var yearinput = document.createElement("select");
		var currentyear = new Date().getFullYear();
		for (var i = currentyear - 50; i <= currentyear + 50; i++) {
			var option = document.createElement("option");
			option.value = i;
			option.innerHTML = i;
			if (i === self.year) {
				option.setAttribute("checked", "checked");
			}
			yearinput.appendChild(option);
		}
		yearinput.value = self.year;
		contentdiv.appendChild(yearinput);
		Dialog.properties(contentdiv, function(done) {
			if (done) {
				self.year = yearinput.options[yearinput.selectedIndex].value;
				self.showCurrentMonth();
			}
		});
	}
};
