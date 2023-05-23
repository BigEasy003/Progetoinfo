<?php
session_start();

if (!isset($_SESSION['userName'])) {
    header("Location: login.php");
    exit();
}

$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'finanza';

$conn = new mysqli($db_host, $db_username, $db_password, $db_name);
if ($conn->connect_error) {
    die('Connessione al database fallita: ' . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT ticker FROM preferiti WHERE userName = ?");
$stmt->bind_param("s", $_SESSION['userName']);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $preferenceTickers = [];
    while ($row = $result->fetch_assoc()) {
        $preferenceTickers[] = $row['ticker'];
    }
} else {
    echo 'Errore durante l\'esecuzione dell\'istruzione SQL.';
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Preferiti</title>
    <style>
        .container {
            display: flex;
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
        

        .content {
            flex: 1;
            padding: 20px;
        }

        .stock-table {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .stock-card {
            flex-basis: calc(33.33% - 20px);
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .stock-card-header {
            background-color: #f1f1f1;
            padding: 10px;
            font-weight: bold;
            border-bottom: 1px solid #ccc;
        }

        .stock-card-content {
            padding: 10px;
        }

        .stock-chart {
            margin-top: 10px;
            text-align: center;
        }

        canvas {
            max-width: 100%;
        }

        h1 {
            margin-bottom: 20px;
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
<div class="container">
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
            <a href="investimenti.php">Nuovo investimento</a>
            <br>
            <a href="soldi.php">Aggiungi fondi</a>
            <br>
            <a href="visualizza_investimenti.php">I miei Investimenti</a>
            <br>
            <a href="index.php">Logout</a>
        </div>
        <div class="content">
            <h1>Preferiti</h1>

            <div class="stock-section">
                <?php
                if (!empty($preferenceTickers)) {
                    echo '<div class="stock-table">';
                    
                    $chartDataArray = [];
                    
                    foreach ($preferenceTickers as $ticker) {
                        $api_key = 'sk_5138bd1aa4bf41628a15cd47dcd75e47';
                        $url = "https://cloud.iexapis.com/stable/stock/$ticker/quote?token=$api_key";
                        $financialData = file_get_contents($url);
                        $financialData = json_decode($financialData, true);

                        if ($financialData) {
                            $symbol = $financialData['symbol'];
                            $price = $financialData['latestPrice'];
                            $change = $financialData['change'];

                            echo '<div class="stock-card">';
                            echo '<div class="stock-card-header">' . $symbol . '</div>';
                            echo '<div class="stock-card-content">';
                            echo '<p>Prezzo: ' . $price . '</p>';
                            echo '<p>Variazione: ' . $change . '</p>';
                            echo '</div>';
                            echo '<div class="stock-chart">';
                            echo '<canvas id="stockChart-' . $ticker . '"></canvas>';
                            echo '</div>';
                            echo '</div>';

                            $chartUrl = "https://cloud.iexapis.com/stable/stock/$ticker/chart/5d?token=$api_key";
                            $chartData = file_get_contents($chartUrl);
                            $chartData = json_decode($chartData, true);

                            if ($chartData) {
                                $chartDataArray[$ticker] = $chartData;
                            }
                        }
                    }
                    echo '</div>';

                    foreach ($chartDataArray as $ticker => $chartData) {
                        $chartLabels = [];
                        $chartPrices = [];
                        foreach ($chartData as $chartItem) {
                            $chartLabels[] = $chartItem['label'];
                            $chartPrices[] = $chartItem['close'];
                        }

                        echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
                        echo '<script>';
                        echo 'var ctx = document.getElementById("stockChart-' . $ticker . '").getContext("2d");';
                        echo 'var stockChart = new Chart(ctx, {';
                        echo '    type: "line",';
                        echo '    data: {';
                        echo '        labels: ' . json_encode($chartLabels) . ',';
                        echo '        datasets: [{';
                        echo '            label: "Prezzo",';
                        echo '            data: ' . json_encode($chartPrices) . ',';
                        echo '            backgroundColor: "rgba(75, 192, 192, 0.2)",';
                        echo '            borderColor: "rgba(75, 192, 192, 1)",';
                        echo '            borderWidth: 1';
                        echo '        }]';
                        echo '    },';
                        echo '    options: {';
                        echo '        responsive: true,';
                        echo '        scales: {';
                        echo '            x: {';
                        echo '                display: true';
                        echo '            },';
                        echo '            y: {';
                        echo '                display: true';
                        echo '            }';
                        echo '        }';
                        echo '    }';
                        echo '});';
                        echo '</script>';
                    }
                } else {
                    echo 'Nessun titolo preferito.';
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
