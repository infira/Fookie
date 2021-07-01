__checkProtoMethod('Function', 'delay');
Function.prototype.delay = function (time, args)
{
	if (time <= 0)
	{
		this.apply(this, args);
	}
	else
	{
		window.setTimeout(this.bind(this, args), time);
	}
	return this;
};

__checkProtoMethod('Function', 'repeat');
Function.prototype.repeat = function (interval, stopAt, callAgainAfterStop)
{
	var rFunc    = this;
	var intevarl = window.setInterval(rFunc, interval);
	var stopper  = function ()
	{
		window.clearInterval(intevarl);
		if (callAgainAfterStop)
		{
			rFunc();
		}
	}
	stopper.delay(stopAt);
};

__checkProtoMethod('Function', 'callback');
Function.prototype.callback = function (callback, callbackScope, callbackArguments)
{
	var f           = this;
	var newFunction = function ()
	{
		f.apply(this, arguments);
		if (callbackScope)
		{
			callback.apply(callbackScope, callbackArguments);
		}
		else
		{
			callback();
		}
	}
	return newFunction;
};

__checkProtoMethod('Function', 'extend');
Function.prototype.extend = function (name, value)
{
	if (typeof name == "object")
	{
		for (var i in name)
		{
			if (typeof name[i] == "function")
			{
				this.prototype[i] = name[i];
			}
			else
			{
				console.error("You can only extend prototype with functions");
				console.log("name", i);
				console.log("value", name[i]);
				console.trace();
			}
		}
	}
	else if (typeof value == "function")
	{
		if (typeof value == "function")
		{
			this.prototype[name] = value;
		}
		else
		{
			console.error("You can only extend prototype with functions");
		}
	}
};

__checkProtoMethod('Function', 'clone');
Function.prototype.clone = function ()
{
	var that = this;
	var temp = function temporary() { return that.apply(this, arguments); };
	for (var key in this)
	{
		if (this.hasOwnProperty(key))
		{
			temp[key] = this[key];
		}
	}
	return temp;
};

__checkProtoMethod('Function', 'eBind');
Function.prototype.eBind = function (obj, args, appendArgs)
{
	var fn = this;
	if (!Is.func(fn))
	{
		return fn;
	}
	return function ()
	{
		var callArgs = args || arguments;
		if (appendArgs === true)
		{
			callArgs = Array.prototype.slice.call(arguments, 0);
			callArgs = callArgs.concat(args);
		}
		else if (Is.number(appendArgs))
		{
			callArgs      = Array.prototype.slice.call(arguments, 0);
			// copy arguments first
			var applyArgs = [appendArgs, 0].concat(args);
			// create method call params
			Array.prototype.splice.apply(callArgs, applyArgs);
			// splice them in
		}
		return fn.apply(obj || window, callArgs);
	};
};

__checkProtoMethod('Function', 'pass');
Function.prototype.pass = function ()
{
	var args = arguments,
	    func = this;
	return function ()
	{
		return func.apply(this, args);
	};
};

__checkProtoMethod('Function', 'add');
Function.prototype.add = function (newFunc)
{
	var args = arguments,
	    func = this;
	return function ()
	{
		func.apply(this, args);
		return newFunc.apply(null, args);
	};
};