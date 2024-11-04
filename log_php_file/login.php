<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


// Database connection settings
$host = 'localhost';
$dbname = 'my_database';
$username = 'root';
$password = 'root';
    
try {
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve the JSON data sent in the request
    $data = json_decode(file_get_contents("php://input"));

    // Check if username and password fields are set
    if (isset($data->username) && isset($data->password)) {
        $username = $data->username;
        $password = $data->password;

        // Query to get the user by username
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        // Check if the user exists
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password (assuming password is hashed)
            if (password_verify($password, $user['password'])) {
                // Send success response with user data
                echo json_encode([
                    "status" => "success",
                    "message" => "Login successful",
                    "data" => [
                        "id" => $user['id'],
                        "username" => $user['username'],
                        "email" => $user['email']
                    ]
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid password"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "User not found"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Username and password required"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $e->getMessage()]);
}

?>
