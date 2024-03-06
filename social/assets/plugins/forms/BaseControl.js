//----------------------------------------------------------------------------------------------------------------------------
/**
@version BaseControl v1.0.8 
Copyright (c) 2014 Vinfotech Ltd. All rights reserved.
 
Release details 
Ownership   : Vinfotech License 
Version     : v1.0.8
Change      :
                • Bug fixes and Update according with best practice. 
                • Added the ability to validate the Currency Format and Password policy according to HIPPA compliance and HL7.
Date        : 13.10.2014
*/
//----------------------------------------------------------------------------------------------------------------------------
$(document).ready(function() {
    //$('[data-controltype]').loadControl();
});


$.fn.loadControl = function() {
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /* To CHECK THE STRING LENGTH IN TEXT AREA */
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    $(this).each(function() {

        if (($(this).is('textarea') || $(this).is('input')) && $(this).attr('maxcount') != undefined) {
            var strHTMLspan = '<span  style="cursor: pointer; color: Red; position: inherit;"  id=\"spn2' + this.id + '\"></span>'
            var strLabel = '</br><span id=\"noOfChar' + this.id + '\"></span>  '
            $(this).parent().append(strHTMLspan + strLabel);
            jQuery("#noOfChar" + this.id).append($(this).attr('maxcount'));
            
            $(this).keydown(function() {                
                setTimeout(autosize, 0, this);
            });
            
            $(this).on('input',function() {
                var input_id = $(this).attr('id');
                var len = $("#"+input_id).val().length;
                var maxChar = $(this).attr('maxcount');
                if (len > maxChar) {
                    $("#"+input_id).val($("#"+input_id).val().substring(0, maxChar));
                    return false;
                }                
            });
        }
    });
    
    $('#PagenameCtrlID').on('keypress blur', function(){
        var myStr = $("#PagenameCtrlID").val();
        var result = myStr.replace(/ /g, "");        
        $('#PagenameCtrlID').val(result); 
    });
    
    //----------------------------------------------------------------------------------------------------------------------------
    /* To CHECK THE INPUT FIELDS VALUES THOSE CONTENTS CONTROL TYPE ATTRIBUTE */
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    $(this).each(function() {

        if ($(this).is('input') && $(this).attr('data-controltype') != undefined) {
            var ControlType = $(this).attr('data-controltype').trim();
            var strHTMLspan = '<br/><span id=\"spnError' + this.id + '\"></span>'
            if ($(this).attr('data-msglocation') == undefined && $(this).attr('data-mandatory') == "true") {
                $(this).parent().append(strHTMLspan);
            }

            switch (ControlType.toLowerCase()) {
                case ("username"):
                    break;
                case ("phonenumber"):
                    break;
                case ("youtubeurl"):
                    break;
                case ("password"):
                    var spnHTMLstrendth = '<span id=\"strenth' + this.id + '\"></span>'
                        // Modified by Dharmendra
                    if ($(this).attr('data-strengthmsglocation') == undefined) {
                        $(this).parent().append(spnHTMLstrendth);
                    } else {
                        var strmszLoc = $(this).attr('data-strengthmsglocation')
                        $('#' + strmszLoc).append(spnHTMLstrendth);
                    }
                    // End
                    break;
                default:
            }

            $(this).keydown(function() {
                switch (ControlType.toLowerCase()) {
                    case ("phonenumber"):
                        break;
                    case ("password"):
                        passwordStrength(this);
                        break;
                    default:
                        break;
                }
            });

            //---------------------------------------------------------------------------------------------------------------------------------------*/
            /* To CHECK THE INPUT FIELDS VALUES VALIDATION ON BLUR.IT WILL SHOW MESSAGE ONLY FOR THOSE FIELDS WHICH CONTENTS CONTROL TYPE ATTRIBUTE  */
            /*---------------------------------------------------------------------------------------------------------------------------------------*/
            $(this).blur(function() {
                    CheckValidForControl(this);
            });
            $(this).focus(function() {
                    CheckValidForControl(this);
            });
            
            $(this).on('keyup',function(e) {
                if ($('#spnError' + $(this).attr('id')).html() != '') {
                    $('#spnError' + $(this).attr('id')).html('');

                    var mszLoca = $(this).attr('data-msglocation')
                    var errorClassName = $(this).parents('[data-error]').attr('data-error');
                    $('#' + mszLoca).html('');
                    $(this).parents('[data-error]').removeClass(errorClassName)
                }
                 //if(e.keyCode!=9 && e.keyCode!=16)
                CheckValidForControl(this);
            });
        };
        if ($(this).is('select') && $(this).attr('data-controltype') != undefined) {
            $(this).on('change',function() {
                CheckValidForControl(this);
            });
        }
    });
}


