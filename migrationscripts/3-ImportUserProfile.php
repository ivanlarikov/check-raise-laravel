<?php
//old DB
include('db.php');
if ($result = $mysqli_new -> query("SELECT * FROM users")) {
    while($row = $result -> fetch_assoc()){
        printf("Importing Data - %s\n",$row['id']);
        printf("--------------\n");
        $sql="SELECT * FROM `jtamvgr0h_usermeta` WHERE `user_id` = ".$row['id'];
        $result_1=$mysqli->query($sql);

        $id=$row['id'];
        $firstname="";
        $lastname="";
        $dob="0000-00-00";
        $street="";
        $nickname="";
        $language="";
        $city="";
        $zipcode="";
        $displayoption="";
        $phonecode="";
        $phonenumber="";
        $phonecountry="";
        $enterprise="";

        while($userInfo = $result_1 -> fetch_assoc()){
            if($userInfo['meta_key']=="first_name")
                $firstname=$mysqli_new->real_escape_string($userInfo['meta_value']);
            else if($userInfo['meta_key']=="last_name")
                $lastname=$mysqli_new->real_escape_string($userInfo['meta_value']);
            /*else if($userInfo['meta_key']=="user_bday")
                $dob=$userInfo['meta_value'];*/
            else if($userInfo['meta_key']=="user_address")
                $street=$mysqli_new->real_escape_string($userInfo['meta_value']);
            else if($userInfo['meta_key']=="nickname")
                $nickname=$mysqli_new->real_escape_string($userInfo['meta_value']);
            else if($userInfo['meta_key']=="language")
                $language=$mysqli_new->real_escape_string($userInfo['meta_value']);
            else if($userInfo['meta_key']=="city")
                $city=$mysqli_new->real_escape_string($userInfo['meta_value']);
            else if($userInfo['meta_key']=="zipcode")
                $zipcode=$mysqli_new->real_escape_string($userInfo['meta_value']);
            else if($userInfo['meta_key']=="user_profile_type")
                $displayoption=$mysqli_new->real_escape_string($userInfo['meta_value']);
            else if($userInfo['meta_key']=="user_phone_country_prefix")
                $phonecode=$mysqli_new->real_escape_string($userInfo['meta_value']);
            else if($userInfo['meta_key']=="user_phone")
                $phonenumber=$mysqli_new->real_escape_string($userInfo['meta_value']);
            else if($userInfo['meta_key']=="user_phone_country")
                $phonecountry=$mysqli_new->real_escape_string($userInfo['meta_value']);
            else if($userInfo['meta_key']=="enterprise")
                $enterprise=$mysqli_new->real_escape_string($userInfo['enterprise']);
        }
        $phonenumber=str_replace(" ","",$phonenumber);
        try
        {
            $sql="INSERT INTO `user_profiles`(`user_id`, `firstname`, `lastname`, `dob`, `street`, `nickname`, `language`, `city`, `zipcode`, `displayoption`, `phonecode`, `phonecountry`, `phonenumber`,`enterprise`) VALUES 
            ('$id','$firstname','$lastname','$dob','$street','$nickname','$language','$city','$zipcode','$displayoption','$phonecode','$phonecountry','$phonenumber','$enterprise')";
            //printf("%s\n",$sql);
            $mysqli_new->query($sql);
        }
        catch(Exception $e)
        {
            printf("%s",$e->getMessage());
            continue;
        }
        //$mysqli_new->close();

        //printf ("Done %s (%s)\n", $row["ID"], $row["user_login"]);
    }
}
$mysqli -> close();
$mysqli_new->close();