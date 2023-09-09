<?php

$file = file('goroda.txt');
$cityList = [];
foreach ($file as $row) {
    $city = trim($row);
    if ($city != ''){ 
    $cityList[] = $city;
    }

}
connectToMySQL($cityList);
function connectToMySQL($array) {
    $servername = "localhost"; // Имя сервера
    $username = "root"; // Ваше имя пользователя
    $password = "King_S-68"; // Ваш пароль
    $dbname = "cities_schema"; // Имя вашей базы данных

    // Создание подключения
    $connection = new mysqli($servername, $username, $password, $dbname);

    // Проверка подключения
    if ($connection->connect_error) {
        die("Ошибка подключения: " . $connection->connect_error);
    }

    echo "Успешное подключение к базе данных";
    createDataBase($array, $connection);
}

function createDataBase($array, $connection) {
    // SQL-запрос для выборки данных
    for ($i = 0; $i <= count($array); $i++) {
        $cityName = $array[$i]; // Произвольное имя города
        $sql = "INSERT INTO cities_table (name) VALUES ('$cityName')"; // name_table название таблицы, а name название столбца куда вносим значения
        if ($connection->query($sql) !== TRUE) {
            echo "Ошибка при добавлении данных: " . $connection->error;
        }
    }

    // Закрытие подключения
    $connection->close();
}
?>