//----------------------------------------------------------------------------------------------------------------------------
// THIS FUNCTION USED TO CHECK THE MANDATORY VALIDATION  AND VALID VALUE VALIDATION .
//----------------------------------------------------------------------------------------------------------------------------
function CheckValidForControl(Control) {

    if(!$(Control).closest('form').hasClass('formSubmitedOnce')){
        return;
    }
    $('#spnError' + $(Control).id).html('');
    var ControlType = $(Control).attr('data-controltype').trim()
    var ControlErrorMessage = '';

    if ($(Control).attr('message') != undefined)
        ControlErrorMessage = $(Control).attr('message').trim();

    var spnError = '#spnError' + Control.id;

    if ($(Control).attr('data-msglocation') != undefined)
        spnError = '#' + $(Control).attr('data-msglocation').trim();

    $(spnError).html('');

    switch (ControlType.toLowerCase()) {
        case ("youtubeurl"):
            if (ValidYouTubeUrl(Control) == false) {
                var id = Control.id
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass)
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Please enter a valid YouTube URL.");
            }
            break;
        case ("alphanum"):
            var regex = /^[a-zA-Z 0-9_-]+$/;
            var controlVal = $(Control).val();
            if(!regex.test(controlVal)){
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $(Control).parents('[data-error]').addClass(errClass)
                $(Control).next('label').html((ControlErrorMessage != '') ? ControlErrorMessage : "Please enter valid name.");
            } else {
                $(Control).next('label').html('');
            }
            if(controlVal.length == 0){
                $(Control).next('label').html('');
            }
            break;
        case ("customval"):
            var regex = /^[A-Za-z 0-9_&.,-]*$/;
            var controlVal = $(Control).val();
            if(!regex.test(controlVal)){
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $(Control).parents('[data-error]').addClass(errClass)
                $(Control).next('label').html((ControlErrorMessage != '') ? ControlErrorMessage : "Please enter valid name.");
            } else {
                $(Control).next('label').html('');
            }
            if(controlVal.length == 0){
                $(Control).next('label').html('');
            }
            break;
        case ("validurl"):
            if (ValidUrl(Control) == false) {
                var id = Control.id
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass);
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Please enter valid url");
            }
            break;

        case ("facebookurl"):
            if (ValidFacebookUrl(Control) == false) {
                var id = Control.id
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass);
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Please enter a valid Facebook URL.");
            }
            break;

        case ("twitterurl"):
            if (ValidTwitterUrl(Control) == false) {
                var id = Control.id
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass);
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Please enter a valid Twitter URL");
            }
            break;

        case ("namefield"):
            if (ValidNameField(Control) == false) {
                var id = Control.id
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass)
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Please enter valid name");
            }
            break;

        case ("username"):
            if (ValidUsername(Control) == false) {
                var id = Control.id
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass)
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Please enter valid username");
            }
            break;
            
        case ("pagename"):
            if (ValidPagename(Control) == false) {
                var id = Control.id
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass)
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Please enter valid URL");
            }
            break;

        case ("usaphonenumber"):
            if (isValidUSPhoneFormat(Control) == false) {
                var id = Control.id
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass)
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Please enter valid USA phone number");
            }
            break;

        case ("phonenumber"):
            if (isValidUSPhoneFormat(Control) == false) {
                var id = Control.id
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass)
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Please enter valid phone number");
            }
            break;

        case ("password"):
            if ($(Control).length == 0) {
                $('#strenth' + id).html('');
            }
            break;

        case ("amount"):
            if ($(Control).val() != 0) {
                var id = Control.id
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                if (isValidAmount(Control) == false) {
                    $('#' + id).parents('[data-error]').addClass(errClass)
                    $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Please enter a valid amount upto 2 decimal places.");
                }
            } else {
                $('#' + id).parents('[data-error]').addClass(errClass)
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Please enter a valid amount.");
            }
            break;

        case ("email"):
            if (isValidEmail(Control) == false) {
                var id = Control.id
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass)
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Enter valid email");
            }
            break;

        case ("creditcard"):
            if (isValidCreditCard(Control) == false) {
                var id = Control.id
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass);
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Enter valid Credit Card");
            }
            break;

        case ("number"):
            if (isValidNumber(Control) == false) {
                var id = Control.id
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass)
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Enter valid Number");
            }
            break;

        case ("zipcode"):
            if (isValidZip(Control) == false) {
                var id = Control.id
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass)
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Enter valid Zip code");
            }
            break;
        case ("passwordpolicy"):
            if (isValidPasswordPolicy(Control) == false) {
                var id = Control.id
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass)
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Enter valid Password");
            }
            break;

        case ("currencyformat"):
            if (isValidCurrencyFormat(Control) == false) {
                var id = Control.id
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass)
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Enter valid format of currency");
            }
            break;
            
        case ("pagetitlefield"):
            if (ValidPageTitle(Control) == false){
                var id = Control.id
                $('#'+id).parents('[data-error]').addClass('hasError')
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Please enter valid page title");
            }
            break;
            

        case ("general"):
            break;

        default:
            break;
    }

    var id = Control.id
    if(ControlType.toLowerCase()=='alphanum' || ControlType.toLowerCase()=='customval'){
        var minAttr = $(Control).attr('data-req-minlen');
        var maxAttr = $(Control).attr('data-req-maxlen');
        if(Control.value.trim().length!==0){
            if (typeof minAttr !== typeof undefined && minAttr !== false) {
                if(Control.value.trim().length<minAttr){
                    var errClass = $(Control).parents('[data-error]').attr('data-error');
                    $(Control).parents('[data-error]').addClass(errClass)
                    $(Control).next('label').html((ControlErrorMessage != '') ? ControlErrorMessage : "Enter at least "+minAttr+" characters");
                }
            }
        }
        if (typeof maxAttr !== typeof undefined && maxAttr !== false) {
            if(Control.value.length>maxAttr){
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $(Control).parents('[data-error]').addClass(errClass)
                $(Control).next('label').html((ControlErrorMessage != '') ? ControlErrorMessage : "Enter maximum "+maxAttr+" characters");
            }
        }
    } else {
        var minAttr = $('#'+id).attr('data-req-minlen');
        var maxAttr = $('#'+id).attr('data-req-maxlen');
        var noSpace = $('#'+id).attr('data-req-nospace');
        
        if (typeof minAttr !== typeof undefined && minAttr !== false) {
            if(Control.value.trim().length<minAttr){
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass)
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Enter at least "+minAttr+" characters");
            }
        }
        if (typeof maxAttr !== typeof undefined && maxAttr !== false) {
            if(Control.value.length>maxAttr){
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass)
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Enter maximum "+maxAttr+" characters");
            }
        }
        if (typeof noSpace !== typeof undefined && noSpace !== false) {
            if(hasWhiteSpace(Control.value)){
                var errClass = $(Control).parents('[data-error]').attr('data-error');
                $('#' + id).parents('[data-error]').addClass(errClass)
                $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "No space allowed.");
            }
        }
    }

    if ($(Control).attr('data-msglocation') == undefined)
        $(spnError).attr('style', "COLOR: red");

    if ($(Control).attr('data-mandatory') != undefined) {
        if ($(Control).val().trim() == '' && $(Control).attr('data-mandatory').toLowerCase() == 'true') {
            ControlErrorMessage = '';
            if ($(Control).attr('data-requiredmessage') != undefined)
                ControlErrorMessage = $(Control).attr('data-requiredmessage').trim();
            var id = Control.id
            var errClass = $(Control).parents('[data-error]').attr('data-error');
            $('#' + id).parents('[data-error]').addClass(errClass)

            $(spnError).html((ControlErrorMessage != '') ? ControlErrorMessage : "Please enter " + ControlType);
        }
    }
    if(ControlType.toLowerCase()=='alphanum' || ControlType.toLowerCase()=='customval'){
        if ($(Control).next('label').html() == ''){
            $(Control).next('label').parent('div').removeClass('hasError');
            return true;
        } else {
            return false;
        }
    } else {
        if ($(spnError).html() == ''){
            $(spnError).parent('div').removeClass($(spnError).parent('div').attr('data-error'));
            return true
        } else {
            return false;
        }
    }
}

