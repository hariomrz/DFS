
var Search = function(){
    self_s = this;
};

$.extend(Search.prototype,{
    self_s:{},
    all_service:'',
    first_level_service:[],
    second_level_service:[],
    users_lat_long:'',
    running_ajax_call :'',
    search_users:{},
	map:'',
	geocoder:'',
    separateService:function(){
        var service =  JSON.parse(this.all_service);
       // console.log(service);
        $.each(service,function(key , value){
            self_s.first_level_service[value.service_id] = value.service_name;
            self_s.second_level_service[value.service_id] = value.child;

           // console.log(self_s.second_level_service);

        });
    },

    getSecondLevelService:function(service_id){
        var html = '<option value="0">Subcategory</option> ';
        if(service_id ==0){
            $('#service_sub_category').html(html);
        } else {
            $('#service_sub_category').html(html);
            $.each(self_s.second_level_service[service_id],function(key,value){

               // if(value.service_name != 'Other Services'){
                    html = '<option value="'+value.service_id+'">'+value.service_name+'</option>';
                    $('#service_sub_category').append(html);
                //}
            });
        }
    },

    locationAutoSuggest:function(){
            /* Location auto suggest with bold search characters */
        $.ui.autocomplete.prototype._renderItem = function( ul, item){
            var term = this.term.split(' ').join('|');
            var re = new RegExp("(" + term + ")", "gi") ;
            var t = item.label.replace(re,"<b>$1</b>");
            return $( "<li></li>" )
                .data( "item.autocomplete", item )
                .append( "<a>" + t + "</a>" )
                .appendTo( ul );
        };

        $( "#search_location" ).autocomplete({
            source: site_url+"search/get_city_autosuggest",
            minLength: 1,
            select: function( event, ui ) {
                //console.log(ui.item.id+' :=: '+ui.item.value);
                $('#location_zip_code').val(ui.item.id);
                $('#confirm_search_location').val(ui.item.value);
            }
        });

    },

    searchOnLocation:function(){
		$('#city-list').html('');
		$('#did_you_mean').hide();
        if($('#search_location').val() !=$('#confirm_search_location').val() || $('#search_location').val() ==''  ){
			if($('#search_location').val() !='') {
				$.ajax({
					url:  site_url + "search/get_similar_location",
					data:'location='+$('#search_location').val() ,
					type: "POST",
					success: function(response){
						if (response == 'no_result') {
							
						} else {
							var result = JSON.parse(response);
							var locations = result.cities;
							if (locations.length == 1) {
								$('#did_you_mean').hide();
								$('#location_zip_code').val(locations[0]['zip_code']);
								$('#confirm_search_location').val(locations[0]['city']+', '+locations[0]['state']);
								$('#search_location').val(locations[0]['city']+', '+locations[0]['state']);
								self_s.searchOnLocation();
							} else {
								$('#did_you_mean').show();
								var html = '';	
								$('#did_you_mean .left').html('Did you mean: ');
								if (result.count > 20) {
									$('#did_you_mean .left').html('Too many locations match "'+$('#search_location').val()+'". Please pick a location from the list: ');
								}
								
								$.each(locations, function(i, item){
									html += '<a href="javascript:void(0)" rel="'+item.zip_code+'" onclick="dym_city_click(this);">'+item.city+' ,'+item.state+'</a>';		
									
								});
								$('#city-list').html(html);
							}
						}
						$('#err_location').hide();
					}
				});
			} else {
            	$('#err_location').html('Please select location.').show();
			}
            return false;
        }
        if($('#service_category').val() == 0){
            $('#err_category').html('Please select category.').show();
            return false;
        }
        $('#search_container_loader').show();

        $.ajax({
            url:  site_url + "search/search_on_location",
            data:$('#form_search_on_location').serialize() ,
            type: "POST",
            success: function(response){
                $('#err_category').html('Please select category.').hide();
                 $('#err_location').html('Please select location.').hide();
                $('#search_container_loader').hide();
                $('#search_container').html(response);
                //alert(response);
				
            }
        });

        return false;

    },
    serialize_form:function() {
          return $('#user-pagination').serialize();
    },

    google_map_initialize:function(){
		geocoder = new google.maps.Geocoder(); 
        //var user_lat_long = JSON.parse(this.users_lat_long);
        var user_lat_long = this.users_lat_long;
            //console.log(user_lat_long);
        var map_center = new google.maps.LatLng(user_lat_long[0].lat,user_lat_long[0].long);
        var mapOptions = {
                            zoom: 12,
							maxZoom: 24,
							minZoom: 6,
                            center: map_center ,
                            mapTypeId: google.maps.MapTypeId.ROADMAP
                        };
        map = new google.maps.Map(document.getElementById("googleMap"), mapOptions);		
        var bounds = new google.maps.LatLngBounds();
        bounds.extend(map_center);
        for (var i = 0; i < user_lat_long.length; i++) {
            var user = user_lat_long[i];
           // console.log(user);
            //var myLatLng = new google.maps.LatLng(user.lat, user.long);
            //var marker = new google.maps.Marker({
                //position: myLatLng,
                //map: map,
                //title: user.user_name,
                //zIndex: i+1
            //});
			self_s.code_address(user.address,i,user.user_name,bounds);           
        }
        
		
    },
	code_address:function(address,i,user_name,bounds){
		console.log(address);
		geocoder.geocode( { 'address': address}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				//map.setCenter(results[0].geometry.location);
				var myLatLng = results[0].geometry.location;
				var marker = new google.maps.Marker({
					map: map,
					position: myLatLng,
					title: user_name,
					zIndex: i+1
				});
				bounds.extend(myLatLng);
				map.fitBounds(bounds);
				if(map.getZoom() > 16) {
					map.setZoom(16);	
				}	
			}else if(status == "OVER_QUERY_LIMIT")
			{
				
				setTimeout(function() {
					self_s.code_address(address,i,user_name,bounds);
				}, 200);
				 
			} else {
				console.log('Geocode was not successful for the following reason: ' + status);
			}
		}); 
	},
    get_selected_service:function(){
        var selected_service    = new Array();
        var all_service         = new Array();
        var all_schools         = new Array();
        var selected_schools    = new Array();
        var selected_community  = new Array();
        var name                = $('#left_panel_name').val();
        var keyword             = $('#left_panel_keyword').val();
        var distance            = $('#left_panel_distance').val();
        var experience          = $('#left_panel_experience').val();
        var lic_credential      = $('#left_panel_lic_credential').val();
        var location_searched   = $('#search_location').val();
        var location_zip_code   = $('#location_zip_code').val();
        var order_by            = $('#order_by').val();
        var other_parent        = '';
 
       

        $( "ul#left_panel_3level_service li input" ).each(function() {

            all_service.push($(this).val());
           if($(this).is(':checked')){
                selected_service.push($(this).val());
            }
        });
   
        $("ul#left_panel_schools li input").each(function(){
            all_schools.push($(this).val());
            if($(this).is(':checked')){
                selected_schools.push($(this).val());
            }
        });

        $("ul#left_panel_community li input").each(function(){

            if($(this).is(':checked')){
                selected_community.push($(this).val());
            }
        });



        var out_service ;
        var out_schools ;
        var out_community;


        if(selected_service.length >0){
            out_service = JSON.stringify(selected_service);
        } else{
            out_service = JSON.stringify(all_service);
             if($('#service_sub_category option:selected').text()=='Other Services'){
                other_parent = $('#service_category').val();
            }
        }

        if(selected_schools.length >0){
            out_schools = JSON.stringify(selected_schools);
        }else {
            //out_schools = JSON.stringify(all_schools);
            out_schools = JSON.stringify(new Array());
        }

        if(selected_community.length >0){
            out_community = JSON.stringify(selected_community);
        }else {
            //out_schools = JSON.stringify(all_schools);
            out_community = JSON.stringify(new Array());
        }

        return {'location_zip_code' : location_zip_code,
                'services'          : out_service,
                'schools'           : out_schools,
                'community'         : out_community,
                'name'              : name,
                'keyword'           : keyword,
                'distance'          : distance,
                'experience'        : experience,
                'lic_credential'    : lic_credential,
                'order_by'          : order_by,
                'other_parent'      : other_parent,
                'location_searched' : location_searched
               };

    },

    search_on_service_filter: function(that,is_clear){

        if($(that).val() == '' && is_clear==0){
           // return false;
        }
		if (self_s.running_ajax_call && self_s.running_ajax_call.readyState != 4) {
			self_s.running_ajax_call.abort();
		}

        self_s.running_ajax_call =  $.ajax({
                                        url:  site_url + "search/search_on_service_filter",
                                        data: self_s.get_selected_service() ,
                                        type: "POST",
                                        success: function(response){
                                                $('#search_container_loader').hide();
                                                $('#search-result-list').html(response);
                                        }
                                    });

        return false;

    },

    clear_text_filter:function(control_id){
        $('#'+control_id).val('');
        var that = $('#'+control_id);
        this.search_on_service_filter(that,1);
    },
    create_save_link:function(){
        var category = $('#service_category option:selected').html();
        var sub_category =$('#service_sub_category').val();
        var location = $('#search_location').val();
		$('#save-search-error').hide();
        if ($('#saveTip').is(':hidden')){
            $('#save_search_link').val(category+'/'+location);
            $('#saveTip').fadeIn(300);
        }
        else {
            $('#saveTip').fadeOut(300);
        }
        $(document).mouseup(function(){
            $('#saveTip').fadeOut(300);
        });
        $('#saveTip').mouseup(function(){
            return false;
        });
    },
    save_search:function(){
		//if ($('ul#search-result-list li').length > 0) {
			$('#save-search-btn').attr('disabled','disabled');
			if($('#save_search_link').val() != ''){
				var category            = $('#service_category').val();
				var sub_category        = $('#service_sub_category').val();
				var save_data           = self_s.get_selected_service();
				save_data.category      = category;
				save_data.sub_category  = sub_category;
				save_data.link          = $('#save_search_link').val();
				//console.log(save_data);
				$('#save-search-error').html('Your search saved successfully.').show();
				$.ajax({
					url:  site_url + "search/save_search_history",
					data: save_data ,
					type: "POST",
					success: function(response){
					  // window.location = site_url;
					  $('#save-search-btn').removeAttr('disabled');
					  $('#saveTip').fadeOut(2000);
					  return false;
					}
				});

			} else {

				return false;
			}

		//} else {
			//return false;
		//}

    },

    put_saved_search_parameter:function(category_id, sub_category_id, location_zip_code, location_name){
        $('#service_category').val(category_id);
        this.getSecondLevelService(category_id);
        $('#service_sub_category').val(sub_category_id);
        $('#search_location').val(location_name);
	    //$('#confirm_search_location').val(location_name);
        $('#location_zip_code').val(location_zip_code);
        this.searchOnLocation();
    },

    put_saved_search_filters_param:function(all_filters){
        var data            = JSON.parse(all_filters);
        var services        = data.third_level_service_ids.split(',');
        var schools         = data.school_ids.split(',');
		var communities     = data.community_ids.split(',');
        var name            = data.name;
        var distance        = data.distance;
        var experience      = data.experience;
        var lic_credential  = data.licences_credential;
        var keyword         = data.keyword;

        if($( "ul#left_panel_3level_service li ").length != services.length){
            $( "ul#left_panel_3level_service li input" ).each(function() {

                for (var i=0;i<services.length;i++)
                {
                    if($(this).val() == services[i]){
                        $(this).attr('checked',true);
                    }
                }
            });
        }

        if($("ul#left_panel_schools li").length != schools.length){
            $("ul#left_panel_schools li input").each(function(){

                for (var i=0;i<schools.length;i++)
                {
                    if($(this).val() == schools[i]){
                        $(this).attr('checked',true);
                    }
                }

            });

        }
		
		if($("ul#left_panel_community li").length != communities.length){
            $("ul#left_panel_community li input").each(function(){

                for (var i=0;i<communities.length;i++)
                {
                    if($(this).val() == communities[i]){
                        $(this).attr('checked',true);
                    }
                }

            });

        }
		
        $('#left_panel_name').val(name);
        $('#left_panel_distance').val(distance);
        $('#left_panel_experience').val(experience);
        $('#left_panel_lic_credential').val(lic_credential);
        $('#left_panel_keyword').val(keyword);

        this.search_on_service_filter($('#left_panel_distance'),0);
    },

    community_see_more:function(that){

        $('.community_see_more').toggle();
        $('.community_show_hide').toggle();
    }



});
function dym_city_click(ths) {
	$('#location_zip_code').val($(ths).attr('rel'));
	$('#confirm_search_location').val($(ths).text());
	$('#search_location').val($(ths).text());
	self_s.searchOnLocation();
}

