<?php
namespace App;

class Model {
    protected $table;
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function all($where = []) {
        return $this->db->select($this->table, "*", $where);
    }

    public function find($id) {
        return $this->db->get($this->table, "*", ["id" => $id]);
    }

    public function create($data) {
        $this->db->insert($this->table, $data);
        return $this->db->id();
    }

    public function update($id, $data) {
        return $this->db->update($this->table, $data, ["id" => $id]);
    }

    public function delete($id) {
        return $this->db->delete($this->table, ["id" => $id]);
    }
}

