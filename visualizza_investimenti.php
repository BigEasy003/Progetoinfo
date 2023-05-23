<!DOCTYPE html>
<html>
<head>
    <title>Lista Investimenti</title>
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 200px;
            height: 100vh;
            background-color: #f1f1f1;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sidebar a {
            display: block;
            margin-bottom: 10px;
            text-decoration: none;
            color: #333;
            padding: 8px;
            border-radius: 5px;
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

        .investments {
            flex: 1;
            padding: 20px;
            box-sizing: border-box;
        }

        h1 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f1f1f1;
        }

        .withdraw-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 16px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin-top: 10px;
            cursor: pointer;
            border-radius: 4px;
        }

        .withdraw-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="container">
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
        <br>
        <a href="preferiti.php">Preferiti</a>
        <br>
        <a href="index.php">Logout</a>
        <br>
        <a href="info.php">Lista Finanze</a>
        <br>
        <a href="investimenti.php">Investimenti</a>
        <br>
        <a href="soldi.php">Aggiungi fondi</a>
        <br>
        <a href="index.php">Logout</a>
    </div>

    <div class="investments">
        <h1>Lista Investimenti</h1>

        <table>
            <tr>
                <th>Finanza</th>
                <th>Prezzo di acquisto</th>
                <th>Prezzo attuale</th>
                <th>Guadagno/Perdita attuale</th>
                <th>Guadagno effettivo</th>
                <th>Data di creazione</th>
                <th>Quantità</th>
                <th></th>
            </tr>
            <?php
            $db_host = 'localhost';
            $db_username = 'root';
            $db_password = '';
            $db_name = 'finanza';

            $conn = new mysqli($db_host, $db_username, $db_password, $db_name);
            if ($conn->connect_error) {
                die('Connessione al database fallita: ' . $conn->connect_error);
            }

            $stmt = $conn->prepare("SELECT * FROM investimenti WHERE userName = ?");
            $stmt->bind_param("s", $_SESSION['userName']);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $ticker = $row['ticker'];
                    $purchasePrice = $row['purchasePrice'];
                    $createdAt = $row['created_at'];
                    $quantita = $row['quantity'];

                    $apiUrl = 'https://cloud.iexapis.com/stable/stock/' . $ticker . '/price?token=sk_5138bd1aa4bf41628a15cd47dcd75e47'; 

                    $currentPrice = getCurrentPrice($apiUrl); 

                    $currentProfit = ($currentPrice - $purchasePrice);
                    $actualProfit = $currentProfit >= 0 ? $currentProfit * 0.8 : $currentProfit - ($currentProfit * 0.8);

                    echo '<tr>';
                    echo '<td>' . $ticker . '</td>';
                    echo '<td>' . $purchasePrice . '</td>';
                    echo '<td>' . $currentPrice . '</td>';
                    echo '<td>' . $currentProfit . '</td>';
                    echo '<td>' . $actualProfit . '</td>';
                    echo '<td>' . $createdAt . '</td>';
                    echo '<td>' . $quantita . '</td>';
                    
                    echo '<td><button class="withdraw-button" onclick="withdraw(\'' . $createdAt . '\')">Ritira soldi</button></td>';
                    echo '</tr>';
                }
            }

            $stmt->close();
            $conn->close();

            function getCurrentPrice($apiUrl) {
                $response = file_get_contents($apiUrl); 
                return $response;
            }
            ?>
        </table>
    </div>
</div>

<script>
    function withdraw(createdAt) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "withdraw.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = xhr.responseText;
                alert(response);
                location.reload();
            }
        };
        xhr.send('created_at=' + createdAt);
    }
</script>

</body>
</html>
