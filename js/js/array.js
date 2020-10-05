__applyTo("Array", "chunk", function (chunkSize)
{
	var R = [];
	for (var i = 0, len = this.length; i < len; i += chunkSize)
	{
		R.push(this.slice(i, i + chunkSize));
	}
	return R;
});
__applyTo("Array", "each", Array.prototype.forEach);

__applyTo("String,Number", "inArray", function (array)
{
	return array.inArray(this.valueOf());
});

__applyTo("Array", "inArray", function (needle)
{
	var needleArr = [];
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
});
__applyTo("Array", "prepend", function (value)
{
	var newArray = this.slice();
	newArray.unshift(value);
	return newArray;
});

__applyTo("Array", "remove", function (from, to)
{
	var rest    = this.slice((to || from) + 1 || this.length);
	this.length = from < 0 ? this.length + from : from;
	return this.push.apply(this, rest);
});

__applyTo("Array", "last", function (from, to)
{
	var arr = this;
	return arr[arr.length - 1];
});

__applyTo("Array", "filterKey", function (key, values)
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
			if (!$.isArray(values))
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
});

__applyTo("Array", "keySort", function (key, asc, asNumber)
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
});

__applyTo("Array", "toObject", function (objectKeyFields)
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
});

__applyTo("Array", "distinct", function ()
{
	return this.filter(function (value, index, self)
	{
		return self.indexOf(value) === index;
	});
});