<?php

/**
 * Two Factor Authentication users controller.
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
 * Two Factor Authentication users controller.
 *
 * @category   apps
 * @package    two-factor-auth
 * @subpackage controllers
 * @author     eGloo <team@egloo.ca>
 * @copyright  2017 Avantech
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/two_factor_auth/
 */

class Users extends ClearOS_Controller
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

        $this->lang->load('base');
        $this->lang->load('two_factor_auth');
        $this->load->library('two_factor_auth/Two_Factor_Auth');

        // Handle form submit
        //-------------------

        if ($this->input->post('submit')) {
            $this->two_factor_auth->set_user_2fa_list();
        }

        // Load view data
        //---------------
        $data['users'] = $this->two_factor_auth->get_user_2fa_list();

        $this->page->view_form('users', $data, lang('base_users'));
    }
}
