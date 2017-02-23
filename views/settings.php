<?php

/**
 * Two Factor Authentication for Webconfig settings view.
 *
 * @category   apps
 * @package    two-factor-auth
 * @subpackage views
 * @author     eGloo <team@egloo.ca>
 * @copyright  2017 Avantech
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/two_factor_auth/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('base');
$this->lang->load('two_factor_auth');

///////////////////////////////////////////////////////////////////////////////
// Form open
///////////////////////////////////////////////////////////////////////////////

echo form_open('two_factor_auth/settings/edit');
echo form_header(lang('base_settings'));

///////////////////////////////////////////////////////////////////////////////
// Form fields and buttons
///////////////////////////////////////////////////////////////////////////////

if ($form_type == 'edit') {
    $read_only = FALSE;
    $buttons = array(
        form_submit_update('submit'),
        anchor_cancel('/app/two_factor_auth')
    );
} else {
    $read_only = TRUE;
    $buttons = array(
        anchor_edit('/app/two_factor_auth/settings/edit'),
        anchor_custom('/app/two_factor_auth/test', lang('two_factor_auth_test_email'), 'low')
    );
}

echo field_toggle_enable_disable('root_enabled', $root_enabled, lang('two_factor_auth_enable_root'), $read_only);
echo field_input('root_email', $root_email, lang('two_factor_auth_root_email'), $read_only);
//echo field_toggle_enable_disable('allow_email', $allow_email, lang('two_factor_auth_allow_email_change'), $read_only);
echo field_dropdown('code_length', $code_length_options, $code_length, lang('two_factor_auth_verification_code_length'), $read_only);
echo field_dropdown('code_expire', $code_expire_options, $code_expire, lang('two_factor_auth_verification_code_expire'), $read_only);
echo field_dropdown('token_expire', $token_expire_options, $token_expire, lang('two_factor_auth_token_expire'), $read_only);
echo field_button_set($buttons);

///////////////////////////////////////////////////////////////////////////////
// Form close
///////////////////////////////////////////////////////////////////////////////

echo form_footer();
echo form_close();
