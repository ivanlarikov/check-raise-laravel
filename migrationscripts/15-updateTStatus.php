<?php
//old DB
include('db.php');

//get all rooms in new db

if ($getAllToomsResult= $mysqli_new->query("SELECT id FROM `tournaments`")) {
    while($room = $getAllToomsResult -> fetch_assoc()){
        printf ("%d\n", $room["id"]);
        //get post status from database
        if($getTStatus=$mysqli->query("SELECT `post_status` FROM `jtamvgr0h_posts` WHERE `ID` = ".$room['id'])){
            $roomMeta = $getTStatus -> fetch_assoc();
            if($roomMeta['post_status']=="draft")
            {
                $sql="UPDATE `tournaments` SET `status`=2 WHERE `id` = ".$room["id"];
                $mysqli_new->query($sql);
            }
            else if($roomMeta['post_status']=="publish")
            {
                $sql="UPDATE `tournaments` SET `status`=1 WHERE `id` = ".$room["id"];
                $mysqli_new->query($sql);
            }
        }
       //now get the post meta from the old site 
       //meta value is_archived , is_registration_closed
       if($getPostMetaResult=$mysqli->query("SELECT `meta_value` FROM `jtamvgr0h_postmeta` WHERE `post_id` = ".$room['id'].
       " AND `meta_key` = 'is_registration_closed' AND `meta_value`=1")){
            
            $roomMeta = $getPostMetaResult -> fetch_assoc();
            //printf ("Done %s\n",$roomMeta["meta_value"]); 
           
            if(!empty($roomMeta["meta_value"]) && $roomMeta["meta_value"]==1){
                //printf("\n%s",implode(",",$tournaments));
                $sql="UPDATE `tournaments` SET `closed`=1 WHERE `id` = ".$room["id"];
                $mysqli_new->query($sql);
                //printf("%s\n",$sql)    
            }
            else{
                $sql="UPDATE `tournaments` SET `closed`=0 WHERE `id` = ".$room["id"];
                $mysqli_new->query($sql);
            }
       }
       if($getPostMetaResult=$mysqli->query("SELECT `meta_value` FROM `jtamvgr0h_postmeta` WHERE `post_id` = ".$room['id'].
       " AND `meta_key` = 'is_archived' AND `meta_value`=1")){
            
            $roomMeta = $getPostMetaResult -> fetch_assoc();
            //printf ("Done %s\n",$roomMeta["meta_value"]); 
           
            if(!empty($roomMeta["meta_value"]) && $roomMeta["meta_value"]==1){
                //printf("\n%s",implode(",",$tournaments));
                $sql="UPDATE `tournaments` SET `archived`=1 WHERE `id` = ".$room["id"];
                $mysqli_new->query($sql);
                //printf("%s\n",$sql)    
            }
            else{
                $sql="UPDATE `tournaments` SET `archived`=0 WHERE `id` = ".$room["id"];
                $mysqli_new->query($sql);
            }
       }
       // printf ("Done %s (%s)\n", $row["ID"], $row["user_login"]);
    }
}


$mysqli -> close();
$mysqli_new->close();
