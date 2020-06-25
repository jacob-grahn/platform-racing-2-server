$(function () {
    // confirmation in IP validity management script
    if (window.location.pathname.substr(0, 27) === '/mod/manage_ip_validity.php') {
        // check for a passed IP
        var params = new URLSearchParams(window.location.search);
        if (params.get('ip') != null && params.get('ip').trim() != '') {
            $('input[name="ip_address"]').val(params.get('ip').trim());
        }

        // check for a change in email value
        $('form').submit(function () {
            var formId = $(this).attr('id');
            var action = $('form#' + formId + ' input[name="action"]:checked').val();

            // sanity: IP specified?
            if (formId === 'main') {
                var ip = $('input[name="ip_address"]').val();
                if (ip == null || ip.trim() == '') {
                    alert('Error: You must enter an IP address.');
                    return false;
                }
            }

            // sanity: action chosen?
            if (action == null || action.trim() == '') {
                alert('Error: You must choose an action.');
                return false;
            }

            // confirmation popups
            if (formId === 'main' && action !== 'check') {
                return confirm('Are you sure you want to ' + action + ' this IP address?');
            } else if (formId === 'management' && action === 'clear') {
                var firstConfirm = confirm('Are you sure you want to clear all saved IP addresses? This cannot be undone.');
                if (firstConfirm === true) {
                    return confirm('Don\'t just yes me... are you REALLY sure?');
                }
                return false;
            }
        });
    }
});
