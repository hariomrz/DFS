<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Stock_push extends CI_Migration {

    public function up() {

        $notifications = array(
            array(
                "notification_type" =>560,
                "en_subject"=>"Holiday",
                "hi_subject"=>"",
                //"tam_subject"=>"",
                "ben_subject"=>"",
                "pun_subject"=>"",
                "fr_subject"=>"",
                "guj_subject"=>"",
                "th_subject"=>"",
                "message"           =>"{{username}},{{newline}}Stock Market will be closed today due to Holiday, Stay tuned, leagues would be published soon.",
                "en_message"        =>"",
                "hi_message"        =>"",
                "guj_message"       =>"",
                "fr_message"        =>"",
                "ben_message"       =>"",
                "pun_message"       =>"",
                "tam_message"       =>"",
                "th_message"        =>"",
                "kn_message"        =>"",
                "ru_message"        =>"",
                "id_message"        =>"",
                "tl_message"        =>"",
                "zh_message"        =>"",
                // "es_message" => "Torneo {{name}} unió con éxito."
                ),
            array(
                "notification_type" =>561,
                "en_subject"=>"Top Gainer",
                "hi_subject"=>"",
                //"tam_subject"=>"",
                "ben_subject"=>"",
                "pun_subject"=>"",
                "fr_subject"=>"",
                "guj_subject"=>"",
                "th_subject"=>"",
                "message"           =>"{{username}},{{newline}}Trending Top Gainers- {{gainer_names}}",
                "en_message"        =>"",
                "hi_message"        =>"",
                "guj_message"       =>"",
                "fr_message"        =>"",
                "ben_message"       =>"",
                "pun_message"       =>"",
                "tam_message"       =>"",
                "th_message"        =>"",
                "kn_message"        =>"",
                "ru_message"        =>"",
                "id_message"        =>"",
                "tl_message"        =>"",
                "zh_message"        =>"",
                // "es_message" => "Torneo {{name}} unió con éxito."
            ),
            array(
                "notification_type" =>562,
                "en_subject"=>"Top Loosers",
                "hi_subject"=>"",
                //"tam_subject"=>"",
                "ben_subject"=>"",
                "pun_subject"=>"",
                "fr_subject"=>"",
                "guj_subject"=>"",
                "th_subject"=>"",
                "message"           =>"{{username}},{{newline}}Top Loosers- {{loosers_name}}",
                "en_message"        =>"",
                "hi_message"        =>"",
                "guj_message"       =>"",
                "fr_message"        =>"",
                "ben_message"       =>"",
                "pun_message"       =>"",
                "tam_message"       =>"",
                "th_message"        =>"",
                "kn_message"        =>"",
                "ru_message"        =>"",
                "id_message"        =>"",
                "tl_message"        =>"",
                "zh_message"        =>"",
                // "es_message" => "Torneo {{name}} unió con éxito."
                ),
            array(
                "notification_type" =>563,
                "en_subject"=>"Stock Added",
                "hi_subject"=>"",
                //"tam_subject"=>"",
                "ben_subject"=>"",
                "pun_subject"=>"",
                "fr_subject"=>"",
                "guj_subject"=>"",
                "th_subject"=>"",
                "message"           =>"{{username}},{{newline}}{{stocks_name}} Moving in Next day's Market session Make changes in your portfolio accordingly for the next match.",
                "en_message"        =>"",
                "hi_message"        =>"",
                "guj_message"       =>"",
                "fr_message"        =>"",
                "ben_message"       =>"",
                "pun_message"       =>"",
                "tam_message"       =>"",
                "th_message"        =>"",
                "kn_message"        =>"",
                "ru_message"        =>"",
                "id_message"        =>"",
                "tl_message"        =>"",
                "zh_message"        =>"",
                // "es_message" => "Torneo {{name}} unió con éxito."
                ),
            array(
                "notification_type" =>566,
                "en_subject"=>"Match Published",
                "hi_subject"=>"",
                //"tam_subject"=>"",
                "ben_subject"=>"",
                "pun_subject"=>"",
                "fr_subject"=>"",
                "guj_subject"=>"",
                "th_subject"=>"",
                "message"           =>"{{username}},{{newline}}{{collection_name}}{{category}} stock fantasy has been published. Go, try your luck to win big!",
                "en_message"        =>"",
                "hi_message"        =>"",
                "guj_message"       =>"",
                "fr_message"        =>"",
                "ben_message"       =>"",
                "pun_message"       =>"",
                "tam_message"       =>"",
                "th_message"        =>"",
                "kn_message"        =>"",
                "ru_message"        =>"",
                "id_message"        =>"",
                "tl_message"        =>"",
                "zh_message"        =>"",
                // "es_message" => "Torneo {{name}} unió con éxito."
            ),
            array(
                "notification_type" =>567,
                "en_subject"=>"Contest Added",
                "hi_subject"=>"",
                //"tam_subject"=>"",
                "ben_subject"=>"",
                "pun_subject"=>"",
                "fr_subject"=>"",
                "guj_subject"=>"",
                "th_subject"=>"",
                "message"           =>"{{username}},{{newline}}New contest {{contest_name}}{{collection_name}} {{category}} is waiting for you.It's time to bring your skills and win amazing prizes.",
                "en_message"        =>"",
                "hi_message"        =>"",
                "guj_message"       =>"",
                "fr_message"        =>"",
                "ben_message"       =>"",
                "pun_message"       =>"",
                "tam_message"       =>"",
                "th_message"        =>"",
                "kn_message"        =>"",
                "ru_message"        =>"",
                "id_message"        =>"",
                "tl_message"        =>"",
                "zh_message"        =>"",
                // "es_message" => "Torneo {{name}} unió con éxito."
            ),
            array(
                "notification_type" =>568,
                "en_subject"=>"15 Minutes to go for {{collection_name}}{{category}}",
                "hi_subject"=>"",
                //"tam_subject"=>"",
                "ben_subject"=>"",
                "pun_subject"=>"",
                "fr_subject"=>"",
                "guj_subject"=>"",
                "th_subject"=>"",
                "message"           =>"{{username}},{{newline}}{{collection_name}}{{category}} stock fantasy is going live in next 15 minutes. Be ready with your portfolio and WIN BIG!",
                "en_message"        =>"",
                "hi_message"        =>"",
                "guj_message"       =>"",
                "fr_message"        =>"",
                "ben_message"       =>"",
                "pun_message"       =>"",
                "tam_message"       =>"",
                "th_message"        =>"",
                "kn_message"        =>"",
                "ru_message"        =>"",
                "id_message"        =>"",
                "tl_message"        =>"",
                "zh_message"        =>"",
                // "es_message" => "Torneo {{name}} unió con éxito."
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