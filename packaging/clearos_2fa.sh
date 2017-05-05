# .bashrc

tty=$(/usr/bin/tty)

# Console exempt from 2FA
if [[ $tty == *"tty"* ]]; then
    return
fi

trap logout INT
otp=$(/usr/clearos/apps/two_factor_auth/deploy/send_otp -u $USER)
code=$?

if [ $code != 0 ]; then
    # If for any reason our exit code is not 0, bounce login
    exit
fi

if [ -n "$otp" ]; then
    # Echo prompt for OTP in user's locale
    /usr/clearos/apps/two_factor_auth/deploy/send_otp -p
    read otpass
    if [ "$otp" == "$otpass" ];
    then
        # Echo success in user's locale
        /usr/clearos/apps/two_factor_auth/deploy/send_otp -y
    else
        # Echo fail in user's locale
        /usr/clearos/apps/two_factor_auth/deploy/send_otp -n
        sleep 1
        exit
    fi
fi
