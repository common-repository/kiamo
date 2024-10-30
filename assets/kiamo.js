// Kiamo
Kiamo = {
  init: function(){
    if (!jQuery('#fake-kiamo-chat')) return;
    if (jQuery.cookie('hide-chat') == 'true' || window.screen.width < 768){
      jQuery('#fake-kiamo-chat').hide();
    }
    setTimeout(function(){
      if (document.kc_invite_unavail = jQuery('.kc-invite-unavail')[0]){
        document.kc_invite_unavail.hide();
      }
    }, 1000);
  },
  start: function(event){
    if (!Kiamo.chat()) return;
    jQuery('#fake-kiamo-chat').hide();
    Kiamo.chat().kcStartConversation(event);
    if (document.kc_invite_unavail){
      document.kc_invite_unavail.show();
    }
  },
  open: function (){
    jQuery('#fake-kiamo-chat .kc-choice').show();
    jQuery('#fake-kiamo-chat .kc-message').hide();
    jQuery('#fake-kiamo-chat').show();
    jQuery.cookie('hide-chat', 'false');
  },
  close: function(){
    jQuery('#fake-kiamo-chat').removeClass('kc-callback');
    jQuery('#fake-kiamo-chat .kc-callback-form').hide();
    jQuery('#fake-kiamo-chat').hide();
    jQuery.cookie('hide-chat', 'true');
  },
  chat: function(){
    if (typeof kiamo_options == 'undefined' || !('chat_id' in kiamo_options)){
      console.log('kiamo_options.chat_id is undefined');
      return false;
    }
    return eval('kcChat' + kiamo_options.chat_id);
  },
  callbackInit: function(){
    jQuery('#fake-kiamo-chat').addClass('kc-callback');
    jQuery('#fake-kiamo-chat .kc-choice').hide();
    jQuery('#fake-kiamo-chat .kc-callback-form').show();
  },
  callbackSubmit: function(form){
    if (typeof kiamo_options == 'undefined' || !('callback_url' in kiamo_options)){
      console.log('kiamo_options.callback_url is undefined');
      return false;
    }
    jQuery.ajax({
      url: kiamo_options.callback_url,
      type: 'POST',
      dataType: 'json',
      data: jQuery(form).serialize(),
      success: function (object){
        if (object.success){
          jQuery('.callback-container').html('<h3>' + object.data + '</h3>');
        }else{
          alert(object.data);
        }
      }
    });
  }
}
jQuery(document).ready(Kiamo.init);
