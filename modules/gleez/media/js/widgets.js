(function ($) {

/**
 * Move a widget in the widgetss table from one region to another via select list.
 *
 * This behavior is dependent on the tableDrag behavior, since it uses the
 * objects initialized in that behavior to update the row.
 */
Gleez.behaviors.widgetDrag = {
  attach: function (context, settings) {
    // tableDrag is required and we should be on the widgets admin page.
    if (typeof Gleez.tableDrag == 'undefined' || typeof Gleez.tableDrag.widgets == 'undefined') {
      return;
    }

    var table = $('table#widgets');
    var tableDrag = Gleez.tableDrag.widgets; // Get the blocks tableDrag object.

    // Add a handler for when a row is swapped, update empty regions.
    tableDrag.row.prototype.onSwap = function (swappedRow) {
      checkEmptyRegions(table, this);
    };

    // A custom message for the blocks page specifically.
    Gleez.theme.tableDragChangedWarning = function () {
      return '<div class="tabledrag-changed-warning alert alert-warning">' + Gleez.theme('tableDragChangedMarker') + ' ' + Gleez.t('The changes to these widgets will not be saved until the <em>Save widgets</em> button is clicked.') + '</div>';
    };

    // Add a handler so when a row is dropped, update fields dropped into new regions.
    tableDrag.onDrop = function () {
      dragObject = this;
      // Use "region-message" row instead of "region" row because
      // "region-{region_name}-message" is less prone to regexp match errors.
      var regionRow = $(dragObject.rowObject.element).prevAll('tr.region-message').get(0);
      var regionName = regionRow.className.replace(/([^ ]+[ ]+)*region-([^ ]+)-message([ ]+[^ ]+)*/, '$2');
      var regionField = $('select.widget-region-select', dragObject.rowObject.element);
      // Check whether the newly picked region is available for this block.
      if ($('option[value=' + regionName + ']', regionField).length == 0) {
        // If not, alert the user and keep the block in its old region setting.
        alert(Gleez.t('The widget cannot be placed in this region.'));
        // Simulate that there was a selected element change, so the row is put
        // back to from where the user tried to drag it.
        regionField.change();
      }
      else if ($(dragObject.rowObject.element).prev('tr').is('.region-message')) {
        var weightField = $('select.widget-weight', dragObject.rowObject.element);
        var oldRegionName = weightField[0].className.replace(/([^ ]+[ ]+)*widget-weight-([^ ]+)([ ]+[^ ]+)*/, '$2');

        if (!regionField.is('.widget-region-' + regionName)) {
          regionField.removeClass('widget-region-' + oldRegionName).addClass('widget-region-' + regionName);
          weightField.removeClass('widget-weight-' + oldRegionName).addClass('widget-weight-' + regionName);
          regionField.val(regionName);
        }
      }
    };

    // Add the behavior to each region select list.
    $('select.widget-region-select', context).once('widget-region-select', function () {
      $(this).change(function (event) {
        // Make our new row and select field.
        var row = $(this).parents('tr:first');
        var select = $(this);
        tableDrag.rowObject = new tableDrag.row(row);

        // Find the correct region and insert the row as the first in the region.
        $('tr.region-message', table).each(function () {
          if ($(this).is('.region-' + select[0].value + '-message')) {
            // Add the new row and remove the old one.
            $(this).after(row);
            // Manually update weights and restripe.
            tableDrag.updateFields(row.get(0));
            tableDrag.rowObject.changed = true;
            if (tableDrag.oldRowElement) {
              $(tableDrag.oldRowElement).removeClass('drag-previous');
            }
            tableDrag.oldRowElement = row.get(0);
            tableDrag.restripeTable();
            tableDrag.rowObject.markChanged();
            tableDrag.oldRowElement = row;
            $(row).addClass('drag-previous');
          }
        });

        // Modify empty regions with added or removed fields.
        checkEmptyRegions(table, row);
        // Remove focus from selectbox.
        select.get(0).blur();
      });
    });

    var checkEmptyRegions = function (table, rowObject) {
      $('tr.region-message', table).each(function () {
        // If the dragged row is in this region, but above the message row, swap it down one space.
        if ($(this).prev('tr').get(0) == rowObject.element) {
          // Prevent a recursion problem when using the keyboard to move rows up.
          if ((rowObject.method != 'keyboard' || rowObject.direction == 'down')) {
            rowObject.swap('after', this);
          }
        }
        // This region has become empty.
        if ($(this).next('tr').is(':not(.draggable)') || $(this).next('tr').size() == 0) {
          $(this).removeClass('region-populated').addClass('region-empty');
        }
        // This region has become populated.
        else if ($(this).is('.region-empty')) {
          $(this).removeClass('region-empty').addClass('region-populated');
        }
      });
    };
  }
};

})(jQuery);