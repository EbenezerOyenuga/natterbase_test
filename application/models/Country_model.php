<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Country_Model extends CI_Model
{
    protected $countries_table = 'countries';

    /**
     * Add a new Country
     * @param: {array} Country Data
     */
    public function create_country(array $data) {
        $this->db->insert($this->countries_table, $data);
        return $this->db->insert_id();
    }

    /**
     * Delete an Article
     * @param: {array} Article Data
     */
    public function delete_country(array $data)
    {
        /**
         * Check Article exist with article_id and user_id
         */
        $query = $this->db->get_where($this->countries_table, $data);
        if ($this->db->affected_rows() > 0) {
            
            // Delete Article
            $this->db->delete($this->countries_table, $data);
            if ($this->db->affected_rows() > 0) {
                return true;
            }
            return false;
        }   
        return false;
    }

    /**
     * Get Countries
     */
    public function get_countries()
    {
        /**
         * Get all Countries
         */
        $this->db->select('name, continent, created_at');
        $this->db->from($this->countries_table);
        $query=$this->db->get();
        return $query->result();
    }

    /**
     * Update an Article
     * @param: {array} Article Data
     */
    public function update_article(array $data)
    {
        /**
         * Check Article exist with article_id and user_id
         */
        $query = $this->db->get_where($this->article_table, [
            'user_id' => $data['user_id'],
            'id' => $data['id'],
        ]);

        if ($this->db->affected_rows() > 0) {
            
            // Update an Article
            $update_data = [
                'title' =>  $data['title'],
                'description' =>  $data['description'],
                'updated_at' => time(),
            ];

            return $this->db->update($this->article_table, $update_data, ['id' => $query->row('id')]);
        }   
        return false;
    }
}