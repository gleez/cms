jQuery(document).ready(function($) {
   // This turns any form into a "post-in-place" form so it is ajaxed to save
   // without a refresh. The form must be within an element with the "AjaxForm"
   // class.
    $.fn.handleAjaxForm = function(options) {
        var handle = this;
      
        var handleValidationErrors = function(errors, form) {
            
            var errorTitle = 'Some errors were encountered, please check the details you entered.';
            // Build our error template
            var tmpl = '<div class="alert alert-error">';
			tmpl += '<p class="message">' + errorTitle + '</p>';
			tmpl += '<ul>';
                        // Loop through the errors 
			$.each(errors.ErrorMessages, function(i, value) {
                            // And add the error to the list.
                            tmpl += '<li>' + value + '</li>';
                            // Let's guesstimate the input that gave us an error
                            var $inputField = $('[name*="'+i+'"]');
                            // .. and error classes to the input field, it's parent div, and the div above that.
                            // This is a quick & dirty workaround since my DOM differs a bit compared to Twitter Bootstrap's.						
                            if ($inputField.length)
                            {
                                $($inputField).parent('div.controls').parent('div.control-group').addClass('error');
                            }
			});					
			tmpl += '</ul>';
			tmpl += '</div>';
        
            // If the target block doesn't exist..
            if (!$('.error-message-container').length) {
                // Create it.
                $(form).prepend('<div class="error-message-container" style="display:none"></div>');
            }
         
            // Empty any previous error messages, insert the new errors and slide it in to view.
            $('.error-message-container').empty().html(tmpl).slideDown(250);
        };
   
      $(this).find('form').each(function() {
         options = $.extend({
            frm:  this,
            //data: { 'DeliveryType' : 'ASSET', 'DeliveryMethod' : 'JSON' },
            dataType: 'json',
            beforeSubmit: function(frm_data, frm) {
               options.frm = frm;
              // Add a spinner
              var btn = $(frm).find('input.btn:last');
              if ($(btn).parent().find('span.Progress').length == 0) {
                 $(btn).after('<span class="Progress">&#160;</span>');
              }
            },
            error: function(xhr) {
               Gleez.informError(xhr);
            },
            success: function(json, status, $frm) {
               json = $.postParseJson(json);
               //console.log( json );
               if (json.FormSaved == true) {
                  Gleez.inform(json);
                  if (json.RedirectUrl && options.Redirect) {
                     setTimeout("document.location='" + json.RedirectUrl + "';", 300);
                  } else if(json.DeliveryType == 'ASSET') {
                     $frm.parents($(handle).selector).html(json.Data);
                  } else {
                     // Remove the spinner if not redirecting...
                     $('span.Progress').remove();
                     $('#modal-form').modal('hide');
                  }
               } else {
                    if (json.FormSaved == false && json.ErrorMessages)
                    {
                        return handleValidationErrors(json, $frm);	
                    }
                  // Check to see if a target has been specified for the data.
                  if(json.Target) {
                     $(json.Target).html(json.Data);
                  } else if(json.DeliveryType == 'MESSAGE') {
                     Gleez.inform(json.Data, false);
                     $frm.find('span.Progress').remove();
                  } else {
                     $frm.parents($(handle).selector).html(json.Data);
                  }
               }
               // If there are additional targets in the result then set them now.
               if(json.Targets) {
                  for(var i = 0; i < json.Targets.length; i++) {
                     var item = json.Targets[i];
                     if(item.Type == 'Text') {
                        $(item.Target).text($.base64Decode(item.Data));
                     } else {
                        $(item.Target).html($.base64Decode(item.Data));
                     }
                  }
               }
               
               // Re-attach the handler
               $($(handle).selector).handleAjaxForm(options);
             }
         }, options || {});
         
         $(this).ajaxForm(options);
      });
   }
});
