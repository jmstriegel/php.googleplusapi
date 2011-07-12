<?php

$db = null;

function open_db() {
    global $config, $db;
        
    $db_user = $config[$config['env']]['db_user'];
    $db_host = $config[$config['env']]['db_host'];
    $db_pass = $config[$config['env']]['db_pass'];
    $db_name = $config[$config['env']]['db_name'];

    $db = mysql_connect( $db_host, $db_user, $db_pass ) or die( 'connection error: ' . mysql_error() );
    mysql_select_db( $db_name, $db ) or die ( 'could not access database' );

    mysql_set_charset('utf8', $db);

}

function close_db() {
    global $db;

    mysql_close( $db );
}

function clean_string( $value ) {
    global $db;
    // pre stripping these from input in config
    //if ( get_magic_quotes_gpc() ) {
    //   echo "magic quotes on"; $value = stripslashes( $value );
    //}
    // escape things properly
    return mysql_real_escape_string( $value, $db );
}

function clean_int( $value ) {
    if ( is_numeric( $value ) ) {
        return (int) $value;
    } else {
        return false;
    }
}

function clean_float( $value ) {
    if ( is_numeric( $value ) ) {
        return (float) $value;
    } else {
        return false;
    }
}

function clean_datetime( $value ) {
    if ( !isset( $value ) || $value == "" ) {
        return "NULL";
    } else {
        $datestamp = strtotime( $value );
        return "'" . date( 'Y-m-d H:i:s', $datestamp ) . "'";
    }
}

function clean_date( $value ) {
    if ( !isset( $value ) || $value == "" ) {
        return "NULL";
    } else {
        $datestamp = strtotime( $value );
        return "'" . date( 'Y-m-d', $datestamp ) . "'";
    }
}
