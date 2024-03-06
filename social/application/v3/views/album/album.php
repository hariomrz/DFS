    <?php
    if(isset($no_permission) && $no_permission){
        $this->load->view('album/album_user_no_permission');
    }
    else if($albumMod=='create' || $albumMod=='edit'){
        $this->load->view('album/create_album');
    }else if($albumMod=='list'){
        $this->load->view('album/album_list');
    }else if($albumMod=='detail'){
        $this->load->view('album/album_detail');
    }
    ?>
    
    <input type="hidden" id="AlbumOffset" value="1">
    <input type="hidden" id="AlbumLimit" value="5">
    <input type="hidden" id="AlbumDetailOffset" value="1">
    <input type="hidden" id="AlbumDetailLimit" value="50">
    <input type="hidden" id="EntityType" value="Album">
    <input type="hidden" id="EntityGUID" value="<?php echo isset($AlbumGUID) ? $AlbumGUID : '' ; ?>">
    <input type="hidden" id="UserID" value="<?php if(isset($UserID)){ echo $UserID; } ?>" />