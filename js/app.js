//! function to notify alerts...
function pNotifyAlert(sNotifyText, sNotifyType="error"){
    new PNotify({
        'text': sNotifyText,
        'type': sNotifyType,
        'animation': 'none',
        'delay': 8000,
        'buttons':{
            'sticker': false
        }
    });
}