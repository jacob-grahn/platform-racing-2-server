$(function () {
    if (window.location.pathname.substr(0, 25) === '/admin/update_account.php') {
        // hide pass reset checkbox
        $('label#pass').hide();

        // check for a change in email value
        var origEmail = $('input[name="email"]').val();
        $('input[name="email"]').keyup(function () {
            if (origEmail !== $(this).val()) {
                $('label#pass').show();
            } else {
                $('label#pass').hide();
            }
        });
    }
});
