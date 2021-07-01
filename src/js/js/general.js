//depend:js/Is
function debug()
{
	if (typeof console == "object" && console && console.log)
	{
		console.log.apply(console, arguments);
	}
}

function stackTrace()
{
	let err = new Error();
	return err.stack;
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
	if (value === "")
	{
		return false;
	}
	value = value.toString().replace(/,/g, ".");
	if (isNaN(value))
	{
		return false;
	}
	else
	{
		return true;
	}
}

let __checkProtoMethod         = function (protoName, method)
{
	if (Object.prototype.toString.call(protoName) === '[object Array]')
	{
		protoName.forEach(function (pn)
		{
			__checkProtoMethod(pn, method);
		});
	}
	else if (protoName.indexOf(',') >= 0)
	{
		protoName.split(',').forEach(function (pn)
		{
			__checkProtoMethod(pn, method);
		});
	}
	else if (protoName === "Array" && typeof Array.prototype[method] != "undefined")
	{
		console.error("Array.prototype." + method + " is already defined");
	}
	else if (protoName === "String" && typeof String.prototype[method] != "undefined")
	{
		console.error("String.prototype." + method + " is already defined");
	}
	else if (protoName === "Number" && typeof Number.prototype[method] != "undefined")
	{
		console.error("Number.prototype." + method + " is already defined");
	}
	else if (protoName === "Date" && typeof Date.prototype[method] != "undefined")
	{
		console.error("Date.prototype." + method + " is already defined");
	}
	else if (protoName === "Function" && typeof Function.prototype[method] != "undefined")
	{
		console.error("Function.prototype." + method + " is already defined");
	}
	else if (protoName === "RegExp" && typeof RegExp.prototype[method] != "undefined")
	{
		console.error("RegExp.prototype." + method + " is already defined");
	}
	else if (protoName === "Boolean" && typeof Boolean.prototype[method] != "undefined")
	{
		console.error("Boolean.prototype." + method + " is already defined");
	}
}
let __checkNonArrayProtoMethod = function (method)
{
	__checkProtoMethod('String,Number,Date,RegExp,Boolean', method);
}

__checkProtoMethod('String,Number', 'stripHTML');
String.prototype.stripHTML = function ()
{
	//return this.replace(/(<([^>]+)>)/ig, "");
	let tmp       = document.createElement("DIV");
	tmp.innerHTML = this.valueOf();
	return tmp.textContent || tmp.innerText || "";
};

__checkProtoMethod('String,Number', 'isMatch');
String.prototype.isMatch = Number.prototype.isMatch = function (regex)
{
	let val = this.valueOf().toString().match(regex);
	return val !== null;
	
};

__checkProtoMethod('String,Number', 'reverse');
String.prototype.reverse = Number.prototype.reverse = function ()
{
	return [].reduceRight.call(this, function (last, secLast) {return last + secLast});
};

__checkProtoMethod('String,Number', 'deparam');
String.prototype.deparam = Number.prototype.deparam = function ()
{
	let querystring = this.valueOf();
	// remove any preceding url and split
	querystring     = querystring.substring(querystring.indexOf('?') + 1).split('&');
	let params      = {},
	    pair,
	    d           = decodeURIComponent;
	// march and parse
	for (let i = querystring.length - 1; i >= 0; i--)
	{
		pair               = querystring[i].split('=');
		params[d(pair[0])] = d(pair[1]);
	}
	return params;
};

__checkProtoMethod('String,Number', 'inArray');
String.prototype.inArray = Number.prototype.inArray = function (array)
{
	if (Object.prototype.toString.call(array) !== '[object Array]')
	{
		console.warn('Fookie says: value is not proper array, converting to array');
		array = [array];
	}
	return array.inArray(this.valueOf());
};

__checkProtoMethod('String,Number', 'math');
String.prototype.math = Number.prototype.math = function (string)
{
	let origValue = this.valueOf().toNumber();
	string        = string.toString().replace(" ", "");
	let $op       = string.substring(0, 1);
	let mathValue = __toNumber(string.substring(1));
	let newValue  = mathValue;
	if ($op === "+")
	{
		newValue = origValue + mathValue;
	}
	else if ($op === "*")
	{
		newValue = origValue * mathValue;
	}
	else if ($op === "-")
	{
		newValue = origValue - mathValue;
	}
	else if ($op === "%")
	{
		newValue = (origValue * mathValue) / 100;
	}
	else if ($op === "/" || $op === ":")
	{
		newValue = origValue / mathValue;
	}
	return newValue;
};

