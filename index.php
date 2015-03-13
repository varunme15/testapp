<?php

require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

use \Slim\Slim;

$app = new Slim();

#Routes
$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";
});
$app->get('/getitems', 'getItems');
$app->post('/saveitems', 'saveItems');
$app->put('/updateitems/:user_id', 'updateItems');
$app->delete('/deleteitems/:user_id', 'deleteItems');

$app->run();


function getItems() {
    $sql = "select * FROM todos_table WHERE user_id=1 LIMIT 1";
    try {
        $db = getConnection();
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        echo '{"todos": ' . json_encode($data) . '}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}'; 
    }
}



#function for add items
function saveItems() {
    $request = Slim::getInstance()->request();
    $data = json_decode($request->getBody());
   
    $sql = "INSERT INTO todos_table (user_id, todo_items) VALUES (:user_id, :todo_items)";
    try {
        $user_id = 6;
        $db = getConnection();
        $stmt = $db->prepare($sql);  
        $stmt->bindParam("user_id", $user_id);
        $stmt->bindParam("todo_items", $data->todos);
        $stmt->execute();
        $id = $db->lastInsertId();
        $db = null;
        error_log($request->getBody(),3,'/var/tmp/php.log');
        echo json_encode($id);
    } catch(PDOException $e) {
       // error_log($e->getMessage(), 3, '/var/tmp/php.log');
        echo '{"error":{"text":'. $e->getMessage() .'}}'; 
    }
}

function updateItems($user_id) {
    //$request = Slim::getInstance()->request();
    $app = new Slim();
    $body = $app -> request->getBody();
    $data = json_decode($body);
    $sql = "UPDATE todos_table SET todo_items=:todo_items WHERE user_id=:user_id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);  
        $stmt->bindParam("todo_items", $data->todos);
	//error_log($data, 3, '/var/tmp/php.log');
        $stmt->bindParam("user_id", $user_id);
        $stmt->execute();
        $db = null;
        echo json_encode($data); 
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}'; 
    }
}

function deleteItems($user_id){

$sql = "DELETE FROM todos_table WHERE user_id=:user_id";
   try {
 	
	$db = getConnection();
        $stmt = $db->prepare($sql);
	$stmt->bindParam("user_id", $user_id);
	$stmt->execute();
	$db = null;
	echo true;
      } catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}';
      }

}

#DB connection function
function getConnection() {
  $dbhost="127.0.0.1";
  $dbuser="root";
  $dbpass="dk";
  $dbname="todos";
  $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);  
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  return $dbh;
}

?>