function hasWhiteSpace(s) {
  return s.indexOf(' ') >= 0;
}

function checkstatus(groupname) {

        var validcontrol = true;
        var flag = true;
        if (groupname != undefined && groupname != ''){
            $('#'+groupname).addClass('formSubmitedOnce');
            groupname = '#' + groupname + ' ';
        }
        else{
            groupname = ''
        }

        $(groupname + ' input,'+groupname+' textarea,'+groupname+' select').each(function() {
            if ($(this).attr('data-controltype') != undefined) {
                flag = CheckValidForControl(this)
                if (validcontrol == true){
                    validcontrol = flag;                    
                }
            }

        });
        if($('.hasError').length>0){
            $('html,body').animate({
                scrollTop : parseInt($('.hasError:eq(0)').offset().top)-85
            });
            $('.hasError:eq(0)').children('input,textarea').focus();
        }
        return validcontrol;

    }
    //----------------------------------------------------------------------------------------------------------------------------------*/
    //THIS FUNCTION USED TO IMPLEMENT TEXTAREA AUTOSIZE FEATURE.
    //----------------------------------------------------------------------------------------------------------------------------------*/
function autosize(el) {
        el.style.cssText = 'height:10; padding:10';
        el.style.cssText = 'height:' + el.scrollHeight + 'px';

        /* TO CHECK CHARACTER LENGTH IN THE TEXT AREA*/
        var qText = document.querySelector('textarea');
        var maxcount = $('#' + el.id).attr('maxcount');
        var qtext = $('#' + el.id).val();
        qtext = qtext.replace(/(\r\n|\r|\n)/g,'  ');
        if (qtext.length < maxcount) {
            jQuery("#noOfChar" + el.id).html(parseInt(maxcount) - parseInt(qtext.length));
        } else {
            if(qtext.length > maxcount){
                $('#' + el.id).val($('#' + el.id).val().substring(0,$('#' + el.id).val().length-1));
            }
            jQuery("#noOfChar" + el.id).html(0);
            //jQuery("#" + el.id).val(qtext.substring(0, maxcount));
        }
    }
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /* To ENCRYPT AND DECRIPT THE SPECIAL CHARACTERS AND TAGS IN TEXTAREA CONTROL --- commented on 18/04/2014
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
function GetValuesForTextarea(sender) {
    var id = $(sender).attr('id');
    if ($('#' + id).attr('maxcount') != undefined) {
        var str = $('#' + id).val();
        str = str.replace(/>/gi, "&gt;").replace(/</gi, "&lt;")
        str = ConvertUrlToAnchor(str)
        str = str.replace(/\n/gi, "<br />").replace(/\n\r/gi, "<br />")
        $('#xyz').html(str);
        //alert(str);
    }
}

/*-----------------------------------------------------------------------------------------------------------------------------------*/
// SUPPORT FUNCTION USED IN THE  ConvertUrlToAnchor() FUNCTION 
//----------------------------------------------------------------------------------------------------------------------------------*/
splitString = function(string, splitters) {
    var list = [string];
    for (var i = 0, len = splitters.length; i < len; i++) {
        traverseList(list, splitters[i], 0);
    }
    return flatten(list);
}

