
<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_New_dfs_notification extends CI_Migration {

    public function up() {
       
        $notifications = array(
            array(
            "notification_type" =>441,
            "en_subject"=>"15 Minutes to go for {{collection_name}}",
            "hi_subject"=>"",
            //"tam_subject"=>"",
            "ben_subject"=>"",
            "pun_subject"=>"",
            "fr_subject"=>"",
            "guj_subject"=>"",
            "th_subject"=>"",
            "message"           =>"{{username}}, {{collection_name}} is going live in next 15 minutes. Be ready with your teams and WIN BIG!",
            "en_message"        =>"{{username}}, {{collection_name}} is going live in next 15 minutes. Be ready with your teams and WIN BIG!",
            "hi_message"        =>"{{username}}, {{collection_name}} अगले 15 मिनट में लाइव जा रहा है। अपनी टीमों के साथ तैयार रहें और बड़ी जीतें!",
            "guj_message"       =>"{{username}}, {{{collection_name}} આગામી 15 મિનિટમાં રહે છે. તમારી ટીમો સાથે તૈયાર રહો અને મોટા જીતી લો!",
            "fr_message"        =>"{{username}}, {{collection_name}} va vivre dans les 15 prochaines minutes. Soyez prêt avec vos équipes et gagnez gros!",
            "ben_message"       =>"{{username}}, {{collection_name}} এর পরবর্তী 15 মিনিটের মধ্যেই চলছে। আপনার দলের সাথে প্রস্তুত হোন এবং বড় জয়!",
            "pun_message"       =>"{{username}}, {{collection_name}} ਅਗਲੇ 15 ਮਿੰਟਾਂ ਵਿੱਚ. ਆਪਣੀਆਂ ਟੀਮਾਂ ਨਾਲ ਤਿਆਰ ਰਹੋ ਅਤੇ ਵੱਡਾ ਜਿੱਤੋ!",
            "tam_message"       =>"{{username}}, {{collection_name}}}} அடுத்த 15 நிமிடங்களில் வாழ்கிறது. உங்கள் அணிகள் தயாராக இருங்கள் மற்றும் பெரிய வெற்றி!",
            "th_message"        =>"{{username}}, {{collection_name}} กำลังจะอยู่ใน 15 นาทีถัดไป เตรียมตัวให้พร้อมกับทีมของคุณและชนะรางวัลใหญ่!",
            "kn_message"        =>"{{username}}, {{collection_name}} ಮುಂದಿನ 15 ನಿಮಿಷಗಳಲ್ಲಿ ಲೈವ್ ಆಗಿರುತ್ತದೆ. ನಿಮ್ಮ ತಂಡಗಳೊಂದಿಗೆ ಸಿದ್ಧರಾಗಿ ದೊಡ್ಡವರಾಗಿರಿ!",
            "ru_message"        =>"{{username}} {{collection_name}} - в ближайшие 15 минут. Будьте готовы с вашими командами и выиграть Большой!",
            "id_message"        =>"{{username}},  {{collection_name}} akan hidup dalam 15 menit ke depan. Bersiaplah dengan tim Anda dan menang besar!",
            "tl_message"        =>"{{username}},  {{collection_name}} ay nabubuhay sa susunod na 15 minuto. Maging handa sa iyong mga koponan at manalo ng malaki!",
            "zh_message"        =>"{{username}}，{{collection_name}}在接下来的15分钟内才能生活。与你的团队准备好赢得大！",
           // "es_message" => "Torneo {{name}} unió con éxito."
            ),
            array(
            "notification_type" =>442,
            "en_subject"=>"Match Published",
            "hi_subject"=>"",
            //"tam_subject"=>"",
            "ben_subject"=>"",
            "pun_subject"=>"",
            "fr_subject"=>"",
            "guj_subject"=>"",
            "th_subject"=>"",
            "message"           =>"{{username}}, {{collection_name}} has been published. Go, try your luck to win big!",
            "en_message"        =>"{{username}}, {{collection_name}} has been published. Go, try your luck to win big!",
            "hi_message"        =>"{{username}}, {{collection_name}} प्रकाशित किया गया है। जाओ, बड़ी जीतने के लिए अपनी किस्मत आजमाएं!",
            "guj_message"       =>"{{username}}, {{collection_name}} પ્રકાશિત કરવામાં આવ્યું છે. જાઓ, મોટી જીતવા માટે તમારા નસીબ અજમાવી જુઓ!",
            "fr_message"        =>"{{username}}, {{collection_name}} a été publié. Allez, essayez votre chance pour gagner grand!",
            "ben_message"       =>"{{username}}, {{collection_name}} প্রকাশিত হয়েছে। যান, বড় জয় আপনার ভাগ্য চেষ্টা করুন!",
            "pun_message"       =>"{{username}}, {{collection_name}} ਪ੍ਰਕਾਸ਼ਤ ਕੀਤਾ ਗਿਆ ਹੈ. ਜਾਓ, ਆਪਣੀ ਕਿਸਮਤ ਨੂੰ ਜਿੱਤਣ ਦੀ ਕੋਸ਼ਿਸ਼ ਕਰੋ!",
            "tam_message"       =>"{{username}}, {{collection_name}} வெளியிடப்பட்டது. செல்ல, பெரிய வெற்றி உங்கள் அதிர்ஷ்டம் முயற்சி!",
            "th_message"        =>"{{username}}, {{collection_name}} ได้รับการเผยแพร่แล้ว ไปลองเสี่ยงโชคที่จะชนะรางวัลใหญ่!",
            "kn_message"        =>"{{username}}, {{collection_name}} ಅನ್ನು ಪ್ರಕಟಿಸಲಾಗಿದೆ. ಹೋಗಿ, ದೊಡ್ಡ ಗೆಲ್ಲಲು ನಿಮ್ಮ ಅದೃಷ್ಟ ಪ್ರಯತ್ನಿಸಿ!",
            "ru_message"        =>"{{username}} {{collection_name}} был опубликован. Иди, попробуйте свою удачу выиграть большую!",
            "id_message"        =>"{{username}}, {{collection_name}} telah dipublikasikan. Pergi, cobalah keberuntungan Anda untuk menang besar!",
            "tl_message"        =>"{{username}}, {{collection_name}} ay na-publish. Pumunta, subukan ang iyong kapalaran upang manalo ng malaki!",
            "zh_message"        =>"{{username}}，{{collection_name}}已发布。去，试试你的运气赢得大！",
            //"es_message" => "Hey, {{name}} torneo es en vivo ahora! Comprobar su puntuación."
            ),
            array(
            "notification_type" =>443,
            "en_subject"=>"Contest Added",
            "hi_subject"=>"",
            //"tam_subject"=>"",
            "ben_subject"=>"",
            "pun_subject"=>"",
            "fr_subject"=>"",
            "guj_subject"=>"",
            "th_subject"=>"",
            "message"           =>"{{username}}, New contest {{contest_name}} in {{collection_name}} is waiting for you.It's time to bring your skills and win amazing prizes.",
            "en_message"        =>"{{username}}, New contest {{contest_name}} in {{collection_name}} is waiting for you.It's time to bring your skills and win amazing prizes.",
            "hi_message"        =>"{{username}}, {{collection_name}} में नया प्रतियोगिता {{contest_name}} आपके लिए इंतज़ार कर रही है। यह आपके कौशल लाने और अद्भुत पुरस्कार जीतने का समय है।",
            "guj_message"       =>"{{username}}, નવી હરીફાઈ {{{collection_name}} માં {{{contest_name}} તમારા માટે રાહ જોઈ રહ્યું છે. તમારી કુશળતા લાવવાનો સમય છે અને આશ્ચર્યજનક ઇનામો જીતવા માટે.",
            "fr_message"        =>"{{username}}, nouveau concours {{contest_name}} in {{collection_name}} vous attend. Il est temps d'apporter vos compétences et de gagner des prix incroyables.",
            "ben_message"       =>"{{username}}, নতুন প্রতিযোগিতা {{contest_name}} {{collection_name}} আপনার জন্য অপেক্ষা করছে। আপনার দক্ষতা আনতে এবং আশ্চর্যজনক পুরস্কারগুলি জিততে সময়টির জন্য অপেক্ষা করছে।",
            "pun_message"       =>"{{username}}, {{collection_name}} ਵਿੱਚ ਨਵਾਂ ਮੁਕਾਬਲਾ {{contest_name}} ਤੁਹਾਡੇ ਲਈ ਉਡੀਕ ਕਰ ਰਿਹਾ ਹੈ. ਹੁਣ ਸਮਾਂ ਆ ਗਿਆ ਹੈ ਕਿ ਤੁਸੀਂ ਆਪਣੇ ਹੁਨਰ ਨੂੰ ਲਿਆਓ ਅਤੇ ਸ਼ਾਨਦਾਰ ਇਨਾਮ ਜਿੱਤੋ.",
            "tam_message"       =>"{{username}}, புதிய போட்டியில் {{contest_name}} {{collection_name}}} காத்திருக்கிறது. உங்கள் திறமைகளை கொண்டு வரவும், அற்புதமான பரிசுகளை வெல்லவும்.",
            "th_message"        =>"{{username}} การประกวดใหม่ {{contest_name}} ใน {{collection_name}} กำลังรอคุณอยู่เวลาที่จะนำทักษะของคุณและชนะรางวัลที่น่าตื่นตาตื่นใจ",
            "kn_message"        =>"{{username}}, ಹೊಸ ಸ್ಪರ್ಧೆ {{contest_name}} {{collection_name}} ನಲ್ಲಿ ಕಾಯುತ್ತಿದೆ. ನಿಮ್ಮ ಕೌಶಲ್ಯಗಳನ್ನು ತರಲು ಮತ್ತು ಅದ್ಭುತ ಬಹುಮಾನಗಳನ್ನು ಗೆಲ್ಲಲು ಸಮಯ.",
            "ru_message"        =>"{{username}}, новый конкурс {{{contest_name}} в {{collection_name}} ждет вас. Время вас, чтобы принести свои навыки и выиграть удивительные призы.",
            "id_message"        =>"{{username}}, kontes baru {{contest_name}} in {{collection_name}} sedang menunggu Anda. Saatnya untuk membawa keahlian Anda dan memenangkan hadiah yang luar biasa.",
            "tl_message"        =>"{{username}}, bagong paligsahan {{contest_name}} sa {{collection_name}} ay naghihintay para sa iyo. Panahon na upang dalhin ang iyong mga kasanayan at manalo ng mga kamangha-manghang mga premyo.",
            "zh_message"        =>"{{username}}，在{{collection_name}}中等待您的新比赛{{contest_name}}",
            //"es_message" => "Usted es un ganador en el {{name}} torneo."
            ),
            );

            $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$notifications);
        }   

            function down()
    {
        //down script  
        // $this->db->where_in('notification_type', array(441,442,443));
        // $this->db->delete(NOTIFICATION_DESCRIPTION);
    }
}