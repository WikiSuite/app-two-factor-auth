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
use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\Validation_Exception as Validation_Exception;
use \clearos\apps\users\User_Factory as User_Factory;
use \clearos\apps\users\User_Manager_Factory;

clearos_load_library('base/File');
clearos_load_library('base/Configuration_File');
clearos_load_library('base/Engine');
clearos_load_library('base/Validation_Exception');
clearos_load_library('users/User_Factory');
clearos_load_library('users/User_Manager_Factory');

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

        // Load view
        //----------

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

    function _set_parameter($key, $value)
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
}
