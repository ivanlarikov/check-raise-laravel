<?php
//old DB
include('db.php');

//get all rooms in new db

if ($getAllToomsResult= $mysqli_new->query("SELECT id FROM `tournaments`")) {
    while($room = $getAllToomsResult -> fetch_assoc()){
        printf ("%d\n", $room["id"]);
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
       // printf ("Done %s (%s)\n", $row["ID"], $row["user_login"]);
    }
}


$mysqli -> close();
$mysqli_new->close();

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}