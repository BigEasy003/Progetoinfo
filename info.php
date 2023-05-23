<?php

$api_key = 'sk_5138bd1aa4bf41628a15cd47dcd75e47';

$default_tickers = ['AAPL', 'GOOGL', 'MSFT', 'AMZN', 'JPM', 'V', 'MA', 'JNJ'];

$max_stocks = 10;

$base_url = 'https://cloud.iexapis.com/stable/stock/';
$fields = 'quote/latestPrice'; 
if (isset($_SESSION['checkbox_selected'])) {
    $checkbox_selected = $_SESSION['checkbox_selected'];
} else {
    $checkbox_selected = false;
}

function getStockData($ticker) {
    global $api_key, $base_url;
    
    $url = $base_url . $ticker . '/batch?types=quote,stats,chart&range=5d&token=' . $api_key;
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (isset($data['quote']) && isset($data['stats']) && isset($data['chart'])) {
        $stats = $data['stats'];
        $chart = $data['chart'];

        $profitMargin = isset($stats['profitMargin']) ? $stats['profitMargin'] : 'N/A';

        return [
            'ticker' => $ticker,
            'latestPrice' => $data['quote']['latestPrice'],
            'changePercent' => $data['quote']['changePercent'],
            'marketCap' => $data['quote']['marketCap'],
            'profitMargin' => $profitMargin,
            'chart' => $chart
        ];
    }
    
    return null;
}

$tickers = isset($_GET['tickers']) ? $_GET['tickers'] : $default_tickers;
if (!is_array($tickers)) {
    $tickers = [$tickers];
}

if (count($tickers) > $max_stocks) {
    $tickers = array_slice($tickers, 0, $max_stocks);
}

function generateStockChart($ticker, $chart) {
    $labels = [];
    $prices = [];

    foreach ($chart as $data) {
        $labels[] = $data['label'];
        $prices[] = $data['close'];
    }

    echo '<h2>' . $ticker . '</h2>';
    echo '<canvas id="' . $ticker . 'Chart"></canvas>';

    echo '<script>';
    echo 'var ' . $ticker . 'Chart = new Chart(document.getElementById("' . $ticker . 'Chart"), {';
    echo '    type: "line",';
    echo '    data: {';
    echo '        labels: ' . json_encode($labels) . ',';
    echo '        datasets: [{';
    echo '            label: "Prezzo di chiusura",';
    echo '            data: ' . json_encode($prices) . ',';
    echo '            fill: false,';
    echo '            borderColor: "rgb(75, 192, 192)",';
    echo '            tension: 0.1';
    echo '        }]';
    echo '    },';
    echo '    options: {}';
    echo '});';
    echo '</script>';
}

function displayStockData($data) {
    echo '<h2>' . $data['ticker'] . '</h2>';
    echo 'Prezzo più recente: ' . $data['latestPrice'] . '<br>';
    echo 'Variazione percentuale: ' . $data['changePercent'] . '%<br>';
    echo 'Capitalizzazione di mercato: ' . $data['marketCap'] . '<br>';
    echo '<br>';
    
    echo '<label>';
    echo '<input type="checkbox" id="' . $data['ticker'] . '" onchange="handleCheckboxChange(this)">';
    echo 'Aggiungi preferiti';
    echo '</label>';
    
    
}
if (isset($_GET['action_checkbox'])) {
    $ticker = $_GET['action_checkbox'];
    $isChecked = isset($_GET['action_checkbox']) && $_GET['action_checkbox'] === $ticker;
    
    if ($isChecked) {
        $db_host = 'localhost';
        $db_username = 'root';
        $db_password = '';
        $db_name = 'finanza';

        $conn = new mysqli($db_host, $db_username, $db_password, $db_name);
        if ($conn->connect_error) {
            die('Connessione al database fallita: ' . $conn->connect_error);
        }

        $stmt = $conn->prepare("INSERT INTO preferiti (ticker, userName) VALUES (?, ?)");
        $stmt->bind_param("ss", $ticker, $_SESSION['username']);

        if ($stmt->execute()) {
            echo 'Dati inseriti con successo nel database.';
        } else {
            echo 'Errore durante l\'inserimento dei dati nel database.';
        }

        $stmt->close();
        $conn->close();
    } else {

        $db_host = 'localhost';
        $db_username = 'root';
        $db_password = '';
        $db_name = 'finanza';

        $conn = new mysqli($db_host, $db_username, $db_password, $db_name);
        if ($conn->connect_error) {
            die('Connessione al database fallita: ' . $conn->connect_error);
        }

        $stmt = $conn->prepare("DELETE FROM preferiti WHERE ticker = ? AND userName = ?");
        $stmt->bind_param("ss", $ticker, $_SESSION['userName']);

        if ($stmt->execute()) {
            echo 'Dati rimossi con successo dalla tabella dei preferiti.';
        } else {
            echo 'Errore durante la rimozione dei dati dalla tabella dei preferiti.';
        }

        $stmt->close();
        $conn->close();
    }
}



