
var Gleez = Gleez || { 'settings': {}, 'behaviors': {}, 'locale': {} };

// Allow other JavaScript libraries to use $.
jQuery.noConflict();

(function ($) {

    Gleez.attachBehaviors = function (context, settings) {
	context = context || document;
	settings = settings || Gleez.settings;
	// Execute all of them.
	$.each(Gleez.behaviors, function () {
	    if ($.isFunction(this.attach)) {
		this.attach(context, settings);
	    }
	});
    };

    Gleez.detachBehaviors = function (context, settings, trigger) {
	context = context || document;
	settings = settings || Gleez.settings;
	trigger = trigger || 'unload';
	// Execute all of them.
	$.each(Gleez.behaviors, function () {
	    if ($.isFunction(this.detach)) {
		this.detach(context, settings, trigger);
	    }
	});
    };

    /**
     * Translate strings to the page language or a given language.
     *
     * See the documentation of the server-side t() function for further details.
     *
     * @param str
     *   A string containing the English string to translate.
     * @param args
     *   An object of replacements pairs to make after translation. Incidences
     *   of any key in this array are replaced with the corresponding value.
     *   Based on the first character of the key, the value is escaped and/or themed:
     *    - !variable: inserted as is
     *    - @variable: escape plain text to HTML (Gleez.checkPlain)
     *    - %variable: escape text and theme as a placeholder for user-submitted
     *      content (checkPlain + Gleez.theme('placeholder'))
     * @return
     *   The translated string.
     */
    Gleez.t = function (str, args) {
	// Fetch the localized version of the string.
	if (Gleez.locale.strings && Gleez.locale.strings[str]) {
	    str = Gleez.locale.strings[str];
	}

	if (args) {
	    // Transform arguments before inserting them.
	    for (var key in args) {
		switch (key.charAt(0)) {
		    // Escaped only.
		    case '@':
			args[key] = Gleez.checkPlain(args[key]);
			break;
		    // Pass-through.
		    case '!':
		    case ':':
			break;
		    // Escaped and placeholder.
		    case '%':
		    default:
			args[key] = Gleez.theme('placeholder', args[key]);
			break;
		}
	    str = str.replace(key, args[key]);
	    }
	}
	return str;
    }

    /**
     * Freeze the current body height (as minimum height). Used to prevent
     * unnecessary upwards scrolling when doing DOM manipulations.
     */
    Gleez.freezeHeight = function () {
	Gleez.unfreezeHeight();
	$('<div id="freeze-height"></div>').css({
	    position: 'absolute',
	    top: '0px',
	    left: '0px',
	    width: '1px',
	    height: $('body').css('height')
	}).appendTo('body');
    };

    /**
     * Unfreeze the body height.
     */
    Gleez.unfreezeHeight = function () {
	$('#freeze-height').remove();
    };

    /**
     * Encodes a Gleez path for use in a URL.
     *
     * For aesthetic reasons slashes are not escaped.
     */
    Gleez.encodePath = function (item, uri) {
	uri = uri || location.href;
	return encodeURIComponent(item).replace(/%2F/g, '/');
    };

    /**
     * Get the text selection in a textarea.
    */
    Gleez.getSelection = function (element) {
	if (typeof element.selectionStart != 'number' && document.selection) {
	    // The current selection.
	    var range1 = document.selection.createRange();
	    var range2 = range1.duplicate();
	    // Select all text.
	    range2.moveToElementText(element);
	    // Now move 'dummy' end point to end point of original range.
	    range2.setEndPoint('EndToEnd', range1);
	    // Now we can calculate start and end points.
	    var start = range2.text.length - range1.text.length;
	    var end = start + range1.text.length;
	    return { 'start': start, 'end': end };
	}
	return { 'start': element.selectionStart, 'end': element.selectionEnd };
    };

    /**
     * Build an error message from an Ajax response.
     */
    Gleez.ajaxError = function (xmlhttp, uri) {
	var statusCode, statusText, pathText, responseText, readyStateText, message;
	if (xmlhttp.status)
	{
	    statusCode = "\n" + Gleez.t("An AJAX HTTP error occurred.") +  "\n" + Gleez.t("HTTP Result Code: !status", {'!status': xmlhttp.status});
	}
	else
	{
	    statusCode = "\n" + Gleez.t("An AJAX HTTP request terminated abnormally.");
	}

	statusCode += "\n" + Gleez.t("Debugging information follows.");
	pathText = "\n" + Gleez.t("Path: !uri", {'!uri': uri} );
	statusText = '';
	// In some cases, when statusCode == 0, xmlhttp.statusText may not be defined.
	// Unfortunately, testing for it with typeof, etc, doesn't seem to catch that
	// and the test causes an exception. So we need to catch the exception here.
	try
	{
	    statusText = "\n" + Gleez.t("StatusText: !statusText", {'!statusText': $.trim(xmlhttp.statusText)});
	}
	catch (e) {}

	responseText = '';
	// Again, we don't have a way to know for sure whether accessing
	// xmlhttp.responseText is going to throw an exception. So we'll catch it.
	try
	{
	    responseText = "\n" + Gleez.t("ResponseText: !responseText", {'!responseText': $.trim(xmlhttp.responseText) } );
	} catch (e) {}

	// Make the responseText more readable by stripping HTML tags and newlines.
	responseText = responseText.replace(/<("[^"]*"|'[^']*'|[^'">])*>/gi,"");
	responseText = responseText.replace(/[\n]+\s+/g,"\n");

	// We don't need readyState except for status == 0.
	readyStateText = xmlhttp.status == 0 ? ("\n" + Gleez.t("ReadyState: !readyState", {'!readyState': xmlhttp.readyState})) : "";

	message = statusCode + pathText + statusText + responseText + readyStateText;
	return message;
    };

    /**
    * Encode special characters in a plain-text string for display as HTML.
    */
    Gleez.checkPlain = function (str) {
	str = String(str);
	var replace = { '&': '&amp;', '"': '&quot;', '<': '&lt;', '>': '&gt;' };
	for (var character in replace) {
	    var regex = new RegExp(character, 'g');
	    str = str.replace(regex, replace[character]);
	}
	return str;
    };
    
    // Check select2 plugin is loaded
    if ($.fn.select2)
    {
	$(window).on('load', function () {
	    $(".select-icons").select2({
		formatResult: Gleez.theme_icon,
		formatSelection: Gleez.theme_icon,
		escapeMarkup: function(m) { return m; }
	    });
	})
    }
    
    //icon theme for select2 plugin
    Gleez.theme_icon = function(icon) {
	if (!icon.id) return icon.text; // optgroup
	return "<i class=" + icon.id.toLowerCase() + "></i> " + icon.text;
    }

    /**
     * Common function to support without writing js, see admin/user/list
     *
     * @todo add i18 support and minor events
     */
    Gleez.dataTable = function()
    {
	if (!$.fn.dataTable) return;

	$('[data-toggle="datatable"]').each(function (i, table) {
	    var $table = $(table)
	    ,   columns = []
	    ,   aaSorting
	    ,   aoColumns
	    ,   bPaginate = $table.data('paginate') || true
	    ,   bInfo     = $table.data('info') || true
	    ,   bFilter   = $table.data('filter') || true
	    ,   bLengthChange = $table.data('lengthchange') || true
	    ,   source = $table.data('target') || false;
	    
	    //dont't init if it's already initialised
	    if ( $.fn.DataTable.fnIsDataTable( table ) ) return
	    
	    //exit if no url
	    if(source == false) return
	    
	    //use data sortable value to disable sorting/searching for a column
	    $('thead th', $(table)).each(function(){
		var obj   = $(this).data("columns");
		
		if(obj && obj != undefined){
		    columns.push(obj);
		}else{
		    columns.push(null);
		}
	    })

	    var oTable = $table.dataTable({
		"aoColumns": columns
	    ,   "aaSorting": $(this).data("sorting")
	    ,   "sPaginationType": "bootstrap"
	    ,   "bProcessing": true
	    ,   "bServerSide": true
	    ,   "bDeferRender": true
	    ,   "bLengthChange": bLengthChange
	    ,   "bPaginate": bPaginate
	    ,   "bFilter ": bFilter 
	    ,   "bInfo ": bInfo
	    ,   "sCookiePrefix": "gleez_datatable_"
	    ,   "sDom": "<'table_head'lfr>t<'row-fluid'<'span4'i><'span8'p>>"
	    ,   "sAjaxSource": source
	    ,   "fnServerData": function ( sUrl, aoData, fnCallback, oSettings ) {
		    oSettings.jqXHR = $.ajax( {
			"url":  sUrl,
			"data": aoData,
			"dataType": "json",
			"cache": false,
			"type": oSettings.sServerMethod
		    }, 300)
		    .done(function(data, textStatus, jqXHR){
			$(oSettings.oInstance).trigger('xhr', oSettings)
			fnCallback( data )
		    })
		    .fail(function (jqXHR, textStatus, errorThrown) {
			//var ierror = Gleez.informError(jqXHR, false, true);
			var errorText = '<div class="empty_page alert alert-block"><i class="icon-info-sign"></i>&nbsp'+errorThrown+'</div>'
			$(oSettings.oInstance).parent().html(errorText)
		    })
		}
	    })
	    
	    //set the datatable object in table data further use
	    $table.data('datatable', oTable)
	})
    }

    /**
     * Dynamic injection of css and js files
     *
     * @todo add minor events
     */
    Gleez.requires = function(Library, filetype)
    {
	if(Library == null || Library == false) return;
	if (!(Library instanceof Array)) Library = [Library];
	
	//if filename is a JavaScript file
	if (filetype=="js")
	{
	    $(Library).each(function (i,Lib){
		// Skip any libs that are ready or processing
		if (Gleez.Libraries[Lib] === false || Gleez.Libraries[Lib] === true)
		{
		    $(document).trigger('attach', Gleez.settings);
		    return;
		}
	    
		// As yet unseen. Try to load
		Gleez.Libraries[Lib] = false;
		var script  = document.createElement('script');
		script.type = 'text/javascript';
		node.async  = true;
		script.src  = Gleez.settings.basePath+Lib;
		script.onload = function(){
		    $(document).trigger('attach', Gleez.settings);
		};

		var src = document.getElementsByTagName('script')[0];
		src.parentNode.insertBefore(script, src);
		Gleez.Libraries[Lib] = true;
	    });
	}
	
	//if filename is an CSS file
	if (filetype=="css")
	{
	    $(Library).each(function (i,Lib){
		// Skip any libs that are ready or processing
		if (Gleez.Libraries[Lib] === false || Gleez.Libraries[Lib] === true)
		    return;
	    
		// As yet unseen. Try to load
		Gleez.Libraries[Lib] = false;
		var fileref=document.createElement("link");
		fileref.setAttribute("rel", "stylesheet");
		fileref.setAttribute("type", "text/css");
		fileref.setAttribute("href", Gleez.settings.basePath+Lib);
		
		if (typeof fileref != "undefined"){
		    document.getElementsByTagName("head")[0].appendChild(fileref);
		    Gleez.Libraries[Lib] = true;
		}
	    });
	}
    };

    // Take any "inform" messages out of an ajax response and display them on the screen.
    Gleez.inform = function(response) {
	
	if (!response)  return false;
	
	if (!response.InformMessages || response.InformMessages.length == 0)
	    return false;
	
	// If there is no message container in the page, add one
	var informMessages = $('div.messages');
	if (informMessages.length == 0)
	{
	    $('<div class="messages"></div>').appendTo('body');
	    informMessages = $('div.messages');
	}
	
	var wrappers = $('div.messages div.InformWrapper');
	
	// Loop through the inform messages and add them to the container
	for (var i = 0; i < response.InformMessages.length; i++)
	{
	    var css = 'alert';
	    if (response.InformMessages[i]['type'])
		css += ' alert-' + response.InformMessages[i]['type'];

	    try
	    {
		var message = response.InformMessages[i]['text'];
		var emptyMessage = message == '';
		var skip = false;
	    
		for (var j = 0; j < wrappers.length; j++)
		{
		    if ($(wrappers[j]).text() == $(message).text()) {
			skip = true;
		    }
		}
		
		if (!skip && !emptyMessage)
		{
		    // If the message is dismissable, add a close button
		    message = '<a class="close" data-dismiss="alert">×</a>' + message;
		    informMessages.prepend('<div class="'+css+'">'+message+'</div>');
		}
		
	    } catch (e) {}
	}
	
	informMessages.show();
	
	return true;
    }

    // Inform an error returned from an ajax call.
    Gleez.informError = function(xhr, silentAbort, returnRes) {
	if (xhr == undefined || xhr == null)
	    return;
       
	if (typeof(xhr) == 'string')
	    xhr = {responseText: xhr, code: 500};
       
	var message = xhr.responseText;
	var code = xhr.status;
       
	if (message == undefined || message == null || message == '') {
	    switch (xhr.statusText) {
		case 'error':
		   if (silentAbort) 
		      return;
		   message = Gleez.t('There was an error performing your request. Please try again.');
		   break;
		case 'timeout':
		   message = Gleez.t('Your request timed out. Please try again.');
		   break;
		case 'abort':
		   return;
	    }
	}
       
	try
	{
	    var data = $.parseJSON(message);
	    if (typeof(data.Exception) == 'string')
		message = data.Exception;
	} catch(e) {}
       
	if (message == '')
	    message = Gleez.t('There was an error performing your request. Please try again.')
       
	if (returnRes) return {responseText: message, code: code}
	
	Gleez.informMessage('<span class="InformSprite Lightbulb Error'+code+'"></span>'+message)
    }
    
    // Send an informMessage to the screen.
    Gleez.informMessage = function(message, options) {
	if (!options)   options = new Array();
	
	options['text'] = message;
	Gleez.inform({'InformMessages' : new Array(options)});
    }
    
    var keyString = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var uTF8Encode = function(string) {
	    string = string.replace(/\x0d\x0a/g, "\x0a");
	    var output = "";
	    for (var n = 0; n < string.length; n++) {
		    var c = string.charCodeAt(n);
		    if (c < 128) {
			    output += String.fromCharCode(c);
		    } else if ((c > 127) && (c < 2048)) {
			    output += String.fromCharCode((c >> 6) | 192);
			    output += String.fromCharCode((c & 63) | 128);
		    } else {
			    output += String.fromCharCode((c >> 12) | 224);
			    output += String.fromCharCode(((c >> 6) & 63) | 128);
			    output += String.fromCharCode((c & 63) | 128);
		    }
	    }
	    return output;
    };
    
    var uTF8Decode = function(input) {
	    var string = "";
	    var i = 0;
	    var c = 0, c1 = 0, c2 = 0, c3 = 0;
	    while ( i < input.length ) {
		    c = input.charCodeAt(i);
		    if (c < 128) {
			    string += String.fromCharCode(c);
			    i++;
		    } else if ((c > 191) && (c < 224)) {
			    c2 = input.charCodeAt(i+1);
			    string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
			    i += 2;
		    } else {
			    c2 = input.charCodeAt(i+1);
			    c3 = input.charCodeAt(i+2);
			    string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
			    i += 3;
		    }
	    }
	    return string;
    }
    
    $.extend({
	    base64Encode: function(input) {
		    var output = "";
		    var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		    var i = 0;
		    input = uTF8Encode(input);
		    while (i < input.length) {
			    chr1 = input.charCodeAt(i++);
			    chr2 = input.charCodeAt(i++);
			    chr3 = input.charCodeAt(i++);
			    enc1 = chr1 >> 2;
			    enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			    enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			    enc4 = chr3 & 63;
			    if (isNaN(chr2)) {
				    enc3 = enc4 = 64;
			    } else if (isNaN(chr3)) {
				    enc4 = 64;
			    }
			    output = output + keyString.charAt(enc1) + keyString.charAt(enc2) + keyString.charAt(enc3) + keyString.charAt(enc4);
		    }
		    return output;
	    },
	    base64Decode: function(input) {
		    var output = "";
		    var chr1, chr2, chr3;
		    var enc1, enc2, enc3, enc4;
		    var i = 0;
		    input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
		    while (i < input.length) {
			    enc1 = keyString.indexOf(input.charAt(i++));
			    enc2 = keyString.indexOf(input.charAt(i++));
			    enc3 = keyString.indexOf(input.charAt(i++));
			    enc4 = keyString.indexOf(input.charAt(i++));
			    chr1 = (enc1 << 2) | (enc2 >> 4);
			    chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			    chr3 = ((enc3 & 3) << 6) | enc4;
			    output = output + String.fromCharCode(chr1);
			    if (enc3 != 64) {
				    output = output + String.fromCharCode(chr2);
			    }
			    if (enc4 != 64) {
				    output = output + String.fromCharCode(chr3);
			    }
		    }
		    output = uTF8Decode(output);
		    return output;
	    }
    });
    
    $.postParseJson = function(json) {
	if (json.Body) json.Body = $.base64Decode(json.Body);
	return json;
    }

    /**
     * Filter Jquery selector by attribute value
     **/
    $.fn.filterAttr = function(attr_name, attr_value) {
	return this.filter(function() { return $(this).attr(attr_name) === attr_value; });
    };
    
    Gleez.theme = function (func) {
	for (var i = 1, args = []; i < arguments.length; i++) {
	    args.push(arguments[i]);
	}

	return (Gleez.theme[func] || Gleez.theme.prototype[func]).apply(this, args);
    };

    /**
     * The default themes.
     */
    Gleez.theme.prototype = {
	/**
	* Formats text for emphasized display in a placeholder inside a sentence.
	*
	* @param str
	*   The text to format (plain-text).
	* @return
	*   The formatted text (html).
	*/
	placeholder: function (str)
	{
	    return '<em class="placeholder">' + Gleez.checkPlain(str) + '</em>';
	}
    };

    $(function () {
	$(window).on('load', function () {
	    $("[rel='tooltip']").tooltip()
	})
	
	//Attach all behaviors.
	Gleez.attachBehaviors(document, Gleez.settings)
	$(document).trigger('attach', Gleez.settings)
    });
    
    // Class indicating that JS is enabled; used for styling purpose.
    $('html').addClass('js');

    // 'js enabled' cookie.
    document.cookie = 'has_js=1; path=/';

})(jQuery);
