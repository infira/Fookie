if (typeof Array.isArray === "undefined")
{
	Array.prototype.isArray = function (v)
	{
		return Object.prototype.toString.call(v) === '[object Array]';
	}
}

__checkProtoMethod('Array', 'chunk');
Array.prototype.chunk = function (chunkSize)
{
	var R = [];
	for (var i = 0, len = this.length; i < len; i += chunkSize)
	{
		R.push(this.slice(i, i + chunkSize));
	}
	return R;
};
__checkProtoMethod('Array', 'each');
Array.prototype.each = Array.prototype.forEach;

__checkProtoMethod('Array', 'inArray');
Array.prototype.inArray = function (needle)
{
	let needleArr = [];
	if (Object.prototype.toString.call(needle) === '[object Array]')
	{
		needleArr = needle;
	}
	else
	{
		needleArr.push(needle);
	}
	for (var i = 0; i < this.length; i++)
	{
		for (var ni = 0; ni < needleArr.length; ni++)
		{
			if (this[i] == needleArr[ni])
			{
				return true;
			}
		}
	}
	return false;
};
__checkProtoMethod('Array', 'prepend');
Array.prototype.prepend = function (value)
{
	var newArray = this.slice();
	newArray.unshift(value);
	return newArray;
};

__checkProtoMethod('Array', 'removeValue');
Array.prototype.removeValue = function (removeable, removeAllOccurence)
{
	let arr            = this;
	removeAllOccurence = typeof removeAllOccurence == 'undefined' ? false : removeAllOccurence;
	let i;
	if (removeAllOccurence)
	{
		i = 0;
		while (i < arr.length)
		{
			if (arr[i] === removeable)
			{
				arr.splice(i, 1);
			}
			else
			{
				++i;
			}
		}
	}
	else
	{
		i = arr.indexOf(removeable);
		if (i > -1)
		{
			arr.splice(i, 1);
		}
	}
	return arr;
};

__checkProtoMethod('Array', 'remove');
Array.prototype.remove = function (from, to)
{
	var rest    = this.slice((to || from) + 1 || this.length);
	this.length = from < 0 ? this.length + from : from;
	return this.push.apply(this, rest);
};

__checkProtoMethod('Array', 'last');
Array.prototype.last = function ()
{
	var arr = this;
	return arr[arr.length - 1];
};

__checkProtoMethod('Array', 'filterKey');
Array.prototype.filterKey = function (key, values)
{
	var filterArr;
	if (typeof key != "undefined" && typeof values != "undefined")
	{
		filterArr = [key, values];
	}
	else
	{
		filterArr = key;
	}
	var newArray = [];
	var isOk,
	    fc       = 0;
	this.each(function (Obj)
	{
		isOk = false;
		fc   = 0;
		filterArr.each(function (filter)
		{
			key    = filter[0];
			values = filter[1];
			if (!Array.isArray(values))
			{
				values = [values];
			}
			if (values.inArray(Obj[key]))
			{
				fc++;
			}
		});
		if (fc == filterArr.length)
		{
			newArray.push(Obj);
		}
	});
	return newArray;
};

__checkProtoMethod('Array', 'keySort');
Array.prototype.keySort = function (key, asc, asNumber)
{
	var __keysrt = function (key, asc)
	{
		return function (a, b)
		{
			if (asNumber)
			{
				return asc ? (parseFloat(a[key]) - parseFloat(b[key])) : (parseFloat(b[key]) - parseFloat(a[key]));
			}
			else
			{
				return asc ? ~~(a[key] > b[key]) : ~~(a[key] < b[key]);
			}
		}
	}
	return this.sort(__keysrt(key, asc));
};

__checkProtoMethod('Array', 'toObject');
Array.prototype.toObject = function (objectKeyFields)
{
	var obj = {};
	this.each(function (Item)
	{
		var objKeyName = [];
		objectKeyFields.split(",").each(function (keyName)
		{
			objKeyName.push(Item[keyName]);
		});
		objKeyName      = objKeyName.join("_");
		obj[objKeyName] = Item;
	});
	return obj;
};

__checkProtoMethod('Array', 'distinct');
Array.prototype.distinct = function ()
{
	return this.filter(function (value, index, self)
	{
		return self.indexOf(value) === index;
	});
};