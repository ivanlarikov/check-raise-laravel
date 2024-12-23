<?php
//old DB
include('db.php');

//get all rooms in new db

if ($getAllToomsResult= $mysqli_new->query("SELECT id FROM `rooms`")) {
    while($room = $getAllToomsResult -> fetch_assoc()){
        printf ("%d\n", $room["id"]);
       //now get the post meta from the old site 
       if($getPostMetaResult=$mysqli->query("SELECT `meta_value` FROM `jtamvgr0h_postmeta` WHERE `post_id` = ".$room['id'].
       " AND `meta_key` = 'select_tournaments'")){
            
            $roomMeta = $getPostMetaResult -> fetch_assoc();
            //printf ("Done %s\n",$roomMeta["meta_value"]); 
            $tournaments=unserialize($roomMeta["meta_value"]);
           
            if(!empty($tournaments)){
                //printf("\n%s",implode(",",$tournaments));
                $sql="UPDATE `tournaments` SET `room_id`='".$room["id"]."' WHERE id IN (".implode(",",$tournaments).")";
                $mysqli_new->query($sql);
            }
       }
       // printf ("Done %s (%s)\n", $row["ID"], $row["user_login"]);
    }
}


$mysqli -> close();
$mysqli_new->close();
