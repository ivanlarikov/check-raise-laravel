<?php
//old DB
include('db.php');

//get all rooms in new db

if ($getAllToomsResult= $mysqli_new->query("SELECT id,user_id FROM `rooms`")) {
    while($room = $getAllToomsResult -> fetch_assoc()){
        /*printf ("%d\n", $room["id"]);
        printf ("%d\n", $room["user_id"]);*/
        //manual users
        
        $sql="SELECT * FROM `jtamvgr0h_options` WHERE `option_name`='ckr_manual_created_users_".$room['user_id']."'";
        if($getPostMetaResult=$mysqli->query($sql)){
            $roomUsers = $getPostMetaResult -> fetch_assoc();
            if(!empty($roomUsers['option_value'])){
                $users=unserialize($roomUsers['option_value']);
                //insert into database
                foreach($users as $user){
                    $sql="INSERT INTO `room_manual_users`(`room_id`, `user_id`) VALUES (".$room['id'].",".$user.")";
                    try{
                        $mysqli_new->query($sql);
                    }
                    catch(Exception $e)
                    {
                        
                    }
                }
            }
                
        }
        
        //room users
        $sql="SELECT * FROM `jtamvgr0h_options` WHERE `option_name`='ckr_room_users_".$room['user_id']."'";
        if($getPostMetaResult=$mysqli->query($sql)){
            $roomUsers = $getPostMetaResult -> fetch_assoc();
            if(!empty($roomUsers['option_value'])){
                $users=unserialize($roomUsers['option_value']);
                //insert into database
                foreach($users as $user){
                    $sql="INSERT INTO `room_users`(`room_id`, `user_id`) VALUES (".$room['id'].",".$user.")";
                    try{
                        $mysqli_new->query($sql);
                    }
                    catch(Exception $e)
                    {
                        
                    }
                }
            }
                
        }
        //room_managers_bans
        $sql="SELECT * FROM `jtamvgr0h_options` WHERE `option_name` LIKE '%options_room_managers_bans%' AND `option_value` = '".$room['user_id']."'";
        if($getPostMetaResult=$mysqli->query($sql)){
            $roomUsers = $getPostMetaResult -> fetch_assoc();
            if(!empty($roomUsers['option_name'])){
                $room_number=str_replace("options_room_managers_bans_","",$roomUsers['option_name']);
                $room_number=str_replace("_room_manager","",$room_number);
                if($room_number!=""){
                    //options_room_managers_bans_8_banned_players
                    echo $sql="SELECT * FROM `jtamvgr0h_options` WHERE `option_name` LIKE 'options_room_managers_bans_".$room_number."_banned_players'";
                    if($res=$mysqli->query($sql)){
                        $banned = $res -> fetch_assoc();
                        if(!empty($banned['option_value'])){
                            $users=unserialize($banned['option_value']);
                            //insert into database
                            foreach($users as $user){
                                $sql="INSERT INTO `room_ban_users`(`room_id`, `user_id`) VALUES (".$room['id'].",".$user.")";
                                try{
                                    $mysqli_new->query($sql);
                                }
                                catch(Exception $e)
                                {
                                    
                                }
                            }
                        }
                    }

                }
            }
        }
    }
}
$mysqli -> close();
$mysqli_new->close();