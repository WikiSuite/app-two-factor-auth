<?php

/**
 * Two Factor Authentication for Webconfig.
 *
 * @category   apps
 * @package    two-factor-auth
 * @subpackage javascript
 * @author     eGloo <team@egloo.ca>
 * @copyright  2017 Avantech
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/two_factor_auth/
 */

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

clearos_load_language('base');

header('Content-Type: application/x-javascript');

?>

var lang_select_all = '<?php echo lang('base_select_all'); ?>';
var lang_select_none = '<?php echo lang('base_select_none'); ?>';

$(document).ready(function() {
    $('#code').select();
    $('#2fa_toggle').on('click', function(event) {
        event.preventDefault();
        toggle_users();
    });
});

function toggle_users() {
    if ($('#2fa_toggle').html() == lang_select_all) {
        $('#2fa_table').find(':checkbox').prop('checked', true);
        $('#2fa_toggle').html(lang_select_none);
    } else {
        $('#2fa_table').find(':checkbox').prop('checked', false);
        $('#2fa_toggle').html(lang_select_all);
    }
}

// vim: syntax=javascript ts=4
