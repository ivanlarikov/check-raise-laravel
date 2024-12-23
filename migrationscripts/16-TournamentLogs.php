<?php
die('need to update');
//old DB
include('db.php');

if ($getAllToomsResult= $mysqli_new->query("SELECT id FROM `rooms`")) {
    while($room = $getAllToomsResult -> fetch_assoc()){
        $sql="SELECT * FROM `jtamvgr0h_postmeta` WHERE `post_id` = ".$room['id']." AND `meta_key` LIKE '%tournaments_log%'";
        if($getPostMetaResult=$mysqli->query($sql)){
            $roomMeta = $getPostMetaResult -> fetch_assoc();
            $items=unserialize($roomMeta['meta_value']);
            printf("Room = %d\n",$room['id']);
            printf("------------------\n",$room['id']);
            foreach($items as $data){
                $user_id=$data['user_id'];
                $tournament_id=$data['tournament_id'];
                $type=$data['type'];
                $changes=$mysqli_new->real_escape_string(json_encode($data['changes']));
                $created_at=date('Y-m-d h:i:s',$data['datetime']);
                $insert="INSERT INTO `tournament_logs`(`tournament_id`, `user_id`, `type`, `changes`, `created_at`) VALUES ($tournament_id,$user_id,$type,'$changes','$created_at')";
                printf("\n Q = %s",$insert);
                $mysqli_new->query($insert);
                
            }
            printf("Done------------------\n",$room['id']);
            
        }
    }
}