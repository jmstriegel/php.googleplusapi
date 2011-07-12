<?php
$templatesdir = '../templates/';
$includesdir = '../includes/';

require_once $includesdir . 'config.inc';
require_once $includesdir . 'common.inc';
require_once $includesdir . 'database.inc';

//libraries we're using (these are in /lib/)
require_once 'GooglePlus/PlusPerson.php';
require_once 'GooglePlus/PlusRelationship.php';

//Sets up normal page variables, performs auth, runs PageMain()
require_once $includesdir . 'standard_page_logic.inc';

function PageMain() {
    global $template, $templatesdir, $includesdir, $config;

    //Do any page logic here.
    //If $using_db is set in standard_page_logic.inc the global $db
    //will be set. Most db queries should be done in a class library, though.

    //This simple example does stuff internally. For complicated json, you
    //might be better off making a json template in templates/js


    $template['callback'] = getVar('callback');
    $template['echo'] = getVar('echo');
    
    if ( $template['callback'] != "" ) {
        //use jsonp
        header('Content-type: text/javascript');
    } else {
        //standard json
        header('Content-type: application/json');
    }



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
        $person->fetched_relationships = 0;
        $person->updateDB();
    }


    $data = array( 
        'googleplus_id' => $person->googleplus_id,
        'first_name' => $person->first_name,
        'last_name' => $person->last_name,
        'profile_photo' => $person->profile_photo,
        'introduction' => $person->introduction,
        'subhead' => $person->subhead
    );

    $responsedata = json_encode( $data );
    
    //wrap jsonp if necessary
    if ( $template['callback'] != "" ) {
        $responsedata = $template['callback'] . '(' . $responsedata . ');';
    }

    echo $responsedata;
}
