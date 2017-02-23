<?php

/**
 * Two Factor Authentication settings controller.
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
 * Two Factor Authentication settings controller.
 *
 * @category   apps
 * @package    two-factor-auth
 * @subpackage controllers
 * @author     eGloo <team@egloo.ca>
 * @copyright  2017 Avantech
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/two_factor_auth/
 */

class Settings extends ClearOS_Controller
{
    /**
     * Two factor settings controller.
     *
     * @return view
     */

    function index()
    {
        $this->view();
    }

    /**
     * Settings edit view.
     *
     * @return view
     */

    function edit()
    {
        $this->_view_edit('edit');
    }

    /**
     * Settings view view.
     *
     * @return view
     */

    function view()
    {
        $this->_view_edit('view');
    }

    /**
     * Settings view/edit common controller
     *
     * @param string $form_type form type
     *
     * @return view
     */

    function _view_edit($form_type)
    {
        // Load dependencies
        //------------------

        $this->lang->load('two_factor_auth');
        $this->load->library('two_factor_auth/Two_Factor_Auth');

        // Set validation rules
        //---------------------
         
        $this->form_validation->set_policy('root_enabled', 'two_factor_auth/Two_Factor_Auth', 'validate_root_enabled', TRUE);
        $this->form_validation->set_policy('root_email', 'two_factor_auth/Two_Factor_Auth', 'validate_email', FALSE);
        //$this->form_validation->set_policy('allow_email', 'two_factor_auth/Two_Factor_Auth', 'validate_email_allow_change', TRUE);
        $form_ok = $this->form_validation->run();

        // Extra Validation
        //------------------

        if ($this->input->post('root_enabled') && $this->input->post('root_email') == '') {
            $this->form_validation->set_error('root_email', lang('two_factor_auth_root_email_required'));
            $form_ok = FALSE;
        }

        // Handle form submit
        //-------------------

        if (($this->input->post('submit') && $form_ok)) {
            try {
                $this->two_factor_auth->set_root_enabled($this->input->post('root_enabled'));
                if ($this->input->post('root_enabled'))
                    $this->two_factor_auth->set_root_email($this->input->post('root_email'));
                $this->two_factor_auth->set_verification_code_length($this->input->post('code_length'));
                $this->two_factor_auth->set_verification_code_lifecycle($this->input->post('code_expire'));
                $this->two_factor_auth->set_token_lifecycle($this->input->post('token_expire'));
                //$this->two_factor_auth->set_email_allow_change($this->input->post('allow_email'));

                $this->page->set_status_updated();
                redirect('/two_factor_auth');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        try {
            $data['form_type'] = $form_type;

            $data['root_enabled'] = $this->two_factor_auth->get_root_enabled();
            $data['root_email'] = $this->two_factor_auth->get_root_email();
            $data['allow_email'] = $this->two_factor_auth->get_email_allow_change();
            $data['code_length_options'] = $this->two_factor_auth->get_verification_code_length_options();
            $data['code_length'] = $this->two_factor_auth->get_verification_code_length();
            $data['code_expire_options'] = $this->two_factor_auth->get_verification_code_lifecycle_options();
            $data['code_expire'] = $this->two_factor_auth->get_verification_code_lifecycle();
            $data['token_expire_options'] = $this->two_factor_auth->get_token_lifecycle_options();
            $data['token_expire'] = $this->two_factor_auth->get_token_lifecycle();
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $this->page->view_form('settings', $data, lang('base_settings'));
    }
}
