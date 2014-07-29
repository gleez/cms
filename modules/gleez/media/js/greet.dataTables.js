/*
 * This is a highly modified version of the bootstrap dataTables.
 * https://github.com/gleez/greet
 * 
 * @package    Greet\DataTables
 * @version    3.0
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
			, "order": options.sorting
			, "processing": options.processing
			, "serverSide": options.serverside
			, "deferRender": options.deferrender
			, "paging": options.paginate
			, "sarching": options.filter 
			, "info ": options.info
			, "dom": options.dom
			, "lengthChange": options.lengthchange
			//, "pagingType": "bootstrap"
			, "language": {
					"emptyTable": options.emptytable
					, "url": options.localize
				}
			, "ajax": function (data, fnCallback, settings ) {
				settings.jqXHR = $.ajax( {
					"url":  options.target,
					"data": data,
					"dataType": "json",
					"cache": false,
					"type": settings.sServerMethod
				}, 300)
				.done(function(response, textStatus, jqXHR){
					$(settings.oInstance).trigger('xhr', settings)
					fnCallback( response )
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					var errorText = '<div class="empty_page alert alert-block"><i class="fa fa-info-circle"></i>&nbsp'+errorThrown+'</div>'
					$(settings.oInstance).parent().html(errorText)
				})
			}
		})
	}

	/* Set the defaults for DataTables initialisation */
	$.extend( true, $.fn.dataTable.defaults, {
		"dom":
			"<'row'<'col-xs-6'l><'col-xs-6'f>r>"+
			"t"+
			"<'row'<'col-xs-6'i><'col-xs-6'p>>",
		"language": {
			"lengthMenu": "_MENU_ records per page"
		}
	} )

	/* Default class modification */
	$.extend( $.fn.dataTableExt.oStdClasses, {
		"sWrapper": "dataTables_wrapper form-inline",
		"sFilterInput": "form-control input-sm",
		"sLengthSelect": "form-control input-sm"
	} )

	// In 1.10 we use the pagination renderers to draw the Bootstrap paging,
	// rather than  custom plug-in
	if ( $.fn.dataTable.Api ) {
		$.fn.dataTable.defaults.renderer = 'bootstrap';
		$.fn.dataTable.ext.renderer.pageButton.bootstrap = function ( settings, host, idx, buttons, page, pages ) {
			var api = new $.fn.dataTable.Api( settings );
			var classes = settings.oClasses;
			var lang = settings.oLanguage.oPaginate;
			var btnDisplay, btnClass;

			var attach = function( container, buttons ) {
				var i, ien, node, button;
				var clickHandler = function ( e ) {
					e.preventDefault();
					if ( e.data.action !== 'ellipsis' ) {
						api.page( e.data.action ).draw( false );
					}
				};

				for ( i=0, ien=buttons.length ; i<ien ; i++ ) {
					button = buttons[i];

					if ( $.isArray( button ) ) {
						attach( container, button );
					}
					else {
						btnDisplay = '';
						btnClass = '';

						switch ( button ) {
							case 'ellipsis':
								btnDisplay = '&hellip;';
								btnClass = 'disabled';
								break;

							case 'first':
								btnDisplay = lang.sFirst;
								btnClass = button + (page > 0 ?
									'' : ' disabled');
								break;

							case 'previous':
								btnDisplay = lang.sPrevious;
								btnClass = button + (page > 0 ?
									'' : ' disabled');
								break;

							case 'next':
								btnDisplay = lang.sNext;
								btnClass = button + (page < pages-1 ?
									'' : ' disabled');
								break;

							case 'last':
								btnDisplay = lang.sLast;
								btnClass = button + (page < pages-1 ?
									'' : ' disabled');
								break;

							default:
								btnDisplay = button + 1;
								btnClass = page === button ?
									'active' : '';
								break;
						}

						if ( btnDisplay ) {
							node = $('<li>', {
									'class': classes.sPageButton+' '+btnClass,
									'aria-controls': settings.sTableId,
									'tabindex': settings.iTabIndex,
									'id': idx === 0 && typeof button === 'string' ?
										settings.sTableId +'_'+ button :
										null
								} )
								.append( $('<a>', {
										'href': '#'
									} )
									.html( btnDisplay )
								)
								.appendTo( container );

							settings.oApi._fnBindAction(
								node, {action: button}, clickHandler
							);
						}
					}
				}
			};

			attach(
				$(host).empty().html('<ul class="pagination"/>').children('ul'),
				buttons
			);
		}
	}

	/* Set the defaults for DataTables initialisation */
/*	$.extend(true, $.fn.dataTable.defaults, {
		"initComplete": function (oSettings, json) {
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
	})*/

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
		, localize     : ''
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