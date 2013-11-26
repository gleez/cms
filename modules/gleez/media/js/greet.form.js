/**
 * Auxiliary utilities for forms elements
 *
 * @package    Greet\Form
 * @version    1.0.0
 * @requires   jQuery v1.9 or later
 * @requires   jQuery Textarea Characters Counter Plugin v 2.0 or later
 * @author     Gleez Team
 * @copyright  (c) 2005-2013 Gleez Technologies
 * @license    The MIT License (MIT)
 */

!function ($) { "use strict";

	var textareaOptions = {
		'maxCharacterSize': $('#countTextarea').data("maxChars"),
		'originalStyle'   : 'help-block',
		'warningStyle'    : 'help-block-warning',
		'warningNumber'   : 40,
		'displayFormat'   : $('#countTextarea').data("displayFormat")
	};

	var inputOptions = {
		'maxCharacterSize': $('#countInput').data("maxChars"),
		'originalStyle'   : 'help-block',
		'warningStyle'    : 'help-block-warning',
		'warningNumber'   : 10,
		'displayFormat'   : $('#countInput').data("displayFormat")
	};

	// Textarea counter
	$('#countTextarea').textareaCount(textareaOptions);

	// Input counter
	$('#countInput').textareaCount(inputOptions);

}(window.jQuery);
