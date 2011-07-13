<?php
$templatesdir = '../templates/';
$includesdir = '../includes/';

require_once $includesdir . 'config.inc';
require_once $includesdir . 'common.inc';
require_once $includesdir . 'database.inc';

//libraries we're using (these are in /lib/)
require_once 'GooglePlus/PlusPerson.php';
require_once 'GooglePlus/PlusPost.php';

//Sets up normal page variables, performs auth, runs PageMain()
require_once $includesdir . 'standard_page_logic.inc';

function PageMain() {
    global $template, $templatesdir, $includesdir, $config;

    //Do any page logic here.
    //If $using_db is set in standard_page_logic.inc the global $db
    //will be set. Most db queries should be done in a class library, though.

    $template['title'] = 'PlusActivity API Test';


    $pid = getVar('plusid');
    if ( $pid == "" ) {
        $pid = '109463793347920104139';
    }

    $person = new PlusPerson();
    $person->loadByGooglePlusID( $pid );

    if ( $person->plusperson_id == 0 ) {
        $person->googleplus_id = $pid;
        $person->updateFromGooglePlusService();
        $person->insertIntoDB();
    } else if ( strtotime( $person->modified_dt ) < strtotime( "-6 hours" ) ) {
        $person->updateFromGooglePlusService();
        $person->updateDB();
    }

    $template['title'] = $person->first_name . " " . $person->last_name . " // " . $template['title'];


    //Get the posts from G+
    $posts = PlusPost::FetchActivityStream( $person->googleplus_id );


    //Save them all into the DB (merge will try to update them if they exist already)
    foreach ( $posts as $post ) {
        $post->mergeStreamPostIntoDB();
    }

    //Pull them back out of the db for display 
    $posts = PlusPost::FetchPostsByGooglePlusID( $person->googleplus_id );

    $template['person'] = $person;
    $template['posts'] = $posts;
    require_once $templatesdir . 'pages/tests/plusactivity.inc';

}

