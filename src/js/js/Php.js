var Php = new function ()
{
	/**
	 * Register ajax method
	 * @param {Object} config
	 */
	this.registerMethod = function (config)
	{
		if (!this[config.namespace])
		{
			this[config.namespace] = {};
		}
		this[config.namespace][config.method] = function (postParams)
		{
			var PhpM = new PhpAjaxExtendor();
			PhpM.init();
			PhpM.callMethod = config.method;
			PhpM.pathName = config.pathName;
			PhpM.setMethodArguments(arguments);
			return PhpM;
		};
	};
	
	/**
	 * Register multiple ajax methods per once
	 * @param {Array} methods
	 */
	this.registerMethods = function (methods)
	{
		for (var i = 0; i < methods.length; i++)
		{
			this.registerMethod(methods[i]);
		}
	};
	
	/**
	 * Register multiple ajax methods per once
	 * @param {Array} methods
	 */
	this.registerClassMethods = function (namespace, pathName, methods)
	{
		for (var i = 0; i < methods.length; i++)
		{
			this.registerMethod(
			{
				namespace: namespace,
				pathName: pathName,
				method: methods[i]
			});
		}
	};
}
