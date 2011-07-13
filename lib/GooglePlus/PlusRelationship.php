<?php

require_once 'GoogleUtil.php';

class PlusRelationship {

    //internal properties
    public $plusrelationship_id;
    public $owner_id;
    public $hasincircle_id;

    public $created_dt;
    public $modified_dt;


    function __construct() {
        $this->cleardata();
    }

    private function cleardata() {
    
        $this->plusrelationship_id = 0;
        $this->owner_id = 0;
        $this->hasincircle_id = 0;
        $this->created_dt = "";
        $this->modified_dt = "";

    }

    private function loadFromRowResult( $row ) {
        $this->plusrelationship_id = $row['plusrelationship_id'];
        $this->owner_id = $row['owner_id'];
        $this->hasincircle_id = $row['hasincircle_id'];
        $this->created_dt = $row['created_dt'];
        $this->modified_dt = $row['modified_dt'];
    }

    public function loadByID( $id ) {
        global $db;

        $this->cleardata();

        $query = sprintf("SELECT * FROM plusrelationship WHERE plusrelationship_id = %d", clean_int( $id ));
        $result = mysql_query( $query, $db );

        if ( $row = mysql_fetch_assoc( $result ) ) {
            $this->loadFromRowResult( $row );
        }

    }


    public function insertIntoDB() {
        global $db;
        
        if ( $this->plusrelationship_id <= 0 ) {
            $query = sprintf("INSERT INTO plusrelationship ( owner_id, hasincircle_id, created_dt, modified_dt ) " .
                            " VALUES ( '','', NOW(), NOW() ) ");
            $result = mysql_query( $query, $db );
            if ( $result ) {
                $this->plusrelationship_id = mysql_insert_id( $db );
                $this->updateDB();
                $this->loadByID( $this->plusrelationship_id );
            }
        }
    }

    public function updateDB() {
        global $db;

        if ( $this->plusrelationship_id > 0 ) {
        
            $query = sprintf("UPDATE plusrelationship SET " .
                            " owner_id='%s', " .
                            " hasincircle_id='%s', " .
                            " modified_dt = NOW() " .
                            " WHERE plusrelationship_id=%d ",
                            clean_string( $this->owner_id ),
                            clean_string( $this->hasincircle_id ),
                            clean_int( $this->plusrelationship_id ) );
            $result = mysql_query( $query, $db );
        
        }
    }

    public function deleteFromDB() {
        global $db;

        if ( $this->plusrelationship_id > 0 ) {

            //DELETE ALL SUB-CONTENT
            //$revs =  DocRevision::FetchByGroupDoc( $this->groupdoc_id );
            //foreach ( $revs as $rev ) {
            //    $rev->deleteFromDB();
            //}

            $query = sprintf("DELETE FROM plusrelationship WHERE plusrelationship_id = %d", clean_int( $this->plusrelationship_id ) );
            $result = mysql_query( $query, $db );
            if ( $result ) {
                $this->plusrelationship_id = 0;
            }
        }
    }


    public static function FetchRelationshipsByOwner( $googleplus_id ) {
        global $db;
        
        $query = sprintf( "SELECT * FROM plusrelationship WHERE owner_id = '%s' ORDER BY modified_dt ASC" , clean_string($googleplus_id) );
        $result = mysql_query( $query, $db );
        $rs = array();
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $r = new PlusRelationship();
            $r->loadFromRowResult( $row );
            $rs[] = $r;
        }

        return $rs;

    }


    public static function FetchRelationshipsByCircled( $googleplus_id ) {
        global $db;
        
        $query = sprintf( "SELECT * FROM plusrelationship WHERE hasincircle_id = '%s' ORDER BY modified_dt ASC" , clean_string($googleplus_id) );
        $result = mysql_query( $query, $db );
        $rs = array();
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $r = new PlusRelationship();
            $r->loadFromRowResult( $row );
            $rs[] = $r;
        }

        return $rs;

    }


}