/**************SAVED SEARCH CLASS START ************/
var Saved_Search = function(){
    self_saved_s = this;
};

$.extend(Saved_Search.prototype,{

    delete_saved_search:function(search_unique_id){
		
		openPopDiv('deleteSavedSearch');
		$("#search_unique_id").val(search_unique_id);
        
    }

});


function delete_saved_search_confirm()
{
	var search_unique_id = $("#search_unique_id").val();
	$('#saved_search_'+search_unique_id).hide();
            $.ajax({
                url:  site_url + "search/delete_saved_search",
                data: 'search_unique_id='+search_unique_id,
                type: "POST",
                success: function(response){
					$("#search_unique_id").val(0);
					closePopDiv('deleteSavedSearch');
                }
            });
	
}

function addhero_popup(name,id)
{	
	openPopDiv('addHero');
	$("#hero_id").val(id);
	$("#form_add_hero input:checkbox").removeAttr('checked');
	$("#form_add_hero").find('textarea').val('');
	$('#error_relation').hide();
	//("#userid_hero_name").html(name);
	var relname = $("#addHerobutton-"+id).attr('rel');
	$("#userid_hero_name").html(relname);
	fixThumbnailMargins();
}

function submitmessage()
{
	var subject = $("#Subject").val();
	var message = $("#message").val();
	var to_names = $("#to_names").val();
	
	 if(to_names == ''){
        $('#to_names_error').html('Please enter name.').show();
        return false;
    }
	if(subject == ''){
        $('#to_subject_error').html('Please enter subject.').show();
        return false;
    }
	if(message == ''){
        $('#to_message_error').html('Please enter message.').show();
        return false;
    }
	
	 $.ajax({
		  url:  site_url + "build_network/send_message",
                data:$('#sendmessage').serialize(),
                type: "POST",
                success: function(data)
				{
					closePopDiv('sendMessage');
                }	
			
	})
	 
}


