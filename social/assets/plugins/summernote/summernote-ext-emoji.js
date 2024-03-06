 var mycount=0;
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory(require('jquery'));
    } else {
        factory(window.jQuery);
    }
}(function ($) {
    $.extend($.summernote.plugins, {
        'emoji': function (context) {
            var self = this;
           
            var ui = $.summernote.ui;
            var emojis =  ['bowtie', 'smile', 'laughing', 'blush', 'smiley', 'relaxed', 'smirk', 'heart_eyes', 'kissing_heart', 'kissing_closed_eyes', 'flushed', 'relieved', 'satisfied', 'grin', 'wink', 'stuck_out_tongue_winking_eye', 'stuck_out_tongue_closed_eyes', 'grinning', 'kissing', 'kissing_smiling_eyes', 'stuck_out_tongue', 'sleeping', 'worried', 'frowning', 'anguished', 'open_mouth', 'grimacing', 'confused', 'hushed', 'expressionless', 'unamused', 'sweat_smile', 'sweat', 'weary', 'pensive', 'disappointed', 'confounded', 'fearful', 'cold_sweat', 'persevere', 'cry', 'sob', 'joy', 'astonished', 'scream', 'neckbeard', 'tired_face', 'angry', 'rage', 'triumph', 'sleepy', 'yum', 'mask', 'sunglasses', 'dizzy_face', 'imp', 'smiling_imp', 'neutral_face', 'no_mouth', 'innocent', 'alien', 'yellow_heart', 'blue_heart', 'purple_heart', 'heart'];
            var emojis2 = ['feelsgood', 'finnadie', 'goberserk', 'godmode', 'hurtrealbad', 'rage1', 'rage2', 'rage3', 'rage4', 'suspect', 'trollface', 'sunny', 'umbrella', 'cloud', 'snowflake', 'snowman', 'zap', 'cyclone', 'foggy', 'ocean', 'cat', 'dog', 'mouse', 'hamster', 'rabbit', 'wolf', 'frog', 'tiger', 'koala', 'bear', 'pig', 'pig_nose', 'cow', 'boar', 'monkey_face', 'monkey', 'horse', 'racehorse', 'camel', 'sheep', 'elephant', 'panda_face', 'snake', 'bird', 'baby_chick', 'hatched_chick', 'hatching_chick', 'chicken', 'penguin', 'turtle', 'bug', 'honeybee', 'ant', 'beetle', 'snail', 'octopus', 'tropical_fish', 'fish', 'whale', 'whale2', 'dolphin', 'cow2', 'ram', 'rat', 'water_buffalo', 'tiger2', 'rabbit2', 'dragon', 'goat', 'rooster', 'dog2', 'pig2', 'mouse2', 'ox', 'dragon_face', 'blowfish', 'crocodile', 'dromedary_camel', 'leopard', 'cat2', 'poodle', 'paw_prints', 'bouquet', 'cherry_blossom', 'tulip', 'four_leaf_clover', 'rose', 'sunflower', 'hibiscus', 'maple_leaf', 'leaves', 'fallen_leaf', 'herb', 'mushroom', 'cactus', 'palm_tree', 'evergreen_tree', 'deciduous_tree', 'chestnut', 'seedling', 'blossom', 'ear_of_rice', 'shell', 'globe_with_meridians', 'sun_with_face', 'full_moon_with_face', 'new_moon_with_face', 'new_moon', 'waxing_crescent_moon', 'first_quarter_moon', 'waxing_gibbous_moon', 'full_moon', 'waning_gibbous_moon', 'last_quarter_moon', 'waning_crescent_moon', 'last_quarter_moon_with_face', 'first_quarter_moon_with_face', 'moon', 'earth_africa', 'earth_americas', 'earth_asia', 'volcano', 'milky_way', 'partly_sunny', 'octocat', 'squirrel'];
            var emojis3 = ['bamboo', 'gift_heart', 'dolls', 'school_satchel', 'mortar_board', 'flags', 'fireworks', 'sparkler', 'wind_chime', 'rice_scene', 'jack_o_lantern', 'ghost', 'santa', 'christmas_tree', 'gift', 'bell', 'no_bell', 'tanabata_tree', 'tada', 'confetti_ball', 'balloon', 'crystal_ball', 'cd', 'dvd', 'floppy_disk', 'camera', 'video_camera', 'movie_camera', 'computer', 'tv', 'iphone', 'phone', 'telephone_receiver', 'pager', 'fax', 'minidisc', 'vhs', 'sound', 'speaker', 'mute', 'loudspeaker', 'mega', 'hourglass', 'hourglass_flowing_sand', 'alarm_clock', 'watch', 'radio', 'satellite', 'loop', 'mag', 'mag_right', 'unlock', 'lock', 'lock_with_ink_pen', 'closed_lock_with_key', 'key', 'bulb', 'flashlight', 'high_brightness', 'low_brightness', 'electric_plug', 'battery', 'calling', 'email', 'mailbox', 'postbox', 'bath', 'bathtub', 'shower', 'toilet', 'wrench', 'nut_and_bolt', 'hammer', 'seat', 'moneybag', 'yen', 'dollar', 'pound', 'euro', 'credit_card', 'money_with_wings', 'e-mail', 'inbox_tray', 'outbox_tray', 'envelope', 'incoming_envelope', 'postal_horn', 'mailbox_closed', 'mailbox_with_mail', 'mailbox_with_no_mail', 'door', 'smoking', 'bomb', 'gun', 'hocho',  'football', 'basketball', 'soccer', 'baseball', 'tennis', '8ball', 'rugby_football', 'bowling', 'golf', 'mountain_bicyclist', 'bicyclist', 'horse_racing', 'snowboarder', 'swimmer', 'surfer', 'ski', 'spades', 'hearts', 'clubs', 'diamonds', 'gem', 'ring', 'trophy', 'musical_score', 'musical_keyboard', 'violin', 'space_invader', 'video_game', 'black_joker', 'flower_playing_cards', 'game_die', 'dart', 'mahjong', 'clapper', 'pencil', 'book', 'art', 'microphone', 'headphones', 'trumpet', 'saxophone', 'guitar', 'shoe', 'sandal', 'high_heel', 'lipstick', 'boot', 'shirt', 'necktie', 'womans_clothes', 'dress', 'running_shirt_with_sash', 'jeans', 'kimono', 'bikini', 'ribbon', 'tophat', 'crown', 'womans_hat', 'mans_shoe', 'closed_umbrella', 'briefcase', 'handbag', 'pouch', 'purse', 'eyeglasses', 'fishing_pole_and_fish', 'coffee', 'tea', 'sake', 'baby_bottle', 'beer', 'beers', 'cocktail', 'tropical_drink', 'wine_glass', 'fork_and_knife', 'pizza', 'hamburger', 'fries', 'poultry_leg', 'meat_on_bone', 'spaghetti', 'curry', 'fried_shrimp', 'bento', 'sushi', 'fish_cake', 'rice_ball', 'rice_cracker', 'rice', 'ramen', 'stew', 'oden', 'dango', 'egg', 'bread', 'doughnut', 'custard', 'icecream', 'ice_cream', 'shaved_ice', 'birthday', 'cake', 'cookie', 'chocolate_bar', 'candy', 'lollipop', 'honey_pot', 'apple', 'green_apple', 'tangerine', 'lemon', 'cherries', 'grapes', 'watermelon', 'strawberry', 'peach', 'melon', 'banana', 'pear', 'pineapple', 'sweet_potato', 'eggplant', 'tomato', 'corn'];
            var emojis4 = ['house', 'house_with_garden', 'school', 'office', 'post_office', 'hospital', 'bank', 'convenience_store', 'love_hotel', 'hotel', 'wedding', 'church', 'department_store', 'european_post_office', 'city_sunrise', 'city_sunset', 'japanese_castle', 'european_castle', 'tent', 'factory', 'tokyo_tower', 'japan', 'mount_fuji', 'sunrise_over_mountains', 'sunrise', 'stars', 'statue_of_liberty', 'bridge_at_night', 'carousel_horse', 'rainbow', 'ferris_wheel', 'fountain', 'roller_coaster', 'ship', 'speedboat', 'boat', 'rowboat', 'anchor', 'rocket', 'airplane', 'helicopter', 'steam_locomotive', 'tram', 'mountain_railway', 'bike', 'aerial_tramway', 'suspension_railway', 'mountain_cableway', 'tractor', 'blue_car', 'oncoming_automobile', 'car', 'taxi', 'oncoming_taxi', 'articulated_lorry', 'bus', 'oncoming_bus', 'rotating_light', 'police_car', 'oncoming_police_car', 'fire_engine', 'ambulance', 'minibus', 'truck', 'train', 'station', 'train2', 'bullettrain_front', 'bullettrain_side', 'light_rail', 'monorail', 'railway_car', 'trolleybus', 'ticket', 'fuelpump', 'vertical_traffic_light', 'traffic_light', 'warning', 'construction', 'beginner', 'atm', 'slot_machine', 'busstop', 'barber', 'hotsprings', 'checkered_flag', 'crossed_flags', 'izakaya_lantern', 'moyai', 'circus_tent', 'performing_arts', 'round_pushpin', 'triangular_flag_on_post'];
            var emojis5 = ['pill', 'syringe', 'page_facing_up', 'page_with_curl', 'bookmark_tabs', 'bar_chart', 'chart_with_upwards_trend', 'chart_with_downwards_trend', 'scroll', 'clipboard', 'calendar', 'date', 'card_index', 'file_folder', 'open_file_folder', 'scissors', 'pushpin', 'paperclip', 'black_nib', 'pencil2', 'straight_ruler', 'triangular_ruler', 'closed_book', 'green_book', 'blue_book', 'orange_book', 'notebook', 'notebook_with_decorative_cover', 'ledger', 'books', 'bookmark', 'name_badge', 'microscope', 'telescope', 'newspaper'];


            var chunk = function (val, chunkSize) {
                var R = [];
                for (var i = 0; i < val.length; i += chunkSize)
                    R.push(val.slice(i, i + chunkSize));
                return R;
            };

            /*IE polyfill*/
            if (!Array.prototype.filter) {
                Array.prototype.filter = function (fun /*, thisp*/) {
                    var len = this.length >>> 0;
                    if (typeof fun != "function")
                        throw new TypeError();

                    var res = [];
                    var thisp = arguments[1];
                    for (var i = 0; i < len; i++) {
                        if (i in this) {
                            var val = this[i];
                            if (fun.call(thisp, val, i, this))
                                res.push(val);
                        }
                    }
                    return res;
                };
            }

            var addListener = function () {
                
                $('body').on('click', '#emoji-filter', function (e) {
                    e.stopPropagation();
                    $('#emoji-filter').focus();
                });
                $('body').on('keyup', '#emoji-filter', function (e) {
                    var filteredList = filterEmoji($('#emoji-filter').val());
                    $("#emoji-dropdown .emoji-list").html(filteredList);
                });
                $(document).on('click', '.closeEmoji', function(){
                    //self.$panel.hide();
                });
                $('.selectEmoji'+mycount).click(function(){
                    //console.log(event);  event.stopPropagation();
                    var img = new Image();
                    img.src = AssetBaseUrl+'img/pngs/'+$(this).attr('data-value')+'.png';
                    img.alt = $(this).attr('data-value');
                    //img.width = '16px';
                    img.className = 'emoji-icon-inline';
                    context.invoke('editor.insertNode', img); //console.log('emoji');return; 
                    //$('.note-editable').find('span').remove();
                });
                var m_cnt = mycount+1;
                /*$(document).on('click', '.selectEmoji'+mycount, function(event){
                    //console.log(event);  event.stopPropagation();
                    var img = new Image();
                    img.src = AssetBaseUrl+'img/pngs/'+$(this).attr('data-value')+'.png';
                    img.alt = $(this).attr('data-value');
                    img.width = '16px';
                    img.className = 'emoji-icon-inline';
                    context.invoke('editor.insertNode', img); //console.log('emoji');return; 
                    //$('.note-editable').find('span').remove();
                });*/
            };

            var render = function (emojis) {
                var emoList = '';
                /*limit list to 24 images*/
                var emojis = emojis;
                var chunks = chunk(emojis, 6);
                for (j = 0; j < chunks.length; j++) {
                    emoList += '<div class="row gutter-5">';
                    for (var i = 0; i < chunks[j].length; i++) {
                        var emo = chunks[j][i]; 
                        emoList += '<div class="col-xs-2">' +
                        '<a href="javascript:void(0)" class="selectEmoji selectEmoji'+mycount+' closeEmoji" data-value="' + emo + '"><span class="emoji-icon" style="background-image: url(\'' + document.emojiSource + emo + '.png\');">'; 
                 if(j< 48){
                            emoList += '<img class="hide" width="64px" height="64px" src="'+ document.emojiSource + emo + '.png" />';
                        } 
                emoList += '</span></a>' +
                        '</div>';
                    }
                    emoList += '</div>';
                }

                return emoList;
            };

            var filterEmoji = function (value) {
                var filtered = emojis.filter(function (el) {
                    return el.indexOf(value) > -1;
                });
                return render(filtered);
            };

            // add emoji button
            context.memo('button.emoji', function () {
                // create button
                var button = ui.button({
                    contents: '<i class="ficon-smiley-face ficon-smiley-face'+mycount+'" item="'+mycount+'"></i><div class="emoji-panel emoji-panel'+mycount+'"></div>' ,
                    tooltip: 'Emoji',
                    click: function () {
                        if(document.emojiSource === undefined)
                            document.emojiSource = ''; 
                         self.$panel.show(); 
                      $("#postNewsFeedTypeModal .modal-content .emoji-dialog1.dropdown-menu").show();  
                      $(this).next('div.emoji-dialog').show()
                    }
                });

                // create jQuery object from button instance.
                var $emoji = button.render();
                return $emoji;
            });

            // This events will be attached when editor is initialized.
            this.events = {
                // This will be called after modules are initialized.
                'summernote.init': function (we, e) {
                   //if(!(we.currentTarget.id=="cmt-{{data.ActivityGUID}}"))
                    addListener();
                },
                // This will be called when user releases a key on editable.
                'summernote.keyup': function (we, e) {
                }
            };

            // This method will be called when editor is initialized by $('..').summernote();
            // You can create elements for plugin
            this.initialize = function () {
                mycount++;
                this.$panel = $('<div class="dropdown-menu dropdown-keep-open emoji-dialog emoji-dialog'+mycount+' animated fadeInUp" id="emoji-dropdown" data-type="stopPropagation">' +
                '<div class="nav-emoji">'+                        
                    '<div class="row m-0-l m-0-r hide">' +
                        '<div class="col-md-12">' +
                            '<h2 class="m-0-t">Emoticons <i class="fa fa-times pull-right cursor-pointer closeEmoji"></i></h2>' +
                            '<input type="text" class="form-control" placeholder="Zoek naar jouw emotie!" id="emoji-filter"/>' +
                            '<br/>' +
                        '</div>' +
                    '</div>' +
                    '<ul class="nav nav-tabs nav-tabs-emoji" role="tablist" id="myTabs">' +
                        '<li role="presentation" class="active">' +
                            '<a data-target="#smiley" role="tab" data-toggle="tab">' +                            
                                '<span class="icon"><i class="ficon-smiley-face"></i></span>' +
                            '</a>' +
                        '</li>' +
                        '<li role="presentation">'+
                            '<a data-target="#flower" role="tab" data-toggle="tab">' +
                                '<span class="icon"><i class="ficon-flower"></i></span>' +
                            '</a>' +
                        '</li>'+
                        '<li role="presentation">'+
                            '<a data-target="#bell" role="tab" data-toggle="tab">' +
                                '<span class="icon"><i class="ficon-bell"></i></span>' +
                            '</a>' +
                        '</li>'+
                        '<li role="presentation">'+
                            '<a data-target="#car" role="tab" data-toggle="tab">' +
                                '<span class="icon"><i class="ficon-car"></i></span>' +
                            '</a>' +
                        '</li>'+
                        '<li role="presentation">'+
                            '<a data-target="#hash" role="tab" data-toggle="tab">' +
                                '<span class="icon"><i class="ficon-hash"></i></span>' +
                            '</a>' +
                        '</li>'+
                    '</ul>' +
                    '<div class="tab-content">' +

                        '<div role="tabpanel" class="tab-pane active" id="smiley" aria-labelledby="smiley">' +    
                            '<div class="mCustomScrollbar emoji-scroll">' +
                                '<div class="emoji-list">' +
                                    render(emojis) +

                                '</div>' +
                            '</div>' +
                        '</div>' +

                        '<div role="tabpanel" class="tab-pane" id="flower" aria-labelledby="flower">' +    
                            '<div class="mCustomScrollbar emoji-scroll">' +
                                '<div class="emoji-list">' +
                                    render(emojis2) +

                                '</div>' +
                            '</div>' +
                        '</div>' +

                        '<div role="tabpanel" class="tab-pane" id="bell" aria-labelledby="bell">' +    
                            '<div class="mCustomScrollbar emoji-scroll">' +
                                '<div class="emoji-list">' +
                                    render(emojis3) +

                                '</div>' +
                            '</div>' +
                        '</div>' +

                        '<div role="tabpanel" class="tab-pane" id="car" aria-labelledby="car">' +    
                            '<div class="mCustomScrollbar emoji-scroll">' +
                                '<div class="emoji-list">' +
                                    render(emojis4) +

                                '</div>' +
                            '</div>' +
                        '</div>' +

                        '<div role="tabpanel" class="tab-pane" id="hash" aria-labelledby="hash">' +    
                            '<div class="mCustomScrollbar emoji-scroll">' +
                                '<div class="emoji-list">' +
                                    render(emojis5) +

                                '</div>' +
                            '</div>' +
                        '</div>' +


                    '</div>' +
                '</div>' +

                
                '</div>').hide();
                
                if(mycount==1 && $('#postNewsFeedTypeModal').length>0){
                    if($('#module_id').val()=='1' || $('#module_id').val()=='14' || $('#module_id').val()=='18' || $('#module_id').val()=='34')
                    {
                        this.$panel.appendTo('.note-misc');
                    }
                    else
                    {
                        $('#postNewsFeedTypeModal .modal-content').remove('.emoji-dialog');
                        this.$panel.appendTo('#postNewsFeedTypeModal .modal-content');
                    }
                }else{
                    this.$panel.appendTo('.note-misc');
                } 
            };
            if(this.$panel)
            {
                this.destroy = function () {
                    this.$panel.remove();
                    this.$panel = null;
                };
            }
        }
    });
    $(document).on('click', function(event){ 
        //$('.emoji-dialog').css({ 'display' : 'none'});
        var $trigger = $(".note-misc");
        if($trigger !== event.target && !$trigger.has(event.target).length){ 
            $(".emoji-dialog.dropdown-menu").css({ 'display' : 'none'});
        } 
    });
    $( window ).scroll(function(event) {
        var $trigger = $(".note-misc");
        if($trigger !== event.target && !$trigger.has(event.target).length){ 
               // $(".emoji-dialog.dropdown-menu").css({ 'display' : 'none'});
        } 
    });
    $(document).on('click','.selectEmoji', function(){ 
        $('.emoji-dialog').hide();
    }); 

    $(document).on('click','a[data-toggle="tab"]', function(event){  

             if($(this).attr('data-target') == '#smiley'){
                $('.tab-pane').removeClass('active');
                $('.tab-content').find('#smiley').addClass('active');
             } 
             else if($(this).attr('data-target') == '#flower'){
                $('.tab-pane').removeClass('active');
                $('.tab-content').find('#flower').addClass('active');
            }
             else if($(this).attr('data-target') == '#bell'){
                 $('.tab-pane').removeClass('active');
                $('.tab-content').find('#bell').addClass('active');
             }
             else if($(this).attr('data-target') == '#car'){
                 $('.tab-pane').removeClass('active');
                $('.tab-content').find('#car').addClass('active');
             }
             else if($(this).attr('data-target') == '#hash'){
                 $('.tab-pane').removeClass('active');
                $('.tab-content').find('#hash').addClass('active');
             }
            /*if($(this).parent('li').hasClass('active')){
                $('.tab-pane').addClass('active');
            }*/
    }); 

    

}));


