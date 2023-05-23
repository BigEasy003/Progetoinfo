<?php
session_start();

if (!isset($_SESSION['userName'])) {
    echo "Errore: Utente non autenticato.";
    exit;
}

if (!isset($_POST['created_at']) || empty($_POST['created_at'])) {
    var_dump($_POST['created_at']); 

    echo "Errore: Data e ora di investimento non valide.";
    exit;
}

$investmentDateTime = $_POST['created_at'];

$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'finanza';

$conn = new mysqli($db_host, $db_username, $db_password, $db_name);
if ($conn->connect_error) {
    die('Connessione al database fallita: ' . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT * FROM investimenti WHERE created_at = ? AND userName = ?");
$stmt->bind_param("ss", $investmentDateTime, $_SESSION['userName']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Errore: Investimento non trovato.";
    exit;
}

$row = $result->fetch_assoc();
$ticker = $row['ticker'];
$purchasePrice = $row['purchasePrice'];
$quantity = $row['quantity'];

$apiUrl = 'https://cloud.iexapis.com/stable/stock/' . $ticker . '/price?token=sk_5138bd1aa4bf41628a15cd47dcd75e47'; 

$currentPrice = getCurrentPrice($apiUrl); 

$actualProfit = ($currentPrice - ($currentPrice * 0.002)) * $quantity;

$userName = $_SESSION['userName'];
$selectUserStmt = $conn->prepare("SELECT saldo FROM saldo_utenti WHERE userName = ?");
$selectUserStmt->bind_param("s", $userName);
$selectUserStmt->execute();
$userResult = $selectUserStmt->get_result();

if ($userResult->num_rows === 0) {
    echo "Errore: Utente non trovato.";
    exit;
}

$userRow = $userResult->fetch_assoc();
$currentBalance = $userRow['saldo'];
$updatedBalance = $currentBalance + $actualProfit;

$updateBalanceStmt = $conn->prepare("UPDATE saldo_utenti SET saldo = ? WHERE userName = ?");
$updateBalanceStmt->bind_param("ds", $updatedBalance, $userName);
$updateBalanceStmt->execute();

$deleteStmt = $conn->prepare("DELETE FROM investimenti WHERE created_at = ?");
$deleteStmt->bind_param("s", $investmentDateTime);
$deleteStmt->execute();

echo "Investimento chiuso";

$stmt->close();
$selectUserStmt->close();
$updateBalanceStmt->close();
$deleteStmt->close();
$conn->close();

function getCurrentPrice($apiUrl) {
    $response = file_get_contents($apiUrl); 
    return $response;
}
?>
