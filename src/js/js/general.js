//depend:js/Is
var __applyToAll           = function (name, method)
{
	__applyTo("Array,Function", method);
	__applyToNonArraObject(name, method);
}
var __applyToNonArraObject = function (name, method)
{
	__applyTo("String,Number,Date,RegExp,Boolean", name, method);
}

var __applyTo = function (toName, name, method, voidWhenAlreadyDefined)
{
	if (toName.match(/,/i))
	{
		var ex = toName.split(/,/i);
		for (i = 0; i < ex.length; i++)
		{
			__apply(ex[i], name, method, voidWhenAlreadyDefined);
		}
	}
	else
	{
		__apply(toName, name, method, voidWhenAlreadyDefined);
	}
}
var __apply   = function (toName, name, method, voidWhenAlreadyDefined)
{
	if (toName == "Array")
	{
		if (typeof Array.prototype[name] == "undefined")
		{
			Array.prototype[name] = method;
		}
		else
		{
			if (!voidWhenAlreadyDefined)
			{
				console.error("Array.prototype." + name + " is already defined");
			}
		}
	}
	else if (toName == "String")
	{
		if (typeof String.prototype[name] == "undefined")
		{
			String.prototype[name] = method;
		}
		else
		{
			if (!voidWhenAlreadyDefined)
			{
				console.error("String.prototype." + name + " is already defined");
			}
		}
	}
	else if (toName == "Number")
	{
		if (typeof Number.prototype[name] == "undefined")
		{
			Number.prototype[name] = method;
		}
		else
		{
			if (!voidWhenAlreadyDefined)
			{
				console.error("Number.prototype." + name + " is already defined");
			}
		}
	}
	else if (toName == "Date")
	{
		if (typeof Date.prototype[name] == "undefined")
		{
			Date.prototype[name] = method;
		}
		else
		{
			if (!voidWhenAlreadyDefined)
			{
				console.error("Date.prototype." + name + " is already defined");
			}
		}
	}
	else if (toName == "Function")
	{
		if (typeof Function.prototype[name] == "undefined")
		{
			Function.prototype[name] = method;
		}
		else
		{
			if (!voidWhenAlreadyDefined)
			{
				console.error("Function.prototype." + name + " is already defined");
			}
		}
	}
	else if (toName == "RegExp")
	{
		if (typeof RegExp.prototype[name] == "undefined")
		{
			RegExp.prototype[name] = method;
		}
		else
		{
			if (!voidWhenAlreadyDefined)
			{
				console.error("RegExp.prototype." + name + " is already defined");
			}
		}
	}
	else if (toName == "Boolean")
	{
		if (typeof Boolean.prototype[name] == "undefined")
		{
			Boolean.prototype[name] = method;
		}
		else
		{
			if (!voidWhenAlreadyDefined)
			{
				console.error("Boolean.prototype." + name + " is already defined");
			}
		}
	}
}

function __isNumeric(value)
{
	if (value === null)
	{
		return false;
	}
	if (value === undefined)
	{
		return false;
	}
	if (value == "")
	{
		return false;
	}
	value = value.toString().replace(/,/g, ".");
	if (isNaN(value) == true)
	{
		return false;
	}
	else
	{
		return true;
	}
}

__applyTo("String,Number,Date", "matchAll", function (reg)
{
	var value   = this.valueOf().toString();
	var match;
	var matches = [];
	var c       = 0;
	var res;
	while (res != false)
	{
		match = reg.exec(value);
		res   = false;
		if (match)
		{
			value = value.substring(match.index + match[0].length);
			res   = match[0];
		}
		if (res != false)
		{
			matches.push(res);
		}
		else
		{
			res = false;
		}
	}
	return matches;
}, true);

function stripHTML(html)
{
	var tmp       = document.createElement("DIV");
	tmp.innerHTML = html;
	return tmp.textContent || tmp.innerText || "";
}

__applyTo("String,Number", "strip", function ()
{
	var val = this.valueOf();
	return stripHTML(val);
});
__applyTo("String,Number", "getMatch", function (regex, returnOnFalse)
{
	var val = this.valueOf().match(regex);
	if (val !== null)
	{
		return val[0];
	}
	return returnOnFalse;
});
__applyTo("String,Number", "isMatch", function (regex)
{
	var val = this.valueOf().match(regex);
	if (val !== null)
	{
		return true;
	}
	return false;
});
__applyTo("String,Number", "reverse", function (regex)
{
	return [].reduceRight.call(this, function (last, secLast) {return last + secLast});
});

__applyTo("String,Number", "deparam", function (vars)
{
	var querystring = this.valueOf();
	// remove any preceding url and split
	querystring     = querystring.substring(querystring.indexOf('?') + 1).split('&');
	var params      = {},
	    pair,
	    d           = decodeURIComponent;
	// march and parse
	for (var i = querystring.length - 1; i >= 0; i--)
	{
		pair               = querystring[i].split('=');
		params[d(pair[0])] = d(pair[1]);
	}
	return params;
});
if (!String.prototype.trim)
{
	String.prototype.trim = function ()
	{
		return this.replace(/^\s+|\s+$/g, '');
	};
}