if (!String.prototype.trim)
{
	String.prototype.trim = function ()
	{
		let whitespace = "[\\x20\\t\\r\\n\\f]";
		let rtrim      = new RegExp("^" + whitespace + "+|((?:^|[^\\\\])(?:\\\\.)*)" + whitespace + "+$", "g");
		return this.replace(rtrim, "");
	};
}
if (!Number.prototype.trim)
{
	Number.prototype.trim = function ()
	{
		return this.toString().trim();
	}
}

__checkProtoMethod('Number', 'format');
Number.prototype.format = function (decPlaces, thouSeparator, decSeparator)
{
	decPlaces     = isNaN(decPlaces = Math.abs(decPlaces)) ? 2 : decPlaces;
	decSeparator  = decSeparator || ".";
	thouSeparator = thouSeparator || ",";
	
	let n = this.toFixed(decPlaces);
	let i;
	let j;
	if (decPlaces)
	{
		i = n.substr(0, n.length - (decPlaces + 1));
		j = decSeparator + n.substr(-decPlaces);
	}
	else
	{
		i = n;
		j = '';
	}
	
	function reverse(str)
	{
		let sr = '';
		for (let l = str.length - 1; l >= 0; l--)
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

__checkProtoMethod('String', 'toCamelCase');
String.prototype.toCamelCase = function ()
{
	return this.replace(/(?:^\w|[A-Z]|\b\w)/g, function (word, index)
	{
		return index === 0 ? word.toLowerCase() : word.toUpperCase();
	}).replace(/\s+/g, '');
}

if (!String.prototype.replaceAll)
{
	String.prototype.replaceAll = function (str1, str2, ignore)
	{
		return this.replace(new RegExp(str1.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g, "\\$&"), (ignore ? "gi" : "g")), (typeof (str2) == "string") ? str2.replace(/\$/g, "$$$$") : str2);
	}
}

__checkNonArrayProtoMethod('toFloat');
String.prototype.toFloat = Number.prototype.toFloat = Date.prototype.toFloat = RegExp.prototype.toFloat = Boolean.prototype.toFloat = function ()
{
	return parseFloat(this.valueOf());
};

__checkNonArrayProtoMethod('toInt');
String.prototype.toInt = Number.prototype.toInt = Date.prototype.toInt = RegExp.prototype.toInt = Boolean.prototype.toInt = function ()
{
	return parseInt(this.valueOf());
};

__checkNonArrayProtoMethod('toBool');
String.prototype.toBool = Number.prototype.toBool = Date.prototype.toBool = RegExp.prototype.toBool = function ()
{
	let v = this.valueOf();
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
};

function __toNumber(v)
{
	if (v === undefined)
	{
		return null;
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
}

__checkNonArrayProtoMethod('toNumber');
__checkNonArrayProtoMethod('toNegative');
String.prototype.toNumber   = Number.prototype.toNumber = Date.prototype.toNumber = RegExp.prototype.toNumber = Boolean.prototype.toNumber = function ()
{
	return __toNumber(this.valueOf());
};
String.prototype.toNegative = Number.prototype.toNegative = Date.prototype.toNegative = RegExp.prototype.toNegative = Boolean.prototype.toNegative = function ()
{
	let v = __toNumber(this.valueOf());
	if (v <= 0)
	{
		return v;
	}
	return v * -1;
};

__checkNonArrayProtoMethod('toPositive');
String.prototype.toPositive = Number.prototype.toPositive = Date.prototype.toPositive = RegExp.prototype.toPositive = Boolean.prototype.toPositive = function ()
{
	let v = __toNumber(this.valueOf());
	if (v <= 0)
	{
		v = v * (-1);
	}
	return v;
};

__checkNonArrayProtoMethod('toArray');
String.prototype.toArray = Number.prototype.toArray = Date.prototype.toArray = RegExp.prototype.toArray = Boolean.prototype.toArray = function ()
{
	let v = this.valueOf();
	if (Object.prototype.toString.call(v) === '[object Array]')
	{
		return v;
	}
	return v.toString().trim().split(/,/g);
};


