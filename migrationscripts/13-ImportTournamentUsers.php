<?php
//old DB
include('db.php');
//get all tournaments in new db
if ($getAllToomsResult= $mysqli_new->query("SELECT id FROM `tournaments`")) {
   
    while($tournament = $getAllToomsResult -> fetch_assoc()){
       //1. get manual_user
       $sql="SELECT `meta_value` FROM `jtamvgr0h_postmeta` WHERE `post_id`= ".$tournament['id']." AND `meta_key` LIKE 'manual_user'";
       $meta_value=$mysqli->query($sql);
       while($user=$meta_value -> fetch_assoc())
       {
            printf("Tournament - Manual User - %d======",$tournament['id']);
           $users=unserialize($user['meta_value']);
           foreach($users as $user){
            $sql="INSERT INTO `tournament_register_players`(`tournament_id`, `user_id`) VALUES (".$tournament['id'].",".$user.")";
                try{
                    $mysqli_new->query($sql);
                }
                catch(Exception $e)
                {
                    
                }
           }
           printf("Done\n",$tournament['id']);
       }
       //2. get waiting_list_players
       $sql="SELECT `meta_value` FROM `jtamvgr0h_postmeta` WHERE `post_id`= ".$tournament['id']." AND `meta_key` LIKE 'waiting_list_players'";
       $meta_value=$mysqli->query($sql);
       while($user=$meta_value -> fetch_assoc())
       {
            printf("Tournament - waiting_list_players - %d======",$tournament['id']);
           $users=unserialize($user['meta_value']);
           foreach($users as $user){
            $sql="INSERT INTO `tournament_waiting_players`(`tournament_id`, `user_id`) VALUES (".$tournament['id'].",".$user.")";
            try{
                $mysqli_new->query($sql);
            }
            catch(Exception $e)
            {
                
            }
           }
           printf("Done\n",$tournament['id']);
       }
       //3 get checkin_players
       $sql="SELECT `meta_value` FROM `jtamvgr0h_postmeta` WHERE `post_id`= ".$tournament['id']." AND `meta_key` LIKE 'checkin_players'";
       $meta_value=$mysqli->query($sql);
       while($user=$meta_value -> fetch_assoc())
       {
            printf("Tournament - checkin_players - %d======",$tournament['id']);
           $users=unserialize($user['meta_value']);
           foreach($users as $user){
            $sql="INSERT INTO `tournament_checkin_players`(`tournament_id`, `user_id`) VALUES (".$tournament['id'].",".$user.")";
            try{
                $mysqli_new->query($sql);
            }
            catch(Exception $e)
            {
                
            }
           }
           printf("Done\n",$tournament['id']);
       }
    }
}


$mysqli -> close();
$mysqli_new->close();