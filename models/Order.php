<?php
class Order {
    private $conn;
    private $table_name = "orders";

    public $id;
    public $user_id;
    public $customer_name;
    public $customer_phone;
    public $total_amount;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT o.id, o.customer_name, o.customer_phone, o.total_amount, o.status, o.order_date, u.full_name as user_name 
                  FROM " . $this->table_name . " o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  ORDER BY o.order_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByUser($user_id) {
        $query = "SELECT id, customer_name, customer_phone, total_amount, status, order_date 
                  FROM " . $this->table_name . " 
                  WHERE user_id = ? 
                  ORDER BY order_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET user_id=:user_id, customer_name=:customer_name, customer_phone=:customer_phone, total_amount=:total_amount, status=:status";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":customer_name", $this->customer_name);
        $stmt->bindParam(":customer_phone", $this->customer_phone);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function readOne() {
        $query = "SELECT user_id, customer_name, customer_phone, total_amount, status FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->user_id = $row['user_id'];
            $this->customer_name = $row['customer_name'];
            $this->customer_phone = $row['customer_phone'];
            $this->total_amount = $row['total_amount'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET customer_name=:customer_name, customer_phone=:customer_phone, total_amount=:total_amount, status=:status WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":customer_name", $this->customer_name);
        $stmt->bindParam(":customer_phone", $this->customer_phone);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " SET status=:status WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
