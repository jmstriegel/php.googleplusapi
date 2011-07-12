<?php 

class GoogleUtil {


    public static function FetchGoogleJSON( $url ) {
        $response = file_get_contents( $url );
        $response = GoogleUtil::CleanGoogleJSON( $response );
        usleep( 200000 ); //be nice. let's sleep for 200ms
        return json_decode( $response, true );
    }
    
    public static function CleanGoogleJSON( $googlejson ) {
        
        //delete anti-xss junk ")]}'\n" (5 chars);
        $googlejson = substr( $googlejson, 5 );

        //pass through result and turn empty elements into nulls
        //echo strlen( $googlejson ) . '<br>';
        $instring = false;
        $inescape = false;
        $lastchar = '';
        $output = "";
        for ( $x=0; $x<strlen( $googlejson ); $x++ ) {

            $char = substr( $googlejson, $x, 1 );

            //toss unnecessary whitespace
            if ( !$instring && ( preg_match( '/\s/', $char ) ) ) {
                continue;
            }
            
            //handle strings
            if ( $instring ) {
                if ( $inescape ) {
                    $output .= $char;
                    $inescape = false;
                } else if ( $char == '\\' ) {
                    $output .= $char;
                    $inescape = true;
                } else if ( $char == '"' ) {
                    $output .= $char;
                    $instring = false;
                } else {
                    $output .= $char;
                }
                $lastchar = $char;
                continue;
            }


            switch ( $char ) {
           
                case '"':
                    $output .= $char;
                    $instring = true;
                    break;

                case ',':
                    if ( $lastchar == ',' || $lastchar == '[' || $lastchar == '{' ) { 
                        $output .= 'null';
                    }
                    $output .= $char;
                    break;

                case ']':
                case '}':
                    if ( $lastchar == ',' ) { 
                        $output .= 'null';
                    }
                    $output .= $char;
                    break;

                default:
                    $output .= $char;
                    break;
            }
            $lastchar = $char;
        }
        return $output;
    }
}
