
Name: app-two-factor-auth
Epoch: 1
Version: 2.3.7
Release: 1%{dist}
Summary: 2FA for Webconfig
License: GPLv3
Group: ClearOS/Apps
Packager: eGloo
Vendor: WikiSuite
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base

%description
Allows administrators to enforce two-factor authentication for Webconfig.  If enabled, a user will be sent an email containing an expiring token that must be provided in addition to a username/password combination to complete the login process.

%package core
Summary: 2FA for Webconfig - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-openldap-directory-core
Requires: app-users
Requires: app-base >= 1:2.3.31
Requires: app-two-factor-auth-extension-core
Requires: app-mail

%description core
Allows administrators to enforce two-factor authentication for Webconfig.  If enabled, a user will be sent an email containing an expiring token that must be provided in addition to a username/password combination to complete the login process.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/two_factor_auth
cp -r * %{buildroot}/usr/clearos/apps/two_factor_auth/

install -d -m 0750 %{buildroot}/var/clearos/framework/cache/t
install -D -m 0644 packaging/app-two-factor-auth.cron %{buildroot}/etc/cron.d/app-two-factor-auth
install -D -m 0644 packaging/public.acl %{buildroot}/var/clearos/base/access_control/public/two_factor_auth
install -D -m 0640 packaging/two_factor_auth.conf %{buildroot}/etc/clearos/two_factor_auth.conf
install -D -m 0644 packaging/two_factor_reverse_proxy.inc %{buildroot}/usr/clearos/sandbox/etc/httpd/conf.d/two_factor_auth.inc

%post
logger -p local6.notice -t installer 'app-two-factor-auth - installing'

%post core
logger -p local6.notice -t installer 'app-two-factor-auth-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/two_factor_auth/deploy/install ] && /usr/clearos/apps/two_factor_auth/deploy/install
fi

[ -x /usr/clearos/apps/two_factor_auth/deploy/upgrade ] && /usr/clearos/apps/two_factor_auth/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-two-factor-auth - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-two-factor-auth-core - uninstalling'
    [ -x /usr/clearos/apps/two_factor_auth/deploy/uninstall ] && /usr/clearos/apps/two_factor_auth/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/two_factor_auth/controllers
/usr/clearos/apps/two_factor_auth/htdocs
/usr/clearos/apps/two_factor_auth/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/two_factor_auth/packaging
%exclude /usr/clearos/apps/two_factor_auth/unify.json
%dir /usr/clearos/apps/two_factor_auth
%dir %attr(0750,webconfig,webconfig) /var/clearos/framework/cache/t
/usr/clearos/apps/two_factor_auth/deploy
/usr/clearos/apps/two_factor_auth/language
/usr/clearos/apps/two_factor_auth/libraries
%config(noreplace) /etc/cron.d/app-two-factor-auth
/var/clearos/base/access_control/public/two_factor_auth
%config(noreplace) /etc/clearos/two_factor_auth.conf
/usr/clearos/sandbox/etc/httpd/conf.d/two_factor_auth.inc