traverseList = function(list, splitter, index) {
    if (list[index]) {
        if ((list.constructor !== String) && (list[index].constructor === String))
            (list[index] != list[index].split(splitter)) ? list[index] = list[index].split(splitter) : null;
        (list[index].constructor === Array) ? traverseList(list[index], splitter, 0): null;
        (list.constructor === Array) ? traverseList(list, splitter, index + 1): null;
    }
}

flatten = function(arr) {
        return arr.reduce(function(acc, val) {
            return acc.concat(val.constructor === Array ? flatten(val) : val);
        }, []);
    }
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    // FUNCTION USED TO CONVERT URL INTO ANCHOR TAG AND ANCHOR TAG INTO TEXT
    //----------------------------------------------------------------------------------------------------------------------------------*/
function ConvertUrlToAnchor(s) {
    var description = s;
    var whitespace = " ";
    var anchortext = "";
    var words;
    var splitList = [whitespace, '\n', '\r\n'];
    words = splitString(description, splitList);

    var regexp = /^((https?|ftp):\/\/|(www|ftp)\.)[a-z0-9-]+(\.[a-z0-9-]+)+([\/?].*)?$/
    var i = 0;
    for (i = 0; i <= words.length; i++) {
        if (words[i] != undefined) {
            var urls = "";
            var testregexp = /^((ftp|http|https):\/\/|(www|ftp)\.)[a-z0-9-]+(\.[a-z0-9-]+)+([\/?].*)?(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!-\/]))?$/
            if (testregexp.test(words[i].toString()) == true) {
                urls = '<a target="_blank" href= \"' + words[i].toString() + '\">' + words[i].toString() + '</a>'

                if (i == words.length - 1) {
                    description = description.substring(0, description.lastIndexOf(words[i].toString()));
                    description = description + urls;
                } else {
                    description = description.replace(words[i].toString() + ' ', urls + ' ').replace(words[i].toString() + '\n', urls + '\n').replace(words[i].toString() + '\r\n', urls + '\r\n');
                }
            }
        }
    }
    return description;
}

