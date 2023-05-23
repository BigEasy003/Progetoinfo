<?php
// Funzione per recuperare il prezzo attuale dalla API
function getCurrentPrice($apiUrl) {
    $response = file_get_contents($apiUrl); 
    $data = json_decode($response, true); 

    if (isset($data['currentPrice'])) {
        return $data['currentPrice'];
    } else {
        return 0; 
    }
}
?>
