<?php defined('BASEPATH') OR exit('No direct script access allowed');
 
 

class Activity
{
    private $CI;
    public function __construct() {
        $this->CI =& get_instance();

    }
    public function insert_actvity($activity)
    {
        //Inserts activity

        header("Access-Control-Allow-Origin: *");
    
        // Load Authorization Token Library
        $this->CI->load->library('Authorization_Token');

        /**
         * User Token Validation
         */
        $is_valid_token = $this->CI->authorization_token->validateToken();
        // Load User Model
        $this->CI->load->model('user_model', 'UserModel');

        //User Activity Log
        $insert_data = [
            'user_id' => $is_valid_token['data']->id,
            'activity' => $activity,
            'date_of_activity' => date("Y-m-d h:i:sa"),
            
        ];

        //insert user activities
        $output = $this->CI->UserModel->insert_user_activity($insert_data);
    }

}