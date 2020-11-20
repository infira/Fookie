var __dateMethods =
    {
	    nrOfDaysInGivenMonth: function ()
	    {
		    var year       = this.getYear(), month = this.getMonth()
		    var monthStart = new Date(year, month, 1);
		    var monthEnd   = new Date(year, month + 1, 1);
		    return (monthEnd - monthStart) / (1000 * 60 * 60 * 24)
	    },
	    lastDayOfTheMonth   : function ()
	    {
		    var newDate = new Date(this.getTime());
		    newDate.setDate(newDate.nrOfDaysInGivenMonth());
		    return newDate;
	    },
	    firstDay            : function ()
	    {
		    var date = new Date(this.getTime());
		    date.setDate(1);
		    return date;
	    },
	    addSeconds          : function (amount)
	    {
		    return new Date(this.getTime() + (amount * 1000));
	    },
	    removeSeconds       : function (amount)
	    {
		    return new Date(this.getTime() - (amount * 1000));
	    },
	
	    addMinutes   : function (amount)
	    {
		    return new Date(this.getTime() + (amount * 60 * 1000));
	    },
	    removeMinutes: function (amount)
	    {
		    return new Date(this.getTime() - (amount * 60 * 1000));
	    },
	    addHours     : function (amount)
	    {
		    return new Date(this.getTime() + (amount * 60 * 60 * 1000));
	    },
	    removeHours  : function (amount)
	    {
		    return new Date(this.getTime() - (amount * 60 * 60 * 1000));
	    },
	    addDays      : function (amount)
	    {
		    return this.addHours(amount * 24);
	    },
	    removeDays   : function (amount)
	    {
		    return this.removeHours(amount * 24);
	    },
	    addWeeks     : function (amount)
	    {
		    return this.addDays(amount * 7);
	    },
	    removeWeeks  : function (amount)
	    {
		    return this.removeDays(amount * 7);
	    },
	    addMonths    : function (amount)
	    {
		    this.setMonth(this.getMonth() + amount);
		    return this;
	    },
	    removeMontsh : function (amount)
	    {
		    this.setMonth(this.getMonth() - amount);
		    return this;
	    },
	    addYears     : function (amount)
	    {
		    this.setFullYear(this.getFullYear() + amount);
		    return this;
	    },
	    removeYears  : function (amount)
	    {
		    this.setFullYear(this.getFullYear() - amount);
		    return this;
	    }
    }
for (m in __dateMethods)
{
	if (!Date.prototype[m])
	{
		Date.prototype[m] = __dateMethods[m];
	}
}