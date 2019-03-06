<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

require APPPATH . '/libraries/REST_Controller.php';
 
class Users extends \Restserver\Libraries\REST_Controller
{
    public function __construct() {
        parent::__construct();
        // Load User Model
        $this->load->model('user_model', 'UserModel');
        //Load Activity Library
        $this->load->library('Activity');
    }

    /**
     * User Register
     * --------------------------
     * @param: firstname
     * @param: lastname
     * @param: date_of_birth
     * @param: email
     * @param: username
     * @param: password
     * --------------------------
     * @method : POST
     * @link : /signup
     */
    public function register_post()
    {
        header("Access-Control-Allow-Origin: *");

        # XSS Filtering 
        $_POST = $this->security->xss_clean($_POST);
        
        # Form Validation
        $this->form_validation->set_rules('firstname', 'First Name', 'trim|required|max_length[45]');
        $this->form_validation->set_rules('lastname', 'Last Name', 'trim|required|max_length[45]');
        $this->form_validation->set_rules('date_of_birth','Date of birth',array('regex_match[([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))]', 'trim', 'required'));
        $this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email|max_length[100]|is_unique[users.email]',
            array('is_unique' => 'This %s already exists please enter another email address')
        );
        $this->form_validation->set_rules('username', 'Username', 'trim|required|is_unique[users.username]|alpha_numeric|max_length[20]',
            array('is_unique' => 'This %s already exists please enter another username')
        );
        
        $this->form_validation->set_rules('password', 'Password', 'trim|required|max_length[100]');
        if ($this->form_validation->run() == FALSE)
        {
            // Form Validation Errors
            $message = array(
                'status' => false,
                'error' => $this->form_validation->error_array(),
                'message' => validation_errors()
            );

            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        else
        {
            $insert_data = [
                'first_name' => $this->input->post('firstname', TRUE),
                'last_name' => $this->input->post('lastname', TRUE),
                'date_of_birth' => $this->input->post('date_of_birth', TRUE),
                'email' => $this->input->post('email', TRUE),
                'username' => $this->input->post('username', TRUE),
                'password' => sha1($this->input->post('password', TRUE)),
                //'created_at' => time(),
                //'updated_at' => time(),
            ];

            // Insert User in Database
            $output = $this->UserModel->insert_user($insert_data);
            if ($output > 0 AND !empty($output))
            {
                // Success 200 Code Send
                $message = [
                    'status' => true,
                    'message' => "User registration successful"
                ];
                $this->insert_actvity($output, "User Registered Successfully");
                $this->response($message, REST_Controller::HTTP_OK);
            } else
            {
                // Error
                $message = [
                    'status' => FALSE,
                    'message' => "Not Register Your Account."
                ];
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }


    /**
     * User Login API
     * --------------------
     * @param: username or email
     * @param: password
     * --------------------------
     * @method : POST
     * @link: api/user/login
     */
    public function login_post()
    {
        header("Access-Control-Allow-Origin: *");

        # XSS Filtering (https://www.codeigniter.com/user_guide/libraries/security.html)
        $_POST = $this->security->xss_clean($_POST);
        
        # Form Validation
        $this->form_validation->set_rules('username', 'Username', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|max_length[100]');
        if ($this->form_validation->run() == FALSE)
        {
            // Form Validation Errors
            $message = array(
                'status' => false,
                'error' => $this->form_validation->error_array(),
                'message' => validation_errors()
            );

            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        else
        {
            // Load Login Function
            $output = $this->UserModel->user_login($this->input->post('username'), $this->input->post('password'));
            if (!empty($output) AND $output != FALSE)
            {
                // Load Authorization Token Library
                $this->load->library('Authorization_Token');

                // Generate Token
                $token_data['id'] = $output->id;
                $token_data['firstname'] = $output->first_name;
                $token_data['lastname'] = $output->last_name;
                $token_data['username'] = $output->username;
                $token_data['email'] = $output->email;
                $token_data['time'] = time();

                $user_token = $this->authorization_token->generateToken($token_data);

                $return_data = [
                    'user_id' => $output->id,
                    'firstname' => $output->first_name,
                    'lastname' => $output->last_name,
                    'username' => $output->username,
                    'email' => $output->email,
                    'token' => $user_token,
                ];

                // Login Success
                $message = [
                    'status' => true,
                    'data' => $return_data,
                    'message' => "User login successful"
                ];
                $this->insert_actvity($output->id, "Successfully Logged in");
                $this->response($message, REST_Controller::HTTP_OK);
            } else
            {
                // Login Error
                $message = [
                    'status' => FALSE,
                    'message' => "Invalid Username or Password"
                ];
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function getActivities_get($offset=0)
    {
        header("Access-Control-Allow-Origin: *");
    
        // Load Authorization Token Library
        $this->load->library('Authorization_Token');

        /**
         * User Token Validation
         */
        $is_valid_token = $this->authorization_token->validateToken();
        if (!empty($is_valid_token) AND $is_valid_token['status'] === TRUE)
        {
            # Get Activities

                // Get all Activities
                
                $output = $this->UserModel->get_activities($is_valid_token['data']->id, $offset);

               

                if (!empty($output))
                {
                    // Success
                    $message = [
                        'status' => true,
                        'data' => $output,
                        'message' => "All Activities"
                    ];
                    $this->activity->insert_actvity("Successfully Viewed all Activities");
                    $this->response($message, REST_Controller::HTTP_OK);
                } else
                {
                    // Error
                    $message = [
                        'status' => FALSE,
                        'message' => "No activities found"
                    ];
                    $this->activity->insert_actvity("Tried Viewing Activities but no activities found");
                    $this->response($message, REST_Controller::HTTP_NOT_FOUND);
                }
            }

         else {
            $this->response(['status' => FALSE, 'message' => $is_valid_token['message'] ], REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function insert_actvity($user_id, $activity)
    {
       

        //User Activity Log
        $insert_data = [
            'user_id' => $user_id,
            'activity' => $activity,
            'date_of_activity' => date("Y-m-d h:i:sa"),
            
        ];

        //insert user activities
        $output = $this->UserModel->insert_user_activity($insert_data);
    }
}