?>

<!DOCTYPE html>
<html>
<head>
    <title>Dati finanziari</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container {
            display: flex;
        }

        .stock-card {
            width: 300px;
            margin: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        canvas {
            max-width: 100%;
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
        .stock-section {
            flex: 1;
        }
        
        .stock-column {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
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
            <a href="preferiti.php">Preferiti</a>
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
            <h1 >Dati finanziari</h1>
            
            <form action="" method="GET">
                <label for="tickers">Ticker symbols (separati da virgola):</label>
                <input type="text" id="tickers" name="tickers" value="<?php echo implode(',', $tickers); ?>">
                <button type="submit">Aggiorna</button>
            </form>
            
            <div class="stock-section">
                <div class="stock-column">
                    <?php
                    $tickers_top = array_slice($tickers, 0, 4);

                    foreach ($tickers_top as $ticker) {
                        $stockData = getStockData($ticker);

                        if ($stockData) {
                            echo '<div class="stock-card">';
                            generateStockChart($ticker, $stockData['chart']);
                            displayStockData($stockData);
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="stock-section">
                <div class="stock-column">
                    <?php
                    $tickers_bottom = array_slice($tickers, 4);

                    foreach ($tickers_bottom as $ticker) {
                        $stockData = getStockData($ticker);

                        if ($stockData) {
                            echo '<div class="stock-card">';
                            generateStockChart($ticker, $stockData['chart']);
                            displayStockData($stockData);
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>
  function handleCheckboxClick(checkbox) {
    var isChecked = checkbox.checked;

    var ticker = checkbox.id;
    var userName = getUserName(); 

    var checkboxState = isChecked ? "checked" : "unchecked";
    setCookie(ticker, checkboxState, 365, userName);

    var nomeFinanza = ticker.toUpperCase();

    var formData = new FormData();
    formData.append('ticker', ticker);
    formData.append('action', isChecked ? 'add' : 'remove');

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'azioni.php', true);
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          console.log(xhr.responseText);
        } else {
          console.error("Errore durante la richiesta: " + xhr.status);
        }
      }
    };

    xhr.send(formData);
  }

  function setCookie(name, value, days, userName) {
    var cookieName = name + "_" + userName;
    var expires = "";
    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
      expires = "; expires=" + date.toUTCString();
    }
    document.cookie = cookieName + "=" + (value || "") + expires + "; path=/";
  }

  function getCookie(name, userName) {
    var cookieName = name + "_" + userName;
    var nameEQ = cookieName + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) === ' ') c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  }

  function getUserName() {
    return "<?php echo $_SESSION['userName']; ?>";
  }

  window.onload = function() {
    var checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(function(checkbox) {
      checkbox.addEventListener("click", function(event) {
        handleCheckboxClick(event.target);
      });

      var checkboxId = checkbox.id;
      var userName = getUserName(); // Ottieni il nome utente dalla sessione o da un'altra fonte
      var checkboxState = getCookie(checkboxId, userName);

      if (checkboxState === "checked") {
        checkbox.checked = true;
      } else {
        checkbox.checked = false;
      }
    });
  };
</script>





</body>
</html>