function send_single_message(username,usersid)
{ 	

		openPopDiv('sendMessage');
		$("#to_names").html('');
		$("#to_names").val('');
		$("#Subject").val('');
		$("#message").val('');
	
		$("#userid_hero_names").html(username);

	
		 htmlstr = '<input type="text" name="to_names[tags]['+usersid+'-a]" value="'+username+'" class="tag" />';
       
		
		$("#toname").html(htmlstr);
		$('input.tag').tagedit(); 
		
	
}  


$(function(){
 
    // add multiple select / deselect functionality
$(document).on("click","#selectall",function()
	{  
		$('.case').attr('checked', this.checked);
		
		if($('.case:checkbox:checked').length>0) 
		{
			document.getElementById("selectall").checked = true;
			$('.cc').attr('checked', true);
		} 
		else 
		{
			$("#selectall").removeAttr("checked");
			$(".cc").removeAttr("checked");
		}     
			var val = [];
			$('.case:checkbox:checked').each(function(i){
			val[i] = $(this).val();
		});
		
    });
});

function selectedall()
{
	
	if($('.case:checkbox:checked').length==0) 
	{
		openPopDiv("selectedallSearch");
	}
	else
	{
		$("#to_names").html('');
		$("#to_names").val('');
		$("#Subject").val('');
		$("#message").val('');
		
		var valid = '';
		var valname = '';
		 var htmlstr = '';

        $('.case:checkbox:checked').each(function(i)
		{
         valid = $(this).val();
		 
		  valname = $("#names_"+valid).val();
		 
		// valname[i] = namevalue;
		 htmlstr += '<input type="text" name="to_names[tags]['+valid+'-a]" value="'+valname+'" class="tag" />';
		 
        });
		
		$("#toname").html(htmlstr);
		$('input.tag').tagedit(); 
 
		
        if($('.case:checkbox:checked').length>0) 
		{
		 openPopDiv('sendMessage');
		}
		
		if($('.case:checkbox:checked').length==1) 
		{
			$("#userid_hero_names").html(valname);
		}
		else
		{
			$("#userid_hero_names").html('all....');
		}
	}
	
		
}

