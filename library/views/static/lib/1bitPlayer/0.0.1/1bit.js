// 1 Bit Audio Player v1.4
// See http://1bit.markwheeler.net for documentation and updates

function OneBit(pluginPath) {
	this.pluginPath = pluginPath || '1bit.swf';
	this.color = false;
	this.background = '#FFFFFF';
	this.playerSize = false;
	this.position = 'after';
	this.analytics = false;
	this.wrapperClass = 'onebit_mp3';
	this.playerCount = 1;
	this.flashVersion = 9;
	this.specify = function(key, value) {
		if (key == "color") {
			this.color = value
		}
		if (key == "background") {
			this.background = value
		}
		if (key == "playerSize") {
			this.playerSize = value
		}
		if (key == "position") {
			this.position = value
		}
		if (key == "analytics") {
			this.analytics = value
		}
	};
	this.apply = function(selector) {
		var links = this.getElementsBySelector(selector);
		for (var i = 0; i < links.length; i++) {
			if (this.hasClass(links[i].parentNode, this.wrapperClass)) {
				continue
			}
			if (links[i].href.substr(links[i].href.length - 4) != '.mp3') {
				continue
			}
			this.insertPlayer(links[i])
		}
	};
	this.insertPlayer = function(elem) {
		if (!this.playerSize) {
			this.autoPlayerSize = Math.floor(elem.scrollHeight * 0.65)
		}
		if (!this.color) {
			this.autoColor = this.getStyle(elem, 'color');
			if (this.autoColor.substr(0, 1) == '#' && this.autoColor.length == 4) {
				this.autoColor = this.autoColor.substr(0, 2) + '0' + this.autoColor.substr(2, 1) + '0' + this.autoColor.substr(3, 1) + '0'
			}
			if (this.autoColor.substr(0, 1) != '#') {
				this.autoColor = this.autoColor.substr(4, this.autoColor.indexOf(')') - 4);
				var rgbSplit = new Array();
				rgbSplit = this.autoColor.split(', ');
				this.autoColor = '#' + this.convertColor(Number(rgbSplit[2]), Number(rgbSplit[1]), Number(rgbSplit[0]))
			}
		}
		var playerWrapper = document.createElement('span');
		this.addClass(playerWrapper, this.wrapperClass);
		var hook_id = 'oneBitInsert_' + this.playerCount;
		var span = document.createElement('span');
		span.setAttribute('id', hook_id);
		elem.parentNode.insertBefore(playerWrapper, elem);
		if (this.position == 'before') {
			playerWrapper.appendChild(span);
			playerWrapper.innerHTML += '&nbsp;';
			playerWrapper.appendChild(elem)
		} else {
			playerWrapper.appendChild(elem);
			playerWrapper.innerHTML += '&nbsp;';
			playerWrapper.appendChild(span)
		}
		if (!this.playerSize) {
			this.insertPlayerSize = this.autoPlayerSize
		} else {
			this.insertPlayerSize = this.playerSize
		}
		var so = new SWFObject(this.pluginPath, hook_id, this.insertPlayerSize, this.insertPlayerSize, this.flashVersion, this.background);
		if (this.background == 'transparent') {
			so.addParam('wmode', 'transparent')
		}
		if (!this.color) {
			so.addVariable('foreColor', this.autoColor)
		} else {
			so.addVariable('foreColor', this.color)
		}
		so.addVariable('analytics', this.analytics);
		so.addVariable('filename', elem.href);
		so.write(hook_id);
		this.playerCount++
	};
	this.getStyle = function(el, styleProp) {
		if (el.currentStyle) {
			var value = el.currentStyle[styleProp]
		} else {
			var value = document.defaultView.getComputedStyle(el, null).getPropertyValue(styleProp)
		}
		return value
	};
	this.convertColor = function(red, green, blue) {
		var decColor = red + 256 * green + 65536 * blue;
		return decColor.toString(16)
	};
	this.getElementsBySelector = function(all_selectors) {
		var selected = new Array();
		if (!document.getElementsByTagName) return selected;
		all_selectors = all_selectors.replace(/\s*([^\w])\s*/g, "$1");
		var selectors = all_selectors.split(",");
		var getElements = function(context, tag) {
				if (!tag) tag = '*';
				var found = new Array;
				for (var a = 0, len = context.length; con = context[a], a < len; a++) {
					var eles;
					if (tag == '*') eles = con.all ? con.all : con.getElementsByTagName("*");
					else eles = con.getElementsByTagName(tag);
					for (var b = 0, leng = eles.length; b < leng; b++) found.push(eles[b])
				}
				return found
			};
		COMMA: for (var i = 0, len1 = selectors.length; selector = selectors[i], i < len1; i++) {
			var context = new Array(document);
			var inheriters = selector.split(" ");
			SPACE: for (var j = 0, len2 = inheriters.length; element = inheriters[j], j < len2; j++) {
				var left_bracket = element.indexOf("[");
				var right_bracket = element.indexOf("]");
				var pos = element.indexOf("#");
				if (pos + 1 && !(pos > left_bracket && pos < right_bracket)) {
					var parts = element.split("#");
					var tag = parts[0];
					var id = parts[1];
					var ele = document.getElementById(id);
					if (!ele || (tag && ele.nodeName.toLowerCase() != tag)) {
						continue COMMA
					}
					context = new Array(ele);
					continue SPACE
				}
				pos = element.indexOf(".");
				if (pos + 1 && !(pos > left_bracket && pos < right_bracket)) {
					var parts = element.split('.');
					var tag = parts[0];
					var class_name = parts[1];
					var found = getElements(context, tag);
					context = new Array;
					for (var l = 0, len = found.length; fnd = found[l], l < len; l++) {
						if (fnd.className && fnd.className.match(new RegExp('(^|\s)' + class_name + '(\s|$)'))) context.push(fnd)
					}
					continue SPACE
				}
				if (element.indexOf('[') + 1) {
					if (element.match(/^(\w*)\[(\w+)([=~\|\^\$\*]?)=?['"]?([^\]'"]*)['"]?\]$/)) {
						var tag = RegExp.$1;
						var attr = RegExp.$2;
						var operator = RegExp.$3;
						var value = RegExp.$4
					}
					var found = getElements(context, tag);
					context = new Array;
					for (var l = 0, len = found.length; fnd = found[l], l < len; l++) {
						if (operator == '=' && fnd.getAttribute(attr) != value) continue;
						if (operator == '~' && !fnd.getAttribute(attr).match(new RegExp('(^|\\s)' + value + '(\\s|$)'))) continue;
						if (operator == '|' && !fnd.getAttribute(attr).match(new RegExp('^' + value + '-?'))) continue;
						if (operator == '^' && fnd.getAttribute(attr).indexOf(value) != 0) continue;
						if (operator == '$' && fnd.getAttribute(attr).lastIndexOf(value) != (fnd.getAttribute(attr).length - value.length)) continue;
						if (operator == '*' && !(fnd.getAttribute(attr).indexOf(value) + 1)) continue;
						else if (!fnd.getAttribute(attr)) continue;
						context.push(fnd)
					}
					continue SPACE
				}
				var found = getElements(context, element);
				context = found
			}
			for (var o = 0, len = context.length; o < len; o++) selected.push(context[o])
		}
		return selected
	};
	this.hasClass = function(elem, cls) {
		return elem.className.match(new RegExp('(\\s|^)' + cls + '(\\s|$)'))
	};
	this.addClass = function(elem, cls) {
		if (!this.hasClass(elem, cls)) elem.className += " " + cls
	};
	this.removeClass = function(elem, cls) {
		if (hasClass(elem, cls)) {
			var reg = new RegExp('(\\s|^)' + cls + '(\\s|$)');
			elem.className = ele.className.replace(reg, ' ')
		}
	};
	this.ready = function(func) {
		var oldonload = window.onload;
		if (typeof window.onload != 'function') {
			window.onload = func
		} else {
			window.onload = function() {
				if (oldonload) {
					oldonload()
				}
				func()
			}
		}
	}
};