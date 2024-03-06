<?php
class Common_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_static_content($page_alias) {
        $result = $this->db->select($this->lang_abbr."_page_content as page_content,".$this->lang_abbr."_page_title as page_title,custom_data")
                ->from(CMS_PAGES)
                ->where("page_alias", $page_alias)
                ->limit(1)
                ->get()
                ->row_array();
        return $result;
    }

    public function get_faq_question_answer($category_id=''){
        $sql = $this->db->select("question_id,
                                    ".$this->lang_abbr."_question as question,"
                                    .$this->lang_abbr."_answer as answer"
                                )
                        ->from(FAQ_QUESTIONS)
                        ->where('status',1)
                        ->where($this->lang_abbr.'_question!=',NULL);
                        if($category_id!='' && !empty($category_id)){
                            $sql = $this->db->where('category_id',$category_id);
                        }

                        $sql = $this->db->order_by('category_id')->get();
        $result = $sql->result_array();
        return $result; 
    }

     public function get_faq_category($language='en')
    {
        $sql = $this->db->select("category_id,
                                    category_alias,
                                    ".$this->lang_abbr."_category as category")
                        ->from(FAQ_CATEGORY)
                        ->where("status", 1)
                        // ->where_in('category_id',$category_ids)
                        ->get();
        $result = $sql->result_array();

        // echo $this->db->last_query();die();
        return $result; 
    }

    public function get_question_num_row($category_id){
        $sql = $this->db->select("count(".$this->lang_abbr."_question) count")
                        ->from(FAQ_QUESTIONS)
                        ->where("status", 1)
                        ->where($this->lang_abbr."_question!=",NULL)
                        ->where("category_id", $category_id)
                        ->get();
        $result = $sql->row();

        // echo $this->db->last_query();die();
        return $result; 
    }

}
