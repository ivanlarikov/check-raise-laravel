<?php
//old DB
include('db.php');
//get the posts first 
$sql="SELECT * FROM `jtamvgr0h_posts` WHERE `post_status`='publish' AND `post_type`='rooms' and ID not IN (
    SELECT post_id FROM `jtamvgr0h_postmeta` WHERE `meta_key` LIKE '%_icl_lang_duplicate_of%' and `post_id` IN (SELECT ID FROM `jtamvgr0h_posts` WHERE `post_status`='publish' AND `post_type`='rooms')
    )";

if ($result = $mysqli -> query($sql)) {
    while($row = $result -> fetch_assoc()){
        printf ("%s (%s)\n", $row["ID"], $row["post_title"]);
        try{
            $post_title = $mysqli_new->real_escape_string($row["post_title"]);
            $insertSql="INSERT INTO `rooms`(`id`, `title`, `slug`, `user_id`,`status`,`expiry`) VALUES ('".$row["ID"]."','".$post_title."','".$row["post_name"]."','".$row["post_author"]."',1,'2023-12-31')";
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