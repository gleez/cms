(function ($) {

    /**
     * Attaches the autocomplete behavior to all required fields.
     */
    $(document).on('attach.autocomplete', function (e) {
	$('input.form-autocomplete', e.target).typeahead({
	    source: function(query, process){
		Gleez.searchItems(this.$element, query, process);
	    },
	});
    });

    Gleez.searchItems = function(element, query, process) {
	// Get the desired term and construct the autocomplete URL for it.
        var term = Gleez.autocompleteExtractLast(query);
	
	$.ajax({
	    url: $(element).data('autocompletePath') + '/' + encodeURIComponent(term),
	    dataType: "JSON",
	    //async: false,
	    success: function(results){
		var items = new Array;
		$.map(results, function(data, item){
		    items.push(data);
		});
		process(items);
	    }
	});
    };

    Gleez.autocompleteSplit = function(val) {
	return val.split(/,\s*/);
    };
    
    Gleez.autocompleteExtractLast = function(term) {
	return Gleez.autocompleteSplit(term).pop();
    };

})(jQuery);
