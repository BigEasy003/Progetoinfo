<?php
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'finanza';

$conn = new mysqli($db_host, $db_username, $db_password, $db_name);
if ($conn->connect_error) {
    die('Connessione al database fallita: ' . $conn->connect_error);
}

session_start();
if (isset($_SESSION['userName'])) {
    $username = $_SESSION['userName'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ticker']) && isset($_POST['action'])) {
        $ticker = $_POST['ticker'];
        $action = $_POST['action'];

        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO preferiti (username, ticker) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $ticker);

            if ($stmt->execute()) {
                echo 'Finanza aggiunta ai preferiti con successo.';
            } else {
                echo 'Errore durante l\'aggiunta della finanza ai preferiti: ' . $stmt->error;
            }

            $stmt->close();
        } elseif ($action === 'remove') {
            $stmt = $conn->prepare("DELETE FROM preferiti WHERE username = ? AND ticker = ?");
            $stmt->bind_param("ss", $username, $ticker);

            if ($stmt->execute()) {
                echo 'Finanza rimossa dai preferiti con successo.';
            } else {
                echo 'Errore durante la rimozione della finanza dai preferiti: ' . $stmt->error;
            }

            $stmt->close();
        }
    } else {
        echo 'Parametri mancanti.';
    }
} else {
    echo 'Metodo non consentito.';
}

$conn->close();
?>