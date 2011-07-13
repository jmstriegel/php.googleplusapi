<?php

require_once 'GoogleUtil.php';
require_once 'PlusRelationship.php';

class PlusPerson {

    //internal properties
    public $plusperson_id;
    public $googleplus_id; // [1][0]
    public $profile_photo; // [1][2][3]
    public $first_name; // [1][2][4][1]
    public $last_name; // [1][2][4][2]
//    public $gender; //where is this?
    public $introduction; // [1][2][14][1]
    public $subhead; // [1][2][33][1]


    public $raw_data;
    public $fetched_relationships;
    public $created_dt;
    public $modified_dt;


    function __construct() {
        $this->cleardata();
    }

    private function cleardata() {
    
        $this->plusperson_id = 0;
        $this->googleplus_id = "";
        $this->profile_photo = "";
        $this->first_name = "";
        $this->last_name = "";
        $this->introduction = "";
        $this->subhead = "";
        $this->raw_data = "";
        $this->fetched_relationships = 0;
        $this->created_dt = "";
        $this->modified_dt = "";

    }

    private function loadFromRowResult( $row ) {
        $this->plusperson_id = $row['plusperson_id'];
        $this->googleplus_id = $row['googleplus_id'];
        $this->profile_photo = $row['profile_photo'];
        $this->first_name = $row['first_name'];
        $this->last_name = $row['last_name'];
        $this->introduction = $row['introduction'];
        $this->subhead = $row['subhead'];
        $this->raw_data = $row['raw_data'];
        $this->created_dt = $row['created_dt'];
        $this->fetched_relationships = $row['fetched_relationships'];
        $this->modified_dt = $row['modified_dt'];
    }

    public function loadByID( $id ) {
        global $db;

        $this->cleardata();

        $query = sprintf("SELECT * FROM plusperson WHERE plusperson_id = %d", clean_int( $id ));
        $result = mysql_query( $query, $db );

        if ( $row = mysql_fetch_assoc( $result ) ) {
            $this->loadFromRowResult( $row );
        }

    }

    public function loadByGooglePlusID( $id ) {
        global $db;

        $this->cleardata();

        $query = sprintf("SELECT * FROM plusperson WHERE googleplus_id = '%s'", clean_string( $id ));
        $result = mysql_query( $query, $db );

        if ( $row = mysql_fetch_assoc( $result ) ) {
            $this->loadFromRowResult( $row );
        }

    }



    public function insertIntoDB() {
        global $db;
        
        if ( $this->plusperson_id <= 0 ) {
            $query = sprintf("INSERT INTO plusperson ( googleplus_id, profile_photo, first_name, last_name, subhead, introduction, raw_data, fetched_relationships, created_dt, modified_dt ) " .
                            " VALUES ( '%s', '', '', '', '', '', '', 0, NOW(), NOW() ) ", 
                            clean_string( $this->googleplus_id ));
            $result = mysql_query( $query, $db );
            if ( $result ) {
                $this->plusperson_id = mysql_insert_id( $db );
                $this->updateDB();
                $this->loadByID( $this->plusperson_id );
            }
        }
    }

    public function updateDB() {
        global $db;

        if ( $this->plusperson_id > 0 ) {
        
            $query = sprintf("UPDATE plusperson SET " .
                            " googleplus_id='%s', " .
                            " profile_photo='%s', " .
                            " first_name='%s', " .
                            " last_name='%s', " .
                            " subhead='%s', " .
                            " introduction='%s', " .
                            " raw_data='%s', " .
                            " fetched_relationships=%d, " .
                            " modified_dt = NOW() " .
                            " WHERE plusperson_id=%d ",
                            clean_string( $this->googleplus_id ),
                            clean_string( $this->profile_photo ),
                            clean_string( $this->first_name ),
                            clean_string( $this->last_name ),
                            clean_string( $this->subhead ),
                            clean_string( $this->introduction ),
                            clean_string( $this->raw_data ),
                            clean_int( $this->fetched_relationships ),
                            clean_int( $this->plusperson_id ) );
            $result = mysql_query( $query, $db );
        
        }
    }

    public function deleteFromDB() {
        global $db;

        if ( $this->plusperson_id > 0 ) {

            //DELETE ALL SUB-CONTENT
            $rs =  PlusRelationship::FetchRelationshipsByOwner( $this->googleplus_id );
            foreach ( $rs as $r ) {
                $r->deleteFromDB();
            }
                
            $rs =  PlusRelationship::FetchRelationshipsByCircled( $this->googleplus_id );
            foreach ( $rs as $r ) {
                $r->deleteFromDB();
            }

            $query = sprintf("DELETE FROM plusperson WHERE plusperson_id = %d", clean_int( $this->plusperson_id ) );
            $result = mysql_query( $query, $db );
            if ( $result ) {
                $this->plusperson_id = 0;
            }
        }
    }


    private function loadFromGooglePlusJSON( $data ) {
        $this->googleplus_id = $data[1][0];
        $this->profile_photo = $data[1][2][3];
        $this->first_name = $data[1][2][4][1];
        $this->last_name = $data[1][2][4][2];
        $this->introduction = $data[1][2][14][1];
        $this->subhead = $data[1][2][33][1];
        $this->raw_data = json_encode( $data );
    }


    public function updateFromGooglePlusService( ) {
        
        if ( $this->googleplus_id != "" ) {
       //echo "update profile<br/>"; 
            $profile_url = 'https://plus.google.com/_/profiles/get/' . $this->googleplus_id;
           
            $jsondata = GoogleUtil::FetchGoogleJSON( $profile_url );

            $this->loadFromGooglePlusJSON( $jsondata );
        
        }

    }

    public static function FetchVisiblePlusPeople( $plusid ) {
    
        $people = array();
        if ( $plusid != "" ) {
       //echo "fetch followees<br/>"; 
            $visible_url = 'https://plus.google.com/_/socialgraph/lookup/visible/?o=%5Bnull%2Cnull%2C%22' . $plusid . '%22%5D';

            $jsondata = GoogleUtil::FetchGoogleJSON( $visible_url );
            $visiblepeople = $jsondata[0][2];

            foreach( $visiblepeople as $pdata ) {
            
                $person = new PlusPerson();
                $person->googleplus_id = $pdata[0][2];
                $people[] = $person;
            }


        }

        return $people;
    
    }

    public static function FetchIncomingPlusPeople( $plusid ) {
    
        $people = array();
        if ( $plusid != "" ) {
       //echo "fetch followers<br/>"; 
            $visible_url = 'https://plus.google.com/_/socialgraph/lookup/incoming/?o=%5Bnull%2Cnull%2C%22' . $plusid .'%22%5D&n=1000';
            $jsondata = GoogleUtil::FetchGoogleJSON( $visible_url );
            $inpeople = $jsondata[0][2];

            foreach( $inpeople as $pdata ) {
            
                $person = new PlusPerson();
                $person->googleplus_id = $pdata[0][2];
                $people[] = $person;
            }


        }

        return $people;
    
    }

}
