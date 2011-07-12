<?php

function local_redirect( $dest )
{
        $host  = $_SERVER['HTTP_HOST'];
        $uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        header("Location: http://$host$uri/$dest");
        exit;
}

function isValidEmail( $address )
{
        if ( preg_match('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/', $address) )
        {
                return true;
        }
        else
        {
                return false;
        }

}


function cookieVar( $varname )
{
        if ( isset( $_COOKIE[$varname] ) )
        {
                return $_COOKIE[$varname];
        }
        else
        {
                return "";
        }
}

function getVar( $varname )
{
        if ( isset( $_GET[$varname] ) )
        {
                return $_GET[$varname];
        }
        else
        {
                return "";
        }
}

function postVar( $varname )
{
        if ( isset( $_POST[$varname] ) )
        {
                return $_POST[$varname];
        }
        else
        {
                return "";
        }
}

function getOrPostVar( $varname ) {
    $result = getVar( $varname );
    if ( ( $result ) == "" ) {
        $result = postVar( $varname );
    }
    return $result;
}

function generateCharacter ()
{
        $possible = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
        return $char;
}

function generateGUID ()
{
        $guid="";
        for ( $x=0; $x<32; $x++ )
        {
                $guid .= generateCharacter();
        }
        return $guid;
}
function generateRandomPassword ()
{
        $pass="";
        for ( $x=0; $x<10; $x++ )
        {
                $pass .= generateCharacter();
        }
        return $pass;
}

function send_text_email($from, $to, $subject, $message)
{
        $headers = "From: " . $from . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/plain; charset=iso-8859-1' . "\r\n";
    
        return mail($to, $subject, $message, $headers);
}

