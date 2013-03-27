
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

    Gleez.behaviors.titlehint = {
        attach: function (context, settings) {
            id = 'title';
            var title = $('#' + id), titleprompt = $('#' + id + '-prompt-text');

            if ( title.val() != '' )
                titleprompt.css('visibility', 'hidden');

            if ( title.val() == '' )
                titleprompt.css('display', 'block');

            titleprompt.click(function(){
                $(this).css('visibility', 'hidden');
                title.focus();
            });

            title.blur(function(){
                if ( this.value == '' )
                    titleprompt.css('display', 'block');
                }).focus(function(){
                    titleprompt.css('visibility', 'hidden');
                }).keydown(function(e){
                    titleprompt.css('visibility', 'hidden');
                    $(this).unbind(e);
            });
        }
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

    Gleez.dataTable = function()
    {
        if (!$.fn.dataTable) return;
        

        
        $('[data-toggle="datatable"]').each(function (i, table) {
            var $table = $(table)
            ,   columns = []
            ,   aaSorting
            ,   aoColumns
            ,   source = $table.data('target') || false;
            
            //dont't init if it's already initialised
            if ( $.fn.DataTable.fnIsDataTable( table ) ) return;
            
            //exit if no url
            if(source == false) return;
            
            //use data sortable value to disable sorting/searching for a column
            $('thead th', $(table)).each(function(){
                var obj   = $(this).data("aocolumns");
                
                if(obj && obj != undefined){
                    columns.push(obj);
                }else{
                    columns.push(null);
                }
            })

            var oTable = $table.dataTable({
                "aoColumns": columns
            ,   "aaSorting": $(this).data("aasorting")
            ,   "sPaginationType": "bootstrap"
            ,   "bProcessing": true
            ,   "bServerSide": true
            ,   "bDeferRender": true
            ,   "sCookiePrefix": "gleez_datatable_"
            ,   "sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span4'i><'span8'p>>"
            ,   "sAjaxSource": source
            ,   "fnServerData": function ( sUrl, aoData, fnCallback, oSettings ) {
			oSettings.jqXHR = $.ajax( {
				"url":  sUrl,
				"data": aoData,
				"success": function (json) {
					$(oSettings.oInstance).trigger('xhr', oSettings);
					fnCallback( json );
				},
				"dataType": "json",
				"cache": false,
				"type": oSettings.sServerMethod,
				"error": function (xhr, error, thrown) {
				    //var ierror = Gleez.informError(xhr, false, true);
				    var errorText = '<div class="empty_page alert alert-block"><i class="icon-info-sign"></i>'+error.responseText+'</div>';
				    $(oSettings.oInstance).parent().html(errorText);
				}
			} );
		}
            });
        })
        
    }
    
    Gleez.theme = function (func) {
      for (var i = 1, args = []; i < arguments.length; i++) {
        args.push(arguments[i]);
      }

      return (Gleez.theme[func] || Gleez.theme.prototype[func]).apply(this, args);
    };

    //Attach all behaviors.
    $(function ()
    {
        Gleez.attachBehaviors(document, Gleez.settings);
    });

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

    $(document).on('attach.datatable', function (e) {
	Gleez.dataTable();
    });

    //Attach all behaviors.
    $(function () {
        $(document).trigger('attach', Gleez.settings);
    });

    $(function () {
        $(window).on('load', function () {
            $("[rel='tooltip']").tooltip();
        })
    });
    
    // Class indicating that JS is enabled; used for styling purpose.
    $('html').addClass('js');

    // 'js enabled' cookie.
    document.cookie = 'has_js=1; path=/';

})(jQuery);