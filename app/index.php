<?php

use GuzzleHttp\Client;

require_once './vendor/autoload.php';

define('API_KEY', 'b23cd0fc188f94c4e2efa1345c14dc05');
define('BASE_URL', 'https://api.openweathermap.org/');

define('addresses', [
    'nukus' => [
        'lon' => 59.611906,
        'lat' => 42.439069,
    ],
    'shomanay' => [
        'lon' => 58.904895,
        'lat' => 42.703411,
    ],
    'xojeli' => [
        'lon' => 59.453057,
        'lat' => 42.412544,
    ]
]);
$region = isset(($_GET['region'])) ? $_GET['region'] : 'nukus';
$query = [
    'appid' => API_KEY,
    'lon' => addresses[$region]['lon'],
    'lat' => addresses[$region]['lat'],
    'units' => 'metric',
];

$client = new Client([
    'base_uri' => BASE_URL,
    'timeout' => 2.0,
]);
function getIcon($descriptions)
{
    $icons = [
        'sun' => [
            'clear sky',
        ],
        'cloud' => [
            'few clouds',
            'scattered clouds',
            'broken clouds',
            'overcast clouds',
        ],
        'cloud-rain' => [
            'shower rain',
            'rain',
            'thunderstorm',
        ],
        'cloud-snow' => [
            'snow',
        ],
        'mist' => [
            'mist',
        ]
    ];

    if (in_array($descriptions, $icons['sun'])) {
        $icon = 'sun';
    } elseif (in_array($descriptions, $icons['cloud'])) {
        $icon = 'cloud';
    } elseif (in_array($descriptions, $icons['cloud-rain'])) {
        $icon = 'cloud-rain';
    } elseif (in_array($descriptions, $icons['cloud-snow'])) {
        $icon = 'cloud-snow';
    } else {
        $icon = 'mist';
    }
    return $icon;
}
$data = $client->get('/data/2.5/weather', [
    'query' => http_build_query($query)
])->getBody();

$res = json_decode($data);

$days = json_decode($client->get('/data/2.5/forecast', [
    'query' => http_build_query($query)
])->getBody());
$dailyData = [];

foreach ($days->list as $item) {
    $date = strtotime($item->dt_txt);
    if (date('H:i', $date) == '12:00') {
        $dailyData[] = (object)[
            'day' => date('D', $date),
            'main' => $item->main,
            'icon' => getIcon($item->weather[0]->description),
            'weather' => $item->weather,
        ];
    }
}


// print_r($dailyData);
// exit;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Weather App</title>
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="./app/style.css">

</head>

<body>
    <!-- partial:index.partial.html -->
    <div class="container">
        <div class="weather-side">
            <div class="weather-gradient"></div>
            <div class="date-container">
                <h2 class="date-dayname"><?= date('l') ?></h2><span class="date-day"><?= date('d M Y') ?></span><i class="location-icon" data-feather="map-pin"></i><span class="location"><?= ucfirst($region) ?></span>
            </div>
            <div class="weather-container"><i class="weather-icon" data-feather="<?= getIcon($res->weather[0]->description) ?>"></i>
                <h1 class="weather-temp"><?= round($res->main->temp) ?>°C</h1>
                <h3 class="weather-desc"><?= ucfirst($res->weather[0]->description) ?></h3>
            </div>
        </div>
        <div class="info-side">
            <div class="today-info-container">
                <div class="today-info">
                    <div class="clear"></div>
                    <div class="humidity"> <span class="title">HUMIDITY</span><span class="value"><?= $res->main->humidity ?>%</span>
                        <div class="clear"></div>
                    </div>
                    <div class="wind"> <span class="title">WIND</span><span class="value"><?= $res->wind->speed ?>m/s</span>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
            <div class="week-container">
                <ul class="week-list">
                    <?php
                    foreach ($dailyData as $item) {
                    ?>
                        <li>
                            <i class="day-icon" data-feather="<?= $item->icon ?>"></i>
                            <span class="day-name"><?= $item->day ?></span>
                            <span class="day-temp"><?= round($item->main->temp) ?>°C</span>
                            <small class="day-temp"><?= '12:00' ?></small>
                        </li>
                    <?php
                    }
                    ?>
                    <div class="clear"></div>
                </ul>
            </div>
            <div class="location-container">
                <form action="index.php" method="post">
                    <select name="region">
                        <?php
                        foreach (addresses as $key => $address) {
                        ?>
                            <option value="<?= $key ?>">
                                <?= ucfirst($key) ?>
                            </option>
                        <?php
                        }
                        ?>
                    </select>
                    <input type="submit" value="submit">
                </form>
            </div>
        </div>
    </div>
    <script src="./app/script.js"></script>

</body>

</html>