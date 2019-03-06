<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;
require APPPATH . '/libraries/PHP_Compat/Compat/Function/file_get_contents.php';
require APPPATH . '/libraries/REST_Controller.php';
 
class Countries extends \Restserver\Libraries\REST_Controller
{
    public function __construct() {
        parent::__construct();
       
        $this->load->library('Activity'); 
        
    }

    /**
     * Add new Country with API
     * -------------------------
     * @method: POST
     */
    public function createCountry_post()
    {
        
            # Create a Country

            header("Access-Control-Allow-Origin: *");
    
            // Load Authorization Token Library
            $this->load->library('Authorization_Token');
           
           
            /**
             * User Token Validation
             */
            //
            $is_valid_token = $this->authorization_token->validateToken();
           
            if (!empty($is_valid_token) OR $is_valid_token['status'] === TRUE){
            # XSS Filtering (https://www.codeigniter.com/user_guide/libraries/security.html)
            $_POST = $this->security->xss_clean($_POST);
            
            # Form Validation
            $this->form_validation->set_rules('name', 'Name of Country', 'trim|required|max_length[45]|is_unique[countries.name]');
            $this->form_validation->set_rules('continent', 'Name of Continent', 'trim|required|max_length[45]');
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
                // Load Country Model
                $this->load->model('country_model', 'CountryModel');
                

                $insert_data = [
                    'name' => $this->input->post('name', TRUE),
                    'continent' => $this->input->post('continent', TRUE),
                    'created_at' => date("Y-m-d h:i:sa"),
                ];

                // Insert Country
                $output = $this->CountryModel->create_country($insert_data);

                if ($output > 0 AND !empty($output))
                {
                    // Success
                    $message = [
                        'status' => true,
                        'message' => "Country Added Successfully"
                    ];
                    $this->activity->insert_actvity("Successfully added Country {$this->input->post('name', TRUE)} under Continent {$this->input->post('continent', TRUE)}");
                    $this->response($message, REST_Controller::HTTP_OK);
                } else
                {
                    // Error
                    $message = [
                        'status' => FALSE,
                        'message' => "Country not created"
                    ];
                    $this->activity->insert_actvity("Error, Country did not Create Successfully");
                    $this->response($message, REST_Controller::HTTP_NOT_FOUND);
                }
            }
        }

        else
             {
             
                $this->response(['status' => FALSE, 'message' => $is_valid_token['message'] ], REST_Controller::HTTP_UNAUTHORIZED);
            }
    }

    /**
     * Delete a Country with API
     * @method: DELETE
     */
    public function deleteCountry_delete($id)
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
            # Delete a Country

            # XSS Filtering (https://www.codeigniter.com/user_guide/libraries/security.html)
            $id = $this->security->xss_clean($id);
            
            if (empty($id) AND !is_numeric($id))
            {
                $this->response(['status' => FALSE, 'message' => 'Invalid Country ID' ], REST_Controller::HTTP_NOT_FOUND);
            }
            else
            {
                // Load Country Model
                $this->load->model('country_model', 'CountryModel');
                
                $delete_country = [
                    'id' => $id,
                ];
                
                // Delete a Country
                $output = $this->CountryModel->delete_country($delete_country);

                if ($output > 0 AND !empty($output))
                {
                    // Success
                    $message = [
                        'status' => true,
                        'message' => "Country Deleted"
                    ];
                    $this->activity->insert_actvity('Successfully Deleted Country');
                    $this->response($message, REST_Controller::HTTP_OK);
                } else
                {
                    // Error
                    $message = [
                        'status' => FALSE,
                        'message' => "Country not deleted"
                    ];
                    
                    $this->activity->insert_actvity('Country Delete Failed');
                    $this->response($message, REST_Controller::HTTP_NOT_FOUND);
                }
            }

        } else {
            $this->response(['status' => FALSE, 'message' => $is_valid_token['message'] ], REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Update a Country with API
     * @method: PUT
     */
    public function updateCountry_put($id)
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
            # Update a Country
            
           // print_r(file_get_contents("php://input"));
            //die;
            
            # XSS Filtering (https://www.codeigniter.com/user_guide/libraries/security.html)
            $_POST = json_decode($this->security->xss_clean(file_get_contents("php://input")), true);
            
            $this->form_validation->set_data([
                
                'name' => $this->input->post('name', TRUE),
                'continent' => $this->input->post('continent', TRUE),
            ]);
            
            # Form Validation
            $this->form_validation->set_rules('name', 'Country', 'trim|required|max_length[45]');
            $this->form_validation->set_rules('continent', 'Continent', 'trim|required|max_length[45]');
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
                // Load Country Model
                $this->load->model('country_model', 'CountryModel');

                $update_data = [
                    'id' => $id,
                    'name' => $this->input->post('country_name', TRUE),
                    'continent' => $this->input->post('continent_name', TRUE),
                ];

                // Update a Country
                $output = $this->CountryModel->update_article($update_data);

                if ($output > 0 AND !empty($output))
                {
                    // Success
                    $message = [
                        'status' => true,
                        'message' => "Country Updated"
                    ];
                    $this->response($message, REST_Controller::HTTP_OK);
                } else
                {
                    // Error
                    $message = [
                        'status' => FALSE,
                        'message' => "Country not updated"
                    ];
                    $this->response($message, REST_Controller::HTTP_NOT_FOUND);
                }
            }

        } else {
            $this->response(['status' => FALSE, 'message' => $is_valid_token['message'] ], REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function getCountry_get()
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
            # Get Countries

                // Load Country Model
                $this->load->model('country_model', 'CountryModel');

                // Get all Countries
                $output = $this->CountryModel->get_countries();

               

                if (!empty($output))
                {
                    // Success
                    $message = [
                        'status' => true,
                        'data' => $output,
                        'message' => "All Countries"
                    ];
                    $this->activity->insert_actvity("Successfully Viewed all Countries");
                    $this->response($message, REST_Controller::HTTP_OK);
                } else
                {
                    // Error
                    $message = [
                        'status' => FALSE,
                        'message' => "No countries found"
                    ];
                    $this->activity->insert_actvity("Tried Viewing Countries but no countries found");
                    $this->response($message, REST_Controller::HTTP_NOT_FOUND);
                }
            }

         else {
            $this->response(['status' => FALSE, 'message' => $is_valid_token['message'] ], REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

   
}