/*
 if (!Function.prototype.bind)
 {
 Function.prototype.bind = function(obj, args, appendArgs) {
 var fn = this;
 return function() {
 var callArgs = args || arguments;
 if (appendArgs === true)
 {
 callArgs = Array.prototype.slice.call(arguments, 0);
 callArgs = callArgs.concat(args);
 }
 else if (typeof appendArgs === 'number' && isFinite(appendArgs))
 {
 callArgs = Array.prototype.slice.call(arguments, 0);
 // copy arguments first
 var applyArgs = [appendArgs, 0].concat(args);
 // create method call params
 Array.prototype.splice.apply(callArgs, applyArgs);
 // splice them in
 }
 return fn.apply(obj || window, callArgs);
 };
 };
 }
 */
Function.prototype.delay       = function (time, args)
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
Function.prototype.repeat      = function (interval, stopAt, callAgainAfterStop)
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
Function.prototype.jqBindDelay = function (scope, extraArguments, delay)
{
	var jQueryF = this;
	var timeout;
	var newFunc = function ()
	{
		if (timeout)
		{
			clearTimeout(timeout);
			timeout = null;
		}
		var args = [$(this)]
		args.push(arguments[0]);
		if (typeof extraArguments == "object")
		{
			args = args.concat(extraArguments);
		}
		timeout = setTimeout(function ()
		{
			jQueryF.apply(scope, args);
		}, delay);
		return;
	}
	return newFunc;
};

Function.prototype.jqBind = function (scope, extraArguments)
{
	var jQueryF = this;
	var newFunc = function ()
	{
		var args = [$(this)]
		for (var i in arguments)
		{
			args.push(arguments[i]);
		}
		if (typeof extraArguments == "object")
		{
			args = args.concat(extraArguments);
		}
		return jQueryF.apply(scope, args);
	}
	return newFunc;
};

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
__applyTo("Function", "extend", function (name, value)
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
})
__applyTo("Function", "clone", function ()
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
});

Function.prototype.jqDeleteCallback = function (scope, extraArguments)
{
	var jQueryF = this;
	var newFunc = function ()
	{
		var $el   = $(this);
		var event = arguments[0];
		var args  = [$el]
		args.push(event);
		if (typeof extraArguments == "object")
		{
			args = args.concat(extraArguments);
		}
		//if (Is.empty($el.getVal().trim()))
		//{
		var rowClass = extraArguments[0];
		var $row     = $el.parents(rowClass);
		$el.addClass("deleteBoxIsActive");
		$row.addClass("deleteBoxIsActive");
		var callback = function ($el, row)
		{
			window.setTimeout(function ()
			{
				$el.removeClass("deleteBoxIsActive");
				$row.removeClass("deleteBoxIsActive");
				$row.find(".deleteBox").remove();
			}, 200);
		};
		$el.unbind("blur.deleteBox").bind("blur.deleteBox", callback.bind(null, $el, $row))
		var ok = true;
		if (extraArguments[2] == true && parseInt($row.getField("count").getVal()) > 0)
		{
			ok = false;
		}
		if ($row.find(".deleteBox").length <= 0 && ok)
		{
			var $deleteBox = '<div class="deleteBox"><img src="images/icon_minus_red.png" /></div>';
			$el.parent().addClass("relative");
			$($deleteBox).insertAfter($el);
			
			var deleteCallback = function ($el, $row)
			{
				Utils.Msg.error("Oled kindel, et soovid kustutada", "Kustutatmine",
				{
					ok: {
						text : "Jah",
						cls  : "red_btn",
						click: function ()
						{
							$el.removeClass("deleteBoxIsActive");
							$row.removeClass("deleteBoxIsActive");
							jQueryF.apply(scope, args)
							Utils.Msg.close();
						}
					}
				});
			};
			$row.find(".deleteBox").bind("click", deleteCallback.bind(null, $el, $row));
			
			/*
			 if (event.type == "keyup" && event.keyCode == 8 && $el.data("jqDeleteCallbackLastKeyCode") === 8) //backspace
			 {
			 var t = new Date().getTime() - $el.data("jqDeleteCallbackLastKeyCodeTime");
			 if (t < 400)
			 {
			 Utils.Msg.error("Oled kindel, et soovid kustutada","Kustutatmine!")
			 $el.data("jqDeleteCallbackLastKeyCode", false);
			 $el.data("jqDeleteCallbackLastKeyCodeTime", 0);
			 return false;
			 }
			 
			 }
			 $el.data("jqDeleteCallbackLastKeyCode", event.keyCode);
			 $el.data("jqDeleteCallbackLastKeyCodeTime", new Date().getTime());
			 */
		}
		//}
		//else
		//{
		//$el.parent().find(".deleteBox").remove();
		//}
	}
	return newFunc;
};
Function.prototype.jqOnEnter        = function (scope, extraArguments)
{
	var jQueryF = this;
	var newFunc = function ()
	{
		var $el   = $(this);
		var event = arguments[0];
		var args  = [$el]
		args.push(event);
		if (typeof extraArguments == "object")
		{
			args = args.concat(extraArguments);
		}
		if (event.keyCode == 13)
		{
			jQueryF.apply(scope, args);
		}
	}
	return newFunc;
};
Function.prototype.eBind            = function (obj, args, appendArgs)
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
Function.prototype.pass             = function ()
{
	var args = arguments,
	    func = this;
	return function ()
	{
		return func.apply(this, args);
	};
};