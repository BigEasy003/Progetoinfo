<?php
session_start();

// Verifica se sono stati inviati dati tramite il metodo POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupera i dati dal modulo
    $ticker = $_POST['ticker'];
    $quantity = $_POST['quantity'];
    $purchasePrice = $_POST['purchase_price'];

    // Connessione al database
    $db_host = 'localhost';
    $db_username = 'root';
    $db_password = '';
    $db_name = 'finanza';

    $conn = new mysqli($db_host, $db_username, $db_password, $db_name);
    if ($conn->connect_error) {
        die('Connessione al database fallita: ' . $conn->connect_error);
    }

    // Ottieni il saldo attuale dell'utente
    $stmt = $conn->prepare("SELECT saldo FROM saldo_utenti WHERE userName = ?");
    $stmt->bind_param("s", $_SESSION['userName']);
    $stmt->execute();
    $stmt->bind_result($saldoAttuale);
    $stmt->fetch();
    $stmt->close();

    $costoTotale = $quantity * $purchasePrice;

    if ($costoTotale <= $saldoAttuale) {
        $nuovoSaldo = $saldoAttuale - $costoTotale;
        $stmt = $conn->prepare("UPDATE saldo_utenti SET saldo = ? WHERE userName = ?");
        $stmt->bind_param("ds", $nuovoSaldo, $_SESSION['userName']);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO investimenti (userName, ticker, quantity, purchasePrice) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdd", $_SESSION['userName'], $ticker, $quantity, $purchasePrice);

        if ($stmt->execute()) {
            $_SESSION['message'] = 'Investimento effettuato con successo.';
            $_SESSION['messageClass'] = 'success-message';
        } else {
            $_SESSION['message'] = 'Errore durante l\'investimento.';
            $_SESSION['messageClass'] = 'error-message';
        }

        $stmt->close();
    } else {
        $_SESSION['message'] = 'Saldo insufficiente per effettuare l\'investimento.';
        $_SESSION['messageClass'] = 'error-message';
    }

    $conn->close();

    header("Location: investimenti.php");
    exit();
}
?>
