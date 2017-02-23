<?php

/**
 * Two Factor Authentication for Webconfig verification view.
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


$buttons = array(
    form_submit_custom('verify', lang('two_factor_auth_verify_code_and_continue')),
    anchor_cancel('/app/base/session/logout')
);
echo row_open();
echo column_open(2);
echo column_close();
echo column_open(8);
echo form_open('/two_factor_auth/verify/' . $username);
echo form_header(lang('two_factor_auth_two_factor_auth'));

echo field_input('redirect', $redirect, "", FALSE, ['hide_field' => TRUE]);
echo field_input('username', $username, lang('base_account'), TRUE);
echo field_input('code', "", lang('two_factor_auth_verification_code'));

echo field_button_set($buttons);

echo form_footer();

if ($errmsg)
    echo infobox_warning(lang('base_warning'), $errmsg);

echo infobox_highlight(lang('base_information'),
    lang('two_factor_auth_protection_enabled') .
    "<div class='text-center' style='padding-top: 20px;'>" .
    form_submit_custom('resend', lang('two_factor_auth_resend_code')) .
    "</div>"
);

echo form_close();
echo column_close();
echo column_open(2);
echo column_close();
echo row_close();
