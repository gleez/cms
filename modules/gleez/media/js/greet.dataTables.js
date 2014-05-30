/*
 * This is a highly modified version of the bootstrap dataTables.
 * https://github.com/gleez/greet
 * 
 * @package    Greet\DataTables
 * @version    2.0
 * @requires   jQuery v1.9 or later
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2005-2014 Gleez Technologies
 * @license    The MIT License (MIT)
 *
 */

+function ($) { 'use strict';

	// GREET DATATABLE CLASS DEFINITION
	// ======================

	var DataTable = function(table, options) {
		var $table = $(table)
		,   columns = []
		
		//dont't init if it's already initialised
		if ( $.fn.DataTable.fnIsDataTable( table ) ) return
		
		//exit if no url
		if(options.target == false) return
		
		//use data sortable value to disable sorting/searching for a column
		$('thead th', $(table)).each(function(){
			var obj   = $(this).data("columns")
		
			if(obj && obj != undefined){
				columns.push(obj);
			}else{
				columns.push(null)
			}
		})

		var oTable = $table.dataTable({
			"aoColumns": columns
			,   "aaSorting": options.sorting
			,   "bProcessing": options.processing
			,   "bServerSide": options.serverside
			,   "bDeferRender": options.deferrender
			,   "bPaginate": options.paginate
			,   "bFilter ": options.filter 
			,   "bInfo ": options.info
			,   "sDom": options.dom
			,   "sCookiePrefix": options.cookie
			,   "bLengthChange": options.lengthchange
			,   "sAjaxSource": options.target
			,   "sPaginationType": "bootstrap"
			,   "oLanguage": {
				"sEmptyTable": options.emptytable
					, "sUrl": options.localize
				}
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
					var errorText = '<div class="empty_page alert alert-block"><i class="fa fa-info-circle"></i>&nbsp'+errorThrown+'</div>'
					$(oSettings.oInstance).parent().html(errorText)
				})
			}
		})
	}

	/* Set the defaults for DataTables initialisation */
	$.extend(true, $.fn.dataTable.defaults, {
		"fnInitComplete": function (oSettings, json) {
			var currentId = $(this).attr('id')

			if (currentId) {
				var thisLength = $('#' + currentId + '_length')
				var thisLengthLabel = $('#' + currentId + '_length label')
				var thisLengthSelect = $('#' + currentId + '_length label select')

				var thisFilter = $('#' + currentId + '_filter')
				var thisFilterLabel = $('#' + currentId + '_filter label')
				var thisFilterInput = $('#' + currentId + '_filter label input')

				// Re-arrange the records selection for a form-horizontal layout
				//thisLength.addClass('form-group')
				thisLengthLabel.addClass('control-label').attr('for', currentId + '_length_select')
				thisLengthSelect.addClass('form-control input-sm').attr('id', currentId + '_length_select')
				//thisLengthSelect.prependTo(thisLength).wrap('<div class="col-xs-12 col-sm-5 col-md-6" />')

				// Re-arrange the search input for a form-horizontal layout
				thisFilter.addClass('form-group')
				thisFilterLabel.addClass('control-label').attr('for', currentId + '_filter_input')
				thisFilterInput.addClass('form-control col-xs-4 col-md-3 col-sm-3').attr('id', currentId + '_filter_input')
				thisFilterInput.appendTo(thisFilter).wrap('<div class="col-xs-8 col-sm-9 col-md-9" />')
			}
		}
	})

	/* Default class modification */
	$.extend( $.fn.dataTableExt.oStdClasses, {
		"sWrapper": "dataTables_wrapper form-inline"
	})

	/* API method to get paging information */
	$.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings ){
		return {
			"iStart":         oSettings._iDisplayStart,
			"iEnd":           oSettings.fnDisplayEnd(),
			"iLength":        oSettings._iDisplayLength,
			"iTotal":         oSettings.fnRecordsTotal(),
			"iFilteredTotal": oSettings.fnRecordsDisplay(),
			"iPage":          Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
			"iTotalPages":    Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
		}
	}

	/* Bootstrap style pagination control */
	$.extend( $.fn.dataTableExt.oPagination, {
		"bootstrap": {
			"fnInit": function( oSettings, nPaging, fnDraw ) {
				var oLang = oSettings.oLanguage.oPaginate
				var fnClickHandler = function ( e ) {
					e.preventDefault()
					if ( oSettings.oApi._fnPageChange(oSettings, e.data.action) ) {
						fnDraw( oSettings )
					}
				}
				
				$(nPaging).addClass('dtpager').append(
					'<ul class="pagination">'+
					'<li class="prev disabled"><a href="#">&larr; '+oLang.sPrevious+'</a></li>'+
					'<li class="next disabled"><a href="#">'+oLang.sNext+' &rarr; </a></li>'+
					'</ul>'
				)
				
				var els = $('a', nPaging)
				$(els[0]).bind( 'click.DT', { action: "previous" }, fnClickHandler )
				$(els[1]).bind( 'click.DT', { action: "next" }, fnClickHandler )
			},
			
			"fnUpdate": function ( oSettings, fnDraw ) {
				var iListLength = 5;
				var iLen;
				var oPaging = oSettings.oInstance.fnPagingInfo();
				var an = oSettings.aanFeatures.p;
				var i, j, sClass, iStart, iEnd, iHalf=Math.floor(iListLength/2);

				if ( oPaging.iTotalPages < iListLength) {
					iStart = 1;
					iEnd = oPaging.iTotalPages;
				}
				else if ( oPaging.iPage <= iHalf ) {
					iStart = 1;
					iEnd = iListLength;
				} else if ( oPaging.iPage >= (oPaging.iTotalPages-iHalf) ) {
					iStart = oPaging.iTotalPages - iListLength + 1;
					iEnd = oPaging.iTotalPages;
				} else {
					iStart = oPaging.iPage - iHalf + 1;
					iEnd = iStart + iListLength - 1;
				}

				for ( i=0, iLen=an.length ; i<iLen ; i++ ) {
					// Remove the middle elements
					$('li:gt(0)', an[i]).filter(':not(:last)').remove();

					// Add the new list items and their event handlers
					for ( j=iStart ; j<=iEnd ; j++ ) {
						sClass = (j==oPaging.iPage+1) ? 'class="active"' : '';
						$('<li '+sClass+'><a href="#">'+j+'</a></li>')
							.insertBefore( $('li:last', an[i])[0] )
							.bind('click', function (e) {
							e.preventDefault();
							oSettings._iDisplayStart = (parseInt($('a', this).text(),10)-1) * oPaging.iLength
							fnDraw( oSettings )
							})
					}
			
					// Add / remove disabled classes from the static elements
					if ( oPaging.iPage === 0 ) {
						$('li:first', an[i]).addClass('disabled')
					} else {
						$('li:first', an[i]).removeClass('disabled')
					}

					if ( oPaging.iPage === oPaging.iTotalPages-1 || oPaging.iTotalPages === 0 ) {
						$('li:last', an[i]).addClass('disabled')
					} else {
						$('li:last', an[i]).removeClass('disabled')
					}
				}
			}
		}
	})

	DataTable.DEFAULTS = {
		paginate       : true
		, info         : true
		, filter       : true
		, lengthchange : true
		, target       : false
		, columns      : false
		, sorting      : false
		, processing   : true
		, serverside   : true
		, deferrender  : true
		, localize     :''
		, cookie       : "gleez_datatable_"
		, emptytable   : "No active record(s) here. Would you like to create one?"
		, dom          : "<'table_head row'<'col-xs-6'l><'col-xs-6'f>r>t<'row'<'col-xs-6'i><'col-xs-6'p>>"
	}

	// GREET DATATABLEs PLUGIN DEFINITION
	// =======================

	var old = $.fn.gdatatable

	$.fn.gdatatable = function (option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('gdatatable')
			var options = $.extend({}, DataTable.DEFAULTS, $this.data(), typeof option == 'object' && option)
			
			if (!data) $this.data('gdatatable', (data = new DataTable(this, options)))
			if (typeof option == 'string') data[option]()
		})
	}

	$.fn.gdatatable.Constructor = DataTable

	// GREET DATATABLES NO CONFLICT
	// =================

	$.fn.gdatatable.noConflict = function () {
		$.fn.gdatatable = old
		return this
	}

	// GREET DATATABLES DATA-API
	// ==============

	$(window).on('load.datatable.data-api', function (e) {
		if (!$.fn.dataTable) return
		
		$('[data-toggle="datatable"]').each(function () {
			var $table = $(this)
			$table.gdatatable($table.data())
		})
	})

}(jQuery);