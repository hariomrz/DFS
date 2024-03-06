<?php defined('BASEPATH') or exit('No direct script access allowed');

class Page extends MYREST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $_POST = $this->input->post();
        $this->admin_lang = $this->lang->line('pages');
        $this->load->model('Page_model');
        //Do your magic here
        $this->admin_roles_manage($this->admin_id, 'content_management');
    }

    public function pages_post()
    {
        $data_arr = $this->input->post();
        $result = $this->Page_model->get_all_page_list($data_arr);
        if (!empty($result)) {
            foreach ($result['result'] as $key => $res) {

                $result['result'][$key]['page_content'] = strip_tags($res['page_content']);
            }
        }
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['status'] = true;
        $this->api_response_arry['message'] = '';
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    public function get_page_detail_post()
    {
        $this->form_validation->set_rules('page_id', "page id", 'trim|required');
        $this->form_validation->set_rules('language', "language", 'trim|required');
        if ($this->form_validation->run() == false) {
            $this->send_validation_errors();
        } else {
            $page_id = $this->input->post("page_id");
            $language = $this->input->post("language");
            $page_detail = $this->Page_model->get_page_detail($page_id, $language);
            if (!empty($page_detail['custom_data'])) {
                $page_detail['custom_data'] = json_decode($page_detail['custom_data'], true);
            }
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['data'] = $page_detail;
            $this->api_response_arry['message'] = '';
            $this->api_response_arry['status'] = true;
            $this->api_response();
        }
    }

    public function update_page_post()
    {

        $this->form_validation->set_rules('page_title', 'Title', 'trim|required');
        $this->form_validation->set_rules('meta_keyword', 'Meta keyword', 'trim');
        $this->form_validation->set_rules('meta_desc', 'Meta description', 'trim');
        $this->form_validation->set_rules('language', 'language', 'trim|required');
        if ($this->input->post('page_alias') != 'CONTACT_US') {
            $this->form_validation->set_rules('page_content', 'Content', 'trim|required');
        }
        if ($this->form_validation->run() == false) {
            $this->send_validation_errors();
        } else {
            $language = $this->input->post('language');
            $page_alias = $this->input->post('page_alias');
            $data[$language . '_page_title'] = $this->input->post('page_title');
            $data[$language . '_meta_keyword'] = $this->input->post('meta_keyword');
            $data[$language . '_meta_desc'] = $this->input->post('meta_desc');
            $data[$language . '_page_content'] = $this->input->post('page_content');

            if ($this->input->post('custom_data') != null && $this->input->post('custom_data') != '') {
                $data['custom_data'] = json_encode($this->input->post('custom_data'));
            }

            $data['modified_date'] = format_date('today');
            $page_id = $this->input->post("page_id");

            $page_alias_data = $this->Page_model->get_single_row('page_alias', CMS_PAGES, array('page_id' => $page_id));
            $this->Page_model->update_page($data, $page_id);
            $page_alias = $page_alias_data['page_alias'] . '_' . $language;
            //delete static page cache data
            if (isset($page_alias) && $page_alias != "") {
                $caceh_key = 'static_page_' . $page_alias;
                $this->delete_cache_data($caceh_key);
            }

            if (BUCKET_STATIC_DATA_ALLOWED) {
                //for delete s3 bucket file
                //echo "static_page_".$page_alias; die;
                $this->load->model('Page_model');
                $this->Page_model->delete_s3_bucket_file("static_page_" . $page_alias . ".json");
                // $this->push_s3_data_in_queue("static_page_".$page_alias,array(),"delete");
            }

            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['data'] = array();
            $this->api_response_arry['message'] = $this->admin_lang['page_updated'];
            $this->api_response_arry['status'] = true;
            $this->api_response();
        }
    }

    public function create_blog_post()
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('title', 'Title', 'trim|required');
            $this->form_validation->set_rules('description', 'Description', 'trim|required');
            if ($this->form_validation->run()) {
                $data['title'] = $this->input->post('title');
                $data['description'] = $this->input->post('description');
                $data['image'] = $this->input->post('image');
                $data['created_date'] = format_date('today', 'Y-m-d');
                $data['updated_date'] = format_date('today', 'Y-m-d');

                if ($this->db->insert('blog', $data)) {
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
                    $this->api_response_arry['status'] = true;
                    $this->api_response_arry['message'] = $this->admin_lang['blog_created'];
                    $this->api_response();
                }
            }
            $this->send_validation_errors();
        }
    }

    public function do_upload_post()
    {

        $file_field_name = $this->post('name');
        $dir = ROOT_PATH . UPLOAD_DIR;
        $subdir = ROOT_PATH . BLOG_IMAGE_DIR;
        $temp_file = $_FILES['file']['tmp_name'];
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $vals = @getimagesize($temp_file);
        $width = $vals[0];
        $height = $vals[1];

        //check minimum dimension condition.
        if ($height < 350 || $width < 670) {
            $invalid_size = str_replace("{max_height}", '350', $this->admin_lang['blog_image_invalid_size']);
            $invalid_size = str_replace("{max_width}", '670', $invalid_size);
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = false;
            $this->api_response_arry['message'] = $invalid_size;
            $this->api_response();
        }

        if (strtolower(IMAGE_SERVER) == 'local') {
            $this->check_folder_exist($dir);
            $this->check_folder_exist($subdir);
        }

        $file_name = time() . "." . $ext;
        $filePath = BLOG_IMAGE_DIR . $file_name;

        /*--Start amazon server upload code--*/
        if (strtolower(IMAGE_SERVER) == 'remote') {
            try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $data = array('image_url'=>  IMAGE_PATH.$filePath,'image_name'=> $file_name);
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                    $this->api_response_arry['data'] = $data;
                    $this->api_response();
                }
            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response();
            }
        } else {
            $config['allowed_types'] = 'jpg|png|jpeg|gif';
            $config['max_size'] = '4048';
            // $config['max_width']        = '365';
            // $config['max_height']        = '160';
            $config['upload_path'] = $subdir;
            $config['file_name'] = time();

            $this->load->library('upload', $config);
            if (!$this->upload->do_upload('file')) {
                $error = $this->upload->display_errors();
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['status'] = false;
                $this->api_response_arry['message'] = strip_tags($error);
                $this->api_response();
            } else {
                $uploaded_data = $this->upload->data();
                $image_path = '../' . BLOG_IMAGE_DIR . $uploaded_data['file_name'];
                $data = array(
                    'image_url' => $image_path,
                    'image_name' => $uploaded_data['file_name'],
                );
                $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
                $this->api_response_arry['data'] = $data;
                $this->api_response_arry['status'] = true;
                $this->api_response();

            }
        }
    }

    /**
     * @Summary: check if folder exists otherwise create new
     * @create_date: 24 july, 2015
     */
    private function check_folder_exist($dir)
    {
        if (!is_dir($dir)) {
            return mkdir($dir, 0777);
        }

        return true;
    }

    public function activate_blog_post()
    {
        $blog_id = $this->input->post('blog_id');
        $data = array(
            'status' => '1',
            'updated_date' => format_date('today', 'Y-m-d'),
        );
        if ($this->Page_model->update_blog_status($blog_id, $data)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['data'] = array();
            $this->api_response_arry['message'] = $this->admin_lang['blog_activated'];
            $this->api_response_arry['status'] = true;
            $this->api_response();
        }

        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['data'] = array();
        $this->api_response_arry['message'] = $this->admin_lang['not_be_activate'];
        $this->api_response_arry['status'] = false;
        $this->api_response();
    }

    public function deactive_blog_post()
    {
        $blog_id = $this->input->post('blog_id');
        $data = array(
            'status' => '0',
            'updated_date' => format_date('today', 'Y-m-d'),
        );
        if ($this->Page_model->update_blog_status($blog_id, $data)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['data'] = array();
            $this->api_response_arry['message'] = $this->admin_lang['un_published'];
            $this->api_response_arry['status'] = true;
            $this->api_response();
        }

        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['data'] = array();
        $this->api_response_arry['message'] = $this->admin_lang['not_be_activate'];
        $this->api_response_arry['status'] = false;
        $this->api_response();
    }

    public function delete_blog_post()
    {
        $blog_id = $this->input->post('blog_id');

        if ($this->Page_model->delete_blog($blog_id)) {

            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['data'] = array();
            $this->api_response_arry['message'] = $this->admin_lang['blog_deleted'];
            $this->api_response_arry['status'] = true;
            $this->api_response();
        }

        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['data'] = array();
        $this->api_response_arry['message'] = $this->admin_lang['can_not_be_delete'];
        $this->api_response_arry['status'] = false;
        $this->api_response();
    }

    public function get_all_blog_comments_post()
    {
        $this->form_validation->set_rules('blog_id', 'Blog id', 'trim|required');
        if ($this->form_validation->run() == false) {
            $this->send_validation_errors();
        } else {
            $blog_id = $this->input->post("blog_id");
            $blog_comments = $this->Page_model->get_all_blog_comments($blog_id);

            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['data'] = $blog_comments;
            $this->api_response_arry['message'] = '';
            $this->api_response_arry['status'] = true;
            $this->api_response();
        }
    }

    public function delete_blog_comment_post()
    {
        $this->form_validation->set_rules('blog_comment_id', 'Blog Comment Id', 'trim|required');
        $this->form_validation->set_rules('blog_id', 'Blog Id', 'trim|required');
        if ($this->form_validation->run() == false) {
            $this->send_validation_errors();
        } else {
            $blog_comment_id = $this->input->post("blog_comment_id");
            $blog_id = $this->input->post("blog_id");
            $blog_comments = $this->Page_model->delete_blog_comment($blog_comment_id, $blog_id);

            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['data'] = $blog_comments;
            $this->api_response_arry['message'] = $this->admin_lang['blog_comment_deleted'];
            $this->api_response_arry['status'] = true;
            $this->api_response();
        }
    }

    //function to remove image
    public function remove_image_post()
    {
        $image_name = $this->input->post('image_name');
        $dir = ROOT_PATH . CMS_DIR;
        $s3_dir = CMS_DIR;
        $dir_path = $s3_dir . $image_name;
        if (strtolower(IMAGE_SERVER) == 'remote') {
            try{
                $data_arr = array();
                $data_arr['file_path'] = $dir_path;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_deleted = $upload_lib->delete_file($data_arr);
                if($is_deleted){
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
                    $this->api_response_arry['message'] = $this->admin_lang['image_removed'];
                    $this->api_response();
                }else{
                    $error_msg = 'Caught exception: ' . $e->getMessage() . "\n";
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error'] = $error_msg;
                    $this->api_response();
                }
            }catch(Exception $e){
                $error_msg = 'Caught exception: ' . $e->getMessage() . "\n";
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $error_msg;
                $this->api_response();
            }
        }
        @unlink($dir . $image_name);
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['message'] = $this->admin_lang['image_removed'];
        $this->api_response();
    }

    //function to upload about us image
    public function upload_about_us_post()
    {
        // $about_img_name = $this->post('name');
        $dir = ROOT_PATH . UPLOAD_DIR;
        $subdir = ROOT_PATH . CMS_DIR;
        if (count($_FILES['file']['name']) > 10) {

            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->admin_lang['more_image_upload_error'];
            $this->api_response();
        }
        if (strtolower(IMAGE_SERVER) == 'local') {
            $this->check_folder_exist($dir);
            $this->check_folder_exist($subdir);
        }
        $i = 1;
        $data = array();

        foreach ($_FILES['file']['tmp_name'] as $key => $temp_file) {
            // $temp_file            = $_FILES['file']['tmp_name'];
            $ext = pathinfo($_FILES['file']['name'][$key], PATHINFO_EXTENSION);

            $file_name = time() . $i . "." . $ext;
            // $file_name = time().".".jpeg ;
            $filePath = CMS_DIR . $file_name;

            /*--Start amazon server upload code--*/
            if (strtolower(IMAGE_SERVER) == 'remote') {
                try{
                    $data_arr = array();
                    $data_arr['file_path'] = $filePath;
                    $data_arr['source_path'] = $temp_file;
                    $this->load->library('Uploadfile');
                    $upload_lib = new Uploadfile();
                    $is_uploaded = $upload_lib->upload_file($data_arr);
                    if($is_uploaded){
                        $data[] = array('image_name' => $file_name, 'image_url' => IMAGE_PATH . $filePath);
                    }
                }catch(Exception $e){
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                    $this->api_response();
                }
            } else {
                $config['allowed_types'] = 'jpg|png|jpeg|gif';
                $config['max_size'] = '4048';
                // $config['max_width']        = '365';
                // $config['max_height']        = '160';
                $config['upload_path'] = $subdir;
                $config['file_name'] = time() . $i;

                $this->load->library('upload', $config);
                if (!$this->upload->do_upload('file')) {
                    $error = $this->upload->display_errors();
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['status'] = false;
                    $this->api_response_arry['message'] = strip_tags($error);
                    $this->api_response();
                } else {
                    $uploaded_data = $this->upload->data();
                    $image_path = '../' . CMS_DIR . $uploaded_data['file_name'];
                    $data[] = array(
                        'image_url' => $image_path,
                        'image_name' => $uploaded_data['file_name'],
                    );
                }
            }
            $i++;
        }
        // $this->response(array(config_item('rest_status_field_name')=>TRUE,'data'=>$data) , rest_controller::HTTP_OK);
        $image_name = array_column($data, 'image_name');

        // $image_name = implode(',', $image_name);
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['data'] = $image_name;
        $this->api_response_arry['status'] = true;
        $this->api_response();
    }

    public function get_faq_category_post()
    {
        $language = $this->input->post("language");
        $categories = $this->Page_model->get_faq_category($language);
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['data'] = $categories;
        $this->api_response_arry['message'] = '';
        $this->api_response_arry['status'] = true;
        $this->api_response();
    }

    public function get_faq_question_answer_post()
    {

        $category_id = $this->input->post("category_id");
        $language = $this->input->post("language");
        $result = array();
        $result = $this->Page_model->get_question_count($language);
        foreach ($result as $key => $category) {
            $result[$key]['questions'] = $this->Page_model->get_faq_question_answer($category['category_id'], $language);
        }
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['data'] = $result;
        $this->api_response_arry['message'] = '';
        $this->api_response_arry['status'] = true;
        $this->api_response();
    }

    public function add_question_answer_post()
    {
        $post_data = $this->input->post();
        if (empty($post_data['questions'])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['data'] = '';
            $this->api_response_arry['global_error'] = $this->admin_lang['valid_data'];
            $this->api_response_arry['status'] = true;
            $this->api_response();
        } else {
            $page_alias = $this->input->post("page_alias") ? $this->input->post("page_alias") : 'faq';
            $language = $this->input->post("language") ? $this->input->post("language") : 'en';
            $add_question = $this->Page_model->add_question_answer($post_data);
            if ($add_question) {

                $page_alias = $page_alias . '_' . $language;
                //delete static page cache data
                if (isset($page_alias) && $page_alias != "") {
                    $caceh_key = 'static_page_' . $page_alias;
                    $this->delete_cache_data($caceh_key);
                }

                if (BUCKET_STATIC_DATA_ALLOWED) {
                    //for delete s3 bucket file
                    //echo "static_page_".$page_alias; die;
                    $this->load->model('Page_model');
                    $this->Page_model->delete_s3_bucket_file("static_page_" . $page_alias . ".json");
                    // $this->push_s3_data_in_queue("static_page_".$page_alias,array(),"delete");
                }

                $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
                $this->api_response_arry['data'] = 'Last question id : ' . $add_question;
                $this->api_response_arry['message'] = $this->admin_lang['question_added'];
                $this->api_response_arry['status'] = true;
                $this->api_response();
            }
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['data'] = '';
            $this->api_response_arry['global_error'] = $this->admin_lang['question_adding_error'];
            $this->api_response_arry['status'] = false;
            $this->api_response();
        }
    }

    public function delete_question_answer_post()
    {
        $this->form_validation->set_rules('question_id', 'Question Id', 'trim|required');
        if ($this->form_validation->run() == false) {
            $this->send_validation_errors();
        } else {
            $question_id = $this->input->post('question_id');
            $page_alias = $this->input->post("page_alias") ? $this->input->post("page_alias") : 'faq';
            $language = $this->input->post("language") ? $this->input->post("language") : 'en';
            $delete_question = $this->Page_model->delete_question_answer($question_id);
            if ($delete_question) {

                $page_alias = $page_alias . '_' . $language;
                //delete static page cache data
                if (isset($page_alias) && $page_alias != "") {
                    $caceh_key = 'static_page_' . $page_alias;
                    $this->delete_cache_data($caceh_key);
                }

                if (BUCKET_STATIC_DATA_ALLOWED) {
                    //for delete s3 bucket file
                    //echo "static_page_".$page_alias; die;
                    $this->load->model('Page_model');
                    $this->Page_model->delete_s3_bucket_file("static_page_" . $page_alias . ".json");
                    // $this->push_s3_data_in_queue("static_page_".$page_alias,array(),"delete");
                }

                $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
                $this->api_response_arry['data'] = 'deleted question id : ' . $delete_question;
                $this->api_response_arry['message'] = $this->admin_lang['question_deleted'];
                $this->api_response_arry['status'] = true;
                $this->api_response();
            }
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['data'] = '';
            $this->api_response_arry['global_error'] = $this->admin_lang['deleted_error'];
            $this->api_response_arry['status'] = false;
        }$this->api_response();
    }

    public function update_question_answer_post()
    {
        $post_data = $this->input->post();
        if (empty($post_data['questions'])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['data'] = '';
            $this->api_response_arry['global_error'] = $this->admin_lang['valid_data'];
            $this->api_response_arry['status'] = true;
            $this->api_response();
        } else {
            $post_data = $this->input->post();
            $page_alias = $this->input->post("page_alias") ? $this->input->post("page_alias") : 'faq';
            $language = $this->input->post("language") ? $this->input->post("language") : 'en';
            $update_question = $this->Page_model->update_question_answer($post_data);
            if (isset($update_question)) {
                $page_alias = $page_alias . '_' . $language;
                //delete static page cache data
                if (isset($page_alias) && $page_alias != "") {
                    $caceh_key = 'static_page_' . $page_alias;
                    $this->delete_cache_data($caceh_key);
                }

                if (BUCKET_STATIC_DATA_ALLOWED) {
                    //for delete s3 bucket file
                    //echo "static_page_".$page_alias; die;
                    $this->load->model('Page_model');
                    $this->Page_model->delete_s3_bucket_file("static_page_" . $page_alias . ".json");
                    // $this->push_s3_data_in_queue("static_page_".$page_alias,array(),"delete");
                }

                $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
                $this->api_response_arry['data'] = 'no of questions updated : ' . $update_question;
                $this->api_response_arry['message'] = $this->admin_lang['question_updated'];
                $this->api_response_arry['status'] = true;
                $this->api_response();
            }
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['data'] = '';
            $this->api_response_arry['global_error'] = $this->admin_lang['question_update_error'];
            $this->api_response_arry['status'] = false;
            $this->api_response();
        }
    }

    public function update_page_status_post()
	{
		$this->form_validation->set_rules('page_id','Page ID', 'trim|required');
		
		if ($this->form_validation->run() == FALSE)
		{
			$this->send_validation_errors();
		}
		else
		{
			$language = $this->input->post('language');		

			$data['modified_date']	= format_date('today');
			$data['status']	= $this->input->post("status");
			$page_id = $this->input->post("page_id");
			
			$page_alias_data = $this->Page_model->get_single_row('page_alias',CMS_PAGES,array('page_id' => $page_id ));

			$page_alias = $page_alias_data['page_alias'].'_'.$language;						
		
			$this->Page_model->update_page($data, $page_id);			

			$config_cache_key = 'app_config';
			$this->delete_cache_data($config_cache_key);

			$this->push_s3_data_in_queue('app_version',array(),"delete");
			$this->push_s3_data_in_queue('app_master_data',array(),"delete");

			$this->flush_cache_data();
			$this->deleteS3BucketFile("app_master_data.json");

			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['data']			= array();
			$this->api_response_arry['message']			= "Status Updated Successfully";
			$this->api_response_arry['status']			= TRUE;
			$this->api_response();
		}
	}

}
