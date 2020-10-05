/**
 * Useful class to check variable type casting
 * @author gen Taliaru
 */
var Is = new function ()
{

	/**
	 * Is value typeof object
	 * @param {Object} v
	 */
	this.empty = function (v)
	{
		if (typeof v === 'string' && isFinite(v))
		{
			if (!eval(v))
			{
				return true;
			}
		}
		if (!v || 0 === v.length || v == undefined)
		//if (typeof str == 'undefined' || !str || (str.length == 0) || (str == "") || (str.replace(/\s/g, "") == "") || (!/[^\s]/.test(str)) || (/^\s*$/.test(str))) 
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Is value typeof object
	 * @param {Object} v
	 */
	this.object = function (v)
	{
		return Object.prototype.toString.call(v) === '[object Object]';
	}
	
	/**
	 * Is value has some kind of value
	 * @param {Object} v
	 */
	this.defined = function (v)
	{
		return typeof v !== "undefined";
	}
	
	/**
	 * Is value typeof array
	 * @param {Object} v
	 */
	this.array = function (v)
	{
		return Object.prototype.toString.call(v) === '[object Array]';
	}
	
	/**
	 * Is value typeof number
	 * @param {Object} v
	 */
	this.number = function (v)
	{
		return typeof v === 'number' && isFinite(v);
	}
	
	/**
	 * Is value typeof string
	 * @param {Object} v
	 */
	this.string = function (v)
	{
		return typeof v === 'string';
	}
	
	/**
	 * Is value typeof function
	 * @param {Object} v
	 */
	this.func = function (v)
	{
		return Object.prototype.toString.call(v) === '[object Function]';
	}
	
	/**
	 * Is value email
	 * @param {Object} v
	 */
	this.email = function (email)
	{
		var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(email);
	}
	
	/**
	 * Is value email
	 * @param {Object} v
	 */
	this.validNumber = function (number)
	{
		return !isNaN(parseFloat(number)) && isFinite(number);
	}
	
	/**
	 * Is value css selector
	 * @param {Object} v
	 */
	this.cssSelector = function (v)
	{
		if (Is.string(v))
		{
			if (v == "#")
			{
				return false;
			}
			if (v.substr(0, 1) == "#" || v.substr(0, 1) == "." || v.substr(0, 1) == ":")
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Is value between numbers
	 * @param {Object} v
	 */
	this.between = function (what, from, to, op)
	{
		var _isBetween = function (w, f, t)
		{
			if (w >= f && w <= t)
			{
				return true;
			}
			return false;
		}
		if ($.isArray(what))
		{
			var ok = false;
			if (op == "and")
			{
				$.each(what, function (i, v)
				{
					if (!_isBetween(v, from, to))
					{
						ok = false;
						return false;
					}
				})
			}
			else
			{
				$.each(what, function (i, v)
				{
					if (_isBetween(v, from, to))
					{
						ok = true;
						return false;
					}
				})
			}
			return ok;
		}
		else
		{
			return _isBetween(what, from, to);
		}
		return false;
	}
};

