<?php
header('Content-Type: text/html; charset=utf-8');

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

    return $connection; // передача конекта в функция копирования списка

}

function greeting($connection){  // функция приветствия 

    echo 'А кто ты?' . "\n";
    $name = readline();
    echo "Привет, $name. Поиграем?" . "\n";

    return $name;
}

function copyTableCity($connection, $name) { // копирруем таблицу в БД к которой присоединились
    $tableName = $name . '_' . 'table'; // создаем имя таблицы смотрящее на игрока
    $citiesForGame = "CREATE TABLE $tableName AS SELECT * FROM cities_table"; // добавить идификатор под игрока, дл уникальной таблицы

    if ($connection->query($citiesForGame) === TRUE) {
        echo 'Таблица успешно скопирована!' . "\n";
    } else {
        echo "Ошибка при копировании таблицы: " . $connection->error;
    }
    return $tableName;  // передаем нашу таблицу и возможно коннект 
}

function newName($connection, $temporaryTable, $fromPlayer) {
    $sql = "SELECT name FROM $temporaryTable where name = '$fromPlayer'"; // сравниваем с таблицей игрока
    var_dump($fromPlayer);
    $result = $connection->query($sql);
    if ($result === false) {
        die("Ошибка выполнения запроса: " . $connection->error);
    }
    if ($result->num_rows === 0) { // если запрос пустой, то добавляем
        $append = "INSERT INTO new_name (name) VALUES ('$fromPlayer')";
        echo 'Это новое слово';
        if ($connection->query($append) !== TRUE) {
            echo "Ошибка при добавлении данных: " . $connection->error;
        }
    }
    else {  // иначе сообщаем, что данное слово есть
        echo 'Такое уже есть';
    }
}


function firstStep() {  // функция первого хода
    echo 'Me - твой ход, You - мой ход, Random - случайный выбор' . "\n";
    $mark = 0; // 0 - рандом, 1 - ход игрока, 2 - ход компьютера
    $decision = trim(fgets(STDIN));  // возможно это не нужно, будет приниматься от кнопки
    if ($decision == 'Me') {
        echo 'Ты начинаешь';
        $mark = 1;
        return $mark;  // передаем нужной функции флаг с ходом игрока или компьютера
    }
    elseif($decision == 'You'){
        echo 'Первый ход за копмпьютером';
        $mark = 2;
        return $mark;
    }
    elseif($decision == 'Random'){
        $mark = rand(1, 2);
        if ($mark == 1){
            echo 'Ты начинаешь';
            return $mark;
        }
        else {
            echo 'Первый ход за копмпьютером';
            return $mark;
        }
    }
}

function enterCity() {  // функция для ввода слова игроком
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
                enterCity();
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
                return $city;  // передаем коннект и последнюю букву слова
            } 
        }
        else {
            echo 'слово не на русском';
            enterCity();
        }
    }
    else {
        echo 'слово содержит не буквы';
        enterCity();
    }
    }
}

// функция для ответа из таблицы
function answerTable($connection, $tableName, $cityFromPlayer) {  // передаем коннект для SQL запроса
    $sql = "SELECT name FROM $tableName where name like '$cityFromPlayer%'";  // ищем в таблице город на Букву введенного города
    $result = $connection->query($sql);
    if ($result === false) {
        die("Ошибка выполнения запроса: " . $connection->error);
    }
    $fromTable = []; 
    while ($row = $result->fetch_row()) {
        $fromTable[] = $row[0]; // собираем массив городов на нужную букву
        }
    $fromPC = $fromTable[array_rand($fromTable)]; // случайно определяем город для вывода
    print_r ($fromPC);
    return $fromPC;
    }

function delName($connection, $temporaryTable, $nameCity) {
    $sql = "SELECT name FROM $temporaryTable where name = '$nameCity'"; 
    $result = $connection->query($sql);
    if ($result === false) {
        die("Ошибка выполнения запроса: " . $connection->error);
    }
    if ($result->num_rows != 0) {
        $del = "delete from $temporaryTable where name = '$nameCity'";
    }
}

function crossRoad() {  // функция распределения (перекресток)
    $connection = connectToDataBase();
    $namePlayer = greeting($connection);
    $temporaryTable = copyTableCity($connection, $namePlayer);
    $chooseStep = firstStep();
    if ($chooseStep == 1) {
        $flag = true;
        while ($flag){
            $fromPlayer = enterCity();
            $chr = mb_substr($fromPlayer, -1);
            newName($connection, $temporaryTable, $fromPlayer);
            delName($connection, $temporaryTable, $fromPlayer);
            $fromPC = answerTable($connection, $temporaryTable, $chr);
            delName($connection, $temporaryTable, $fromPC);
        }

    }
    else {
        $rusChr = 'абвгдеёжзиклмнопрстуфхцчшщ';
        $randomIndex = rand(0, mb_strlen($rusChr) - 1);
        $startChr = mb_substr($rusChr, $randomIndex, 1, 'UTF-8');
        $fromPC = answerTable($connection, $temporaryTable, $startChr);
        delName($connection, $temporaryTable, $fromPC);
        $flag = true;
        while ($flag){
            $fromPlayer = enterCity();
            $chr = mb_substr($fromPlayer, -1);
            newName($connection, $temporaryTable, $fromPlayer);
            delName($connection, $temporaryTable, $$fromPlayer);
            $fromPC = answerTable($connection, $temporaryTable, $chr);
            delName($connection, $temporaryTable, $fromPC);
        }

    }
}

crossRoad();
?>
