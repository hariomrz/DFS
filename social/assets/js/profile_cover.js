var windowWidth = 1920;    
//Function for change cover image and set imagee_src and offset of uploded image
$(function(){
    $('#coverViewimg > img').hide();
    $("#coverViewimg > img").load(function () { 
        $('#coverViewimg > img').show();
        setTimeout(function(){  
            $('.imgFill').imagefill();
        },50);
    });

    $(window).on("orientationchange",function(){
        setTimeout(function(){  
            $('.imgFill').imagefill();
        },50);
    });

!function(a){"use strict";var b=function(a,c,d){var e,f,g=document.createElement("img");if(g.onerror=c,g.onload=function(){!f||d&&d.noRevoke||b.revokeObjectURL(f),c&&c(b.scale(g,d))},b.isInstanceOf("Blob",a)||b.isInstanceOf("File",a))e=f=b.createObjectURL(a),g._type=a.type;else{if("string"!=typeof a)return!1;e=a,d&&d.crossOrigin&&(g.crossOrigin=d.crossOrigin)}return e?(g.src=e,g):b.readFile(a,function(a){var b=a.target;b&&b.result?g.src=b.result:c&&c(a)})},c=window.createObjectURL&&window||window.URL&&URL.revokeObjectURL&&URL||window.webkitURL&&webkitURL;b.isInstanceOf=function(a,b){return Object.prototype.toString.call(b)==="[object "+a+"]"},b.transformCoordinates=function(){},b.getTransformedOptions=function(a,b){var c,d,e,f,g=b.aspectRatio;if(!g)return b;c={};for(d in b)b.hasOwnProperty(d)&&(c[d]=b[d]);return c.crop=!0,e=a.naturalWidth||a.width,f=a.naturalHeight||a.height,e/f>g?(c.maxWidth=f*g,c.maxHeight=f):(c.maxWidth=e,c.maxHeight=e/g),c},b.renderImageToCanvas=function(a,b,c,d,e,f,g,h,i,j){return a.getContext("2d").drawImage(b,c,d,e,f,g,h,i,j),a},b.hasCanvasOption=function(a){return a.canvas||a.crop||a.aspectRatio},b.scale=function(a,c){c=c||{};var d,e,f,g,h,i,j,k,l,m=document.createElement("canvas"),n=a.getContext||b.hasCanvasOption(c)&&m.getContext,o=a.naturalWidth||a.width,p=a.naturalHeight||a.height,q=o,r=p,s=function(){var a=Math.max((f||q)/q,(g||r)/r);a>1&&(q*=a,r*=a)},t=function(){var a=Math.min((d||q)/q,(e||r)/r);1>a&&(q*=a,r*=a)};return n&&(c=b.getTransformedOptions(a,c),j=c.left||0,k=c.top||0,c.sourceWidth?(h=c.sourceWidth,void 0!==c.right&&void 0===c.left&&(j=o-h-c.right)):h=o-j-(c.right||0),c.sourceHeight?(i=c.sourceHeight,void 0!==c.bottom&&void 0===c.top&&(k=p-i-c.bottom)):i=p-k-(c.bottom||0),q=h,r=i),d=c.maxWidth,e=c.maxHeight,f=c.minWidth,g=c.minHeight,n&&d&&e&&c.crop?(q=d,r=e,l=h/i-d/e,0>l?(i=e*h/d,void 0===c.top&&void 0===c.bottom&&(k=(p-i)/2)):l>0&&(h=d*i/e,void 0===c.left&&void 0===c.right&&(j=(o-h)/2))):((c.contain||c.cover)&&(f=d=d||f,g=e=e||g),c.cover?(t(),s()):(s(),t())),n?(m.width=q,m.height=r,b.transformCoordinates(m,c),b.renderImageToCanvas(m,a,j,k,h,i,0,0,q,r)):(a.width=q,a.height=r,a)},b.createObjectURL=function(a){return c?c.createObjectURL(a):!1},b.revokeObjectURL=function(a){return c?c.revokeObjectURL(a):!1},b.readFile=function(a,b,c){if(window.FileReader){var d=new FileReader;if(d.onload=d.onerror=b,c=c||"readAsDataURL",d[c])return d[c](a),d}return!1},"function"==typeof define&&define.amd?define(function(){return b}):a.loadImage=b}(window),function(a){"use strict";"function"==typeof define&&define.amd?define(["load-image"],a):a(window.loadImage)}(function(a){"use strict";if(window.navigator&&window.navigator.platform&&/iP(hone|od|ad)/.test(window.navigator.platform)){var b=a.renderImageToCanvas;a.detectSubsampling=function(a){var b,c;return a.width*a.height>1048576?(b=document.createElement("canvas"),b.width=b.height=1,c=b.getContext("2d"),c.drawImage(a,-a.width+1,0),0===c.getImageData(0,0,1,1).data[3]):!1},a.detectVerticalSquash=function(a,b){var c,d,e,f,g,h=a.naturalHeight||a.height,i=document.createElement("canvas"),j=i.getContext("2d");for(b&&(h/=2),i.width=1,i.height=h,j.drawImage(a,0,0),c=j.getImageData(0,0,1,h).data,d=0,e=h,f=h;f>d;)g=c[4*(f-1)+3],0===g?e=f:d=f,f=e+d>>1;return f/h||1},a.renderImageToCanvas=function(c,d,e,f,g,h,i,j,k,l){if("image/jpeg"===d._type){var m,n,o,p,q=c.getContext("2d"),r=document.createElement("canvas"),s=1024,t=r.getContext("2d");if(r.width=s,r.height=s,q.save(),m=a.detectSubsampling(d),m&&(e/=2,f/=2,g/=2,h/=2),n=a.detectVerticalSquash(d,m),m||1!==n){for(f*=n,k=Math.ceil(s*k/g),l=Math.ceil(s*l/h/n),j=0,p=0;h>p;){for(i=0,o=0;g>o;)t.clearRect(0,0,s,s),t.drawImage(d,e,f,g,h,-o,-p,g,h),q.drawImage(r,0,0,s,s,i,j,k,l),o+=s,i+=k;p+=s,j+=l}return q.restore(),c}}return b(c,d,e,f,g,h,i,j,k,l)}}}),function(a){"use strict";"function"==typeof define&&define.amd?define(["load-image"],a):a(window.loadImage)}(function(a){"use strict";var b=a.hasCanvasOption,c=a.transformCoordinates,d=a.getTransformedOptions;a.hasCanvasOption=function(c){return b.call(a,c)||c.orientation},a.transformCoordinates=function(b,d){c.call(a,b,d);var e=b.getContext("2d"),f=b.width,g=b.height,h=d.orientation;if(h&&!(h>8))switch(h>4&&(b.width=g,b.height=f),h){case 2:e.translate(f,0),e.scale(-1,1);break;case 3:e.translate(f,g),e.rotate(Math.PI);break;case 4:e.translate(0,g),e.scale(1,-1);break;case 5:e.rotate(.5*Math.PI),e.scale(1,-1);break;case 6:e.rotate(.5*Math.PI),e.translate(0,-g);break;case 7:e.rotate(.5*Math.PI),e.translate(f,-g),e.scale(-1,1);break;case 8:e.rotate(-.5*Math.PI),e.translate(-f,0)}},a.getTransformedOptions=function(b,c){var e,f,g=d.call(a,b,c),h=g.orientation;if(!h||h>8||1===h)return g;e={};for(f in g)g.hasOwnProperty(f)&&(e[f]=g[f]);switch(g.orientation){case 2:e.left=g.right,e.right=g.left;break;case 3:e.left=g.right,e.top=g.bottom,e.right=g.left,e.bottom=g.top;break;case 4:e.top=g.bottom,e.bottom=g.top;break;case 5:e.left=g.top,e.top=g.left,e.right=g.bottom,e.bottom=g.right;break;case 6:e.left=g.top,e.top=g.right,e.right=g.bottom,e.bottom=g.left;break;case 7:e.left=g.bottom,e.top=g.right,e.right=g.top,e.bottom=g.left;break;case 8:e.left=g.bottom,e.top=g.left,e.right=g.top,e.bottom=g.right}return g.orientation>4&&(e.maxWidth=g.maxHeight,e.maxHeight=g.maxWidth,e.minWidth=g.minHeight,e.minHeight=g.minWidth,e.sourceWidth=g.sourceHeight,e.sourceHeight=g.sourceWidth),e}}),function(a){"use strict";"function"==typeof define&&define.amd?define(["load-image"],a):a(window.loadImage)}(function(a){"use strict";var b=window.Blob&&(Blob.prototype.slice||Blob.prototype.webkitSlice||Blob.prototype.mozSlice);a.blobSlice=b&&function(){var a=this.slice||this.webkitSlice||this.mozSlice;return a.apply(this,arguments)},a.metaDataParsers={jpeg:{65505:[]}},a.parseMetaData=function(b,c,d){d=d||{};var e=this,f=d.maxMetaDataSize||262144,g={},h=!(window.DataView&&b&&b.size>=12&&"image/jpeg"===b.type&&a.blobSlice);(h||!a.readFile(a.blobSlice.call(b,0,f),function(b){if(b.target.error)return console.log(b.target.error),void c(g);var f,h,i,j,k=b.target.result,l=new DataView(k),m=2,n=l.byteLength-4,o=m;if(65496===l.getUint16(0)){for(;n>m&&(f=l.getUint16(m),f>=65504&&65519>=f||65534===f);){if(h=l.getUint16(m+2)+2,m+h>l.byteLength){console.log("Invalid meta data: Invalid segment size.");break}if(i=a.metaDataParsers.jpeg[f])for(j=0;j<i.length;j+=1)i[j].call(e,l,m,h,g,d);m+=h,o=m}!d.disableImageHead&&o>6&&(g.imageHead=k.slice?k.slice(0,o):new Uint8Array(k).subarray(0,o))}else console.log("Invalid JPEG file: Missing JPEG marker.");c(g)},"readAsArrayBuffer"))&&c(g)}}),function(a){"use strict";"function"==typeof define&&define.amd?define(["load-image","load-image-meta"],a):a(window.loadImage)}(function(a){"use strict";a.ExifMap=function(){return this},a.ExifMap.prototype.map={Orientation:274},a.ExifMap.prototype.get=function(a){return this[a]||this[this.map[a]]},a.getExifThumbnail=function(a,b,c){var d,e,f;if(!c||b+c>a.byteLength)return void console.log("Invalid Exif data: Invalid thumbnail data.");for(d=[],e=0;c>e;e+=1)f=a.getUint8(b+e),d.push((16>f?"0":"")+f.toString(16));return"data:image/jpeg,%"+d.join("%")},a.exifTagTypes={1:{getValue:function(a,b){return a.getUint8(b)},size:1},2:{getValue:function(a,b){return String.fromCharCode(a.getUint8(b))},size:1,ascii:!0},3:{getValue:function(a,b,c){return a.getUint16(b,c)},size:2},4:{getValue:function(a,b,c){return a.getUint32(b,c)},size:4},5:{getValue:function(a,b,c){return a.getUint32(b,c)/a.getUint32(b+4,c)},size:8},9:{getValue:function(a,b,c){return a.getInt32(b,c)},size:4},10:{getValue:function(a,b,c){return a.getInt32(b,c)/a.getInt32(b+4,c)},size:8}},a.exifTagTypes[7]=a.exifTagTypes[1],a.getExifValue=function(b,c,d,e,f,g){var h,i,j,k,l,m,n=a.exifTagTypes[e];if(!n)return void console.log("Invalid Exif data: Invalid tag type.");if(h=n.size*f,i=h>4?c+b.getUint32(d+8,g):d+8,i+h>b.byteLength)return void console.log("Invalid Exif data: Invalid data offset.");if(1===f)return n.getValue(b,i,g);for(j=[],k=0;f>k;k+=1)j[k]=n.getValue(b,i+k*n.size,g);if(n.ascii){for(l="",k=0;k<j.length&&(m=j[k],"\x00"!==m);k+=1)l+=m;return l}return j},a.parseExifTag=function(b,c,d,e,f){var g=b.getUint16(d,e);f.exif[g]=a.getExifValue(b,c,d,b.getUint16(d+2,e),b.getUint32(d+4,e),e)},a.parseExifTags=function(a,b,c,d,e){var f,g,h;if(c+6>a.byteLength)return void console.log("Invalid Exif data: Invalid directory offset.");if(f=a.getUint16(c,d),g=c+2+12*f,g+4>a.byteLength)return void console.log("Invalid Exif data: Invalid directory size.");for(h=0;f>h;h+=1)this.parseExifTag(a,b,c+2+12*h,d,e);return a.getUint32(g,d)},a.parseExifData=function(b,c,d,e,f){if(!f.disableExif){var g,h,i,j=c+10;if(1165519206===b.getUint32(c+4)){if(j+8>b.byteLength)return void console.log("Invalid Exif data: Invalid segment size.");if(0!==b.getUint16(c+8))return void console.log("Invalid Exif data: Missing byte alignment offset.");switch(b.getUint16(j)){case 18761:g=!0;break;case 19789:g=!1;break;default:return void console.log("Invalid Exif data: Invalid byte alignment marker.")}if(42!==b.getUint16(j+2,g))return void console.log("Invalid Exif data: Missing TIFF marker.");h=b.getUint32(j+4,g),e.exif=new a.ExifMap,h=a.parseExifTags(b,j,j+h,g,e),h&&!f.disableExifThumbnail&&(i={exif:{}},h=a.parseExifTags(b,j,j+h,g,i),i.exif[513]&&(e.exif.Thumbnail=a.getExifThumbnail(b,j+i.exif[513],i.exif[514]))),e.exif[34665]&&!f.disableExifSub&&a.parseExifTags(b,j,j+e.exif[34665],g,e),e.exif[34853]&&!f.disableExifGps&&a.parseExifTags(b,j,j+e.exif[34853],g,e)}}},a.metaDataParsers.jpeg[65505].push(a.parseExifData)}),function(a){"use strict";"function"==typeof define&&define.amd?define(["load-image","load-image-exif"],a):a(window.loadImage)}(function(a){"use strict";a.ExifMap.prototype.tags={256:"ImageWidth",257:"ImageHeight",34665:"ExifIFDPointer",34853:"GPSInfoIFDPointer",40965:"InteroperabilityIFDPointer",258:"BitsPerSample",259:"Compression",262:"PhotometricInterpretation",274:"Orientation",277:"SamplesPerPixel",284:"PlanarConfiguration",530:"YCbCrSubSampling",531:"YCbCrPositioning",282:"XResolution",283:"YResolution",296:"ResolutionUnit",273:"StripOffsets",278:"RowsPerStrip",279:"StripByteCounts",513:"JPEGInterchangeFormat",514:"JPEGInterchangeFormatLength",301:"TransferFunction",318:"WhitePoint",319:"PrimaryChromaticities",529:"YCbCrCoefficients",532:"ReferenceBlackWhite",306:"DateTime",270:"ImageDescription",271:"Make",272:"Model",305:"Software",315:"Artist",33432:"Copyright",36864:"ExifVersion",40960:"FlashpixVersion",40961:"ColorSpace",40962:"PixelXDimension",40963:"PixelYDimension",42240:"Gamma",37121:"ComponentsConfiguration",37122:"CompressedBitsPerPixel",37500:"MakerNote",37510:"UserComment",40964:"RelatedSoundFile",36867:"DateTimeOriginal",36868:"DateTimeDigitized",37520:"SubSecTime",37521:"SubSecTimeOriginal",37522:"SubSecTimeDigitized",33434:"ExposureTime",33437:"FNumber",34850:"ExposureProgram",34852:"SpectralSensitivity",34855:"PhotographicSensitivity",34856:"OECF",34864:"SensitivityType",34865:"StandardOutputSensitivity",34866:"RecommendedExposureIndex",34867:"ISOSpeed",34868:"ISOSpeedLatitudeyyy",34869:"ISOSpeedLatitudezzz",37377:"ShutterSpeedValue",37378:"ApertureValue",37379:"BrightnessValue",37380:"ExposureBias",37381:"MaxApertureValue",37382:"SubjectDistance",37383:"MeteringMode",37384:"LightSource",37385:"Flash",37396:"SubjectArea",37386:"FocalLength",41483:"FlashEnergy",41484:"SpatialFrequencyResponse",41486:"FocalPlaneXResolution",41487:"FocalPlaneYResolution",41488:"FocalPlaneResolutionUnit",41492:"SubjectLocation",41493:"ExposureIndex",41495:"SensingMethod",41728:"FileSource",41729:"SceneType",41730:"CFAPattern",41985:"CustomRendered",41986:"ExposureMode",41987:"WhiteBalance",41988:"DigitalZoomRatio",41989:"FocalLengthIn35mmFilm",41990:"SceneCaptureType",41991:"GainControl",41992:"Contrast",41993:"Saturation",41994:"Sharpness",41995:"DeviceSettingDescription",41996:"SubjectDistanceRange",42016:"ImageUniqueID",42032:"CameraOwnerName",42033:"BodySerialNumber",42034:"LensSpecification",42035:"LensMake",42036:"LensModel",42037:"LensSerialNumber",0:"GPSVersionID",1:"GPSLatitudeRef",2:"GPSLatitude",3:"GPSLongitudeRef",4:"GPSLongitude",5:"GPSAltitudeRef",6:"GPSAltitude",7:"GPSTimeStamp",8:"GPSSatellites",9:"GPSStatus",10:"GPSMeasureMode",11:"GPSDOP",12:"GPSSpeedRef",13:"GPSSpeed",14:"GPSTrackRef",15:"GPSTrack",16:"GPSImgDirectionRef",17:"GPSImgDirection",18:"GPSMapDatum",19:"GPSDestLatitudeRef",20:"GPSDestLatitude",21:"GPSDestLongitudeRef",22:"GPSDestLongitude",23:"GPSDestBearingRef",24:"GPSDestBearing",25:"GPSDestDistanceRef",26:"GPSDestDistance",27:"GPSProcessingMethod",28:"GPSAreaInformation",29:"GPSDateStamp",30:"GPSDifferential",31:"GPSHPositioningError"},a.ExifMap.prototype.stringValues={ExposureProgram:{0:"Undefined",1:"Manual",2:"Normal program",3:"Aperture priority",4:"Shutter priority",5:"Creative program",6:"Action program",7:"Portrait mode",8:"Landscape mode"},MeteringMode:{0:"Unknown",1:"Average",2:"CenterWeightedAverage",3:"Spot",4:"MultiSpot",5:"Pattern",6:"Partial",255:"Other"},LightSource:{0:"Unknown",1:"Daylight",2:"Fluorescent",3:"Tungsten (incandescent light)",4:"Flash",9:"Fine weather",10:"Cloudy weather",11:"Shade",12:"Daylight fluorescent (D 5700 - 7100K)",13:"Day white fluorescent (N 4600 - 5400K)",14:"Cool white fluorescent (W 3900 - 4500K)",15:"White fluorescent (WW 3200 - 3700K)",17:"Standard light A",18:"Standard light B",19:"Standard light C",20:"D55",21:"D65",22:"D75",23:"D50",24:"ISO studio tungsten",255:"Other"},Flash:{0:"Flash did not fire",1:"Flash fired",5:"Strobe return light not detected",7:"Strobe return light detected",9:"Flash fired, compulsory flash mode",13:"Flash fired, compulsory flash mode, return light not detected",15:"Flash fired, compulsory flash mode, return light detected",16:"Flash did not fire, compulsory flash mode",24:"Flash did not fire, auto mode",25:"Flash fired, auto mode",29:"Flash fired, auto mode, return light not detected",31:"Flash fired, auto mode, return light detected",32:"No flash function",65:"Flash fired, red-eye reduction mode",69:"Flash fired, red-eye reduction mode, return light not detected",71:"Flash fired, red-eye reduction mode, return light detected",73:"Flash fired, compulsory flash mode, red-eye reduction mode",77:"Flash fired, compulsory flash mode, red-eye reduction mode, return light not detected",79:"Flash fired, compulsory flash mode, red-eye reduction mode, return light detected",89:"Flash fired, auto mode, red-eye reduction mode",93:"Flash fired, auto mode, return light not detected, red-eye reduction mode",95:"Flash fired, auto mode, return light detected, red-eye reduction mode"},SensingMethod:{1:"Undefined",2:"One-chip color area sensor",3:"Two-chip color area sensor",4:"Three-chip color area sensor",5:"Color sequential area sensor",7:"Trilinear sensor",8:"Color sequential linear sensor"},SceneCaptureType:{0:"Standard",1:"Landscape",2:"Portrait",3:"Night scene"},SceneType:{1:"Directly photographed"},CustomRendered:{0:"Normal process",1:"Custom process"},WhiteBalance:{0:"Auto white balance",1:"Manual white balance"},GainControl:{0:"None",1:"Low gain up",2:"High gain up",3:"Low gain down",4:"High gain down"},Contrast:{0:"Normal",1:"Soft",2:"Hard"},Saturation:{0:"Normal",1:"Low saturation",2:"High saturation"},Sharpness:{0:"Normal",1:"Soft",2:"Hard"},SubjectDistanceRange:{0:"Unknown",1:"Macro",2:"Close view",3:"Distant view"},FileSource:{3:"DSC"},ComponentsConfiguration:{0:"",1:"Y",2:"Cb",3:"Cr",4:"R",5:"G",6:"B"},Orientation:{1:"top-left",2:"top-right",3:"bottom-right",4:"bottom-left",5:"left-top",6:"right-top",7:"right-bottom",8:"left-bottom"}},a.ExifMap.prototype.getText=function(a){var b=this.get(a);switch(a){case"LightSource":case"Flash":case"MeteringMode":case"ExposureProgram":case"SensingMethod":case"SceneCaptureType":case"SceneType":case"CustomRendered":case"WhiteBalance":case"GainControl":case"Contrast":case"Saturation":case"Sharpness":case"SubjectDistanceRange":case"FileSource":case"Orientation":return this.stringValues[a][b];case"ExifVersion":case"FlashpixVersion":return String.fromCharCode(b[0],b[1],b[2],b[3]);case"ComponentsConfiguration":return this.stringValues[a][b[0]]+this.stringValues[a][b[1]]+this.stringValues[a][b[2]]+this.stringValues[a][b[3]];case"GPSVersionID":return b[0]+"."+b[1]+"."+b[2]+"."+b[3]}return String(b)},function(a){var b,c=a.tags,d=a.map;for(b in c)c.hasOwnProperty(b)&&(d[c[b]]=b)}(a.ExifMap.prototype),a.ExifMap.prototype.getAll=function(){var a,b,c={};for(a in this)this.hasOwnProperty(a)&&(b=this.tags[a],b&&(c[b]=this.getText(b)));return c}});
    
    if(window.FileReader) { //do this        
        $("#upload_cover").change(function(){
            console.log('Uploading cover photo via FileReader.');
            var n = 1;
            $('#image_cover').error(function(){  
                angular.element(document.getElementById('UserProfileCtrl')).scope().apply_old_image_profilepic();
            });
            $('#image_cover').offset({top:0});
            var control = document.getElementById("upload_cover");
            var files = control.files;
            loadImage.parseMetaData(files[0], function (data) {

                var fileName = files[0].name;
                var fileSize = files[0].size;
                var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);
                var validExtensions = ['jpeg', 'jpg', 'gif', 'png', 'JPEG', 'JPG', 'GIF', 'PNG']; //array of valid extensions
                if ($.inArray(fileNameExt, validExtensions) == -1) {
                    showResponseMessage('Allowed file types only jpeg, jpg, gif and png.', 'alert-danger');
                    return false;
                }
                if (fileSize > 4194304) {
                   showResponseMessage('Image file '+fileName+' should be less than 4 MB', 'alert-danger');                    
                    return false;
                }
                var options = {canvas:true};
                if (data.exif) {
                    options.orientation = data.exif.get('Orientation');
                }
                var loadingImage = loadImage(
                    files[0],
                    function (img) {
                        var src = img.toDataURL("image/jpeg");
                        readURL(src);
                    },
                    options
                );
            });
            
            //readURL(loadingImage);
            $('#hidden_image_cover').val(files[0].name);
        });
    } else {
//      Uploading using ngf file upload $scope.uploadCoverPhoto(file, errFiles);
//      initFineUploader();
//      $('#profilebanner2').attr('onClick','$(\'#CoverUpload input\').trigger(\'click\');');
    }
});
    

    function readURL(src)
    {
        $('#image_cover').attr('src', src);
        $('#image_cover').hide();
        $('#coverViewimg').hide();
        $('#coverDragimg').show();
        $('.cover-picture-loader').show();
        setTimeout(function(){
            var width = $('#image_cover').width();
            var height = $('#image_cover').height();
            //console.log(height);                         
            if(!$('.ajs-error').is(':visible')){
                if(width>1 && height>1){
                    /*if(width<800 || height<380){
                        showResponseMessage('Image width should be bigger than 800 and height should be bigger than 380','alert-danger');
                        $('.cancel-upload').trigger('click');
                        $('#image_cover').show();
                        $('.cover-picture-loader').hide();
                        $('#coverDragimg').hide();
                        $('#coverViewimg').show();            
                        $('.imgFill').imagefill();

                    } else {*/
                        $('#editiconToggle').hide();
                        if($('#module_id').val()==1){
                            angular.element(document.getElementById('GroupMemberCtrl')).scope().checkCoverExists();
                        } else {
                            angular.element(document.getElementById('UserProfileCtrl')).scope().checkCoverExists();
                        }
                        $('#image_cover').css('width',$(window).width()+'px');
                        //$('#image_cover').attr('width',windowWidth+'px');                                    
                        $('#image_cover').show();
                        $('.cover-picture-loader, .spinner30').hide();
                        $('.btn.drag-cover').show();
                        $('#image_cover').dragncrop({        
                            // Drag instruction
                            instruction: false,
                            instructionText: 'Drag to crop',
                            instructionHideOnHover: true,
                            overlay: true,
                            drag: function(event, position){
                               //console.log(position.dimension[1]);
                                $('#coY').val(position.dimension[1]);
                            },
                            stop: function(e){
                               // console.log(getPosition());
                            }
                        });

                    //}
                }
            }
        },2000);
        $('#hidden_image_cover_data').val(src);
        $('#image_cover').offset({top:0});
        $('.edit-profile').hide();
        $('.action-conver').show();
        $('.inner-follow-frnds').hide();
        
    }
   
    //Function for set image_src when upload image
    /*function readURL(input)
    {
        if (input.files && input.files[0])
        {
            var reader = new FileReader();
            var image = new Image();

            if(input.files[0].size>4194304){
                showResponseMessage("Image file should be less than 4 MB","alert-danger");
                return;
            }

            var img = input.files[0]['name'];
            var image_regex =  /^.*\.(jpeg|JPEG|png|PNG|jpg|JPG)$/;
            if (!image_regex.test(img)) {
                showResponseMessage("Only jpg,jpeg and png file are accepted","alert-danger");
                return false;
            }
            reader.onload = function (e) {
                    //e.onload = process();
                    $('#image_cover').attr('src', e.target.result);
                    $('#image_cover').hide();
                    $('#coverViewimg').hide();
                    $('#coverDragimg').show();
                    $('.cover-picture-loader').show();
                    setTimeout(function(){
                        var width = $('#image_cover').width();
                        var height = $('#image_cover').height();
                        //console.log(height);                         
                        if(!$('.ajs-error').is(':visible')){
                            if(width>1 && height>1){
                                if(width<800 || height<380){
                                    showResponseMessage('Image width should be bigger than 800 and height should be bigger than 380','alert-danger');
                                    $('.cancel-upload').trigger('click');
                                    $('#image_cover').show();
                                    $('.cover-picture-loader').hide();
                                    $('#coverDragimg').hide();
                                    $('#coverViewimg').show();            
                                    $('.imgFill').imagefill();

                                } else {
                                    $('#editiconToggle').hide();
                                    if($('#module_id').val()==1){
                                        angular.element(document.getElementById('GroupMemberCtrl')).scope().checkCoverExists();
                                    } else {
                                        angular.element(document.getElementById('UserProfileCtrl')).scope().checkCoverExists();
                                    }
                                    $('#image_cover').attr('width',windowWidth);                                    
                                    $('#image_cover').show();
                                    $('.cover-picture-loader, .spinner30').hide();
                                    $('#image_cover').dragncrop({        
                                        // Drag instruction
                                        instruction: false,
                                        instructionText: 'Drag to crop',
                                        instructionHideOnHover: true,
                                        overlay: true,
                                        drag: function(event, position){
                                           //console.log(position.dimension[1]);
                                            $('#coY').val(position.dimension[1]);
                                        },
                                        stop: function(e){
                                           // console.log(getPosition());
                                        }
                                    });

                                }
                            }
                        }
                    },2000);
                    //$('#image_cover').attr('width', 1600);
                    $('#hidden_image_cover_data').val(e.target.result);
                    $('#image_cover').offset({top:0});
                    $('.edit-profile').hide();
                    $('.action-conver').show();
                    $('.inner-follow-frnds').hide();
                    $('.btn.drag-cover').show();
            }
            reader.readAsDataURL(input.files[0]);
        }
    }*/
   
    //Function for save cropped image and reload current page
    function ajax_save_crop_image()
    {       
            $('.cover-picture-loader').show();
            $('.ajs-message.ajs-visible').hide();
            
            var UserProfileCtrlEle = document.getElementById('UserProfileCtrl');
            if(UserProfileCtrlEle) {
                angular.element(UserProfileCtrlEle).scope().applyCoverPictureLoader = 1;
            }
            
            
            /*var headerHeight = $('#ib-main-wrapper').offset().top;
            var imageCoverOffset  = parseInt($('#image_cover').offset().top);
            console.log("headerHeight = "+headerHeight);
            console.log("imageCoverOffset = "+imageCoverOffset);
            if(imageCoverOffset < 0 ) {
                imageCoverOffset = imageCoverOffset * -1;
                headerHeight = 125;
            } else {
                imageCoverOffset = imageCoverOffset * -1;
            } 
            console.log("imageCoverOffset = "+imageCoverOffset);
            var coY = (imageCoverOffset + headerHeight);
            console.log("coY = "+coY);
                        
            $('#coY').val(coY);
            console.log($('#coY').val());*/
            //$('#coY').val((parseInt($('#image_cover').offset().top)));
            $('.profile-footer .spinner30').show();
            var orignal_image = $('#hidden_image_cover').val();
            var orignal_image_data = $('#hidden_image_cover_data').val();            
             
             var upload_type = $("#upload_type").val();
             var typeRowID   = $("#typeRowID").val();
   

            var img = document.getElementById('image_cover'); 
            var width = img.clientWidth;
            var height = img.clientHeight;            
            var extension = orignal_image.substr( (orignal_image.lastIndexOf('.') +1) );
            if(extension != 'bmp' || extension != 'BMP' ) {
                
                    var coY = $('#coY').val();
                    coY = coY * height;
                    console.log(coY);
                    //return false;
             
                    $.ajax({
                    type: "POST",
                    url: base_url+'api/upload_image/updateProfileBanner',
                    data: 'ModuleID='+$('#module_id').val()+'&ModuleEntityGUID='+$('#module_entity_guid').val()+'&ImageUrl='+$('#image_src').val()+'&CanCrop=1&Type=profilebanner&DeviceType=Native&src='+orignal_image+'&img_offset_x='+$("#coX").val()+'&cropAxis='+coY+'&ImageData='+encodeURIComponent(orignal_image_data)+'&height='+height+'&width='+width+'&unique_id='+LoginSessionKey+'&originalWidth='+$('#windowWidth').val(),
                    async : false,
                    dataType:'json',
                    success: function (res) {
                        if(res.Message !== 'Success'){
                            //console.log(res.Message);
                            showResponseMessage(res.Message,"alert-success");
                            //window.location.reload();
                        }else{
                            console.log('Profile Image upload successfully.');
                            $('#apply_button').removeClass('loading');
                            $('.action-conver').hide();    
                            $('.inner-follow-frnds').show();
                            if($('#module_id').val()==1){
                                angular.element(document.getElementById('GroupMemberCtrl')).scope().changeProfileCover(res);
                            } else if($('#module_id').val()==18) {
                                angular.element(document.getElementById('PageCtrl')).scope().changeProfileCover(res);
                            } else {
                                angular.element(document.getElementById('UserProfileCtrl')).scope().changeProfileCover(res);
                            }
                            $('.cover-picture-loader').hide();
                            $('.overlay-cover').show();
                            $("#image_cover").dragncrop('destroy');
                            
                            $('#coverDragimg').hide();
                            $('#coverViewimg').show();
                            
                            if(UserProfileCtrlEle) {
                                angular.element(UserProfileCtrlEle).scope().applyCoverPictureLoader = 0;
                            }
                            
                        }

                    }
                });
        }else{
            showResponseMessage('File Type not supported.','alert-danger');
            //window.location.reload(); 
        }
        //$('#ib-main-wrapper').kinetic('detach');
    }           

    function changeCoverImageFromPopup(url){

            $('#image_cover').attr('src', url);
            $('#image_cover').hide();
            $('#coverViewimg').hide();
            $('#coverDragimg').show();
            $('.cover-picture-loader').show();
            setTimeout(function(){
                var width = $('#image_cover').width();
                var height = $('#image_cover').height();
                //console.log(height);                         
                if(!$('.ajs-error').is(':visible')){
                    if(width>1 && height>1){
                        /*if(width<800 || height<380){
                            showResponseMessage('Image width should be bigger than 800 and height should be bigger than 380','alert-danger');
                            $('.cancel-upload').trigger('click');
                            $('#image_cover').show();
                            $('.cover-picture-loader').hide();
                            $('#coverDragimg').hide();
                            $('#coverViewimg').show();            
                            $('.imgFill').imagefill();

                        } else {*/
                            $('#editiconToggle').hide();
                            if($('#module_id').val()==1){
                                angular.element(document.getElementById('GroupMemberCtrl')).scope().checkCoverExists();
                            } else {
                                angular.element(document.getElementById('UserProfileCtrl')).scope().checkCoverExists();
                            }
                            console.log($(window).width());
                            $('#image_cover').css('width',$(window).width());                                    
                            $('#image_cover').show();
                            $('.cover-picture-loader, .spinner30').hide();
                            $('#image_cover').dragncrop({        
                                // Drag instruction
                                instruction: false,
                                instructionText: 'Drag to crop',
                                instructionHideOnHover: true,
                                overlay: true,
                                drag: function(event, position){
                                   //console.log(position.dimension[1]);
                                    $('#coY').val(position.dimension[1]);
                                },
                                stop: function(e){
                                   // console.log(getPosition());
                                }
                            });

                        //}
                    }
                }
            },2000);
            //$('#image_cover').attr('width', 1600);
            $('#hidden_image_cover_data').val(url);
            $('#image_cover').offset({top:0});
            $('.edit-profile').hide();
            $('.action-conver').show();
            $('.inner-follow-frnds').hide();
            $('.btn.drag-cover').show();
    
    }

    function coverImage() {/*
    var e = $("#ib-main-wrapper"),
        t = function() {
            var t = false,
                n = -1,
                r = false,
                i = e.find("div.ib-main > a"),
                s = i.not(".ib-content"),
                o = s.length,
                u = function() {
                    s.addClass("ib-image");
                    a();
                    l()
                },
                a = function() {
                    f();
                    e.kinetic({
                        moved: function() {
                            t = true
                        },
                        stopped: function() {
                            $("#coX").val($("#ib-main-wrapper").css("top"));
                            t = false
                        }
                    })
                },
                f = function() {
                    var t = $("#ib-top").outerHeight(true) + $("#header").outerHeight(true) + parseFloat(i.css("margin-top"));
                    e.css("height", $(window).height() - t)
                },
                l = function() {
                    i.bind("click.ibTemplate", function(e) {
                        if (!t) c($(this));
                        return false
                    });
                    $(window).bind("resize.ibTemplate", function(e) {
                        f();
                        $("#ib-img-preview, #ib-content-preview").css({
                            width: $(window).width(),
                            height: $(window).height()
                        })
                    })
                },
                c = function(e) {
                    if (r) return false;
                    if (e.hasClass("ib-content")) {
                        r = true;
                        n = e.index(".ib-content");
                        p(e, function() {
                            r = false
                        })
                    } else {
                        r = true;
                        n = e.index(".ib-image");
                        h(e, function() {
                            r = false
                        })
                    }
                },
                h = function(t, n) {
                    var r = t.children("img").data("largesrc"),
                        i = t.children("span").text(),
                        s = {
                            src: r,
                            description: i
                        };
                    t.addClass("ib-loading");
                    d(r, function() {
                        t.removeClass("ib-loading");
                        var u = $("#ib-img-preview").length > 0;
                        if (!u) $("#previewTmpl").tmpl(s).insertAfter(e);
                        else $("#ib-img-preview").children("img.ib-preview-img").attr("src", r).end().find("span.ib-preview-descr").text(i);
                        var a = w(r);
                        t.removeClass("ib-img-loading");
                        $("#ib-img-preview").css({
                            width: t.width(),
                            height: t.height(),
                            left: t.offset().left,
                            top: t.offset().top
                        }).children("img.ib-preview-img").hide().css({
                            width: a.width,
                            height: a.height,
                            left: a.left,
                            top: a.top
                        }).fadeIn(400).end().show().animate({
                            width: $(window).width(),
                            left: 0
                        }, 500, "easeOutExpo", function() {
                            $(this).animate({
                                height: $(window).height(),
                                top: 0
                            }, 400, function() {
                                var e = $(this);
                                e.find("span.ib-preview-descr, span.ib-close").show();
                                if (o > 1) e.find("div.ib-nav").show();
                                if (n) n.call()
                            })
                        });
                        if (!u) v()
                    })
                },
                p = function(t, n) {
                    var r = $("#ib-content-preview").length > 0,
                        i = t.children("div.ib-teaser").html(),
                        s = t.children("div.ib-content-full").html(),
                        o = {
                            teaser: i,
                            content: s
                        };
                    if (!r) $("#contentTmpl").tmpl(o).insertAfter(e);
                    $("#ib-content-preview").css({
                        width: t.width(),
                        height: t.height(),
                        left: t.offset().left,
                        top: t.offset().top
                    }).show().animate({
                        width: $(window).width(),
                        left: 0
                    }, 500, "easeOutExpo", function() {
                        $(this).animate({
                            height: $(window).height(),
                            top: 0
                        }, 400, function() {
                            var e = $(this),
                                t = e.find("div.ib-teaser"),
                                o = e.find("div.ib-content-full"),
                                u = e.find("span.ib-close");
                            if (r) {
                                t.html(i);
                                o.html(s)
                            }
                            t.show();
                            o.show();
                            u.show();
                            if (n) n.call()
                        })
                    });
                    if (!r) m()
                },
                d = function(e, t) {
                    $("<img/>").load(function() {
                        if (t) t.call()
                    }).attr("src", e)
                },
                v = function() {
                    var e = $("#ib-img-preview");
                    e.find("span.ib-nav-prev").bind("click.ibTemplate", function(e) {
                        g("prev")
                    }).end().find("span.ib-nav-next").bind("click.ibTemplate", function(e) {
                        g("next")
                    }).end().find("span.ib-close").bind("click.ibTemplate", function(e) {
                        y()
                    });
                    $(window).bind("resize.ibTemplate", function(t) {
                        var n = e.children("img.ib-preview-img"),
                            r = w(n.attr("src"));
                        n.css({
                            width: r.width,
                            height: r.height,
                            left: r.left,
                            top: r.top
                        })
                    })
                },
                m = function() {
                    $("#ib-content-preview").find("span.ib-close").bind("click.ibTemplate", function(e) {
                        b()
                    })
                },
                g = function(t) {
                    if (r) return false;
                    r = true;
                    var i = $("#ib-img-preview"),
                        u = i.find("div.ib-loading-large");
                    u.show();
                    if (t === "next") {
                        n === o - 1 ? n = 0 : ++n
                    } else if (t === "prev") {
                        n === 0 ? n = o - 1 : --n
                    }
                    var a = s.eq(n),
                        f = a.children("img").data("largesrc"),
                        l = a.children("span").text();
                    d(f, function() {
                        u.hide();
                        var t = w(f);
                        i.children("img.ib-preview-img").attr("src", f).css({
                            width: t.width,
                            height: t.height,
                            left: t.left,
                            top: t.top
                        }).end().find("span.ib-preview-descr").text(l);
                        e.scrollTop(a.offset().top).scrollLeft(a.offset().left);
                        r = false
                    })
                },
                y = function() {
                    if (r) return false;
                    r = true;
                    var e = s.eq(n);
                    $("#ib-img-preview").find("span.ib-preview-descr, div.ib-nav, span.ib-close").hide().end().animate({
                        height: e.height(),
                        top: e.offset().top
                    }, 500, "easeOutExpo", function() {
                        $(this).animate({
                            width: e.width(),
                            left: e.offset().left
                        }, 400, function() {
                            $(this).fadeOut(function() {
                                r = false
                            })
                        })
                    })
                },
                b = function() {
                    if (r) return false;
                    r = true;
                    var e = i.not(".ib-image").eq(n);
                    $("#ib-content-preview").find("div.ib-teaser, div.ib-content-full, span.ib-close").hide().end().animate({
                        height: e.height(),
                        top: e.offset().top
                    }, 500, "easeOutExpo", function() {
                        $(this).animate({
                            width: e.width(),
                            left: e.offset().left
                        }, 400, function() {
                            $(this).fadeOut(function() {
                                r = false
                            })
                        })
                    })
                },
                w = function(e) {
                    var t = new Image;
                    t.src = e;
                    var n = $(window).width(),
                        r = $(window).height(),
                        i = r / n,
                        s = t.width,
                        o = t.height,
                        u = o / s,
                        a, f, l, c;
                    if (i > u) {
                        f = r;
                        a = r / u
                    } else {
                        f = n * u;
                        a = n
                    }
                    return {
                        width: a,
                        height: f,
                        left: (n - a) / 2,
                        top: (r - f) / 2
                    }
                };
            return {
                init: u
            }
        }();
    t.init();
*/} 

$('#cancelCover').on('click',function(){
    $('.btn.drag-cover').hide();
   // $('#ib-main-wrapper').kinetic('detach');
   $('.cover-picture-loader').hide();
    $('#editiconToggle').show();    
});

