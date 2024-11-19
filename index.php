<?php
require("phpMQTT-master/phpMQTT.php");

// MQTT configuration
$server = "broker.hivemq.com"; // Broker MQTT
$port = 1883;
$username = ""; // Optional (untuk broker public biasanya kosong)
$password = ""; // Optional
$client_id = "PHPSubscriber_" . uniqid(); // Unique client ID

// MQTT Topic
$topic = "esp32/potentiometer";

// File untuk menyimpan data
$dataFile = "data.json";

// Connect to MQTT broker
$mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);

if ($mqtt->connect(true, NULL, $username, $password)) {
    $mqtt->subscribe([$topic => ["qos" => 0, "function" => "processMessage"]]);
    while ($mqtt->proc()) {
    }
    $mqtt->close();
} else {
    echo "Failed to connect to MQTT broker.";
    exit(1);
}

// Process received MQTT message
function processMessage($topic, $msg) {
    global $dataFile;
    $data = json_decode($msg, true);

    // Add timestamp
    $data['timestamp'] = date('Y-m-d H:i:s');

    // Save data to JSON file
    file_put_contents($dataFile, json_encode($data));

    echo "Data saved: " . $msg . PHP_EOL;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Potentiometer Data</title>
    <style>
        table {
            border-collapse: collapse;
            width: 50%;
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #ddd;
            text-align: center;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Real-Time Potentiometer Data</h1>
    <table>
        <thead>
            <tr>
                <th>Potentiometer 1</th>
                <th>Potentiometer 2</th>
                <th>Potentiometer 3</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td id="pot1">-</td>
                <td id="pot2">-</td>
                <td id="pot3">-</td>
                <td id="timestamp">-</td>
            </tr>
        </tbody>
    </table>

    <script>
        // Function to fetch data from the server
        function fetchData() {
            fetch("index.php")
                .then(response => response.json())
                .then(data => {
                    // Update table values
                    document.getElementById("pot1").textContent = data.pot1;
                    document.getElementById("pot2").textContent = data.pot2;
                    document.getElementById("pot3").textContent = data.pot3;
                    document.getElementById("timestamp").textContent = data.timestamp;
                })
                .catch(error => console.error("Error fetching data:", error));
        }

        // Fetch data every second
        setInterval(fetchData, 1000);
    </script>
</body>
</html>
