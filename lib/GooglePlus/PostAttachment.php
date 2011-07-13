<?php

require_once 'GoogleUtil.php';
require_once 'PlusPost.php';

//This is just a helper class. Data is stored in PlusPost
class PostAttachment {

    public $title;
    public $description;
    public $url;
    public $media_url;
    public $media_width;
    public $media_height;
    public $media_type;
    public $preview_img;
    public $raw_data;

    function __construct() {
        $this->cleardata();
    }

    private function cleardata() {
    
        $this->title = "";
        $this->description = "";
        $this->url = "";
        $this->media_url = "";
        $this->media_width = "";
        $this->media_height = "";
        $this->media_type = "";
        $this->preview_img = "";
        $this->raw_data = "";

    }


    public function loadFromGooglePlusJSON( $data ) {
        $this->title = $data[3];
        $this->description = $data[21];

        if ( isset( $data[24] ) ) {
            $this->url = $data[24][1];
        }
        if ( isset( $data[5] ) ) {
            $this->media_url = $data[5][1];
            $this->media_width = $data[5][2];
            $this->media_height = $data[5][3];
            $this->media_type = $data[24][4];
            $this->preview_img = $data[41][0][1];
        }

        $this->raw_data = json_encode( $data );
    }


}
