import { translate } from "../lang/init.js";

jQuery('#mpg_update_tables_structure').on('click', async function () {

    const event = await jQuery.post(ajaxurl, {
        action: 'mpg_activation_events',
        isAjax: true
    });

    let eventData = JSON.parse(event);

    if (!eventData.success) {
        toastr.error(eventData.error, translate['Failed']);
    } else {
        toastr.success(translate['MPG tables structure updated successfully'], translate['Success'], { timeOut: 5000 });
    }
})

jQuery('.advanced-page .mpg-hooks-block').on('submit', async function (e) {

    e.preventDefault();

    const selectedHook = jQuery('#mpg_hook_name').val();
    const hookPriority = jQuery('#mpg_hook_priority').val();

    const event = await jQuery.post(ajaxurl, {
        action: 'mpg_set_hook_name_and_priority',
        'hook_name': selectedHook,
        'hook_priority': hookPriority
    });

    let eventData = JSON.parse(event);

    if (!eventData.success) {
        toastr.error(eventData.error, translate['Failed']);
    } else {
        toastr.success(translate['Hook settings updated sucessfully'], translate['Success'], { timeOut: 5000 });
    }
});

