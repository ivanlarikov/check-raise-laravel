<?php
//old DB
include('db.php');
//Import all

//$sql="SELECT * FROM `jtamvgr0h_posts` where `post_type`='tournaments'";
$sql="SELECT * FROM `jtamvgr0h_posts` WHERE `post_type`='tournaments' and ID not IN (
    SELECT post_id FROM `jtamvgr0h_postmeta` WHERE `meta_key` LIKE '%_icl_lang_duplicate_of%' and `post_id` IN (SELECT ID FROM `jtamvgr0h_posts` WHERE `post_type`='tournaments')
    )";
if ($result = $mysqli -> query($sql)) {
    while($row = $result -> fetch_assoc()){
        printf ("%s (%s)\n", $row["ID"], $row["post_title"]);
        try{
            $post_title = $mysqli_new->real_escape_string($row["post_title"]);
            $insertSql="INSERT INTO `tournaments`(`id`, `title`, `slug`, `user_id`) VALUES ('".$row["ID"]."','".$post_title."','tournaments-".$row["ID"]."','".$row["post_author"]."')";
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