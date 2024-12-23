<?php
//old DB
include('db.php');
//get all rooms in new db
if ($getAllToomsResult= $mysqli_new->query("SELECT id FROM `rooms`")) {
    while($room = $getAllToomsResult -> fetch_assoc()){
        //printf ("%d\n", $room["id"]);
       //now get the post meta from the old site 
       if($getPostMetaResult=$mysqli->query("SELECT * FROM `jtamvgr0h_postmeta` WHERE `post_id` = ".$room['id'].
       " AND `meta_key` = 'featured_image'")){
            $room_id=$room['id'];
            $roomLogo = $getPostMetaResult -> fetch_assoc();
            printf("%s\n",$roomLogo['meta_value']);
            if(!empty($roomLogo['meta_value'])){
                ///get URL of Logo
                $getLOGOURLRes= $mysqli->query("SELECT `post_name`,`guid` FROM `jtamvgr0h_posts` WHERE `ID` = ".$roomLogo['meta_value']);
                $roomLogoURL = $getLOGOURLRes -> fetch_assoc();
                $image = file_get_contents($roomLogoURL['guid']);
                file_put_contents('../public/room/'.$roomLogoURL['post_name'].".png", $image);
                printf("%s\n",$roomLogoURL['guid']);
                $sql="UPDATE `room_details` SET `logo`='".$roomLogoURL['post_name'].".png' WHERE `room_id` = ".$room['id'];
                $mysqli_new->query($sql);

            }
       }
       // printf ("Done %s (%s)\n", $row["ID"], $row["user_login"]);
    }
}


$mysqli -> close();
$mysqli_new->close();
