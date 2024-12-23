<?php
//old DB
include('db.php');
//Import all

//$sql="SELECT * FROM `jtamvgr0h_posts` where `post_type`='tournaments'";
$sql="SELECT * FROM `ckr_tournament_registration_log`";
if ($result = $mysqli -> query($sql)) {
    while($row = $result -> fetch_assoc()){
        printf ("%s\n", $row["id"]);
        try{
            //$post_title = $mysqli_new->real_escape_string($row["post_title"]);
            $insertSql="INSERT INTO `tournament_registration_logs`(`id`, `tournament_id`, `user_id`, `status_from`, `status_to`, `created_at`) VALUES (".$row['id'].",".$row['tournament_id'].",".$row['user_id'].",".$row['status_from'].",".$row['status_to'].",'".$row['created_at']."')";
            $mysqli_new->query($insertSql);
            printf("Done\n");
        }
        catch(Exception $e)
        {
            printf("%s",$e->getMessage());
            continue;
        }
    }
}

$mysqli -> close();
$mysqli_new->close();