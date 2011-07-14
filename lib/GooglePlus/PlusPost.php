<?php

require_once 'GoogleUtil.php';
require_once 'PlusPerson.php';
require_once 'PostAttachment.php';

class PlusPost {

    //internal properties
    public $pluspost_id;
    public $googleplus_postid; // [21]
    public $author_id; // [16]
    public $post_data; // [4]
    public $share_content; // [47]
    public $shared_postid; // [77]

    public $raw_data;
    public $created_dt;
    public $modified_dt;


    function __construct() {
        $this->cleardata();
    }

    private function cleardata() {
    
        $this->pluspost_id = 0;
        $this->googleplus_postid = "";
        $this->author_id = "";
        $this->post_data = "";
        $this->share_content = "";
        $this->shared_postid = "";

        $this->raw_data = "";
        $this->created_dt = "";
        $this->modified_dt = "";

    }

    private function loadFromRowResult( $row ) {
        $this->pluspost_id = $row['pluspost_id'];
        $this->googleplus_postid = $row['googleplus_postid'];
        $this->author_id = $row['author_id'];
        $this->post_data = $row['post_data'];
        $this->share_content = $row['share_content'];
        $this->shared_postid = $row['shared_postid'];

        $this->raw_data = $row['raw_data'];
        $this->created_dt = $row['created_dt'];
        $this->modified_dt = $row['modified_dt'];
    }

    public function loadByID( $id ) {
        global $db;

        $this->cleardata();

        $query = sprintf("SELECT * FROM pluspost WHERE pluspost_id = %d", clean_int( $id ));
        $result = mysql_query( $query, $db );

        if ( $row = mysql_fetch_assoc( $result ) ) {
            $this->loadFromRowResult( $row );
        }

    }

    public function loadByGooglePlusPostID( $id ) {
        global $db;

        $this->cleardata();

        $query = sprintf("SELECT * FROM pluspost WHERE googleplus_postid = '%s'", clean_string( $id ));
        $result = mysql_query( $query, $db );

        if ( $row = mysql_fetch_assoc( $result ) ) {
            $this->loadFromRowResult( $row );
        }

    }

    public function mergeStreamPostIntoDB() {
        global $db;
    
        if ( $this->pluspost_id <= 0 ) {
            //if the google post id exists, hijack this insert and update the existing record instead
            if ( $this->googleplus_postid != "" ) {
                $existing = new PlusPost();
                $existing->loadByGooglePlusPostID( $this->googleplus_postid );
                if ( $existing->pluspost_id != 0 ) {
                    
                    $this->pluspost_id = $existing->pluspost_id;
                    $this->updateDB();
                
                } else {
                
                    $this->insertIntoDB();

                }
            }
        }
    }

    public function insertIntoDB() {
        global $db;


        if ( $this->pluspost_id <= 0 ) {
            

            $query = sprintf("INSERT INTO pluspost ( googleplus_postid, author_id, post_data, share_content, shared_postid, raw_data, created_dt, modified_dt ) " .
                            " VALUES ( '%s', '', '', '', '', '', NOW(), NOW() ) ", 
                            clean_string( $this->googleplus_postid ));
            $result = mysql_query( $query, $db );
            if ( $result ) {
                $this->pluspost_id = mysql_insert_id( $db );
                $this->updateDB();
                $this->loadByID( $this->pluspost_id );
            }
        }
    }

    public function updateDB() {
        global $db;

        if ( $this->pluspost_id > 0 ) {
        
            $query = sprintf("UPDATE pluspost SET " .
                            " googleplus_postid='%s', " .
                            " author_id='%s', " .
                            " post_data='%s', " .
                            " share_content='%s', " .
                            " shared_postid='%s', " .
                            " raw_data='%s', " .
                            " modified_dt = NOW() " .
                            " WHERE pluspost_id=%d ",
                            clean_string( $this->googleplus_postid ),
                            clean_string( $this->author_id ),
                            clean_string( $this->post_data ),
                            clean_string( $this->share_content ),
                            clean_string( $this->shared_postid ),
                            clean_string( $this->raw_data ),
                            clean_int( $this->pluspost_id ) );
            $result = mysql_query( $query, $db );
        
        }
    }

    public function deleteFromDB() {
        global $db;

        if ( $this->pluspost_id > 0 ) {

            //DELETE ALL SUB-CONTENT

            $query = sprintf("DELETE FROM pluspost WHERE pluspost_id = %d", clean_int( $this->pluspost_id ) );
            $result = mysql_query( $query, $db );
            if ( $result ) {
                $this->pluspost_id = 0;
            }
        }
    }

    public function getAttachments() {
    
        $attachments = array();

        $data = json_decode( $this->raw_data );
        
        if ( isset( $data ) && isset( $data[11] ) ) {
            foreach ( $data[11] as $attachdata ) {
            
                $attch = new PostAttachment();
                $attch->loadFromGooglePlusJSON( $attachdata );
                $attachments[] = $attch;
            
            }
        }

        return $attachments;
    }

    private function loadFromGooglePlusJSON( $data ) {
        $this->googleplus_postid = $data[21];
        $this->author_id = $data[16];
        $this->share_content = $data[47];
        $this->post_data = $data[4];
        $this->shared_postid = $data[77];

        $this->raw_data = json_encode( $data );
    }

    public function getUpdateID() {
        $updateid="";
        if ( preg_match( '/\/posts\/(\w+)$/', $this->googleplus_postid, $matches ) ) {
            $updateid = $matches[1];
        }
        return $updateid;
    }
    public function getAuthorIDFromPostID() {
        $authorid="";
        if ( preg_match( '/^(\w+)\/posts\//', $this->googleplus_postid, $matches ) ) {
            $authorid = $matches[1];
        }
        return $authorid;
    }

    //this updates a single post
    public function updateFromGooglePlusService( ) {
        
        if ( $this->googleplus_postid != "" ) {
            $post_url = 'https://plus.google.com/_/stream/getactivity/' . $this->getAuthorIDFromPostID() . '?updateId=' . $this->getUpdateID();
           
            $jsondata = GoogleUtil::FetchGoogleJSON( $post_url );

            $this->loadFromGooglePlusJSON( $jsondata[1] );
        
        }
    }


    public static function FetchPostsByGooglePlusID( $googleplus_id ) {
        global $db;

        $query = sprintf( "SELECT * FROM pluspost WHERE author_id = '%s' ORDER BY created_dt DESC" , clean_string($googleplus_id) );
        $result = mysql_query( $query, $db );
        $ps = array();
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $p = new PlusPost();
            $p->loadFromRowResult( $row );
            $ps[] = $p;
        }

        return $ps;
    }

    public static function FetchActivityStream( $googleplus_id ) {
    
        $activity_url = 'https://plus.google.com/_/stream/getactivities/' . $googleplus_id . '/?sp=%5B1%2C2%2C%22' . $googleplus_id . '%22%2Cnull%2Cnull%2Cnull%2Cnull%2C%22social.google.com%22%2C%5B%5D%5D';
        $jsondata = GoogleUtil::FetchGoogleJSON( $activity_url );
        $activities = $jsondata[1][0];

//var_dump( $jsondata );
        $posts = array();
        foreach( $activities as $postdata ) {
            $post = new PlusPost();
            $post->loadFromGooglePlusJSON( $postdata );
            $posts[] = $post;
        }
        return $posts;

    }



}
