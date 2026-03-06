<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

require_once __DIR__ . '/../backend/database.php';

// Get action from query param or post body
$action = $_GET['action'] ?? $_POST['action'] ?? null;
$db;
try {
    if ($db==null){
        $db = new Database();
    }
    if ($action === "insertitem" && $_SERVER['REQUEST_METHOD'] === 'POST'){
        $inputData = json_decode(file_get_contents('php://input'), true);
        $message = '';
        if (!isset($inputData['name']) || empty(trim($inputData['name'])) || !isset($inputData['price']) || empty(trim($inputData['price']))) { // Validate incoming data
            throw new Exception('Missing or empty required field.');
        }
        $name = trim($inputData['name']);
        $price = $inputData['price'];
        $checkQuery = "SELECT * FROM items WHERE name = $1 and price = $2"; // Check if this item was already inputted
        $existingitem = $db->query($checkQuery, $name, $price);
        if ($existingitem && count($existingitem) > 0) {
            $item = $existingitem[0];
            $message = 'item already in database';
        }
        else{
            $insertQuery = "INSERT INTO items (name, price) VALUES ($1, $2) RETURNING item_id, name, price";
            $insertResult = $db->query($insertQuery, $name, $price);
            if (!$insertResult || count($insertResult) === 0) {
                    throw new Exception('Failed to insert item into "items" table.');
            }
            $item = $insertResult[0];
            $message = 'added new item';
        }
        echo json_encode([
            'result' => 'success',
            'message' ->$message,
            'data' => [
                'id' => $item['item_id'],
                'name' => $item['name'],
                'price' => $item['price'],
            ]
        ]);
    }elseif ($action==="addUserToitem" && $_SERVER['REQUEST_METHOD'] === 'POST'){
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['item_name']) || empty(trim($data['item_name']))) { // Validate incoming data
                throw new Exception('Missing or empty required field: item name.');
            }
            if (!isset($data['user']) || empty(trim($data['user']))) { // Validate incoming data
                throw new Exception('Missing or empty required field: user.');
            }
            $name = trim($data['item_name']);
            $user = trim($data['user']);
            $user_id = get_user_id($user);
            $item_id = get_item_id($name);
            $selectQuery = "SELECT * FROM items_used WHERE user_id = $1 and item_id = $2";
            $selectResult = $db->query($selectQuery,$user_id, $item_id);
            if (!$selectResult || count($selectResult) === 0) {
                $insertQuery = "INSERT INTO items_used (user_id, item_id) VALUES ($1, $2) RETURNING item_id";
                $insertResult = $db->query($insertQuery, $user_id, $item_id);
                if (!$insertResult || count($insertResult) === 0) {
                        throw new Exception('Failed to add user to item.');
                }
                echo json_encode([
                    'result'=> 'success',
                    'message' => 'inserted user and item into table'
                ]);
            }
            else{
                echo json_encode([
                    'result' =>'success',
                    'message' => 'item and user was already in table'
                ]);
            }
    }elseif ($action==="deleteUserfromitem" && $_SERVER['REQUEST_METHOD'] === 'POST'){
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['item_name']) || empty(trim($data['item_name']))) { // Validate incoming data
            throw new Exception('Missing or empty required field: item name.');
        }
        if (!isset($data['user']) || empty(trim($data['user']))) { // Validate incoming data
            throw new Exception('Missing or empty required field: user.');
        }
        $name = trim($data['item_name']);
        $user = trim($data['user']);
        $user_id = get_user_id($user);
        $item_id = get_item_id($name);
        $selectQuery = "SELECT * FROM items_used WHERE user_id = $1 and item_id = $2";
        $selectResult = $db->query($selectQuery,$user_id, $item_id);
        if ($selectResult && count($selectResult) !== 0) {
            $deleteQuery = "DELETE FROM items_used WHERE user_id = $1 AND item_id = $2 RETURNING item_id";
            $deleteResult = $db->query($deleteQuery, $user_id, $item_id);
            if (!$deleteResult || count($deleteResult) === 0) {
                    throw new Exception('Failed to delete user and item.');
            }
            echo json_encode([
                'result'=> 'success',
                'message' => 'deleted user and item from table'
            ]);
        }
        else{
            echo json_encode([
                'result' =>'success',
                'message' => 'item and user was not in table'
            ]);
        }
    }

} catch (Exception $e) {
    // Catch and return error as JSON
    echo json_encode([
        'result' => 'error',
        'message' => $e->getMessage()
    ]);
}
//helper methods
function get_item_id($item_name){
    global $db;
    try{
        $result = $db->query("SELECT * from items WHERE name = $1", $item_name);
        if (!$result || count($result) === 0) {
            throw new Exception('Failed to get a item with that name.');
        }
        return $result[0]['item_id'];
    } catch (Exception $e) {
        echo json_encode([
            'result' => 'error',
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

function get_user_id($user_name){
    global $db;
    try{
        $result = $db->query("SELECT * from people WHERE name = $1", $user_name);
        if (!$result || count($result) === 0) {
            throw new Exception('Failed to get a user with that name.');
        }
        return $result[0]['user_id'];
    } catch (Exception $e) {
        echo json_encode([
            'result' => 'error',
            'message' => $e->getMessage()
        ]);
        exit;
    }
}
function get_group_id($group_name){
    global $db;
    try{
        $result = $db->query("SELECT * from groups_list WHERE name = $1", $group_name);
        if (!$result || count($result) === 0) {
            throw new Exception('Failed to get a group with that name.');
        }
        return $result[0]['group_id'];
    } catch (Exception $e) {
        echo json_encode([
            'result' => 'error',
            'message' => $e->getMessage()
        ]);
        exit;
    }
}
exit;
?>