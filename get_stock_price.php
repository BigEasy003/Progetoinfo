<?php
if (isset($_GET['ticker'])) {
    $ticker = $_GET['ticker'];

    // Effettua una richiesta all'API IEX Cloud per ottenere il prezzo corrente
    $api_key = 'sk_5138bd1aa4bf41628a15cd47dcd75e47';  // Sostituisci con il tuo token API IEX Cloud
    $api_url = "https://cloud.iexapis.com/stable/stock/$ticker/quote/latestPrice?token=$api_key";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    // Restituisci il prezzo come risposta
    echo $response;
}
?>