Number.prototype.formatNumber = function (decPlaces, thouSeparator, decSeparator)
{
	decPlaces     = isNaN(decPlaces = Math.abs(decPlaces)) ? 2 : decPlaces;
	decSeparator  = decSeparator == undefined ? "." : decSeparator;
	thouSeparator = thouSeparator == undefined ? "," : thouSeparator;
	
	var n = this.toFixed(decPlaces);
	if (decPlaces)
	{
		var i = n.substr(0, n.length - (decPlaces + 1));
		var j = decSeparator + n.substr(-decPlaces);
	}
	else
	{
		i = n;
		j = '';
	}
	
	function reverse(str)
	{
		var sr = '';
		for (var l = str.length - 1; l >= 0; l--)
		{
			sr += str.charAt(l);
		}
		return sr;
	}
	
	if (parseInt(i))
	{
		i = reverse(reverse(i).replace(/(\d{3})(?=\d)/g, "$1" + thouSeparator));
	}
	return i + j;
};
String.prototype.toCamelCase  = function ()
{
	return this.replace(/-([a-z])/g, function ($0, $1)
	{
		return $1.toUpperCase();
	}).replace('-', '');
};
String.prototype.replaceAll   = function (str1, str2, ignore)
{
	return this.replace(new RegExp(str1.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g, "\\$&"), (ignore ? "gi" : "g")), (typeof (str2) == "string") ? str2.replace(/\$/g, "$$$$") : str2);
}

__applyToNonArraObject("toFloat", function ()
{
	return parseFloat(this.valueOf());
});

__applyToNonArraObject("toInt", function ()
{
	return parseInt(this.valueOf());
});

__applyToNonArraObject("toBool", function ()
{
	var v = this.valueOf();
	if (v === "true" || v === true || v === "1" || v === 1)
	{
		return true;
	}
	else if (v === "false" || v === false || v === "0" || v === 0)
	{
		return false;
	}
	else if (parseInt(v) > 0)
	{
		return true;
	}
	else if (v)
	{
		return true;
	}
	return false;
});
__applyTo("String", "jq", function ()
{
	return jQuery(this.valueOf());
});

function __toNumber(v)
{
	if (typeof v == "undefined")
	{
		v = this.valueOf();
	}
	if (v == undefined)
	{
		return false;
	}
	v = v.toString().replace(/,/g, ".");
	if (__isNumeric(v))
	{
		if (v.match(/\./))
		{
			return parseFloat(v);
		}
		else
		{
			return parseInt(v);
		}
	}
	return 0;
};
__applyToNonArraObject("toNumber", __toNumber);
__applyToNonArraObject("toNegative", function ()
{
	var v = __toNumber(this.valueOf());
	if (v <= 0)
	{
		return v;
	}
	return v * -1;
});
__applyToNonArraObject("toPositive", function ()
{
	var v = __toNumber(this.valueOf());
	if (v <= 0)
	{
		v = v * (-1);
	}
	return v;
});
/**
 * Useful javascript extensions
 * @author gen Taliaru
 */
__applyTo("String,Number", "math", function (string)
{
	var origValue = this.valueOf().toNumber();
	string        = string.toString().replace(" ", "");
	$op           = string.substring(0, 1);
	mathValue     = __toNumber(string.substring(1));
	newValue      = mathValue;
	if ($op == "+")
	{
		newValue = origValue + mathValue;
	}
	else if ($op == "*")
	{
		newValue = origValue * mathValue;
	}
	else if ($op == "-")
	{
		newValue = origValue - mathValue;
	}
	else if ($op == "%")
	{
		newValue = (origValue * mathValue) / 100;
	}
	else if ($op == "/" || $op == ":")
	{
		newValue = origValue / mathValue;
	}
	return newValue;
});

var ObjectProps = function (items)
{
	this.items = items;
};
jQuery.extend(ObjectProps.prototype, {
	
	exists: function (name)
	{
		if (typeof this.items[name] == "undefined")
		{
			return false;
		}
		return true;
	},
	
	set: function (name, val)
	{
		this.items[name] = val;
	},
	
	get: function (name, onNotFound)
	{
		if (!this.exists(name))
		{
			return onNotFound;
		}
		return this.items[name];
	}
});

/**
 * This functions is to debug variables.
 * NB! Only works with browsers that have a console, usualy firefox with firebug
 * @param {Mixed} value
 */
function debug()
{
	if (typeof console == "object" && console && console.log)
	{
		console.log.apply(console, arguments);
	}
};

function stackTrace()
{
	var err = new Error();
	return err.stack;
}

var getBodyActualWidth = function ()
{
	jQuery("body").css("overflow", "hidden");
	var ww = $(window).width();
	jQuery("body").css("overflow", "");
	return ww;
};

__applyToAll("debug", function ()
{
	debug(this.valueOf());
});

__applyTo("String,Number", "stripHTML", function ()
{
	return this.replace(/(<([^>]+)>)/ig, "");
});