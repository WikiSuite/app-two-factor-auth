<?php

/**
 * Two Factor Authentication for Webconfig.
 *
 * @category   apps
 * @package    two-factor-auth
 * @subpackage libraries
 * @author     eGloo <team@egloo.ca>
 * @copyright  2017 Avantech
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/two_factor_auth/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\two_factor_auth;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('two_factor_auth');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \clearos\apps\base\Configuration_File as Configuration_File;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\Engine as Engine;
use \clearos\apps\mail_notification\Mail_Notification as Mail_Notification;
use \clearos\apps\users\User_Factory as User_Factory;
use \clearos\apps\users\User_Manager_Factory;

clearos_load_library('base/Configuration_File');
clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('base/Engine');
clearos_load_library('mail_notification/Mail_Notification');
clearos_load_library('users/User_Factory');
clearos_load_library('users/User_Manager_Factory');

// Exceptions
//-----------

use \Exception as Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Two factor authentication for Webconfig.
 *
 * @category   apps
 * @package    two-factor-auth
 * @subpackage libraries
 * @author     eGloo <team@egloo.ca>
 * @copyright  2017 Avantech
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/two_factor_auth/
 */

class Two_Factor_Auth extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const FILE_CONFIG = '/etc/clearos/two_factor_auth.conf';
    const FILE_2FA_CODE = '.2fa';
    const FOLDER_TOKENS = '/var/clearos/framework/tmp/t';
    const COOKIE_NAME = '2FA';
    const DEFAULT_VERIFICATION_CODE_SIZE = 5;
    const DEFAULT_VERIFICATION_CODE_LIFECYCLE = 300;
    const DEFAULT_TOKEN_LIFECYCLE = 1800;

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $is_loaded = FALSE;
    protected $config = array();


    /**
     * Two_Factor_Auth constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Sets allow email change.
     *
     * @param boolean $enabled
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function set_email_allow_change($enabled)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($enabled === 'on' || $enabled == 1 || $enabled == TRUE)
            $enabled = TRUE;
        else
            $enabled = FALSE;

        Validation_Exception::is_valid($this->validate_email_allow_change($enabled));

        $this->_set_parameter('email_allow_change', $enabled);
    }

    /**
     * Sets root enabled.
     *
     * @param boolean $enabled
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function set_root_enabled($enabled)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($enabled === 'on' || $enabled == 1 || $enabled == TRUE)
            $enabled = TRUE;
        else
            $enabled = FALSE;

        Validation_Exception::is_valid($this->validate_root_enabled($enabled));

        $this->_set_parameter('root_enabled', $enabled);
    }

    /**
     * Sets root email.
     *
     * @param String $email
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function set_root_email($email)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_email($email));

        $this->_set_parameter('root_email', $email);
    }

    /**
     * Sets verification code length.
     *
     * @param int length
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function set_verification_code_length($length)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_verification_code_length($length));

        $this->_set_parameter('verification_code_length', $length);
    }

    /**
     * Sets verification code lifecycle.
     *
     * @param int lifecycle
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function set_verification_code_lifecycle($lifecycle)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_verification_code_lifecycle($lifecycle));

        $this->_set_parameter('verification_code_lifecycle', $lifecycle);
    }

    /**
     * Sets token lifecycle.
     *
     * @param int lifecycle
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function set_token_lifecycle($lifecycle)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_token_lifecycle($lifecycle));

        $this->_set_parameter('token_lifecycle', $lifecycle);
    }

    /**
     * Returns the allow email change setting.
     *
     * @return boolean
     * @throws Engine_Exception
     */

    public function get_email_allow_change()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['email_allow_change'];
    }

    /**
     * Returns the status of the root account.
     *
     * @return boolean
     * @throws Engine_Exception
     */

    public function get_root_enabled()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['root_enabled'];
    }

    /**
     * Returns the email of the root account.
     *
     * @return String
     * @throws Engine_Exception
     */

    public function get_root_email()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['root_email'];
    }

    /**
     * Returns array of all users with flag for 2FA.
     *
     * @return array
     * @throws Engine_Exception
     */

    public function get_user_2fa_list()
    {
        clearos_profile(__METHOD__, __LINE__);

        $list = array();
        $user_factory = new User_Manager_Factory();
        $user_manager = $user_factory->create();
        $users = $user_manager->get_details();
        foreach ($users as $username => $details)
            $list[$username] = array(
                'enabled' => $details['extensions']['two_factor_auth']['state'],
                'email' => $details['extensions']['two_factor_auth']['mail']
            );

        return $list;
    }

    /**
     * Returns the verification code lifecycle (in seconds).
     *
     * @return int
     */

    public function get_verification_code_lifecycle()
    {
        clearos_profile(__METHOD__, __LINE__);

        $sec = self::DEFAULT_VERIFICATION_CODE_LIFECYCLE;

        if (!$this->is_loaded)
            $this->_load_config();

        $s = (int)$this->config['verification_code_lifecycle'];
        if ($s)
            $sec = $s;

        return $sec;
    }

    /**
     * Returns the token lifecycle (in seconds).
     *
     * @return int
     */

    public function get_token_lifecycle()
    {
        clearos_profile(__METHOD__, __LINE__);

        $sec = self::DEFAULT_TOKEN_LIFECYCLE;

        if (!$this->is_loaded)
            $this->_load_config();

        $s = (int)$this->config['token_lifecycle'];
        if ($s)
            $sec = $s;

        return $sec;
    }

    /**
     * Returns the length of the verification code.
     *
     * @return int
     */

    public function get_verification_code_length()
    {
        clearos_profile(__METHOD__, __LINE__);

        $length = self::DEFAULT_VERIFICATION_CODE_SIZE;

        if (!$this->is_loaded)
            $this->_load_config();

        $l = (int)$this->config['verification_code_length'];
        if ($l)
            $length = $l;

        return $length;
    }

    /**
     * Check if user been verified with 2FA.
     *
     * @return boolean
     * @throws Engine_Exception
     */

    public function is_verified($username, $cookie)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($username == 'root') {
            if ($this->get_root_enabled()) {
                if (!$this->verify_token($cookie))
                    return FALSE;
            }
        } else {
            $user = User_Factory::create($username);
            $extensions = $user->get_info()['extensions'];
            if ($extensions['two_factor_auth']['state'] && !$this->verify_token($cookie))
                return FALSE;
        }
        return TRUE;
    }

    /**
     * Returns 2-factor verification code.
     *
     * @param string  $username username
     * @param boolean $resend   force resend
     *
     * @return string code
     * @throws Engine_Exception
     */

    public function get_verification_code($username, $resend = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);
        try {
            $file = new File(CLEAROS_TEMP_DIR . "/" . self::FILE_2FA_CODE . ".$username", TRUE);

            if ($file->exists() && $file->last_modified() && (time() - $file->last_modified() < $this->get_verification_code_lifecycle())) {
                $code = $file->get_contents();
                if ($resend)
                    $this->_send_verification_code($username, $code);
                return $code;
            } else if ($file->exists()) {
                $file->delete();
            }

            $file->create('root', 'root', '0600');
            $code = $this->_create_verification_code();
            $file->add_lines($code . "\n");
            $this->_send_verification_code($username, $code);
            return $code;
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message());
        }
    }

    /**
     * Verify token.
     *
     * @return boolean
     */

    public function verify_token($token)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            Validation_Exception::is_valid($this->validate_token($token));

            if (empty($token))
                return FALSE;

            $file = new File(self::FOLDER_TOKENS . "/$token", TRUE);
            if ($file->exists() && (time() - $file->last_modified() < $this->get_token_lifecycle()))
                return TRUE;
            else if ($file->exists())
                $file->delete();

            return FALSE;
        } catch (Exception $e) {
            return FALSE;
        }
    }

    /**
     * Recycle tokens.
     *
     * @return void
     * @throws Engine_Exception
     */

    public function recycle_tokens()
    {
        clearos_profile(__METHOD__, __LINE__);

        $folder = new Folder(self::FOLDER_TOKENS, TRUE);
        $tokens = $folder->get_listing();
        $token_lifecycle = $this->get_token_lifecycle();
        foreach ($tokens as $token) {
            try {
                Validation_Exception::is_valid($this->validate_token($token));

                $file = new File(self::FOLDER_TOKENS . "/$token", TRUE);

                if (time() - $file->last_modified() > $token_lifecycle)
                    $file->delete();
            } catch (Exception $e) {
                clearos_log('app-two-factor-auth', clearos_exception_message($e));
            }
        }
    }

    /**
     * Returns 2-factor authentication token for cookie.
     *
     * @return array cookie
     * @throws Engine_Exception
     */

    public function create_cookie($username)
    {
        clearos_profile(__METHOD__, __LINE__);
        try {
            // Make sure folder exists
            $folder = new Folder(self::FOLDER_TOKENS, TRUE);
            if (!$folder->exists())
                $folder->create('webconfig', 'webconfig', '0750');
            $token = bin2hex(openssl_random_pseudo_bytes(24)); 
            $file = new File(self::FOLDER_TOKENS . "/$token", TRUE);
            $file->create('webconfig', 'webconfig', '0600');
            $cookie = array(
                'name'   => self::COOKIE_NAME,
                'value'  => $token,
                'expire' => 0,
                'domain' => '',
                'path'   => '/',
                'prefix' => '',
            );
            return $cookie;
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message());
        }
    }

    /**
     * Set flag that credentials OK for multi-factor authentication.
     * 
     * @param boolean $status status
     * 
     * @return void
     */

    public function set_session_status($status)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->CI =& get_instance();
        if ($status)
            $this->CI->session->set_userdata('2factor_auth', 'TRUE');
        else
            $this->CI->session->unset_userdata('2factor_auth');
    }

    /**
     * Is login session OK.
     * 
     * @return boolean
     */

    public function is_ready_2fa_verification()
    {
        clearos_profile(__METHOD__, __LINE__);
        $this->CI =& get_instance();
        if ($this->CI->session->userdata('2factor_auth') === 'TRUE')
            return TRUE;
        return FALSE;
    }

    /**
     * Create unique verification code.
     *
     * @return String
     * @throws Engine_Exception
     */

    private function _create_verification_code()
    {
        clearos_profile(__METHOD__, __LINE__);

        $length = $this->get_verification_code_length();
        $min = pow(10, $length - 1) ;
        $max = pow(10, $length) - 1;
        return mt_rand($min, $max);
    }

    /**
     * Get verification code length options.
     *
     * @return array
     * @throws Engine_Exception
     */

    function get_verification_code_length_options()
    {
        clearos_profile(__METHOD__, __LINE__);
        for ($index = 3; $index <= 16; $index++)
            $options[$index] = $index . ' ' . lang('two_factor_auth_digits');
        return $options;
    }

    /**
     * Get verification code lifecycle options.
     *
     * @return array
     * @throws Engine_Exception
     */

    function get_verification_code_lifecycle_options()
    {
        clearos_profile(__METHOD__, __LINE__);
        $options = array(
            60 => '1 ' . strtolower(lang('base_minute')),
            120 => '2 ' . strtolower(lang('base_minutes')),
            180 => '3 ' . strtolower(lang('base_minutes')),
            240 => '4 ' . strtolower(lang('base_minutes')),
            300 => '5 ' . strtolower(lang('base_minutes')),
            600 => '10 ' . strtolower(lang('base_minutes')),
            900 => '15 ' . strtolower(lang('base_minutes')),
            1200 => '20 ' . strtolower(lang('base_minutes')),
            1500 => '25 ' . strtolower(lang('base_minutes')),
            1800 => '30 ' . strtolower(lang('base_minutes')),
        );
        return $options;
    }

    /**
     * Get token lifecycle options.
     *
     * @return array
     * @throws Engine_Exception
     */

    function get_token_lifecycle_options()
    {
        clearos_profile(__METHOD__, __LINE__);
        $options = array(
            300 => '5 ' . strtolower(lang('base_minutes')),
            1800 => '30 ' . strtolower(lang('base_minutes')),
            3600 => '1 ' . strtolower(lang('base_hour')),
            7200 => '2 ' . strtolower(lang('base_hours')),
            21600 => '6 ' . strtolower(lang('base_hours')),
        );
        return $options;
    }

    /**
     * Cleanup function when user logs out.
     *
     * @param String $username username
     *
     * @return void
     */

    function logout_cleanup($username)
    {
        clearos_profile(__METHOD__, __LINE__);
        $file = new File(CLEAROS_TEMP_DIR . "/" . self::FILE_2FA_CODE . ".$username", TRUE);
        if ($file->exists())
            $file->delete();
    }

    /**
     * Loads configuration.
     *
     * @return void
     * @throws Engine_Exception
     */

    private function _load_config()
    {
        clearos_profile(__METHOD__, __LINE__);

        $configfile = new Configuration_File(self::FILE_CONFIG);

        $this->config = $configfile->load();

        $this->is_loaded = TRUE;
    }

    /**
     * Generic set routine.
     *
     * @param string $key   key name
     * @param string $value value for the key
     *
     * @return  void
     * @throws Engine_Exception
     */

    private function _set_parameter($key, $value)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_CONFIG, TRUE);
            $match = $file->replace_lines("/^$key\s*=\s*/", "$key = $value\n");

            if (!$match)
                $file->add_lines("$key = $value\n");
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }

        $this->is_loaded = FALSE;
    }

    /**
     * Send two-factor verification code.
     *
     * @param string $username username
     * @param string $code     code
     *
     * @access private
     * @return void
     * @throws Engine_Exception
     */

    protected function _send_verification_code($username, $code)
    {
        clearos_profile(__METHOD__, __LINE__);

        $mailer = new Mail_Notification();
        $subject = lang('two_factor_auth_verification_code');
        $body = lang('two_factor_auth_verification_code') . ":  $code\n";

        $email = NULL;

        if ($username == 'root') {
            $email = $this->get_root_email();
        } else {
            $user = User_Factory::create($username);
            $extensions = $user->get_info()['extensions'];
            $email = $extensions['two_factor_auth']['mail'];
        }

        if (!$email)
            throw new Engine_Exception(lang('two_factor_auth_not_configured'));
        $mailer->add_recipient($email);
        $mailer->set_message_subject($subject);
        $mailer->set_message_html_body($body);

        $mailer->send();
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Validation routine to set root 2FA.
     *
     * @param integer $state enable status
     *
     * @return string error message if state invalid
     */

    public function validate_root_enabled($status)
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Validation routine to allow users to change their own email.
     *
     * @param integer $state enable status
     *
     * @return string error message if state invalid
     */

    public function validate_email_allow_change($status)
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Validation routine for e-mail address.
     *
     * @param string $email e-mail address
     *
     * @return string error message if e-mail address invalid
     */

    public function validate_email($email)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! preg_match("/^([a-z0-9_\-\.\$]+)@/", $email))
            return lang('two_factor_auth_email_invalid');
    }

    /**
     * Validation routine for verification code.
     *
     * @param string $code code
     *
     * @return string error message if code invalid
     */

    public function validate_verification_code($code)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! preg_match("/^([a-zA-Z0-9]+)$/", $code))
            return lang('two_factor_auth_verification_code_invalid');
    }

    /**
     * Validation routine for verification code length.
     *
     * @param int $length length of verification code
     *
     * @return string error message if length invalid
     */

    public function validate_verification_code_length($length)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!array_key_exists($length, $this->get_verification_code_length_options()))
            return lang('two_factor_auth_verifiation_code_length_invalid');
    }

    /**
     * Validation routine for verification code lifecycle.
     *
     * @param int $lifecycle lifecycle of verification code
     *
     * @return string error message if lifecycle invalid
     */

    public function validate_verification_code_lifecycle($lifecycle)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!array_key_exists($lifecycle, $this->get_verification_code_lifecycle_options()))
            return lang('two_factor_auth_lifecycle_invalid');
    }

    /**
     * Validation routine for token lifecycle.
     *
     * @param int $lifecycle lifecycle of token
     *
     * @return string error message if lifecycle invalid
     */

    public function validate_token_lifecycle($lifecycle)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!array_key_exists($lifecycle, $this->get_token_lifecycle_options()))
            return lang('two_factor_auth_lifecycle_invalid');
    }

    /**
     * Validation routine for token.
     *
     * @param string $token token
     *
     * @return string error message if token invalid
     */

    public function validate_token($token)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! preg_match("/^([a-zA-Z0-9]+)$/", $token))
            return lang('two_factor_auth_token_invalid');
    }
}
