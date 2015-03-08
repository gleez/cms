/*
 * This is a highly modified version of the bootstrap dataTables.
 * Requires jquery tmpl plugin @link
 * https://github.com/gleez/greet
 * 
 * @package    Greet\DataTables
 * @version    4.0
 * @requires   jQuery v1.9 or later
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2005-2015 Gleez Technologies
 * @license    The MIT License (MIT)
 * @link       https://github.com/blueimp/JavaScript-Templates
 *
 */

+function ($) {
	'use strict';

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

		if(options.template){
			options.pagingType = "simple"
			options.pretty = true
			options.dom = "<'panel panel-default'<'panel-body hide'<'search-form'<'row'<'col-xs-9'f><'col-xs-3'>>>r><'list-group't><'panel-footer'<'row'<'col-sm-6'i><'col-sm-6'p>>>"
		}

		var oTable = $table.dataTable({
			"columns": columns
			, "order": options.sorting
			, "processing": options.processing
			, "serverSide": options.serverside
			, "deferRender": options.deferrender
			, "paging": options.paginate
			, "pagingType" : options.pagingType
			, "sarching": options.filter 
			, "info ": options.info
			, "dom": options.dom
			, "lengthChange": options.lengthchange
			, "stateSave": options.statesave
			, "stateDuration": options.stateduration
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
			, "drawCallback": function( settings ) {
				if(options.pretty){
					$table.find("thead").remove()
				}
			}
			, "rowCallback": function ( row, data ) {
				if(options.pretty && data.length){
					tmpl.arg = "c";
					var html = tmpl(options.template, data);

					$(row).empty()
					$(row).append(html)

					return row
				}
			}
			, "createdRow": function( row, data, dataIndex ) {
				if(options.pretty && options.template && data.length){
					// Row click
					$(row).on('click', function() {
						//console.log('Row Clicked. Look I have access to all params, thank You closures.', this, data, dataIndex)
					})
				}
			}
		})
	}

	/* Default class modification */
	$.extend( $.fn.dataTableExt.oStdClasses, {
		"sWrapper": "dataTables_wrapper form-inline",
		"sFilterInput": "form-control",
		"sLengthSelect": "form-control input-sm"
	} )

	// Pagination renderers to draw the Bootstrap paging,
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
								btnClass = button + (page > 0 ? '' : ' disabled');
								break;

							case 'previous':
								btnDisplay = lang.sPrevious;
								btnClass = button + (page > 0 ? '' : ' disabled');
								break;

							case 'next':
								btnDisplay = lang.sNext;
								btnClass = button + (page < pages-1 ? '' : ' disabled');
								break;

							case 'last':
								btnDisplay = lang.sLast;
								btnClass = button + (page < pages-1 ? '' : ' disabled');
								break;

							default:
								btnDisplay = button + 1;
								btnClass = page === button ? 'active' : '';
								break;
						}

						if ( btnDisplay ) {
							node = $('<li>', {
									'class': classes.sPageButton+' '+btnClass,
									'aria-controls': settings.sTableId,
									'tabindex': settings.iTabIndex,
									'id': idx === 0 && typeof button === 'string' ? settings.sTableId +'_'+ button : null
								} )
								.append( $('<a>', {'href': '#'}).html(btnDisplay) )
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
	$.extend(true, $.fn.dataTable.defaults, {
		"initComplete": function (oSettings, json) {
			var currentId = $(this).attr('id')

			if (currentId) {
				var thisLength     = $('#' + currentId + '_length')
				, thisLengthLabel  = $('#' + currentId + '_length label')
				, thisLengthSelect = $('#' + currentId + '_length label select')
				, thisFilter       = $('#' + currentId + '_filter')
				, thisFilterLabel  = $('#' + currentId + '_filter label')
				, thisFilterInput  = $('#' + currentId + '_filter label input')
				, title            = $(this).data('title') || false
				, pretty           = $(this).data('template') || false

				// Re-arrange the records selection for a form-horizontal layout
				thisLengthLabel.addClass('control-label').attr('for', currentId + '_length_select')
				thisLengthSelect.addClass('form-control input-sm').attr('id', currentId + '_length_select')

				// Re-arrange the search input for a form-horizontal layout
				thisFilter.addClass('form-group')
				thisFilterInput.appendTo(thisFilter)
				thisFilterLabel.remove()
				thisFilterInput.attr('placeholder', 'Search...')
				thisFilter.parent().removeClass('hide')
				$(this).find('thread.hide').removeClass('hide')

				// Pretty Support
				if(title && pretty) {
					var add = $(document).find('script.card-add').html() || false
					thisFilter.parents("div.search-form").find("div.col-xs-3").append(add)

					thisFilter.append('<i class="fa fa-search"></i>')
					thisFilterInput.attr('placeholder', 'Search '+title+'...')
					thisFilter.parents("div.panel-body.hide").removeClass('hide')
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
		, statesave    : true
		, stateduration: 7200
		, serverside   : true
		, deferrender  : true
		, pretty  	   : false
		, template     : ''
		, localize     : ''
		, emptytable   : "No active record(s) here. Would you like to create one?"
		, dom          : "<'table_head row'<'col-sm-6 hidden-xs'l><'col-xs-12 col-sm-6 hide'f>r>t<'row'<'col-sm-6'i><'col-sm-6'p>>"
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

	// Added pajax and jquery mobile support
	$(document).on('pjax:complete pagecontainerchange', function (e) {
		if (!$.fn.dataTable) return
		
		$('[data-toggle="datatable"]').each(function () {
			var $table = $(this)
			$table.gdatatable($table.data())
		})
	})

}(jQuery);