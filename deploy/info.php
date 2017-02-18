<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'two_factor_auth';
$app['version'] = '2.3.0';
$app['release'] = '1';
$app['vendor'] = 'Avantech';
$app['packager'] = 'eGloo';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('two_factor_auth_app_description');
$app['priority'] = 9999;

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('two_factor_auth_app_name');
$app['category'] = lang('base_category_system');
$app['subcategory'] = lang('base_subcategory_accounts');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['core_requires'] = array(
    'app-openldap-directory-core',
    'app-users',
    'app-two-factor-auth-extension-core',
    /*'app-user-two-factor-auth',*/
    'app-mail',
);

$app['core_file_manifest'] = array( 
    'two_factor_auth.conf' => array(
        'target' => '/etc/clearos/two_factor_auth.conf',
        'mode' => '0640',
        'owner' => 'root',
        'group' => 'root',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
);

$app['delete_dependency'] = array(
    'app-two-factor-auth-core',
    'app-two-factor-auth-extension',
    /*'app-user-two-factor-auth',*/
);
