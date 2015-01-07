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
 * Contains client side functions for handling calendars. Designed as class Calendar
 */
Calendar = {
	renderCalendarPage : function(contentdivid, year, month) {
		var self = this;
		self.currentZoomFactor = 1;
		self.touchcount = 0;
		self.contentdiv = document.getElementById(contentdivid);
		self.calendardiv = document.createElement("div");
		self.calendardiv.classList.add("Calendar");
		self.contentdiv.appendChild(self.calendardiv);
		self.upperdiv = document.createElement("div");
		self.upperdiv.classList.add("Upper");
		self.upperdiv.setAttribute("style", " background: #ddd url(https://www.avorium.de/myphotostorage/photos/images.php?type=preview&id=141) no-repeat; background-size: 100%; ");
		self.upperdiv.posX = 0;
		self.upperdiv.posY = 0;
		self.upperdiv.addEventListener("touchstart", function() {
			self.touchcount++;
		}, false);
		self.upperdiv.addEventListener("touchend", function() {
			self.touchcount--;
		}, false);
		self.upperdiv.addEventListener("touchmove", function(e) {
			if (e.touches.length > 1) {
				var diff = e.touches[0].pageX - e.touches[1].pageX;
				self.zoomBackground(1.01);
			}
		}, false);
		self.upperdiv.addEventListener("mousewheel", function(e) {
			self.zoomBackground(self.currentZoomFactor * (e.wheelDelta > 0 ? 1.02 : 0.98), e.offsetX, e.offsetY);
		}, false);
		self.upperdiv.addEventListener("mousedown", function(e) {
			self.upperdiv.movestartpointerx = e.offsetX;
			self.upperdiv.movestartpointery = e.offsetY;
			self.upperdiv.movestartposx = self.upperdiv.posX;
			self.upperdiv.movestartposy = self.upperdiv.posY;
			self.upperdiv.ismoving = true;
		}, false);
		self.upperdiv.addEventListener("mouseup", function() {
			self.upperdiv.ismoving = false;
		}, false);
		self.upperdiv.addEventListener("mousemove", function(e) {
			if (self.upperdiv.ismoving) {
				var diffx = e.offsetX - self.upperdiv.movestartpointerx;
				var diffy = e.offsetY - self.upperdiv.movestartpointery;
				self.upperdiv.posX = self.upperdiv.movestartposx + diffx;
				self.upperdiv.posY = self.upperdiv.movestartposy + diffy;
				self.upperdiv.style.backgroundPosition = self.upperdiv.posX + "px " + self.upperdiv.posY + "px";
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
	},
	zoomBackground : function(factor, pointerx, pointery) {
		var self = this;
		var oldfactor = self.currentZoomFactor;
		self.currentZoomFactor = factor;
		if (self.currentZoomFactor < 1) {
			self.currentZoomFactor = 1;
		}
		else if (self.currentZoomFactor > 100) {
			self.currentZoomFactor = 100;
		}
		self.upperdiv.style.backgroundSize = (self.currentZoomFactor * 100) + "%";
		var f = self.currentZoomFactor / oldfactor;
		var newx = f * self.upperdiv.posX + f * pointerx - pointerx;
		var newy = f * self.upperdiv.posY + f * pointery - pointery;
		self.upperdiv.posX = newx;
		self.upperdiv.posY = newy;
		self.upperdiv.style.backgroundPosition = (-newx | 0) + "px " + (-newy | 0) + "px";
	},
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
	}
};