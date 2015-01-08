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
	 * Initializes the calendar with new empty data and the year set to the 
	 * current year. Used when making a new calendar.
	 */
	init : function() {
		this.months = [];
		for (var i = 0; i < 13; i++) { // In 0 is the cover image
			this.months.push({
				image : { // TODO: Initialize id with null
					id : 141, // ID of the image to show
					x : 0, // X-Distance of the center of the image to the center of the placeholder relative to the placeholder width (-0.5 means that the center of the image is on the left border of the placeholder)
					y : 0, // Y-Distance of the center of the image to the center of the placeholder relative to the placeholder width (yes, not relative to the height!)
					scale : 1 } // Scale factor of the image relative to the placeholder width, 1 = the image has the width of the placeholder
			});
		}
		this.year = new Date().getFullYear();
		this.currentmonth = 1; // 0 means that the cover is selected, else the month starting with 1 is selected
	},
	/**
	 * Shows the image of the currently selected month in the placeholder and
	 * updates it position and scale.
	 */
	showCurrentMonthImage : function() {
		var image = this.months[this.currentmonth].image;
		this.upperdiv.style.backgroundImage = image.id === null ? "none" : "url(../../photos/images.php?type=preview&id=" + image.id + ")";
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
		self.upperdiv.addEventListener("mousedown", function(e) {
			this.movestartx = e.offsetX;
			this.movestarty = e.offsetY;
		}, false);
		self.upperdiv.addEventListener("mousemove", function(e) {
			if (e.which === 1) {
				self.move(e.offsetX - this.movestartx, e.offsetY - this.movestarty);
				this.movestartx = e.offsetX;
				this.movestarty = e.offsetY;
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
		}, false);
		self.calendardiv.appendChild(self.upperdiv);
		var lowerdiv = document.createElement("div");
		lowerdiv.classList.add("Lower");
		self.calendardiv.appendChild(lowerdiv);
		self.ringsimg = document.createElement("img");
		self.ringsimg.src = "../../static/img/calendar/a4-rings.svg";
		self.calendardiv.appendChild(self.ringsimg);
		window.addEventListener("resize", function() { self.handleResize(); });
		self.handleResize();
		self.showCurrentMonthImage();
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
		var ratiowithrings = (297 - 10) / 210; // A4 + 10mm height for rings
		if ((contentwidth * ratiowithrings) < contentheight) {
			self.calendardiv.style.width = contentwidth + "px";
			self.calendardiv.style.height = ((contentwidth * ratiowithrings) | 0) + "px";
		} else {
			self.calendardiv.style.width = ((contentheight / ratiowithrings) | 0) + "px";
			self.calendardiv.style.height = contentheight + "px";
		}
		var topmargin = (self.calendardiv.offsetWidth / 21) | 0;
		self.calendardiv.style.paddingTop = topmargin + "px";
		self.updateImage();
	}
};