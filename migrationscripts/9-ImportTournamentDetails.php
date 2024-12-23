<?php
//old DB
include('db.php');
//get all rooms in new db

if ($getAllToomsResult= $mysqli_new->query("SELECT id FROM `tournaments`")) {
    while($tournament = $getAllToomsResult -> fetch_assoc()){
        
       //now get the post meta from the old site 
       if($getPostMetaResult=$mysqli->query("SELECT * FROM `jtamvgr0h_postmeta` WHERE `post_id` = ".$tournament['id'])){

            $tournament_id=$tournament['id'];
            $type="";
            $isshorthanded="";
            $dealertype="";
            $buyin="";
            $bounty="";
            $maxreentries="";
            $startingstack="";
            $level_duration="";
            $maxplayers="";
            $reservedplayers="";
            $startday="";
            $lastday="";
            $lateregformat="";
            $lateregtime="";
            $latereground="";
            $rake="";


            while($roomMeta = $getPostMetaResult -> fetch_assoc()){
                
                if($roomMeta['meta_key']=="type")
                    $type=$mysqli_new->real_escape_string($roomMeta['meta_value']);
                else if($roomMeta['meta_key']=="is_short_handed")
                    $isshorthanded=$mysqli_new->real_escape_string($roomMeta['meta_value']);
                else if($roomMeta['meta_key']=="dealers")
                    $dealertype=$mysqli_new->real_escape_string($roomMeta['meta_value']);
                else if($roomMeta['meta_key']=="buy-in")
                    $buyin=$mysqli_new->real_escape_string($roomMeta['meta_value']);
                else if($roomMeta['meta_key']=="bounty")
                    $bounty=$mysqli_new->real_escape_string($roomMeta['meta_value']);
                else if($roomMeta['meta_key']=="max_number_of_rebuys")
                    $maxreentries=$mysqli_new->real_escape_string($roomMeta['meta_value']);       
                else if($roomMeta['meta_key']=="starting_stack")
                    $startingstack=$mysqli_new->real_escape_string($roomMeta['meta_value']);  
                else if($roomMeta['meta_key']=="level_duration")
                    $level_duration=$mysqli_new->real_escape_string($roomMeta['meta_value']);  
                else if($roomMeta['meta_key']=="max_number_of_players")
                    $maxplayers=$mysqli_new->real_escape_string($roomMeta['meta_value']);      
                else if($roomMeta['meta_key']=="reserved_players_count")
                    $reservedplayers=$mysqli_new->real_escape_string($roomMeta['meta_value']);
                else if($roomMeta['meta_key']=="start_day")
                    $startday=$mysqli_new->real_escape_string($roomMeta['meta_value']);       
                else if($roomMeta['meta_key']=="last_day")
                    $lastday=$mysqli_new->real_escape_string($roomMeta['meta_value']);  
                else if($roomMeta['meta_key']=="late_reg_format")
                    $lateregformat=$mysqli_new->real_escape_string($roomMeta['meta_value']);  
                else if($roomMeta['meta_key']=="late_reg_time")
                    $lateregtime=$mysqli_new->real_escape_string($roomMeta['meta_value']);
                else if($roomMeta['meta_key']=="late_reg_round")
                    $latereground=$mysqli_new->real_escape_string($roomMeta['meta_value']);
                else if($roomMeta['meta_key']=="rake")
                    $rake=$mysqli_new->real_escape_string($roomMeta['meta_value']);

            }
            //check if data is there else skip 
            if($type=="" || empty($type))
            {
                //skip this
                printf ("Skip - %d\n", $tournament["id"]);
                continue;
            }
            if($lateregtime=="")
                $lateregtime="00:00";
            if($latereground=="")
                $latereground=0;
            if($bounty=="")
                $bounty=0;
            if($level_duration=="")
                $level_duration=0;
            if($lastday=="")
                $lastday="0000-00-00";
            if($reservedplayers=="")
                $reservedplayers=0;
            if($rake=="")
                $rake=0;
            $sql="INSERT INTO `tournament_details`(`tournament_id`, `type`, `isshorthanded`, `dealertype`, `buyin`, `bounty`, `rake`, `maxreentries`, `startingstack`, `level_duration`, `maxplayers`, `reservedplayers`, `startday`, `lastday`, `lateregformat`, `lateregtime`, `latereground`) VALUES ('".$tournament_id."','".$type."','".$isshorthanded."','".$dealertype."','".$buyin."','".$bounty."',".$rake.",'".$maxreentries."','".$startingstack."','".$level_duration."','".$maxplayers."',".$reservedplayers.",'".$startday."','".$lastday."','".$lateregformat."','".$lateregtime."',".$latereground.")";
            printf ("Tournament - %d\n", $tournament["id"]);
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