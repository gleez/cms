/*
This is a highly modified version of the vanillaforms jquery.popup.js.

@package    Gleez\Facebox
@version    2.0
@version     1.0 (12/19/2007)
@author     Sandeep Sangamreddi - Gleez
@author     Chris Wanstrath [ chris@ozmm.org ]
@copyright  (c) 2011-2013 Gleez Technologies
@copyright  (c) 2007 Chris Wanstrath
@license    http://gleezcms.org/license Gleez CMS License
@license    http://www.opensource.org/licenses/mit-license.php
*/

(function($) {

   // Allows generating a popup by jQuery.popup('contents')
   $.popup = function(options, data) {
      var settings = $.extend({}, $.popup.settings, options);
      $.popup.init(settings)
      if (!settings.confrm)
         $.popup.loading(settings)

      $.isFunction(data) ? data.call(this, settings) : $.popup.reveal(settings, data)
   }

   $.fn.popup = function(options) {
      // IE7 or less gets no popups because they're jerks
      if ($.browser.msie && parseInt($.browser.version, 10) < 8)
         return false;
   
      // Merge the two settings arrays into a central data store
      var settings = $.extend({}, $.popup.settings, options);
      var sender = this;

      this.on('click', function () {
         settings.sender = this;
         $.extend(settings, { popupType: $(this).attr('popupType') });

         $.popup.init(settings);
         if (!settings.confirm)
            $.popup.loading(settings);

         var target = $.popup.findTarget(settings);
         if (settings.confirm) {
            // Bind to the "Okay" button click
            $('#'+settings.popupId+' .Okay').focus().click(function() {
               if (settings.followConfirm) {
                  // follow the target
                  document.location = target;
               } else {
                  // request the target via ajax
                  $.ajax({
                     type: "GET",
                     url: target,
                     //data: {'DeliveryType' : settings.deliveryType, 'DeliveryMethod' : 'JSON'},
                     dataType: 'json',
                     error: function(xhr) {
                        Gleez.informError(xhr);
                     },
                     success: function(json) {
                        json = $.postParseJson(json);

                        $.popup.close(settings);
                        settings.afterConfirm(json, settings.sender);
                        Gleez.inform(json);
                        if (json.RedirectUrl  && settings.redirect)
                           setTimeout(function() { document.location.replace(json.RedirectUrl); }, 300);
                     }
                  });
               }
            });
         } else {
            if (target) {
               $.ajax({
                  type: 'GET',
                  url: target,
                  error: function(request, textStatus, errorThrown) {
                     $.popup.reveal(settings, request.responseText);
                  },
                  success: function(data) {
                     $.popup.reveal(settings, data);
                  }
               });
            }
         }
        
         return false;
      });
    
      this.mouseover(function() {
         settings.sender = this;
         if ($.popup.findTarget(settings))
            $(this).addClass(settings.mouseoverClass);
      });
    
      this.mouseout(function() {
         settings.sender = this;
         if ($.popup.findTarget(settings))
            $(this).removeClass(settings.mouseoverClass);
      });
    
      return this;
   }
  
   $.popup.findTarget = function(settings) {
      settings.foundTarget = settings.targetUrl;
    
      // See if the matched element was an anchor. If it was, use the href.
      if (!settings.foundTarget && $(settings.sender).attr('href') != 'undefined') {
         settings.foundTarget = settings.sender.href;
      } else {
         // See if there were any anchors within the matched element.
         // If there are, use the href from the first one.
         if (!settings.foundTarget) {
            var anchor = $(settings.sender).find('a:first');
            if (anchor.length > 0)
               settings.foundTarget = anchor[0].href;
         }
      }
    
      return settings.foundTarget;
   }

   // Close a jquery popup and release escape key bindings
   $.popup.close = function(settings, response) {
      $(document).unbind('keydown.popup');
      $('#'+settings.popupId).trigger('popupClose');
      $('.Overlay').remove();
    
      return false;
   }
   
   $.popup.init = function(settings) {
      // Define a unique identifier for this popup
      var i = 1;
      var popupId = 'Popup';
      while ($('#'+popupId).size() > 0) {
         popupId = 'Popup'+i;
         i++;
      }
      settings.popupId = popupId;
      var popupHtml = '';
      if (!settings.confirm)
         popupHtml = settings.popupHtml;
      else
         popupHtml = settings.confirmHtml;
    
      popupHtml = popupHtml.replace('{popup.id}', settings.popupId);
    
      $('body').append(popupHtml);
      if (settings.containerCssClass != '')
         $('#'+settings.popupId).addClass(settings.containerCssClass);
      
      var pagesize = $.popup.getPageSize();
      $('div.Overlay').css({height: pagesize[1]});
    
      var pagePos = $.popup.getPagePosition();
      $('#'+settings.popupId).css({
         top: pagePos.top,
         left: pagePos.left
      });
      $('#'+settings.popupId).show();

      $(document).bind('keydown.popup', function(e) {
         if (e.keyCode == 27)
            $.popup.close(settings);
      })    

      if (settings.onUnload) {
         $('#'+settings.popupId).bind('popupClose',function(){
            setTimeout(settings.onUnload,1);
         });
      }

      // Replace language definitions
      if (!settings.confirm) {
         $('#'+settings.popupId+' .Close').click(function() {
            return $.popup.close(settings);
         });
      } else {
         $('#'+settings.popupId+' .Content h1').text(gdn.definition('ConfirmHeading', 'Confirm'));
         $('#'+settings.popupId+' .Content p').text(gdn.definition('ConfirmText', 'Are you sure you want to do that?'));
         $('#'+settings.popupId+' .Okay').val(gdn.definition('Okay', 'Okay'));
         $('#'+settings.popupId+' .Cancel').val(gdn.definition('Cancel', 'Cancel')).click(function() {
            $.popup.close(settings);
         });
      }
   }

   $.popup.loading = function(settings) {
      settings.onLoad(settings);
      if ($('#'+settings.popupId+' .Loading').length == 1)
         return true;
    
      $('#'+settings.popupId+' .Content').empty();
      //$('#'+settings.popupId+' .Body').children().hide().end().append('<div class="Loading">&#160;</div>');
      $('#'+settings.popupId+' .Border').hide();
      $('#'+settings.popupId+'.Popup').append('<div class="Loading">&#160;</div>');
      // Trigger an even that plugins can attach to when popups are loading.
      $('body').trigger('popupLoading');
   }
  
   $.popup.reveal = function(settings, data) {
      // First see if we've retrieved json or something else
      var json = false;
      if (data instanceof Array) {
         json = false;
      } else if (data !== null && typeof(data) == 'object') {
         json = data;
      }

      if (json == false) {
         // This is something other than json, so just put it into the popup directly
         if (data) // Prevent blank popups
            $('#'+settings.popupId+' .Content').append(data);
      } else {
         Gleez.inform(json);
         formSaved = json['FormSaved'];
         data = json['Data'];

         // Add any js that's come in.
         $(json.js).each(function(i, el){
            var v_js  = document.createElement('script');
            v_js.type = 'text/javascript';
            v_js.src = gdn.url(el);
            document.getElementsByTagName('head')[0].appendChild(v_js);
         });

         // mosullivan - need to always reload the data b/c when uninviting ppl
         // we need to reload the invitation table. Is there a reason not to reload
         // the content?
         // if (formSaved == false)
         if (data) // Prevent blank popups
            $('#'+settings.popupId+' .Content').html(data);
      }
    
      $('#'+settings.popupId+' .Loading').remove();
      $('#'+settings.popupId+' .Border').fadeIn('normal');
      $('#'+settings.popupId+' .Body h1').text(Gleez.settings.pageTitle);
      
      $('#'+settings.popupId+' .Close').unbind().click(function() {
         return $.popup.close(settings);
      });

      settings.afterLoad();
    
      // Now, if there are any forms in the popup, hijack them if necessary.
      if (settings.hijackForms == true) {
         $('#'+settings.popupId+' form').ajaxForm({
            dataType: 'json',
            beforeSubmit: function() {
	       $('.error-message-container').slideUp(250); //// Hide any errorContainers
               settings.onSave(settings); // Notify the user that it is being saved.
            },  
            success: function(json) {
               json = $.postParseJson(json);
               Gleez.inform(json);

               if (json.FormSaved == true) {
                  if (json.RedirectUrl && settings.redirect)
                     setTimeout(function() { document.location.replace(json.RedirectUrl); }, 300);

                  settings.afterSuccess(settings, json);
                  $.popup.close(settings, json);
               } else {
                     if (json.FormSaved == false && json.ErrorMessages){
                        return $.popup.handleValidationErrors(json, '#'+settings.popupId+' form');	
                     }
                  $.popup.reveal(settings, json) // Setup the form again
               }
            },
            error: function(xhr) {
               $('.InProgress', this).removeClass('InProgress');
               //console.log(this.url);
               alert( Gleez.ajaxError(xhr, this.url) );
               $.popup.close(settings, json);
            }
         });


         // Hijack links to navigate within the same popup.
         $('#'+settings.popupId+' .PopLink').click(function() {
            $.popup.loading(settings);

            // Ajax the link into the current popup.
            $.get($(this).attr('href'), {'DeliveryType': settings.deliveryType}, function(data, textStatus, xhr) {
               if (typeof(data) == 'object') {
                  if (data.RedirectUrl)
                     setTimeout(function() { document.location.replace(data.RedirectUrl); }, 300);

                  $.postParseJson(data);
               }
               $.popup.reveal(settings, data);
               //$('#'+settings.popupId+' .Content').html(data);
            });

            return false;
         });
      }
    
      // If there is a cancel button in the popup, hide it (the popup has it's own close button)
      $('#'+settings.popupId+' a.Cancel').hide();
      $('#'+settings.popupId+' input.btn-danger').hide();

      // Trigger an even that plugins can attach to when popups are revealed.
      $('body').trigger('popupReveal');

      return false;
   }
  
   $.popup.settings = {
      targetUrl:        false,        // Use this URL instead of one provided by the matched element?
      confirm:          false,        // Pop up a confirm message?
      followConfirm:    false,        // Follow the confirm url after OK, or request it with ajax?
      model:            false,        // current model name
      model_id:         false,        // current model id
      afterConfirm:     function(json, sender) {
         // Called after the confirm url has been loaded via ajax
      },                              // Event to fire if the confirm was ajaxed
      hijackForms:      true,         // Hijack popup forms so they are handled in-page instead of posting the entire page back
      //deliveryType:     'VIEW',            // Adds DeliveryType=3 to url so Garden doesn't pull the entire page
      mouseoverClass:   'Popable',    // CssClass to be applied to a popup link when hovering
      onSave:           function(settings) {
         if (settings.sender) {
            $('#'+settings.popupId+' .btn:submit').attr('disabled', true).addClass('InProgress');
         }
      },
      onLoad:           function(settings) {
         // Called before the "loading..." is displayed
      },
      onUnload:         function(settings, response) {
         // Called after the popup is closed
      },
      afterSuccess:     function() {
         // Called after an ajax request resulted in success, and before "close" is called.
      },
      containerCssClass: '',
      popupHtml:       '\
  <div class="Overlay"> \
    <div id="{popup.id}" class="Popup"> \
      <div class="Border"> \
        <div class="Body"> <h1>&nbsp;</h1> \
          <div class="Content well"> \
          </div> \
          <div class="Footer"> \
            <a href="#" class="Close"><span>×</span></a> \
          </div> \
        </div> \
      </div> \
    </div> \
  </div>',
      confirmHtml:       '\
  <div class="Overlay"> \
    <div id="{popup.id}" class="Popup"> \
      <div class="Border"> \
        <div class="Body"> \
          <div class="Content"><h1>Confirm</h1><p>Are you sure you want to do that?</p></div> \
          <div class="Footer"> \
            <input type="button" class="btn Okay" value="Okay" /> \
            <input type="button" class="btn Cancel" value="Cancel" /> \
          </div> \
        </div> \
      </div> \
    </div> \
  </div>',
      afterLoad: function() {}
   }

   $.popup.inFrame = function() {
      try {
         if (top !== self && $(parent.document).width())
            return true;
      } catch(e) { }
    
      return false;
   }
  
   $.popup.getPageSize = function() {
      var inFrame = $.popup.inFrame();
      var doc = $(inFrame ? parent.document : document);
      var win = $(inFrame ? parent.window : window);
      arrayPageSize = new Array(
         $(doc).width(),
         $(doc).height(),
         $(win).width(),
         $(win).height()
      );
      return arrayPageSize;
   };
  
   $.popup.getPagePosition = function() {
      var inFrame = $.popup.inFrame();
      var doc = $(inFrame ? parent.document : document);
      var win = $(inFrame ? parent.window : window);
      var scroll = { 'top':doc.scrollTop(), 'left':doc.scrollLeft() };
      var t = scroll.top + ($(win).height() / 10);
      if (inFrame) {
         var el = $(parent.document).find('iframe[id^=gleez]');
         el = el ? el : $(document); // Just in case iframe is not id'd properly
         t -= (el.offset().top);
         // Don't slide above or below the frame bounds.
         var diff = $(doc).height() - $(document).height();
         var maxOffset = $(document).height() - diff;
         if (t < 0) {
            t = 0;
         } else if (t > maxOffset) {
            t = maxOffset;
         }
      }
      return {'top':t, 'left':scroll.left};
   };

   $.popup.handleValidationErrors = function(errors, form) {
      var errorTitle = Gleez.t('Some errors were encountered, please check the details you entered.');
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
                     if ($inputField.length){
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
      $('.InProgress').removeClass('InProgress');
      $(form+' .btn:submit').removeAttr('disabled');
   };
   
})(jQuery);
