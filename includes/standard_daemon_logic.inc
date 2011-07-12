<?

//BEGIN MAIN

//This should handle the typical daemon process lifecycle
//Calls PageMain() when setup is complete. Define that function in
//your top level php file and perform the app logic there.


//TODO
//decide if you're using db or sessions here
$uses_db = false;


if ( $uses_db ) {
    open_db();
}



PageMain();

if ( $uses_db ) {
    close_db();
}
//END MAIN



//TODO
//add any other utility functions that are commonly used in your
//application's daemons and server processes
