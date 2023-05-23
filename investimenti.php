<!DOCTYPE html>
<html>
<head>
    <title>Simulatore di investimenti</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#ticker').change(function() {
                var ticker = $(this).val();

                $.ajax({
                    url: 'get_stock_price.php',
                    type: 'GET',
                    data: { ticker: ticker },
                    success: function(response) {
                        $('#purchase_price').val(response);
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });
            });

            setTimeout(function() {
                $('#message-container').fadeOut();
            }, 7000);
        });
    </script>
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

        select, input[type="number"] {
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
        .sidebar a {
            display: block;
            margin-bottom: 10px;
            text-decoration: none;
            color: #333;
            padding: 8px;
            border-radius: 5px;
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

        .error-message {
    color: red;
    font-weight: bold;
    text-align: center;
    margin-top: 10px;
}

        #message-container {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            display: none;
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
<div id="message-container">
    <?php
    session_start();
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $messageClass = $_SESSION['messageClass'];

        echo '<div class="' . $messageClass . '">' . $message . '</div>';

        unset($_SESSION['message']);
        unset($_SESSION['messageClass']);
    }
    ?>
</div>
        <div class="sidebar">
    <div class="session-name">
    <?php
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
    <a href="info.php">Lista Finanze</a>
    <br>
    <a href="preferiti.php">Preferiti</a>
    <br>
    <a href="soldi.php">Aggiungi fondi</a>
    <br>
    <a href="visualizza_investimenti.php">I miei Investimenti</a>
    
    <br>
    <a href="index.php">Logout</a>
        </div>

<h1>Simulatore di investimenti</h1>

<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <label for="ticker">Finanza:</label>
    <select id="ticker" name="ticker" required>
        <option value="">Seleziona una finanza</option>
        <?php
        $db_host = 'localhost';
        $db_username = 'root';
        $db_password = '';
        $db_name = 'finanza';

        $conn = new mysqli($db_host, $db_username, $db_password, $db_name);
        if ($conn->connect_error) {
            die('Connessione al database fallita: ' . $conn->connect_error);
        }

        $stmt = $conn->prepare("SELECT DISTINCT ticker FROM preferiti WHERE userName = ?");
        $stmt->bind_param("s", $_SESSION['userName']);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $ticker = $row['ticker'];
                echo '<option value="' . $ticker . '">' . $ticker . '</option>';
            }
        } else {
            echo 'Errore durante l\'esecuzione dell\'istruzione SQL.';
        }

        $stmt->close();
        $conn->close();
        ?>
    </select><br>

    <label for="quantity">Quantità:</label>
    <input type="number" id="quantity" name="quantity" required><br>

    <label for="purchase_price">Prezzo di acquisto:</label>
    <input type="number" step="0.01" id="purchase_price" name="purchase_price" readonly required><br>

    <input type="submit" value="Investi">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ticker = $_POST['ticker'];
    $quantity = $_POST['quantity'];
    $purchasePrice = $_POST['purchase_price'];

    $db_host = 'localhost';
    $db_username = 'root';
    $db_password = '';
    $db_name = 'finanza';

    $conn = new mysqli($db_host, $db_username, $db_password, $db_name);
    if ($conn->connect_error) {
        die('Connessione al database fallita: ' . $conn->connect_error);
    }

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
            echo '<div class="success-message">Investimento effettuato con successo.</div>';
        } else {
            echo '<div class="error-message">Errore durante l\'investimento.</div>';
        }

        $stmt->close();
    } else {
        echo '<div class="error-message">Saldo insufficiente per effettuare l\'investimento.</div>';
    }

    $conn->close();
}
?>
</body>
</html>
