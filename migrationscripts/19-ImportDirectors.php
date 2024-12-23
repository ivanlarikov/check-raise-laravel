<?php
//old DB
include('db.php');

//get all rooms in new db

if ($getAllToomsResult= $mysqli_new->query("SELECT id,user_id FROM `rooms`")) {
    while($room = $getAllToomsResult -> fetch_assoc()){
        printf ("%d\n", $room["id"]);
        //SELECT * FROM `jtamvgr0h_postmeta` WHERE `post_id` = 2909 AND `meta_key` = 'select_manager'
        $sql="SELECT * FROM `jtamvgr0h_postmeta` WHERE `post_id` = ".$room['id']." AND `meta_key` = 'select_manager'";
        if($getPostMetaResult=$mysqli->query($sql)){
            $roomUsers = $getPostMetaResult -> fetch_assoc();
            if(!empty($roomUsers['meta_value'])){
                $users=unserialize($roomUsers['meta_value']);
                //insert into database
                print_r($users);
                foreach($users as $user){
                    $sql="INSERT INTO `room_directors`(`room_id`, `user_id`) VALUES (".$room['id'].",".$user.")";
                    $mysqli_new->query($sql);
                }
            }
                
        }
        
        
    }
}
//capablities 
if ($getAllToomsResult= $mysqli_new->query("SELECT * FROM `room_directors` ")) {
    while($room = $getAllToomsResult -> fetch_assoc()){
        printf ("%d\n", $room["user_id"]);
        //SELECT * FROM `jtamvgr0h_postmeta` WHERE `post_id` = 2909 AND `meta_key` = 'select_manager'
        $sql="SELECT * FROM `jtamvgr0h_usermeta` WHERE `user_id` = ".$room["user_id"]." AND `meta_key` = 'ckr_custom_capabilities'";
        if($getPostMetaResult=$mysqli->query($sql)){
            $roomUsers = $getPostMetaResult->fetch_assoc();
            if(!empty($roomUsers['meta_value']) && $roomUsers['meta_value']!="Removed"){
                $capabilities=unserialize($roomUsers['meta_value']);
                //insert into database
                print_r($capabilities);
                foreach($capabilities as $cap){
                    $sql="INSERT INTO `director_capabilities`(`user_id`, `capability`) VALUES (".$room['user_id'].",'".$cap."')";
                    $mysqli_new->query($sql);
                }
            }
                
        }
        
        
    }
}

$mysqli -> close();
$mysqli_new->close();