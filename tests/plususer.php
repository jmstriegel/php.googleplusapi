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

    $template['title'] = 'PlusUser API Test';


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

    $template['title'] = $person->first_name . " " . $person->last_name . " // " . $template['title'];

    $hascircled = array();
    $circledby = array();

    if ( $person->fetched_relationships != 1 ) {

        $followees = PlusPerson::FetchVisiblePlusPeople( $person->googleplus_id );
        $followers = PlusPerson::FetchIncomingPlusPeople( $person->googleplus_id );


        $or = PlusRelationship::FetchRelationshipsByOwner( $person->googleplus_id );
        foreach ( $or as $r ) {
            $r->deleteFromDB();
        }
        $cr = PlusRelationship::FetchRelationshipsByCircled( $person->googleplus_id );
        foreach ( $cr as $r ) {
            $r->deleteFromDB();
        }

        //These could be easily sped up with a map that loads all existing in one db hit.
        //Leaving as-is to improve readability for demo.
        foreach( $followees as $fp ) {
            $pid = $fp->googleplus_id;
            $fp->loadByGooglePlusID( $pid );
            if ( $fp->plusperson_id <= 0 ) {
                $fp->googleplus_id = $pid;
                $fp->updateFromGooglePlusService();
                $fp->insertIntoDB();
            }
            $hascircled[] = $fp;

            $rel = new PlusRelationship();
            $rel->owner_id = $person->googleplus_id;
            $rel->hasincircle_id = $fp->googleplus_id;
            $rel->insertIntoDB();

        }


        //Some people are circled by a ton of other people.
        //Let's not fetch these profiles if they aren't already cached.
        foreach( $followers as $fp ) {
            $pid = $fp->googleplus_id;
            $fp->loadByGooglePlusID( $pid );
            if ( $fp->plusperson_id <= 0 ) {
                $fp->googleplus_id = $pid;
            }
            $circledby[] = $fp;
            
            $rel = new PlusRelationship();
            $rel->owner_id = $fp->googleplus_id;
            $rel->hasincircle_id = $person->googleplus_id;
            $rel->insertIntoDB();
            
        }

        $person->fetched_relationships = 1;
        $person->updateDB();

    } else {
    
        $or = PlusRelationship::FetchRelationshipsByOwner( $person->googleplus_id );
        foreach ( $or as $r ) {
            $p = new PlusPerson();
            $p->loadByGooglePlusID( $r->hasincircle_id );
            if ( $p->plusperson_id <= 0 ) {
                $p->googleplus_id = $r->hasincircle_id;
            }
            $hascircled[] = $p;
        }

        $cr = PlusRelationship::FetchRelationshipsByCircled( $person->googleplus_id );
        foreach ( $cr as $r ) {
            $p = new PlusPerson();
            $p->loadByGooglePlusID( $r->owner_id );
            if ( $p->plusperson_id <= 0 ) {
                $p->googleplus_id = $r->owner_id;
            }
            $circledby[] = $p;
        }

    }

    $template['person'] = $person;
    $template['circledby'] = $circledby;
    $template['hascircled'] = $hascircled;
    require_once $templatesdir . 'pages/tests/plususer.inc';

}

