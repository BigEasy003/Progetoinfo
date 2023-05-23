<!DOCTYPE html>
<html>
<head>
    <title>Aggiungi fondi</title>
    <style>
        body {
        }

        h1 {
            text-align: center;
        }

        form {
            margin: 0 auto;
            width: 300px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="number"] {
            width: 100%;
            padding: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .sidebar a {
            display: block;
            margin-bottom: 10px;
            text-decoration: none;
            color: #333;
            padding: 8px;
            border-radius: 5px;
        }

        .sidebar {
            width: 200px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #f1f1f1;
            padding: 20px;
            box-sizing: border-box;
        }

        .sidebar h2 {
            margin-top: 0;
        }

        .success-message {
            color: green;
            font-weight: bold;
            text-align: center;
            margin-top: 10px;
        }
        .sidebar a:hover {
            background-color: #eaeaea;
        }
        .session-name {
            margin-top: 20px;
            font-weight: bold;
            text-align: center;
            color: #666;
        }
        .saldo-verde {
    color: green;
}
    </style>
</head>
<body>
<div class="sidebar">
    <div class="session-name">
    <?php
    session_start();
if (isset($_SESSION['userName'])) {
    echo "Benvenuto, " . $_SESSION['userName'];

    $conn = mysqli_connect("localhost", "root", "", "finanza");

    if (!$conn) {
        die("Connessione al database fallita: " . mysqli_connect_error());
    }

    $username = $_SESSION['userName'];

    $query = "SELECT saldo FROM saldo_utenti WHERE userName = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $saldo = $row['saldo'];

        echo "<br>";
        echo "<span class='saldo-verde'>$" . $saldo . "</span>";
    }

    mysqli_close($conn);
} else {
    echo "Benvenuto!";
}
?>
    </div>
    <br>
    <a href="preferiti.php">Preferiti</a>
    <br>
    <a href="investimenti.php">Nuovo Investimento</a>
    <br>
    <a href="info.php">Lista Finanze</a>
    <br>
    <a href="visualizza_investimenti.php">I miei Investimenti</a>
    <br>
    <a href="index.php">Logout</a>
</div>
<h1>Aggiungi fondi</h1>

<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <label for="amount">Importo:</label>
    <input type="number" step="0.01" id="amount" name="amount" required><br>

    <input type="submit" value="Aggiungi fondi">
</form>

<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "finanza";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST['amount'];

    $query = "UPDATE saldo_utenti SET saldo = saldo + $amount WHERE username = '{$_SESSION['userName']}'";

    if ($conn->query($query) === TRUE) {
        $query = "SELECT saldo FROM saldo_utenti WHERE username = '{$_SESSION['userName']}'";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $saldoAttuale = $row['saldo'];
            echo '<div class="success-message">I soldi sono stati ricaricati. Saldo attuale: ' . $saldoAttuale . '</div>';
        }
    } else {
        echo "Errore durante l'aggiunta dei fondi: " . $conn->error;
    }
}

$conn->close();
?>
</body>
</html>
