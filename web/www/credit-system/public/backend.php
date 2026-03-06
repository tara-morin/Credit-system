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
    if ($action === "insertUser" && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $inputData = json_decode(file_get_contents('php://input'), true);
    
        if (!isset($inputData['name']) || empty(trim($inputData['name']))) {
            throw new Exception('Missing or empty required field: name.');
        }
        if (!isset($inputData['password']) || empty(trim($inputData['password']))) {
            throw new Exception('Missing or empty required field: password.');
        }
    
        $name = trim($inputData['name']);
        $pass = trim($inputData['password']);
    
        // Check if user exists
        $checkQuery = "SELECT * FROM people WHERE name = $1";
        $existingUser = $db->query($checkQuery, $name);
    
        if ($existingUser && count($existingUser) > 0) {
            // User exists, check password
            $passCheck = "SELECT * FROM people WHERE name = $1 AND password = $2";
            $passResult = $db->query($passCheck, $name, $pass);
    
            if (!$passResult || count($passResult) === 0) {
                throw new Exception('User found, but password incorrect.');
            }
    
            $user = $passResult[0];
            $message = 'logged in';
        } else {
            // Insert new user
            $insertQuery = "INSERT INTO people (name, password) VALUES ($1, $2) RETURNING user_id, name, password";
            $insertResult = $db->query($insertQuery, $name, $pass);
    
            if (!$insertResult || count($insertResult) === 0) {
                throw new Exception('SQL error: Failed to insert user into "people" table.');
            }
    
            $user = $insertResult[0];
            $message = 'new user created';
        }
    
        echo json_encode([
            'result' => 'success',
            'message' => $message,
            'data' => [
                'id' => $user['user_id'],
                'name' => $user['name'],
                'password' => $user['password'],
            ]
        ]);
    }elseif ($action ==="getAllitems"){
        $selectQuery = "SELECT name, price FROM items ORDER BY name ASC;";
        $selectResult = $db->query($selectQuery);
        if (!$selectResult || count($selectResult) === 0) {
            throw new Exception('Failed to getAllitems from "items" table.');
        }
        echo json_encode([
            'result' => 'success',
            'message'=>'got all items',
            'data' => $selectResult
        ]);
    }elseif ($action ==="checkUseranditem"){
        $inputData = json_decode(file_get_contents('php://input'), true);
        if (!isset($inputData['user_name']) || empty(trim($inputData['user_name']))) {
            throw new Exception('Missing or empty required field: user name.');
        }
        if (!isset($inputData['item_name']) || empty(trim($inputData['item_name']))) {
            throw new Exception('Missing or empty required field: item name.');
        }
        $user_id = get_user_id(trim($inputData['user_name']));
        $item_id = get_item_id(trim($inputData['item_name']));
        $selectQuery = "SELECT * FROM items_used WHERE user_id = $1 and item_id = $2";
        $selectResult = $db->query($selectQuery, $user_id, $item_id);
        if (!$selectResult || count($selectResult) === 0) {
            echo json_encode([
                'found' => 'false'
            ]);
        }
        else{
            echo json_encode([
                'found' => 'true'
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