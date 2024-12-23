<?php
//old DB
include('db.php');
//get all tournaments in new db
if ($getAllToomsResult= $mysqli_new->query("SELECT id FROM `tournaments`")) {
   
    while($room = $getAllToomsResult -> fetch_assoc()){
        $posts=[];
        $descriptions=[];
        printf ("tournaments Description - %d\n", $room["id"]);
        $posts[]=$room["id"];
        $sql="SELECT `post_id` FROM `jtamvgr0h_postmeta` WHERE `meta_key` = '_icl_lang_duplicate_of' AND `meta_value` = '".$room["id"]."'";
        $duplicated_posts=$mysqli->query($sql);
        while($d_post=$duplicated_posts -> fetch_assoc())
        {
            $posts[]=$d_post['post_id'];   
        }
        
        $sql="SELECT * FROM `jtamvgr0h_icl_translations` WHERE `element_id` IN (".implode(',',$posts).")";
        $translations=$mysqli->query($sql);
        while($tran=$translations-> fetch_assoc())
        {
            //$description=$mysqli_new->real_escape_string($roomMeta['meta_value']);
            //get the descriptions from the post id
            $sql="SELECT `meta_value` FROM `jtamvgr0h_postmeta` WHERE `post_id` = ".$tran['element_id']." AND `meta_key` = 'tournament_description'";
            $description="";
            $getPostMetaResult=$mysqli->query($sql);
            $roomMeta = $getPostMetaResult -> fetch_assoc();
            $description=$mysqli_new->real_escape_string($roomMeta['meta_value']);
            $descriptions[]=[
                'tournament_id'=>$room['id'],
                'language'=>$tran['language_code'],
                'description'=>$description
            ];
        }
        foreach($descriptions as $key=>$value){
            $sql="INSERT INTO `tournament_descriptions`(`tournament_id`, `language`, `description`) VALUES ('".$value['tournament_id']."','".$value['language']."','".$value['description']."')";
            $mysqli_new->query($sql);
        }
    }
}


$mysqli -> close();
$mysqli_new->close();