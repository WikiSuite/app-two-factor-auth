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

    /**
     * Two-factor Authentication verification.
     *
     * @param string $username username
     * @param string $redirect redirect page after login, base64 encoded
     *
     * @return view
     */

    function verify($username, $redirect = NULL)
    {
        $this->load->helper('cookie');
        $this->load->library('two_factor_auth/Two_Factor_Auth');

        if ($username == NULL) {
            redirect('base/session/login');
            return;
        }

        // Redirect to safe page if logged in already
        if ($this->login_session->is_authenticated()) {
            redirect('base');
            return;
        }

        // Protect two-factor authentication page
        if (!$this->two_factor_auth->is_ready_2fa_verification())
            redirect('base/session/login');

        $page['type'] = MY_Page::TYPE_2FACTOR_AUTH;

        $data = array(
            'username' => $username,
            'redirect' => $redirect,
        );

        // Set validation rules
        //---------------------
         
        $this->form_validation->set_policy('code', 'two_factor_auth/Two_Factor_Auth', 'validate_verification_code');

        $form_ok = $this->form_validation->run();

        try {
            if ($this->input->post('verify') && $form_ok) {
                $code = $this->two_factor_auth->get_verification_code($username, FALSE, FALSE);
                if ($this->input->post('code') == $code) {
                    if ($this->input->post('redirect'))
                        $redirect = $this->input->post('redirect');
                    set_cookie($this->two_factor_auth->create_cookie($username));
                    $post_redirect = is_null($redirect) ? '/base/index' : base64_decode(strtr($redirect, '-@_', '+/='));
                    $post_redirect = preg_replace('/.*app\//', '/', $post_redirect); // trim /app prefix
                    $this->login_session->start_authenticated($username);
                    redirect($post_redirect);
                } else {
                    $this->form_validation->set_error('code', lang('two_factor_auth_verification_code_invalid'));
                }
            } else if ($this->input->post('resend')) {
                $this->two_factor_auth->get_verification_code($username, TRUE, FALSE, FALSE);
                $this->form_validation->set_error('code', lang('two_factor_auth_verification_code_resent'));
            }
        } catch (Exception $e) {
            $data['errmsg'] = clearos_exception_message($e);
        }
        $this->page->view_form('two_factor_auth/verify', $data, lang('two_factor_auth_two_factor_auth'), $page);
    }

    /**
     * Two-factor Authentication API.
     *
     *
     * @return view
     */

    function api($action, $username)
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        try {

            $this->load->library('two_factor_auth/Two_Factor_Auth');
            $this->lang->load('base');

            if ($this->input->get_request_header("X-api-key") != $this->two_factor_auth->get_api_key())
                throw new Exception (lang('base_access_denied'), 1);

            if ($this->input->ip_address() != "127.0.0.1")
                throw new Exception (lang('base_access_denied'), 1);

            if ($action == 'ssh_login') {
                $this->two_factor_auth->get_verification_code($username, TRUE, FALSE);;
                echo json_encode(array('code' => 0));
                return;
            }

            throw new Exception (lang('base_access_denied'), 1);
        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }
}