function selectedallbottom()
{
	
	if($('.case:checkbox:checked').length==0) 
	{
		openPopDiv("selectedallSearch");
		return false;
	}
	else
	{
		$("#to_names").html('');
		$("#to_names").val('');
		$("#Subject").val('');
		$("#message").val('');
		
		var valid = '';
		var valname = '';
	    var htmlstr = '';

        $('.case:checkbox:checked').each(function(i)
		{
         valid = $(this).val();
		 
		 valname = $("#names_"+valid).val();
	
		 htmlstr += '<input type="text" name="to_names[tags]['+valid+'-a]" value="'+valname+'" class="tag" />';
		 
        });
		
		$("#toname").html(htmlstr);
		$('input.tag').tagedit(); 
		
		
		if($('.case:checkbox:checked').length==1) 
		{
			$("#userid_hero_names").html(valname);
		}
		else
		{
			$("#userid_hero_names").html('all....');
		}
		
        if($('.case:checkbox:checked').length>0) 
		{
		 openPopDiv('sendMessage');
		 return false;
		}
	}
	
}


function views_heros_list(friend_id)
{	
	
	$.ajax({
				   url:site_url+'community/views_heros_member_list',
				   data:'friend_id='+friend_id,
				   type:'POST',
				   success:function(data){ 
					var user_name = $("#user_name_"+friend_id).val();
					$("#connection_views_heros_list").html(data);
					openPopDiv('mutualPopup');
					$("#usernames").html(user_name);
					 
				   }
	 });
	
}
		
	

