<?php
header('Content-Type: text/html; charset=utf-8');

// функция приветствия 
function greeting($connection){ 
    echo 'А кто ты?' . "\n";
    $name = readline();
    echo "Привет, $name. Поиграем?" . "\n";

    copyTableCity($connection, $name);
}

function connectToDataBase() { // соединяемся с БД
    $servername = "localhost"; 
    $username = "root"; 
    $password = "King_S-68"; 
    $dbname = "cities_schema"; 

    // Создание подключения
    $connection = new mysqli($servername, $username, $password, $dbname);

    // Проверка подключения
    if ($connection->connect_error) {
        die("Ошибка подключения: " . $connection->connect_error);
    }

    echo 'Успешное подключение к базе данных' . "\n";

    greeting($connection); // передача конекта в функция копирования списка

}

function copyTableCity($connection, $name) { // копирруем таблицу в БД к которой присоединились
    $tableName = 'table' . $name; // создаем имя таблицы смотрящее на игрока
    $citiesForGame = "CREATE TABLE $tableName AS SELECT * FROM cities_table"; // добавить идификатор под игрока, дл уникальной таблицы

    if ($connection->query($citiesForGame) === TRUE) {
        echo 'Таблица успешно скопирована!' . "\n";
    } else {
        echo "Ошибка при копировании таблицы: " . $connection->error;
    }
    enterCity($connection, $tableName);  // передаем нашу таблицу и возможно коннект 
}

// функция для ввода слова игроком
function enterCity($connection, $tableName) {
    echo 'Введите название города: ';
    $city = readline();  // readline лучше всех подходит для кириллицы, если ввод с консоли
    if (mb_strlen($city) <= 1){  // допускаем, что нет города из одной буквы
        echo 'Введите другое слово';
    }
    else {
    $city = mb_strtolower($city, 'UTF-8');  // Преобразовываем строку к нижнему регистру перед проверкой mb_ - с данной приставкой работаем для кириллицы
    $prevChar = '';
    $Count = 1;
    for ($i = 0; $i < mb_strlen($city, 'UTF-8'); $i++) {  // проверяем, чтобы не было трех одинаковых букв к ряду
        $currentChar = mb_substr($city, $i, 1, 'UTF-8');
        if ($currentChar === $prevChar) {  
            $Count++;
            if ($Count > 3) {
                echo 'Введите другое слово с не более чем тремя одинаковыми буквами подряд.';
                return;
            }
            } 
            else {
                $Count = 1;
            }
            $prevChar = $currentChar;  // собираем букву
        }
    if (preg_match('/^\pL+$/u', $city)) { // регулярное выражение, которое проверяет что в строке только буквы
        if (preg_match('/[А-Яа-яЁё]/u', $city)) {  // проверяем, что буквы на русском
            $stopLetters = ['ъ', 'ы', 'ь'];  // Проверяем первую букву на наличие в массиве стоп-символов
            if (in_array(mb_substr($city, 0, 1, 'UTF-8'), $stopLetters)) {
                print_r('содержит ъьы');
            }
            else {
                answerTable($connection, $tableName, mb_substr($city, -1));  // передаем коннект и последнюю букву слова
            } 
        }
        else {
            echo 'слово не на русском';
        }
    }
    else {
        echo 'слово содержит не буквы';
    }
    }
}

// функция для ответа из таблицы
function answerTable($connection, $tableName, $cityFromPlayer) {  // передаем коннект для SQL запроса
    $sql = "SELECT name FROM $tableName where name like '$cityFromPlayer%'";  // ищем в таблице город на Букву ввуденного города
    $result = $connection->query($sql);
    if ($result === false) {
        die("Ошибка выполнения запроса: " . $connection->error);
    }
    $fromTable = []; 
    while ($row = $result->fetch_row()) {
        $fromTable[] = $row[0]; // собираем массив городов на нужную букву
        }
    print_r($fromTable[array_rand($fromTable)]); // случайно определяем город для вывода
    }

connectToDataBase();
?>
