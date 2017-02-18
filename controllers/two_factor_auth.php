<?php

/**
 * Two Factor Authentication controller.
 *
 * @category   apps
 * @package    two-factor-auth
 * @subpackage controllers
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
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Two Factor Authentication controller.
 *
 * @category   apps
 * @package    two-factor-auth
 * @subpackage controllers
 * @author     eGloo <team@egloo.ca>
 * @copyright  2017 Avantech
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/two_factor_auth/
 */

class Two_Factor_Auth extends ClearOS_Controller
{
    /**
     * Two Factor Auth summary view.
     *
     * @return view
     */

    function index()
    {
        // Load libraries
        //---------------

        $this->lang->load('two_factor_auth');
        $this->load->library('two_factor_auth/Two_Factor_Auth');

        // Load views
        //-----------

        $views = array('two_factor_auth/settings', 'two_factor_auth/users');

        $this->page->view_forms($views, lang('two_factor_auth_app_name'));
    }

    /**
     * Test view.
     *
     * @return view
     */

    function test()
    {
        // Load libraries
        //---------------

        $this->lang->load('mail');

        $this->page->view_form('mail/test', NULL, lang('mail_notification_test'));
    }
}
