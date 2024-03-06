<?php if(!empty($search_result)){
    foreach($search_result as $row){        
        ?>
<li id="liid-<?php echo $row['user_id']; ?>">
        	<div class="image">
<a href="profile-view/<?php echo $row['user_id']; ?>">
<img src="<?php echo get_full_path('profile_image',$row['unique_id'],$row['image'],36,36); ?>" width="36" height="36"  class="left">
</a>
                </div>
            <div class="condiv">
            	<a class="left m-t7 semibold font14" href="profile-view/<?php echo $row['user_id']; ?>"><?php echo $row['name']; ?></a>
                <section class="btns">
					<?php 	 
                    
                    
                    if($row['status']=='' && $this->user_id!=$row['user_id'])
					{
						?>  
						<input name="" data-tip="" rel="<?php echo $row['name']; ?>"  title="Request to connect to this hero!" type="button" class="btn btn-green small right m-t5 m-l3 add-as-heros" value="Add Hero" id="addHerobutton-<?php echo $row['user_id'];?>" onClick="addhero_popup('','<?php echo $row['user_id']; ?>');">
                        
                        
					<?php 
					} elseif ($row['status'] == 1) { ?>
					<button class="btn btn-gray small font14" type="button" name=""><i class="s1 icon-tick"></i>Hero</button>
					<?php }  
					/*elseif($row['status']==1) 
					{
					?>
                        <section class="left relative all_<?php echo $row['user_id'];?>" id="alreadyhero-<?php echo $row['user_id'];?>">
                            <button name="" type="button" id="btnHero-<?php echo $row['user_id'];?>" class="btn btn-gray small m-left7"
                            onclick="$('#disconnect-tooltip-<?php echo $row['user_id'];?>').toggle();">
                            <i class="s1 icon-tick"></i>Hero
                            </button>
                            <div class="arrow-box" id="disconnect-tooltip-<?php echo $row['user_id'];?>">
                                <a onclick="return delete_user_hero('<?php echo $row['user_id'];?>','<?php echo $this->user_id;?>','<?php echo $row['user_id'];?>'); $('#disconnect-tooltip-<?php echo $row['user_id'];?>, #btnHero-<?php echo $row['user_id'];?>').hide(); $('#addHerobutton-<?php echo $row['user_id'];?>').show(); "
                                hidefocus="true" style="outline: none;">Disconnect</a>
                            </div>
                        </section>
                    
                            <input name="" data-tip="tooltip" rel="<?php echo $row['name']; ?>" title="Request to connect to this hero!" type="button" class="btn btn-gray small font14" value="Add Hero" id="addHerobutton-<?php echo $row['user_id'];?>" onClick="addhero_popup('','<?php echo $row['user_id']; ?>');" rel="disconnected_hidden_<?php echo $row['user_id'];?>" style="display:none;">
                    
                    <?php }*/ ?>
                </section>
            </div>
        </li>
        <?php
    }
}else{ echo '<li>The hero you\'re looking for can\'t be found. Send an invitation; it\'ll only take 30 seconds.</li>';} ?>


<?php 
    $this->load->view('include/add_as_hero_pop_up');
?>
  
