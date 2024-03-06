<?php
class Coins_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }


    function get_daily_streak_coins(){
        
        $result = $this->db->select('SS.*')
        ->from(MODULE_SETTING.' MS')
        ->join(SUBMODULE_SETTING.' SS','SS.module_setting_id=MS.module_setting_id')
        ->where('MS.module_setting_id',1)
        ->where('SS.submodule_setting_id',4)
        ->where('SS.status',1)->get()->result_array();

       return $result;
    }

    function get_all_module_settings(){
        
        $result = $this->db->select('SS.*')
        ->from(MODULE_SETTING.' MS')
        ->join(SUBMODULE_SETTING.' SS','SS.module_setting_id=MS.module_setting_id')
        //->where('MS.module_setting_id',1)
        //->where('SS.submodule_setting_id',4)
        ->where('SS.status',1)->get()->result_array();

       return $result;
    }

    function get_all_module_settings_with_status(){
        
        $result = $this->db->select('SS.*')
        ->from(MODULE_SETTING.' MS')
        ->join(SUBMODULE_SETTING.' SS','SS.module_setting_id=MS.module_setting_id')
        //->where('MS.module_setting_id',1)
        //->where('SS.submodule_setting_id',4)
       ->get()->result_array();

       return $result;
    }

    public function get_last_daily_streak_coin()
    {
        $result = $this->db->select('*')
        ->from(ORDER)
        ->where('source',144)
        ->where('status',1)
        ->where('user_id',$this->user_id)
        ->order_by('date_added','DESC')
        ->limit(1)
        ->get()->row_array();

        return $result;    
    }

    public function get_last_coin_entries($limit)
    {
        $result = $this->db->select('*')
        ->from(ORDER)
        ->where('source',144)
        ->where('status',1)
        ->where('user_id',$this->user_id)
        ->order_by('date_added','DESC')
        ->limit($limit)
        ->get()->result_array();

        return $result;    
    }

    public function sync_earn_coins() {
        $this->db->select("module_key,en,hi,guj,fr,ben,pun,tam,th,ru,id,tl,zh,kn,es,image_url,url,status",FALSE);
        $this->db->from(EARN_COINS);
        $query = $this->db->get();
        $resultList = $query->result_array();

        // echo "<pre>";
        // print_r($resultList);
        // die('df');
        if($resultList) {
            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->delete_collection(COLL_EARN_COINS);
            foreach ($resultList as &$result) {
                $result['en'] = json_decode($result['en'],TRUE);
                $result['hi'] = json_decode($result['hi'],TRUE);
                $result['guj'] = json_decode($result['guj'],TRUE);
                $result['fr'] = json_decode($result['fr'],TRUE);
                $result['ben'] = json_decode($result['ben'],TRUE);
                $result['pun'] = json_decode($result['pun'],TRUE);
                $result['tam'] = json_decode($result['tam'],TRUE);
                $result['th'] = json_decode($result['th'],TRUE);
                $result['ru'] = json_decode($result['ru'],TRUE);
                $result['id'] = json_decode($result['id'],TRUE);
                $result['tl'] = json_decode($result['tl'],TRUE);
                $result['zh'] = json_decode($result['zh'],TRUE);
                $result['kn'] = json_decode($result['kn'],TRUE);
                $result['es'] = json_decode($result['es'],TRUE);
                $result['status'] =(int)$result['status'];
                $this->Notify_nosql_model->insert_nosql(COLL_EARN_COINS,$result);
            }
        }

        die('dfd');
    }

    function get_download_app_coins_status()
    {
        $result = $this->db->select('order_id')
        ->from(ORDER)
        ->where('source',471)
        ->where('status',1)
        ->where('user_id',$this->user_id)
        ->order_by('date_added','DESC')
        ->limit(1)
        ->get()->row_array();

        return $result;    
    }

    function get_app_install_date()
    {
        $result = $this->db->select('IFNULL(android_install_date,IFNULL(ios_install_date,"")) as install_date')
        ->from(USER)
        ->where('status',1)
        ->where('user_id',$this->user_id)
        ->limit(1)
        ->get()->row_array();

        return $result;    
    }


   

}