function AnchorToText(s) {
        var description = s;
        var whitespace = " ";
        var i = 0;
        while (description.indexOf("<a") > 0) {
            var words = description.split("<a");
            var xyz = words[1].substring(0, words[1].indexOf(">"));
            description = description.replace(xyz, "");
            description = description.replace("<a>", "");
            description = description.replace("</a>", "");
        }
        return description;
    }
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /* CREATE PASSWORD STRENTH CHECK CONTROL
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
function passwordStrength(sender) {

        var desc = new Array();
        desc[0] = "Very Weak";
        desc[1] = "Weak";
        //desc[2] = "Better";
        desc[2] = "Medium";
        desc[3] = "Strong";
        desc[4] = "Strongest";

        var id = $(sender).attr('id');
        var password = $('#' + id).val().trim();
        var score = 0;

        //if password bigger than 6 give 1 point
        if (password.length > 6) score++;

        //if password has both lower and uppercase characters give 1 point
        if ((password.match(/[a-z]/)) && (password.match(/[A-Z]/))) score++;

        //if password has at least one number give 1 point
        if (password.match(/\d+/)) score++;

        //if password has at least one special caracther give 1 point
        if (password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/)) score++;

        //if password bigger than 12 give another 1 point
        if (password.length > 12) score++;

        jQuery('#strenth' + id)

        //$('#strenth' + id).html(desc[score]);
        $('#strenth' + id).className = "strength" + score;


        var str = desc[score];
        str = str.replace(/ /g, '').toLowerCase();

        $('#strenth' + id).removeClass().addClass(str); // Modified by Dharmendra

        if (password == '') {
            $('#strenth' + id).html('')
            $('#strenth' + id).html('');
        }
    }
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /*  VALID YOUTUBE URL REGULER EXPRESSION
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
function ValidYouTubeUrl(sender) {
        var id = $(sender).attr('id');
        var url = $('#' + id).val().toLowerCase();;
        var expValid = /^((((https?):\/\/)|(www)\.)|(((https?):\/\/)(www)\.)?)(youtube\.com|youtu\.be)\/|watch\?v\=\w+$/

        if (url.length > 0) {
            if (url.slice(-1) == '/')
                return false;
            else
                return (url.match(expValid)) ? RegExp.$1 : false;
        } else
            return true;
    }
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /*  VALID URL REGULER EXPRESSION
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
function ValidUrl(sender) {
        var id = $(sender).attr('id');
        var url = $('#' + id).val().toLowerCase();
        var regexp = /^((ftp|http|https):\/\/|(www|ftp)\.)[a-z0-9-]+(\.[a-z0-9-]+)+([\/?].*)?(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!-\/]))?$/

        if (url.length > 0)
            return regexp.test(url);
        else
            return true;
    }
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /*  VALID FACEBOOK URL REGULER EXPRESSION
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
function ValidFacebookUrl(sender) {
        var id = $(sender).attr('id');
        var url = $('#' + id).val().toLowerCase();
        var regexp = /^((((https?):\/\/)|(www)\.)|(((https?):\/\/)(www)\.)?)facebook.com\/(#!\/)?[a-z0-9_]+$/
        if (url.length > 0)
            return regexp.test(url);
        else
            return true;
    }
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /*  VALID TWITTER URL REGULER EXPRESSION
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
function ValidTwitterUrl(sender) {
        var id = $(sender).attr('id');
        var url = $('#' + id).val().toLowerCase();;
        var regexpp = /^((((https?):\/\/)|(www)\.)|(((https?):\/\/)(www)\.)?)twitter\.com\/(#!\/)?[a-z0-9_]+$/

        if (url.length > 0)
            return regexpp.test(url);
        else
            return true;
    }
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /*  VALID USERNAME REGULER EXPRESSION 
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
function ValidUsername(sender) {
        var id = $(sender).attr('id');
        var url = $('#' + id).val();
        var regexpp = /^[a-zA-Z]*[a-zA-Z0-9]*[a-zA-Z0-9_-]*$/
        if (url.length > 0)
            return regexpp.test(url);
        else
            return true;
    }
    
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /*  VALID PAGENAME REGULER EXPRESSION 
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
function ValidPagename(sender) {
        var id = $(sender).attr('id');
        var url = $('#' + id).val();
        var regexpp = /^[a-zA-Z]*[a-zA-Z0-9]*[a-zA-Z0-9_-]*$/
        if (url.length > 0)
            return regexpp.test(url);
        else
            return true;
    }
    
/*-----------------------------------------------------------------------------------------------------------------------------------*/
/*  VALID PAGETITLE REGULER EXPRESSION 
/*-----------------------------------------------------------------------------------------------------------------------------------*/
function ValidPageTitle(sender) {
    var id = $(sender).attr('id');
    var url = $('#' + id).val();
    var regexpp = /^[a-zA-Z0-9\s]+$/;

    if (url.length > 0)
        return regexpp.test(url);
    else
        return true;
}
    
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /* VALID NAME FIELD REGULER EXPRESSION
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
function ValidNameField(sender) {
        var id = $(sender).attr('id');
        var nameField = $('#' + id).val();
        var regexpp = /^[a-zA-Z][a-zA-ZéüöêåÁÅÉá .´'`-]*$/
        var Exp = /^[0-9]+$/;

        if (nameField.length > 0) {
            if (nameField.trim() == '')
                return false
            else {
                if (Exp.test(nameField.trim()))
                    return false
                else
                    return regexpp.test(nameField)
            }
        } else
            return true;
    }
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /* VALID PHONE NUMBER REGULER EXPRESSION
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
function isValidUSPhoneFormat(sender) {
        var id = $(sender).attr('id');
        var elementValue = $('#' + id).val();
        var phoneNumberPattern = /^[+]?([\d]{3}(-| )?[\d]{3}(-| )?[\d]{4}|[\d]{6,12}|}|[(][\d]{3}[)](-| )?[\d]{3}(-| )?[\d]{4})$/;

        if (elementValue.length > 0)
            return phoneNumberPattern.test(elementValue);
        else
            return true;
    }
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /* VALID AMOUNT REGULER EXPRESSION
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
function isValidAmount(sender) {
        var id = $(sender).attr('id');
        var elementValue = $('#' + id).val();
        var expamount = /^(?:\d*\.\d{1,2}|\d+)$/;

        if (elementValue.length > 0)
            return expamount.test(elementValue)
        else
            return true;
    }
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /* VALID EMAIL REGULER EXPRESSION
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
function isValidEmail(sender) {
        var id = $(sender).attr('id');
        var elementValue = $('#' + id).val();
        elementValue = $.trim(elementValue);
        var expEmail = /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/
        var ExpFirstChar = /^[A-Za-z0-9]*$/

        if (elementValue.length > 0) {
            if (ExpFirstChar.test(elementValue.charAt(0)) && ExpFirstChar.test(elementValue.slice(-2)))
                return expEmail.test(elementValue)
            else
                return false;
        } else
            return true;
    }
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /*  VALID CREDIT CARD REGULER EXPRESSION (ALLOWED VISA, AMERICAN EXPRESS,MASTER CARD)
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
function isValidCreditCard(sender) {
        var id = $(sender).attr('id');
        var elementValue = $('#' + id).val();
        var expCCard = /^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})$/

        if (elementValue.length > 0)
            return expCCard.test(elementValue)
        else
            return true;
    }
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /*  VALID NUMERIC VALUE WITHOUR DECIMAL REGULER EXPRESSION 
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
function isValidNumber(sender) {
        var id = $(sender).attr('id');
        var elementValue = $('#' + id).val();
        var regexp = /^[0-9]+$/;

        if (elementValue.length > 0)
            return regexp.test(elementValue)
        else
            return true;
    }
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /*  VALID ZIP CODE REGULER EXPRESSION 
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
function isValidZip(sender) {
        var id = $(sender).attr('id');
        var elementValue = $('#' + id).val();
        var regexp = /(^[a-zA-Z]{1,2}\d{1}[\dA-Za-z]{0,1}\s*\d{1}[A-Za-z]{2}$)|(^\d{5,6}$)|(^[A-Za-z]{1}\d{1}[A-Z]{1}\s*\d{1}[A-Z]{1}\d{1}$)/;

        if (elementValue.length > 0) {
            return regexp.test(elementValue)
        } else {
            return true;
        }
    }
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /*  PASSWORD POLICY
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
function isValidPasswordPolicy(sender) {
        var id = $(sender).attr('id');
        var elementValue = $('#' + id).val();
        var regexp = /(^((((?=.*\d)(?=.*[a-zA-Z])(?=.*[~!@#$%^&_;\?\.\*\+]))|((?=.*[a-z])(?=.*[A-Z])(?=.*[~!@#$%^&_;\?\.\*\+]))|((?=.*\d)(?=.*[A-Z])(?=.*[a-z])))[a-zA-Z0-9~!@#$%^&_;\?\.\*\+]{8,20})$)/;

        if (elementValue.length > 0)
            return regexp.test(elementValue)
        else
            return true;
    }
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
    /*  Currency Format
    /*-----------------------------------------------------------------------------------------------------------------------------------*/
function isValidCurrencyFormat(sender) {
        var id = $(sender).attr('id');
        var elementValue = $('#' + id).val();
        var regexp = /(^([1-9]{1}[\d]{0,2}(\,[\d]{3})*(\.[\d]{0,2})?|[1-9]{1}[\d]{0,2}(\.[\d]{0,2})?|0(\.[\d]{0,2})?|(\.[\d]{1,2})?)$)/;

        if (elementValue.length > 0)
            return regexp.test(elementValue)
        else
            return true;
    }
    /*-----------------------------------------------------------------------------------------------------------------------------------*/