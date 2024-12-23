<?php
include('db.php');
if ($result = $mysqli -> query("SELECT * FROM jtamvgr0h_users")) {
    while($row = $result -> fetch_assoc()){
        printf ("%s (%s)\n", $row["ID"], $row["user_login"]);
        try
        {
            //password = password
            $email=$row['user_email'];
            if($email=="")
                $email=$row["ID"]."-noemail@checkrise.ch";
            $sql="INSERT INTO `users`(`id`, `username`, `email`,`password`) VALUES ('".$row["ID"]."','".$row["user_login"]."','".$email."','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')";
            $mysqli_new->query($sql);
        }
        catch(Exception $e)
        {
            printf("%s",$e->getMessage());
            continue;
        }
        printf ("Done %s (%s)\n", $row["ID"], $row["user_login"]);
    }
}
$mysqli -> close();
