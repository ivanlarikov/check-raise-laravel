<?php
//old DB
include("db.php");

//get all rooms in new db

if ($getAllToomsResult= $mysqli_new->query("SELECT id FROM `rooms`")) {
    while($room = $getAllToomsResult -> fetch_assoc()){
        printf ("%d\n", $room["id"]);
       //now get the post meta from the old site 
       if($getPostMetaResult=$mysqli->query("SELECT * FROM `jtamvgr0h_postmeta` WHERE `post_id` = ".$room['id'])){
            $room_id=$room['id'];
            $logo="";
            $street="";
            $town="";
            $canton="";
            $phone="";
            $phonecode="";
            $phonecountry="";
            $website="";
            $contact="";
            $city="";
            $zipcode="";
            
            while($roomMeta = $getPostMetaResult -> fetch_assoc()){
                
                if($roomMeta['meta_key']=="address")
                    $street=$mysqli_new->real_escape_string($roomMeta['meta_value']);
                else if($roomMeta['meta_key']=="address_2")
                    $town=$mysqli_new->real_escape_string($roomMeta['meta_value']);
                else if($roomMeta['meta_key']=="canton")
                    $canton=$mysqli_new->real_escape_string($roomMeta['meta_value']);
                else if($roomMeta['meta_key']=="canton")
                    $canton=$mysqli_new->real_escape_string($roomMeta['meta_value']);
                else if($roomMeta['meta_key']=="phone")
                    $phone=$mysqli_new->real_escape_string($roomMeta['meta_value']);
                else if($roomMeta['meta_key']=="user_phone_country_prefix")
                    $phonecode=$mysqli_new->real_escape_string($roomMeta['meta_value']);       
                else if($roomMeta['meta_key']=="user_phone_country")
                    $phonecountry=$mysqli_new->real_escape_string($roomMeta['meta_value']);  
                else if($roomMeta['meta_key']=="website")
                    $website=$mysqli_new->real_escape_string($roomMeta['meta_value']);  
                else if($roomMeta['meta_key']=="contact_email")
                    $contact=$mysqli_new->real_escape_string($roomMeta['meta_value']);
                else if($roomMeta['meta_key']=="city")
                    $city=$mysqli_new->real_escape_string($roomMeta['meta_value']);
                else if($roomMeta['meta_key']=="zipcode")
                    $zipcode=$mysqli_new->real_escape_string($roomMeta['meta_value']);
            }

            $sql="INSERT INTO `room_details`(`room_id`, `logo`, `street`, `town`, `canton`, `phone`, `phonecode`, `phonecountry`, `website`, `contact`,`city`,`zipcode`) VALUES ('".$room_id."','".$logo."','".$street."','".$town."','".$canton."','".$phone."','".$phonecode."','".$phonecountry."','".$website."','".$contact."','".$city."','".$zipcode."')";
            printf("%s\n",$sql);
            $mysqli_new->query($sql);
       }
       // printf ("Done %s (%s)\n", $row["ID"], $row["user_login"]);
    }
}


$mysqli -> close();
$mysqli_new->close();

/*function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}*/