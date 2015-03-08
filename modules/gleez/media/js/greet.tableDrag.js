/** 
 * This is a highly modified version of the Drupal core, misc/tabledrag.js.
 * https://github.com/gleez/greet
 * 
 * Drag and drop table rows with field manipulation.
 *
 * Any table with weights or parent relationships may be made into draggable tables. 
 * Columns containing a field may optionally be hidden, providing a better user experience.
 *
 * @package    Greet\TableDrag
 * @version    1.0
 * @requires   jQuery v1.9 or later
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2005-2015 Gleez Technologies
 * @license    The MIT License (MIT)
 *
 */

+function ($) {
	'use strict';
	/**
	 * Constructor for the tableDrag object. Provides table and field manipulation.
	 *
	 * @param table
	 *   DOM object for the table to be made draggable.
	 * @param tableSettings
	 *   Settings for the table added via drupal_add_dragtable().
	 */
	var TableDrag = function (table, options) {
		var self = this

		// Required object variables.
		this.table 			= table
		this.tableSettings	= options
		this.$element 		= $(table)
		this.dragObject		= null; // Used to hold information about a current drag operation.
		this.rowObject		= null; // Provides operations for row manipulation.
		this.oldRowElement	= null; // Remember the previous element.
		this.oldY			= 0; // Used to determine up or down direction from last mouse move.
		this.changed 		= false; // Whether anything in the entire table has changed.
		this.maxDepth 		= 0; // Maximum amount of allowed parenting.
		this.rtl 			= $(this.table).css('direction') == 'rtl' ? -1 : 1; // Direction of the table.

		// Configure the scroll settings.
		this.scrollSettings = { amount: 4, interval: 50, trigger: 70 }
		this.scrollInterval = null
		this.scrollY 		= 0
		this.windowHeight 	= 0

		// Check this table's settings to see if there are parent relationships in
		// this table. For efficiency, large sections of code can be skipped if we
		// don't need to track horizontal movement and indentations.
		this.indentEnabled = !!options.parent.fieldClass
		this.maxDepth 	   = this.indentEnabled ? options.group.depthLimit : this.maxDepth

		if (this.indentEnabled) {
			this.indentCount  = 1; // Total width of indents, set in makeDraggable.
			// Find the width of indentations to measure mouse movements against.
			// Because the table doesn't need to start with any indentations, we
			// manually append 2 indentations in the first draggable row, measure
			// the offset, then remove.
			var indent 		  = Gleez.theme('tableDragIndentation')
			var testRow 	  = $('<tr/>').addClass('draggable').appendTo(table)
			var testCell 	  = $('<td/>').appendTo(testRow).prepend(indent).prepend(indent)
			this.indentAmount = $('.indentation', testCell).get(1).offsetLeft - $('.indentation', testCell).get(0).offsetLeft
			testRow.remove()
		}

		// Make each applicable row draggable.
		// Match immediate children of the parent element to allow nesting.
		$('> tr.draggable, > tbody > tr.draggable', table).each(function () { self.makeDraggable(this); })

		// Initialize the specified columns (for example, weight or parent columns)
		// to show or hide according to user preference. This aids accessibility
		// so that, e.g., screen reader users can choose to enter weight values and
		// manipulate form elements directly, rather than using drag-and-drop..
		self.initColumns()

		// Add mouse bindings to the document. The self variable is passed along
		// as event handlers do not have direct access to the tableDrag object.
		$(document).bind('mousemove', function (event) { return self.dragRow(event, self) })
		$(document).bind('mouseup', function (event) { return self.dropRow(event, self) })

		// To stimulate MouseEvent in touch screen devices
		$(document).bind('touchmove', function(event) {
			if ($("body").hasClass("drag")) {
				if (event.originalEvent.touches && event.originalEvent.touches.length) {
					var touch = event.originalEvent.touches[0]
				} else if (event.originalEvent.changedTouches && event.originalEvent.changedTouches.length) {
					var touch = event.originalEvent.changedTouches[0]
				}

				var simulatedEvent = document.createEvent("MouseEvent")
				simulatedEvent.initMouseEvent('mousemove', true, true, window, 1,
			        touch.screenX, touch.screenY, touch.clientX, touch.clientY,
			        false, false, false, false, 0/*left*/, null);

				touch.target.dispatchEvent(simulatedEvent)
				event.preventDefault()
			}
		})

		$(document).bind('touchend', function(event) {
			if ($("body").hasClass("drag")) {
				if (event.originalEvent.touches && event.originalEvent.touches.length) {
					var touch = event.originalEvent.touches[0]
				} else if (event.originalEvent.changedTouches && event.originalEvent.changedTouches.length) {
					var touch = event.originalEvent.changedTouches[0]
				}

				var simulatedEvent = document.createEvent("MouseEvent")
				simulatedEvent.initMouseEvent('mouseup', true, true, window, 1,
			                touch.screenX, touch.screenY, touch.clientX, touch.clientY,
			                false, false, false, false, 0/*left*/, null);

				touch.target.dispatchEvent(simulatedEvent)
				event.preventDefault()
			}
		})
	}

	/**
	 * Initialize columns containing form elements to be hidden by default,
	 * according to the settings for this tableDrag instance.
	 *
	 * Identify and mark each cell with a CSS class so we can easily toggle
	 * show/hide it. Finally, hide columns if user does not have a
	 * 'Drupal.tableDrag.showWeight' cookie.
	 */
	TableDrag.prototype.initColumns = function () {
		for (var group in { group: 1, weight: 1, parent: 1}) {
			// Find the first field in this group.
			if (this.tableSettings[group].fieldClass !== undefined) {
				var field = $('.' + this.tableSettings[group].fieldClass + ':first', this.table)

				if (/*field.size()*/ field.length && this.tableSettings[group].hidden) {
					var hidden 	= this.tableSettings[group].hidden
					var cell 	= field.parents('td:first')
					//break
				}
			}

			// Mark the column containing this field so it can be hidden.
			if (hidden && cell[0] && cell.css('display') != 'none') {
				// Add 1 to our indexes. The nth-child selector is 1 based, not 0 based.
				// Match immediate children of the parent element to allow nesting.
				var columnIndex = $('> td', cell.parent()).index(cell.get(0)) + 1
				var headerIndex = $('> td:not(:hidden)', cell.parent()).index(cell.get(0)) + 1

				$('> thead > tr, > tbody > tr, > tr', this.table).each(function (){
					var row 	  = $(this)
					var parentTag = row.parent().get(0).tagName.toLowerCase()
					var index     = (parentTag == 'thead') ? headerIndex : columnIndex

					// Adjust the index to take into account colspans.
					row.children().each(function (n) {
						if (n < index) {
							index -= (this.colSpan && this.colSpan > 1) ? this.colSpan - 1 : 0
						}
					})

					if (index > 0) {
						cell = row.children(':nth-child(' + index + ')')
						if (cell[0].colSpan > 1) {
							// If this cell has a colspan, mark it so we can reduce the colspan.
							$(cell[0]).addClass('tabledrag-has-colspan')
						}
						else {
							// Mark this cell so we can hide it.
							$(cell[0]).addClass('tabledrag-hide')
						}
					}
				})
			}
		}

		// Now hide cells and reduce colspans 
	    this.hideColumns()
	}

	/**
	 * Hide the columns containing weight/parent form elements.
	 * Undo showColumns().
	 */
	TableDrag.prototype.hideColumns = function () {
		// Hide weight/parent cells and headers.
		$('.tabledrag-hide', this.table).css('display', 'none')

		// Show TableDrag handles.
		$('.tabledrag-handle', this.table).css('display', '')

		// Reduce the colspan of any effected multi-span columns.
		$('.tabledrag-has-colspan', this.table).each(function () {
			this.colSpan = this.colSpan - 1
		})

		// Trigger an event to allow other scripts to react to this display change.
		$(this.table).trigger('columnschange', 'hide')
	}

	/**
	 * Show the columns containing weight/parent form elements
	 * Undo hideColumns().
	 */
	TableDrag.prototype.showColumns = function () {
		// Show weight/parent cells and headers.
		$('.tabledrag-hide', this.table).css('display', '')

		// Hide TableDrag handles.
		$('.tabledrag-handle', this.table).css('display', 'none')

		// Increase the colspan for any columns where it was previously reduced.
		$('.tabledrag-has-colspan', this.table).each(function () {
			this.colSpan = this.colSpan + 1
		})

		// Trigger an event to allow other scripts to react to this display change.
		$(this.table).trigger('columnschange', 'show'); // @todo Cache $(this.table)
	}

	/**
	 * Find the target used within a particular row and group.
	 */
	TableDrag.prototype.rowSettings = function (group, row) {
		if (this.tableSettings[group].fieldClass !== undefined) {
			var field = $('.' + this.tableSettings[group].fieldClass, row)

			if (field.length) {
				// Return a copy of the row settings.
				var rowSettings = this.tableSettings[group]
				rowSettings.relationship = group == 'group' ? 'group' : (group == 'parent' ? 'parent' : (group == 'weight' ? 'sibling' : 'self'))
				rowSettings.action = group == 'group' ? 'depth' : (group == 'parent' ? 'match' : (group == 'weight' ? 'order' : 'order'))
				return rowSettings
			}
		}
	}

	/**
	 * Take an item and add event handlers to make it become draggable.
	 */
	TableDrag.prototype.makeDraggable = function (item) {
		var self = this;

		// Create the handle.
		var handle = $('<a href="#" class="tabledrag-handle"><div class="handle">&nbsp;</div></a>').attr('title', 'Drag to re-order');
	  
		// Insert the handle after indentations (if any).
		if ($('td:first .indentation:last', item).length) {
			$('td:first .indentation:last', item).after(handle)

			// Update the total width of indentation in this entire table.
			self.indentCount = Math.max($('.indentation', item).size(), self.indentCount)
		}
		else {
			$('td:first', item).prepend(handle)
		}

		// Add hover action for the handle.
		handle.hover(function () {
			self.dragObject == null ? $(this).addClass('tabledrag-handle-hover') : null
		}, function () {
			self.dragObject == null ? $(this).removeClass('tabledrag-handle-hover') : null
		})

		// Add the mousedown action for the handle.
		handle.mousedown(function (event) {
			// Create a new dragObject recording the event information.
			self.dragObject = {};
			self.dragObject.initMouseOffset = self.getMouseOffset(item, event);
			self.dragObject.initMouseCoords = self.mouseCoords(event);
			if (self.indentEnabled) {
			  self.dragObject.indentMousePos = self.dragObject.initMouseCoords;
			}

			// If there's a lingering row object from the keyboard, remove its focus.
			if (self.rowObject) {
			  $('a.tabledrag-handle', self.rowObject.element).blur();
			}

			// Create a new rowObject for manipulation of this row.
			self.rowObject = new self.row(item, 'mouse', self.indentEnabled, self.maxDepth, true)

			// Save the position of the table.
			self.table.topY = $(self.table).offset().top
			self.table.bottomY = self.table.topY + self.table.offsetHeight

			// Add classes to the handle and row.
			$(this).addClass('tabledrag-handle-hover')
			$(item).addClass('drag')

			// Set the document to use the move cursor during drag.
			$('body').addClass('drag')
			if (self.oldRowElement) {
				$(self.oldRowElement).removeClass('drag-previous')
			}

			// Hack for IE6 that flickers uncontrollably if select lists are moved.
			if (navigator.userAgent.indexOf('MSIE 6.') != -1) {
				$('select', this.table).css('display', 'none')
			}

			// Hack for Konqueror, prevent the blur handler from firing.
			// Konqueror always gives links focus, even after returning false on mousedown.
			self.safeBlur = false
		
			// @ Todo better event Handling
			self.$element.trigger('dragRow.tabledrag', self)

			return false
		})

		// Prevent the anchor tag from jumping us to the top of the page.
		handle.click(function () {
			return false
		})

		// Similar to the hover event, add a class when the handle is focused.
		handle.focus(function () {
			$(this).addClass('tabledrag-handle-hover')
			self.safeBlur = true
		})

		// Remove the handle class on blur and fire the same function as a mouseup.
		handle.blur(function (event) {
			$(this).removeClass('tabledrag-handle-hover');
			if (self.rowObject && self.safeBlur) {
				self.dropRow(event, self)
			}
		})

		// Add arrow-key support to the handle.
		handle.keydown(function (event) {
			// If a rowObject doesn't yet exist and this isn't the tab key.
			if (event.keyCode != 9 && !self.rowObject) {
			  self.rowObject = new self.row(item, 'keyboard', self.indentEnabled, self.maxDepth, true);
			}

			var keyChange = false;
			switch (event.keyCode) {
				case 37: // Left arrow.
				case 63234: // Safari left arrow.
					keyChange = true;
					self.rowObject.indent(-1 * self.rtl)
				break
				case 38: // Up arrow.
				case 63232: // Safari up arrow.
					var previousRow = $(self.rowObject.element).prev('tr').get(0)
					while (previousRow && $(previousRow).is(':hidden')) {
						previousRow = $(previousRow).prev('tr').get(0)
					}
					if (previousRow) {
						self.safeBlur = false; // Do not allow the onBlur cleanup.
						self.rowObject.direction = 'up'
						keyChange = true

						if ($(item).is('.tabledrag-root')) {
							// Swap with the previous top-level row.
							var groupHeight = 0
							while (previousRow && $('.indentation', previousRow).size()) {
								previousRow = $(previousRow).prev('tr').get(0)
								groupHeight += $(previousRow).is(':hidden') ? 0 : previousRow.offsetHeight
							}
							if (previousRow) {
								self.rowObject.swap('before', previousRow, self)
								// No need to check for indentation, 0 is the only valid one.
								window.scrollBy(0, -groupHeight)
							}
						}
						else if (self.table.tBodies[0].rows[0] != previousRow || $(previousRow).is('.draggable')) {
							// Swap with the previous row (unless previous row is the first one
							// and undraggable).
							self.rowObject.swap('before', previousRow, self)
							self.rowObject.interval = null
							self.rowObject.indent(0)
							window.scrollBy(0, -parseInt(item.offsetHeight, 10))
						}

						handle.get(0).focus(); // Regain focus after the DOM manipulation.
					}
			    break
				case 39: // Right arrow.
				case 63235: // Safari right arrow.
					keyChange = true;
					self.rowObject.indent(1 * self.rtl);
				break;
				case 40: // Down arrow.
				case 63233: // Safari down arrow.
					var nextRow = $(self.rowObject.group).filter(':last').next('tr').get(0)
					while (nextRow && $(nextRow).is(':hidden')) {
						nextRow = $(nextRow).next('tr').get(0)
					}

					if (nextRow) {
						self.safeBlur = false; // Do not allow the onBlur cleanup.
						self.rowObject.direction = 'down'
						keyChange = true

					 	if ($(item).is('.tabledrag-root')) {
							// Swap with the next group (necessarily a top-level one).
							var groupHeight = 0;
							nextGroup = new self.row(nextRow, 'keyboard', self.indentEnabled, self.maxDepth, false);
							if (nextGroup) {
								$(nextGroup.group).each(function () {
									groupHeight += $(this).is(':hidden') ? 0 : this.offsetHeight
								})

								nextGroupRow = $(nextGroup.group).filter(':last').get(0)
								self.rowObject.swap('after', nextGroupRow, self)

								// No need to check for indentation, 0 is the only valid one.
								window.scrollBy(0, parseInt(groupHeight, 10))
							}
						}
						else {
							// Swap with the next row.
							self.rowObject.swap('after', nextRow, self);
							self.rowObject.interval = null;
							self.rowObject.indent(0);
							window.scrollBy(0, parseInt(item.offsetHeight, 10));
						}

					  handle.get(0).focus(); // Regain focus after the DOM manipulation.
					}
			    break
			}

			if (self.rowObject && self.rowObject.changed == true) {
				$(item).addClass('drag')

				if (self.oldRowElement) {
					$(self.oldRowElement).removeClass('drag-previous')
				}

				self.oldRowElement = item
				self.restripeTable()

				// @ Todo better event Handling
				self.$element.trigger('dragRow.tabledrag', self)
			}

			// Returning false if we have an arrow key to prevent scrolling.
			if (keyChange) {
				return false
			}
		})

		// Compatibility addition, return false on keypress to prevent unwanted scrolling.
		// IE and Safari will suppress scrolling on keydown, but all other browsers
		// need to return false on keypress. http://www.quirksmode.org/js/keys.html
		handle.keypress(function (event) {
			switch (event.keyCode) {
				case 37: // Left arrow.
				case 38: // Up arrow.
				case 39: // Right arrow.
				case 40: // Down arrow.
					return false
			}
		})

		// To stimulate MouseEvent in touch screen devices
		handle.bind('touchstart', function(event) {
			if (event.originalEvent.touches && event.originalEvent.touches.length) {
				var touch = event.originalEvent.touches[0]
			} else if (event.originalEvent.changedTouches && event.originalEvent.changedTouches.length) {
				var touch = event.originalEvent.changedTouches[0]
			}

			var simulatedEvent = document.createEvent("MouseEvent")
			simulatedEvent.initMouseEvent('mousedown', true, true, window, 1,
			                             touch.screenX, touch.screenY, touch.clientX, touch.clientY,
			                             false, false, false, false, 0/*left*/, null);

			touch.target.dispatchEvent(simulatedEvent)
			event.preventDefault()
		})

	}

	/**
	 * Mousemove event handler, bound to document.
	 */
	TableDrag.prototype.dragRow = function (event, self) {
		if (self.dragObject) {
			self.currentMouseCoords = self.mouseCoords(event)

			var y = self.currentMouseCoords.y - self.dragObject.initMouseOffset.y
			var x = self.currentMouseCoords.x - self.dragObject.initMouseOffset.x

			// Check for row swapping and vertical scrolling.
			if (y != self.oldY) {
				self.rowObject.direction = y > self.oldY ? 'down' : 'up'
				self.oldY = y; // Update the old value.

				// Check if the window should be scrolled (and how fast).
				var scrollAmount = self.checkScroll(self.currentMouseCoords.y)

				// Stop any current scrolling.
				clearInterval(self.scrollInterval)

				// Continue scrolling if the mouse has moved in the scroll direction.
				if (scrollAmount > 0 && self.rowObject.direction == 'down' || scrollAmount < 0 && self.rowObject.direction == 'up') {
					self.setScroll(scrollAmount)
				}

				// If we have a valid target, perform the swap and restripe the table.
				var currentRow = self.findDropTargetRow(x, y)

				if (currentRow) {
					if (self.rowObject.direction == 'down') {
						self.rowObject.swap('after', currentRow, self)
					}
					else {
						self.rowObject.swap('before', currentRow, self)
					}
					self.restripeTable()
				}
			}

			// Similar to row swapping, handle indentations.
			if (self.indentEnabled) {
				var xDiff = self.currentMouseCoords.x - self.dragObject.indentMousePos.x

				// Set the number of indentations the mouse has been moved left or right.
				var indentDiff = Math.round(xDiff / self.indentAmount * self.rtl)

				// Indent the row with our estimated diff, which may be further
				// restricted according to the rows around this row.
				var indentChange = self.rowObject.indent(indentDiff)

				// Update table and mouse indentations.
				self.dragObject.indentMousePos.x += self.indentAmount * indentChange * self.rtl
				self.indentCount = Math.max(self.indentCount, self.rowObject.indents)
			}

	    	return false
	  	}
	}

	/**
	 * Mouseup event handler, bound to document.
	 * Blur event handler, bound to drag handle for keyboard support.
	 */
	TableDrag.prototype.dropRow = function (event, self) {
		// Drop row functionality shared between mouseup and blur events.
		if (self.rowObject != null) {
			var droppedRow = self.rowObject.element

			// The row is already in the right place so we just release it.
			if (self.rowObject.changed == true) {
				// Update the fields in the dropped row.
				self.updateFields(droppedRow)

				// If a setting exists for affecting the entire group, update all the
				// fields in the entire dragged group.
				if (!!self.tableSettings.group.fieldClass) {
					for (var n in self.rowObject.children) {
						self.updateField(self.rowObject.children[n], "group");
					}
				}

		      	self.rowObject.markChanged()

		      	if (self.changed == false) {
		    		$(Gleez.theme('tableDragChangedWarning')).insertBefore(self.table).hide().fadeIn('slow')
					self.changed = true
		      	}
		    }

		    if (self.indentEnabled) {
		    	self.rowObject.removeIndentClasses()
		    }
		    if (self.oldRowElement) {
		    	$(self.oldRowElement).removeClass('drag-previous')
		    }

		    $(droppedRow).removeClass('drag').addClass('drag-previous')
		    self.oldRowElement = droppedRow

			// @ Todo better event Handling
			self.$element.trigger('dropRow.tabledrag', self)

		    self.rowObject = null
		}

		// Functionality specific only to mouseup event.
		if (self.dragObject != null) {
			$('.tabledrag-handle', droppedRow).removeClass('tabledrag-handle-hover')

			self.dragObject = null
			$('body').removeClass('drag')
			clearInterval(self.scrollInterval)

			// Hack for IE6 that flickers uncontrollably if select lists are moved.
			if (navigator.userAgent.indexOf('MSIE 6.') != -1) {
				$('select', this.table).css('display', 'block')
			}
		}
	}

	/**
	 * Get the mouse coordinates from the event (allowing for browser differences).
	 */
	TableDrag.prototype.mouseCoords = function (event) {
		if (event.pageX || event.pageY) {
			return { x: event.pageX, y: event.pageY };
		}
		return {
			x: event.clientX + document.body.scrollLeft - document.body.clientLeft,
			y: event.clientY + document.body.scrollTop  - document.body.clientTop
		}
	}

	/**
	 * Given a target element and a mouse event, get the mouse offset from that
	 * element. To do this we need the element's position and the mouse position.
	 */
	TableDrag.prototype.getMouseOffset = function (target, event) {
		var docPos   = $(target).offset()
		var mousePos = this.mouseCoords(event)
		return { x: mousePos.x - docPos.left, y: mousePos.y - docPos.top }
	}

	/**
	 * Find the row the mouse is currently over. This row is then taken and swapped
	 * with the one being dragged.
	 *
	 * @param x
	 *   The x coordinate of the mouse on the page (not the screen).
	 * @param y
	 *   The y coordinate of the mouse on the page (not the screen).
	 */
	TableDrag.prototype.findDropTargetRow = function (x, y) {
		var rows = $(this.table.tBodies[0].rows).not(':hidden')

	  for (var n = 0, len = rows.length; n < len; ++n) {
	    var row = rows[n];
	    var indentDiff = 0;
	    var rowY = $(row).offset().top;
	    // Because Safari does not report offsetHeight on table rows, but does on
	    // table cells, grab the firstChild of the row and use that instead.
	    // http://jacob.peargrove.com/blog/2006/technical/table-row-offsettop-bug-in-safari.
	    if (row.offsetHeight == 0) {
	      var rowHeight = parseInt(row.firstChild.offsetHeight, 10) / 2;
	    }
	    // Other browsers.
	    else {
	      var rowHeight = parseInt(row.offsetHeight, 10) / 2;
	    }

	    // Because we always insert before, we need to offset the height a bit.
	    if ((y > (rowY - rowHeight)) && (y < (rowY + rowHeight))) {
	      if (this.indentEnabled) {
	        // Check that this row is not a child of the row being dragged.
	        for (var n in this.rowObject.group) {
	          if (this.rowObject.group[n] == row) {
	            return null;
	          }
	        }
	      }
	      else {
	        // Do not allow a row to be swapped with itself.
	        if (row == this.rowObject.element) {
	          return null;
	        }
	      }

	      // Check that swapping with this row is allowed.
	      if (!this.rowObject.isValidSwap(row)) {
	        return null;
	      }

	      // We may have found the row the mouse just passed over, but it doesn't
	      // take into account hidden rows. Skip backwards until we find a draggable
	      // row.
	      while ($(row).is(':hidden') && $(row).prev('tr').is(':hidden')) {
	        row = $(row).prev('tr').get(0);
	      }
	      return row;
	    }
	  }
	  return null;
	}

	/**
	 * After the row is dropped, update the table fields according to the settings
	 * set for this table.
	 *
	 * @param changedRow
	 *   DOM object for the row that was just dropped.
	 */
	TableDrag.prototype.updateFields = function (changedRow) {
		for (var group in { group: 1, weight: 1, parent: 1 }) {
			// Each group may have a different setting for relationship, so we find
			// the source rows for each separately.
			this.updateField(changedRow, group)
		}
	}

	/**
	 * After the row is dropped, update a single table field according to specific
	 * settings.
	 *
	 * @param changedRow
	 *   DOM object for the row that was just dropped.
	 * @param group
	 *   The settings group on which field updates will occur.
	 */
	TableDrag.prototype.updateField = function (changedRow, group) {
		var rowSettings = this.rowSettings(group, changedRow)
		, useSibling 	= false
		, sourceRow

		if(rowSettings == undefined || rowSettings.relationship == undefined){
			return
		}
		// Set the row as its own target.
		else if (rowSettings.relationship == 'self' || rowSettings.relationship == 'group') {
			sourceRow = changedRow
		}
		// Siblings are easy, check previous and next rows.
		else if (rowSettings.relationship == 'sibling') {
			var previousRow = $(changedRow).prev('tr').get(0)
			var nextRow 	= $(changedRow).next('tr').get(0)
			sourceRow 		= changedRow

			if ($(previousRow).is('.draggable') && $('.' + group, previousRow).length) {
				if (this.indentEnabled) {
					if ($('.indentations', previousRow).size() == $('.indentations', changedRow)) {
						sourceRow = previousRow
					}
				}
				else {
					sourceRow = previousRow
				}
			}
			else if ($(nextRow).is('.draggable') && $('.' + group, nextRow).length) {
				if (this.indentEnabled) {
					if ($('.indentations', nextRow).size() == $('.indentations', changedRow)) {
						sourceRow = nextRow
					}
				}
				else {
					sourceRow = nextRow
				}
			}
		}
		// Parents, look up the tree until we find a field not in this group.
		// Go up as many parents as indentations in the changed row.
		else if (rowSettings.relationship == 'parent') {
			var previousRow = $(changedRow).prev('tr');
			while (previousRow.length && $('.indentation', previousRow).length >= this.rowObject.indents) {
				previousRow = previousRow.prev('tr')
			}

			// If we found a row.
			if (previousRow.length) {
				sourceRow = previousRow[0]
			}
			// Otherwise we went all the way to the left of the table without finding
			// a parent, meaning this item has been placed at the root level.
			else {
				// Use the first row in the table as source, because it's guaranteed to
				// be at the root level. Find the first item, then compare this row
				// against it as a sibling.
				sourceRow = $(this.table).find('tr.draggable:first').get(0)
				if (sourceRow == this.rowObject.element) {
					sourceRow = $(this.rowObject.group[this.rowObject.group.length - 1]).next('tr.draggable').get(0)
				}
				useSibling = true
			}
		}

		// Because we may have moved the row from one category to another,
		// take a look at our sibling and borrow its sources and targets.
		this.copyDragClasses(sourceRow, changedRow, group)
		rowSettings = this.rowSettings(group, changedRow)

		// In the case that we're looking for a parent, but the row is at the top
		// of the tree, copy our sibling's values.
		if (useSibling) {
			rowSettings.relationship = 'sibling'
			rowSettings.source 		 = rowSettings.fieldClass
		}

		var targetClass = '.' + rowSettings.fieldClass
		var targetElement = $(targetClass, changedRow)

	  // Check if a target element exists in this row.
	  if (targetElement) {
		var sourceClass = '.' + (rowSettings.relationship == 'parent' ? rowSettings.sourceFieldClass : rowSettings.fieldClass)
		var sourceElement = $(sourceClass, sourceRow)
	    switch (rowSettings.action) {
	      case 'depth':
	        // Get the depth of the target row.
	        targetElement.val($('.indentation', sourceElement.closest('tr')).length);
	        break;
	      case 'match':
	        // Update the value.
	        targetElement.val(sourceElement.val());
	        break;
	      case 'order':
	        var siblings = this.rowObject.findSiblings(rowSettings);
	        if ($(targetElement).is('select')) {
	          // Get a list of acceptable values.
	          var values = [];
	          $('option', targetElement).each(function () {
	            values.push(this.value);
	          });
	          var maxVal = values[values.length - 1];
	          // Populate the values in the siblings.
	          $(targetClass, siblings).each(function () {
	            // If there are more items than possible values, assign the maximum value to the row.
	            if (values.length > 0) {
	              this.value = values.shift();
	            }
	            else {
	              this.value = maxVal;
	            }
	          });
	        }
	        else {
	          // Assume a numeric input field.
	          var weight = parseInt($(targetClass, siblings[0]).val(), 10) || 0;
	          $(targetClass, siblings).each(function () {
	            this.value = weight;
	            weight++;
	          });
	        }
	        break;
	    }
	  }
	}

	/**
	 * Copy all special tableDrag classes from one row's form elements to a
	 * different one, removing any special classes that the destination row
	 * may have had.
	 */
	TableDrag.prototype.copyDragClasses = function (sourceRow, targetRow, group) {
		var sourceElement = $('.' + group, sourceRow)
		var targetElement = $('.' + group, targetRow)

		if (sourceElement.length && targetElement.length) {
			targetElement[0].className = sourceElement[0].className
		}
	}

	TableDrag.prototype.checkScroll = function (cursorY) {
		var de  = document.documentElement
		var b  = document.body

		var windowHeight = this.windowHeight = window.innerHeight || (de.clientHeight && de.clientWidth != 0 ? de.clientHeight : b.offsetHeight);
		var scrollY = this.scrollY = (document.all ? (!de.scrollTop ? b.scrollTop : de.scrollTop) : (window.pageYOffset ? window.pageYOffset : window.scrollY));
		var trigger = this.scrollSettings.trigger
		var delta = 0

		// Return a scroll speed relative to the edge of the screen.
		if (cursorY - scrollY > windowHeight - trigger) {
			delta = trigger / (windowHeight + scrollY - cursorY)
			delta = (delta > 0 && delta < trigger) ? delta : trigger
			return delta * this.scrollSettings.amount
		}
		else if (cursorY - scrollY < trigger) {
			delta = trigger / (cursorY - scrollY)
			delta = (delta > 0 && delta < trigger) ? delta : trigger
			return -delta * this.scrollSettings.amount
		}
	}

	TableDrag.prototype.setScroll = function (scrollAmount) {
		var self = this

		this.scrollInterval = setInterval(function () {
			// Update the scroll values stored in the object.
			self.checkScroll(self.currentMouseCoords.y)
			var aboveTable = self.scrollY > self.table.topY
			var belowTable = self.scrollY + self.windowHeight < self.table.bottomY

			if (scrollAmount > 0 && belowTable || scrollAmount < 0 && aboveTable) {
				window.scrollBy(0, scrollAmount);
			}
		}, this.scrollSettings.interval)
	}

	TableDrag.prototype.restripeTable = function () {
		// :even and :odd are reversed because jQuery counts from 0 and
		// we count from 1, so we're out of sync.
		// Match immediate children of the parent element to allow nesting.
		$('> tbody > tr.draggable:visible, > tr.draggable:visible', this.table)
			.removeClass('odd even')
			.filter(':odd').addClass('even').end()
			.filter(':even').addClass('odd');
	}

	/**
	 * Constructor to make a new object to manipulate a table row.
	 *
	 * @param tableRow
	 *   The DOM element for the table row we will be manipulating.
	 * @param method
	 *   The method in which this row is being moved. Either 'keyboard' or 'mouse'.
	 * @param indentEnabled
	 *   Whether the containing table uses indentations. Used for optimizations.
	 * @param maxDepth
	 *   The maximum amount of indentations this row may contain.
	 * @param addClasses
	 *   Whether we want to add classes to this row to indicate child relationships.
	 */
	TableDrag.prototype.row = function (tableRow, method, indentEnabled, maxDepth, addClasses) {
		this.element 		= tableRow
		this.method 		= method
		this.group 			= [tableRow]
		this.groupDepth 	= $('.indentation', tableRow).size()
		this.changed 		= false
		this.table 			= $(tableRow).parents('table:first').get(0)
		this.indentEnabled 	= indentEnabled
		this.maxDepth 		= maxDepth
		this.direction 		= ''; // Direction the row is being moved.

		if (this.indentEnabled) {
			this.indents 	= $('.indentation', tableRow).size()
			this.children 	= this.findChildren(addClasses)
			this.group 		= $.merge(this.group, this.children)

			// Find the depth of this entire group.
			for (var n = 0; n < this.group.length; n++) {
				this.groupDepth = Math.max($('.indentation', this.group[n]).size(), this.groupDepth)
			}
		}

		return this
	}

	/**
	 * Find all children of rowObject by indentation.
	 *
	 * @param addClasses
	 *   Whether we want to add classes to this row to indicate child relationships.
	 */
	TableDrag.prototype.row.prototype.findChildren = function (addClasses) {
		var parentIndentation = this.indents;
		var currentRow = $(this.element, this.table).next('tr.draggable');
		var rows = [];
		var child = 0;

		while (currentRow.length) {
			var rowIndentation = $('.indentation', currentRow).length

			// A greater indentation indicates this is a child.
			if (rowIndentation > parentIndentation) {
				child++
				rows.push(currentRow[0])
				if (addClasses) {
					$('.indentation', currentRow).each(function (indentNum) {
						if (child == 1 && (indentNum == parentIndentation)) {
							$(this).addClass('tree-child-first')
						}
						if (indentNum == parentIndentation) {
							$(this).addClass('tree-child')
						}
						else if (indentNum > parentIndentation) {
							$(this).addClass('tree-child-horizontal')
						}
					})
				}
			}
			else {
				break
			}

			currentRow = currentRow.next('tr.draggable')
		}

		if (addClasses && rows.length) {
			$('.indentation:nth-child(' + (parentIndentation + 1) + ')', rows[rows.length - 1]).addClass('tree-child-last')
		}

		return rows
	}

	/**
	 * Ensure that two rows are allowed to be swapped.
	 *
	 * @param row
	 *   DOM object for the row being considered for swapping.
	 */
	TableDrag.prototype.row.prototype.isValidSwap = function (row) {
		if (this.indentEnabled) {
			var prevRow, nextRow

			if (this.direction == 'down') {
				prevRow = row
				nextRow = $(row).next('tr').get(0)
			}
			else {
				prevRow = $(row).prev('tr').get(0)
				nextRow = row
			}

			this.interval = this.validIndentInterval(prevRow, nextRow);

			// We have an invalid swap if the valid indentations interval is empty.
			if (this.interval.min > this.interval.max) {
				return false
			}
		}

		// Do not let an un-draggable first row have anything put before it.
		if (this.table.tBodies[0].rows[0] == row && $(row).is(':not(.draggable)')) {
			return false
		}

		return true
	}

	/**
	 * Perform the swap between two rows.
	 *
	 * @param position
	 *   Whether the swap will occur 'before' or 'after' the given row.
	 * @param row
	 *   DOM element what will be swapped with the row group.
	 */
	TableDrag.prototype.row.prototype.swap = function (position, row, self) {
		$(row)[position](this.group)
		this.changed = true

		// @ Todo better event Handling
		// Allows a custom handler when a row is swapped.
		self.$element.trigger('swapRow.tabledrag', row)
	}

	/**
	 * Determine the valid indentations interval for the row at a given position
	 * in the table.
	 *
	 * @param prevRow
	 *   DOM object for the row before the tested position
	 *   (or null for first position in the table).
	 * @param nextRow
	 *   DOM object for the row after the tested position
	 *   (or null for last position in the table).
	 */
	TableDrag.prototype.row.prototype.validIndentInterval = function (prevRow, nextRow) {
		var minIndent, maxIndent;

		// Minimum indentation:
		// Do not orphan the next row.
		minIndent = nextRow ? $('.indentation', nextRow).size() : 0

		// Maximum indentation:
		if (!prevRow || $(prevRow).is(':not(.draggable)') || $(this.element).is('.tabledrag-root')) {
			// Do not indent:
			// - the first row in the table,
			// - rows dragged below a non-draggable row,
			// - 'root' rows.
			maxIndent = 0
		}
		else {
			// Do not go deeper than as a child of the previous row.
			maxIndent = $('.indentation', prevRow).size() + ($(prevRow).is('.tabledrag-leaf') ? 0 : 1)

			// Limit by the maximum allowed depth for the table.
			if (this.maxDepth) {
				maxIndent = Math.min(maxIndent, this.maxDepth - (this.groupDepth - this.indents))
			}
		}

		return { 'min': minIndent, 'max': maxIndent }
	}

	/**
	 * Indent a row within the legal bounds of the table.
	 *
	 * @param indentDiff
	 *   The number of additional indentations proposed for the row (can be
	 *   positive or negative). This number will be adjusted to nearest valid
	 *   indentation level for the row.
	 */
	TableDrag.prototype.row.prototype.indent = function (indentDiff) {
		// Determine the valid indentations interval if not available yet.
		if (!this.interval) {
			var prevRow   = $(this.element).prev('tr').get(0)
			var nextRow   = $(this.group).filter(':last').next('tr').get(0)
			this.interval = this.validIndentInterval(prevRow, nextRow)
		}

		// Adjust to the nearest valid indentation.
		var indent 	= this.indents + indentDiff
		indent 		= Math.max(indent, this.interval.min)
		indent 		= Math.min(indent, this.interval.max)
		indentDiff 	= indent - this.indents

		for (var n = 1; n <= Math.abs(indentDiff); n++) {
			// Add or remove indentations.
			if (indentDiff < 0) {
				$('.indentation:first', this.group).remove()
				this.indents--
			}
			else {
				$('td:first', this.group).prepend(Gleez.theme('tableDragIndentation'))
				this.indents++
			}
		}

		if (indentDiff) {
			// Update indentation for this row.
			this.changed = true
			this.groupDepth += indentDiff
			this.onIndent()
		}

		return indentDiff
	}

	/**
	 * Find all siblings for a row, either according to its subgroup or indentation.
	 * Note that the passed in row is included in the list of siblings.
	 *
	 * @param settings
	 *   The field settings we're using to identify what constitutes a sibling.
	 */
	TableDrag.prototype.row.prototype.findSiblings = function (rowSettings) {
	  var siblings = [];
	  var directions = ['prev', 'next'];
	  var rowIndentation = this.indents;
	  for (var d = 0; d < directions.length; d++) {
	    var checkRow = $(this.element)[directions[d]]();
	    while (checkRow.length) {
	      // Check that the sibling contains a similar target field.
	      if ($('.' + rowSettings.target, checkRow)) {
	        // Either add immediately if this is a flat table, or check to ensure
	        // that this row has the same level of indentation.
	        if (this.indentEnabled) {
	          var checkRowIndentation = $('.indentation', checkRow).length;
	        }

	        if (!(this.indentEnabled) || (checkRowIndentation == rowIndentation)) {
	          siblings.push(checkRow[0]);
	        }
	        else if (checkRowIndentation < rowIndentation) {
	          // No need to keep looking for siblings when we get to a parent.
	          break;
	        }
	      }
	      else {
	        break;
	      }
	      checkRow = $(checkRow)[directions[d]]();
	    }
	    // Since siblings are added in reverse order for previous, reverse the
	    // completed list of previous siblings. Add the current row and continue.
	    if (directions[d] == 'prev') {
	      siblings.reverse()
	      siblings.push(this.element)
	    }
	  }
	  return siblings;
	};

	/**
	 * Remove indentation helper classes from the current row group.
	 */
	TableDrag.prototype.row.prototype.removeIndentClasses = function () {
		for (var n in this.children) {
			$('.indentation', this.children[n])
			  .removeClass('tree-child')
			  .removeClass('tree-child-first')
			  .removeClass('tree-child-last')
			  .removeClass('tree-child-horizontal')
		}
	}

	/**
	 * Add an asterisk or other marker to the changed row.
	 */
	TableDrag.prototype.row.prototype.markChanged = function () {
		var marker = Gleez.theme('tableDragChangedMarker')
		var cell   = $('td:first', this.element)

		if ($('span.tabledrag-changed', cell).length == 0) {
			cell.append(marker)
		}
	}

	/**
	 * Stub function. Allows a custom handler when a row is indented.
	 */
	TableDrag.prototype.row.prototype.onIndent = function () {
		return null
	}

	Gleez.theme.prototype.tableDragChangedMarker = function () {
		return '<span class="warning tabledrag-changed">*</span>'
	}

	Gleez.theme.prototype.tableDragIndentation = function () {
		return '<div class="indentation">&nbsp;</div>'
	}

	Gleez.theme.prototype.tableDragChangedWarning = function () {
		return '<div class="tabledrag-changed-warning  alert alert-warning">' + Gleez.theme('tableDragChangedMarker') + ' ' + 'Changes made in this table will not be saved until the form is submitted.' + '</div>';
	}

	// GREET TABLEDRAG PLUGIN DEFINITION
	// =======================

	var old = $.fn.tabledrag

	$.fn.tabledrag = function (option) {
		// ugly hack to access the object for widgets
		$.fn.extend({
			vObject: function() {
				return $(this).data('tabledrag')
			}
		})

		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('tabledrag')
			var options = $.extend({}, $.fn.tabledrag.defaults, $this.data(), typeof option == 'object' && option)

			if (!data) $this.data('tabledrag', (data = new TableDrag(this, options)))
			if (typeof option == 'string') data[option]()
		})
	}

	$.fn.tabledrag.defaults = {
		draggableClass: 'draggable'

		// The parent option allows elements to be children of one another.
		// Pass a "falsy" value to fieldClass to deactivate.
		// To allow the child-parent relationship to function correctly, each row must contain an input 
		// with the class defined in sourceFieldClass wich represents the current row. 
		// This can be a hidden input.
		, parent: {
			// The class of the field containing the "parent" id. Can be any form item.
			fieldClass: 'row-parent',

			// The class of the field containing the current "row" id. Can be any form item.
			sourceFieldClass: 'row-id',

			// Hides the parent <td> of the "parent" field for better usability.
			hidden: true
    	}

		// The weight option allows elements to be sorted. 
		// Pass a "falsy" value to fieldClass to deactivate (false, null, undefined, etc).
		, weight: {
			// The class of the <select> list. Weights will be deduced by the <option>s in this list. <option> values should be integers.
			fieldClass: 'row-weight',

			// Hides the <select>s parent <td> for better usability.
			hidden: true
		}

		// The group option allows elements to be dragged with their children as a whole. 
  		// This is usually what you want.
  		// It's related to parent and can only function with it.
  		// Pass a "falsy" value to fieldClass to deactivate.
		, group: {
			// The class of the field containing the row "depth". Can be any form item.
			fieldClass: 'row-parent', 

			// The depth limit to which items can be nested and dragged as a group.
			depthLimit: 15 
		}
	}

	$.fn.tabledrag.Constructor = TableDrag


	// GREET TABLEDRAG NO CONFLICT
	// =================

	$.fn.tabledrag.noConflict = function () {
		$.fn.tabledrag = old
		return this
	}

	// GREET TABLEDRAG DATA-API
	// ==============

	$(window).on('load.tabledrag.data-api', function (e) {
		$('[data-toggle="tabledrag"]').each(function () {
			var $table = $(this)
			$table.tabledrag($table.data())
		})
	})
	
	// Added pajax and jquery mobile support
	$(document).on('pjax:complete pagecontainerchange', function (e) {
		$('[data-toggle="tabledrag"]').each(function () {
			var $table = $(this)
			$table.tabledrag($table.data())
		})
	})

}(jQuery);