<?php
include('db.php');
//administrator //player //manager //director

if ($result = $mysqli -> query("SELECT user_id FROM `jtamvgr0h_usermeta` WHERE `meta_key` = 'jtamVgr0H_capabilities' AND `meta_value` LIKE '%administrator%'")) {
    while($row = $result -> fetch_assoc()){
        printf ("%s\n", $row["user_id"]);
       
        try
        {
            $model=$mysqli_new->real_escape_string('App\Models\User\User');
            $sql="INSERT INTO `model_has_roles`(`role_id`, `model_type`, `model_id`) VALUES (1,'".$model."',".$row['user_id'].")";
            $mysqli_new->query($sql);
        }
        catch(Exception $e)
        {
            printf("%s",$e->getMessage());
            continue;
        }
        //printf("Done %s (%s)\n", $row["ID"], $row["user_login"]);
    }
}
//player
if ($result = $mysqli -> query("SELECT user_id FROM `jtamvgr0h_usermeta` WHERE `meta_key` = 'jtamVgr0H_capabilities' AND `meta_value` LIKE '%player%'")) {
    while($row = $result -> fetch_assoc()){
        printf ("%s\n", $row["user_id"]);
       
        try
        {
            $model=$mysqli_new->real_escape_string('App\Models\User\User');
            $sql="INSERT INTO `model_has_roles`(`role_id`, `model_type`, `model_id`) VALUES (3,'".$model."',".$row['user_id'].")";
            $mysqli_new->query($sql);
        }
        catch(Exception $e)
        {
            printf("%s",$e->getMessage());
            continue;
        }
        //printf("Done %s (%s)\n", $row["ID"], $row["user_login"]);
    }
}
//manager
if ($result = $mysqli -> query("SELECT user_id FROM `jtamvgr0h_usermeta` WHERE `meta_key` = 'jtamVgr0H_capabilities' AND `meta_value` LIKE '%manager%'")) {
    while($row = $result -> fetch_assoc()){
        printf ("%s\n", $row["user_id"]);
       
        try
        {
            $model=$mysqli_new->real_escape_string('App\Models\User\User');
            $sql="INSERT INTO `model_has_roles`(`role_id`, `model_type`, `model_id`) VALUES (2,'".$model."',".$row['user_id'].")";
            $mysqli_new->query($sql);
        }
        catch(Exception $e)
        {
            printf("%s",$e->getMessage());
            continue;
        }
        //printf("Done %s (%s)\n", $row["ID"], $row["user_login"]);
    }
}
//director
if ($result = $mysqli -> query("SELECT user_id FROM `jtamvgr0h_usermeta` WHERE `meta_key` = 'jtamVgr0H_capabilities' AND `meta_value` LIKE '%director%'")) {
    while($row = $result -> fetch_assoc()){
        printf ("%s\n", $row["user_id"]);
       
        try
        {
            $model=$mysqli_new->real_escape_string('App\Models\User\User');
            $sql="INSERT INTO `model_has_roles`(`role_id`, `model_type`, `model_id`) VALUES (4,'".$model."',".$row['user_id'].")";
            $mysqli_new->query($sql);
        }
        catch(Exception $e)
        {
            printf("%s",$e->getMessage());
            continue;
        }
        //printf("Done %s (%s)\n", $row["ID"], $row["user_login"]);
    }
}
  
  $mysqli -> close();