<?php
//old DB
include('db.php');
//get all rooms in new db

if ($getAllToomsResult= $mysqli_new->query("SELECT id FROM `tournaments` order by id")) {
    while($tournament = $getAllToomsResult -> fetch_assoc()){
         printf ("Strating %s\n", $tournament["id"]);
        if($getPostMetaResult=$mysqli->query("SELECT * FROM `jtamvgr0h_postmeta` WHERE `post_id` = ".$tournament['id']." AND `meta_key` LIKE 'starting_stack_table_%'")){
            $details=[];
            while($data = $getPostMetaResult -> fetch_assoc()){
                $key=str_replace("break_title","breaktitle",$data['meta_key']);
                $key=str_replace("starting_stack_table_","",$key);

                $de=explode("_",$key);
                
                if(empty($de[1]))
                    continue;
                $details[$de[0]+1][$de[1]]=$data['meta_value'];

            }
            foreach($details as $order=>$value){
                $isbreak=0;
                $breaktitle="";
                $bb=0;
                $sb=0;
                $ante=0;
                $duration="";
                if(empty($value['bb']) || !is_numeric($value['bb']))
                    $bb=0;
                else
                    $bb=$value['bb'];
                if(empty($value['sb']) || !is_numeric($value['sb']))
                    $sb=0;
                else
                    $sb=$value['sb'];
                if(empty($value['ante']) || !is_numeric($value['ante']))
                    $ante=0;
                else
                    $ante=$value['ante'];
                if(empty($value['duration']))
                    $duration=0;
                else
                    $duration=$value['duration'];

                if(empty($value['break']))
                    $isbreak=0;
                else
                    $isbreak=$value['break'];
                
                if(empty($value['breaktitle']))
                    $breaktitle="";
                else
                    $breaktitle=$value['breaktitle'];

                $sql="INSERT INTO `tournament_structures`(
                    `tournament_id`, `order`, `sb`, `bb`, `ante`, `duration`, `isbreak`, `breaktitle`
                    ) 
                    VALUES (".$tournament["id"].",".$order.",".$sb.",".$bb.",".$ante.",'".$duration."',".$isbreak.",'".$breaktitle."')";

                $mysqli_new->query($sql);
            }
            printf ("Ending %s\n", $tournament["id"]);
        }
       //now get the post meta from the old site 
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