<?php
// Start session kung hindi pa naka-start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ✅ BASIC LOGGER FUNCTION
 * Ginagamit para mag-insert ng logs manually
 */
function addLog($conn, $action, $description){

    try {
        // Kunin user_id kung meron (else NULL = system)
        $user_id = $_SESSION['user_id'] ?? null;

        $sql = "INSERT INTO logs (user_id, action, description, timestamp)
                VALUES (:user_id, :action, :description, NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':user_id'     => $user_id,
            ':action'      => $action,
            ':description' => $description
        ]);

    } catch (PDOException $e) {
        // OPTIONAL: pwede mo i-log error sa file instead
        error_log("Log Error: " . $e->getMessage());
    }
}


/**
 * 🚀 AUTO LOGGER (WRAPPER)
 * Auto magla-log kapag successful ang query
 */
function runQuery($conn, $sql, $params = [], $action = '', $description = ''){

    try {
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute($params);

        // Kung success at may action → mag log
        if($success && $action !== ''){
            addLog($conn, $action, $description);
        }

        return $stmt;

    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        return false;
    }
}


/**
 * 🔐 OPTIONAL: AUTO LOG FOR LOGIN
 */
function logLogin($conn, $username){
    addLog($conn, "Login", "User logged in: $username");
}


/**
 * 🔐 OPTIONAL: AUTO LOG FOR LOGOUT
 */
function logLogout($conn){
    addLog($conn, "Logout", "User logged out");
}


/**
 * ⚠️ OPTIONAL: SYSTEM LOG (no user)
 */
function systemLog($conn, $action, $description){
    try {
        $sql = "INSERT INTO logs (user_id, action, description, timestamp)
                VALUES (NULL, :action, :description, NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':action'      => $action,
            ':description' => $description
        ]);

    } catch (PDOException $e) {
        error_log("System Log Error: " . $e->getMessage());
    }
}
?>