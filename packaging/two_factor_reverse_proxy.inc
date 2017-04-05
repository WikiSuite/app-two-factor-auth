RewriteCond %{HTTP_COOKIE} !2FA=([a-zA-Z0-9]+)
RewriteRule .* https://%{SERVER_NAME}:%{SERVER_PORT}/app/base/session/login [R=302,L]
RewriteCond %{HTTP_COOKIE} .*2FA=([a-zA-Z0-9]+);.*
RewriteCond /var/clearos/framework/tmp/t/%1 !-f
RewriteRule .* https://%{SERVER_NAME}:%{SERVER_PORT}/app/base/session/login/?%1 [co=req_2fa:true,R=302,L]
