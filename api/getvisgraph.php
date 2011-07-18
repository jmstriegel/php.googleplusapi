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
    
    if ( $template['callback'] != "" && preg_match( '/^\w+$/', $template['callback']  ) ) {
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


    $plookup = array();
    $linklookup = array();
    $people = array();
    $links = array();


    //This person
    $person = getCachedPerson( $pid );
    $pdata = extractPersonData( $person, 0 );
    $people[] = $pdata;
    $plookup[ $pdata['id'] ] = 1;


    //First degree
    $circledpeople = getCachedCircled( $person );
    foreach( $circledpeople as $cp ) {
        $pdata = extractPersonData( $cp, 1 );
        if ( !isset( $plookup[$pdata['id']] ) ) {
            $people[] = $pdata;
            $plookup[ $pdata['id'] ] = 1;
        }

        $link = array();
        $link['from'] = $pid;
        $link['to'] = $pdata['id'];
        $link['weight'] = 1;
        $linklookup[ $link['from'] . "-" . $link['to']  ] = count($links);
        $links[] = $link;
    }
   

    //Only matching relationships for third degree
    foreach( $circledpeople as $cp ) {
        
        $rels = array();
        if ( $cp->fetched_relationships != 1 ) {

            $rels = PlusPerson::FetchVisiblePlusPeople( $cp->googleplus_id );

        } else {
            
            $or = PlusRelationship::FetchRelationshipsByOwner( $cp->googleplus_id );

            foreach ( $or as $r ) {
                $p = new PlusPerson();
                $p->googleplus_id =  $r->hasincircle_id;
            
                $rels[] = $p;
            }
        }

        
        foreach ( $rels as $rel ) {
            $link = array();
            $link['from'] = $cp->googleplus_id;
            $link['to'] = $rel->googleplus_id;
            $link['weight'] = 1;
            
            
            //only add the links if they link back to someone we know
            //and only add them once per connection pair
            if ( isset( $plookup[ $rel->googleplus_id ] ) ) {
            
/*                if ( !isset( $linklookup[ $link['from'] . "-" . $link['to']  ] ) && 
                    !isset( $linklookup[ $link['to'] . "-" . $link['from']  ] ) ) {
            
                    $linklookup[ $link['from'] . "-" . $link['to']  ] = count( $links );
                    $links[] = $link;
                } else {
                    if ( isset( $linklookup[ $link['from'] . "-" . $link['to']  ] )) {
                        $links[ $linklookup[ $link['from'] . "-" . $link['to']  ] ]['weight'] += 1;
                    } else {
                        $links[ $linklookup[ $link['to'] . "-" . $link['from']] ]['weight'] += 1;
                    }
                }
*/

                if ( !isset( $linklookup[ $link['from'] . "-" . $link['to']  ] ) ) {
                    $linklookup[ $link['from'] . "-" . $link['to']  ] = count( $links );
                    $links[] = $link;
                } 
                if ( isset( $linklookup[ $link['to'] . "-" . $link['from']  ] )) {
                    $links[ $linklookup[ $link['from'] . "-" . $link['to']  ]]['weight'] += 1;
                    $links[ $linklookup[ $link['to'] . "-" . $link['from']  ]]['weight'] += 1;
                }

            }
        }
    }


    //lets filter out only the bidirectional links
    $bilinks = array();
    foreach( $links as $link ) {
        if ( $link['weight'] > 1 ) {
            $bilinks[] = $link;
        }
    }


    $data = array( 
        'people' => $people,
        'relationships' => $links
    );

    $responsedata = json_encode( $data );
    
    //wrap jsonp if necessary
    if ( $template['callback'] != "" && preg_match( '/^\w+$/', $template['callback']  ) ) {
        $responsedata = $template['callback'] . '(' . $responsedata . ');';
    }

    echo $responsedata;
}

function extractPersonData( $person, $group ) {
    $result = array();
    $result['id'] = $person->googleplus_id;
    $result['name'] = $person->first_name . " " . $person->last_name;
    $result['group'] = $group;

    return $result;
}


function getCachedPerson( $pid ) {
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

    return $person;
}

function getCachedCircled( $person ) {

    $circled = array();

    if ( $person->fetched_relationships != 1 ) {
   
        $followees = PlusPerson::FetchVisiblePlusPeople( $person->googleplus_id );

        $or = PlusRelationship::FetchRelationshipsByOwner( $person->googleplus_id );
        foreach ( $or as $r ) {
            $r->deleteFromDB();
        } 

        foreach( $followees as $fp ) {
            $pid = $fp->googleplus_id;
            $followee = getCachedPerson( $pid );

            $circled[] = $followee;

            $rel = new PlusRelationship();
            $rel->owner_id = $person->googleplus_id;
            $rel->hasincircle_id = $followee->googleplus_id;
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
            $circled[] = $p;
        }

    }

    return $circled;
}

