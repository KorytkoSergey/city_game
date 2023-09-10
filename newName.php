<?php
// Создание подключения
$connection = new mysqli($servername, $username, $password, $dbname);
// Проверка подключения
if ($connection->connect_error) {
    die("Ошибка подключения: " . $connection->connect_error);
}

echo 'Успешное подключение к базе данных' . "\n";

$city = readline(); // работаем с кириллицей
$sql = "SELECT name FROM cities_table where name = '$city'";
$result = $connection->query($sql);
if ($result === false) {
    die("Ошибка выполнения запроса: " . $connection->error);
}
if ($result->num_rows === 0) { // если запрос пустой, то добавляем
    $append = "INSERT INTO new_name (name) VALUES ('$city')";
    echo 'О бля, а мы и не знали';
    if ($connection->query($append) !== TRUE) {
        echo "Ошибка при добавлении данных: " . $connection->error;
    }
}
else {  // иначе сообщаем, что данное слово есть
    echo 'О бля, а мы и знали';
}
