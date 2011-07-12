<?

//BEGIN MAIN

//This should handle the typical page lifecycle
//Calls PageMain() when setup is complete. Define that function in
//your top level php file and render an html template within that.

//TODO
//Define IsAuthenticated in here to set up any authentication logic that
//would be used by all pages

//TODO
//decide if you're using db or sessions here
$uses_db = true;
$uses_session = false;
$cookie_guid = false;

if ( $uses_session ) {
    session_start();
}

if ( $uses_db ) {
    open_db();
}

$template = array();
$template['page_url'] = basename( $_SERVER['PHP_SELF'] );


if ( $cookie_guid ) {
    if ( !isset( $_COOKIE['user_guid']) ) {
        $template['user_guid'] = generateGUID();
    } else {
        $template['user_guid'] = $_COOKIE['user_guid'];
    }
    setcookie( 'user_guid', 
        $template['user_guid'], 
        time() + 60*60*24*300, //keep it for 300 days
        '/' );
}

if ( IsAuthenticated() ) {
    $template['authenticated'] = true;
} else {
    $template['authenticated'] = false;
}

PageMain();

if ( $uses_db ) {
    close_db();
}
//END MAIN



//TODO
//Define this per your application's needs
function IsAuthenticated() {

global $template;

    $authenticated = false;

    return $authenticated;

}

//Use this instead of localRedirect, since it will close the db first
function HandleLocalRedirect( $location ) {
  if ( $uses_db ) {
    close_db();
  }
  localRedirect( $location );
}

//TODO
//add any other utility functions that are commonly used in pages that use
//this application flow
