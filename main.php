<?php
header('Content-Type: text/html; charset=utf-8');
function connectToDataBase() { // соединяемся с БД
    $servername = "localhost"; 
    $username = "root"; 
    $password = "King_S-68"; 
    $dbname = "cities_schema"; 

    // Создание подключения
    try {
        $connection = new mysqli($servername, $username, $password, $dbname);
    } catch (mysqli_sql_exception $e) {
        $time = getdate();
        $errorMessage = "{$time['mday']} {$time['month']} {$time['year']} - {$time['hours']}:{$time['minutes']}:{$time['seconds']} Ошибка подключения: " . $e->getMessage();
        die($errorMessage);
    }
    return $connection; // передача конекта в функция копирования списка

}

function copyTableCity($connection, $name) { // копируем таблицу в БД к которой присоединились
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
    $result = $connection->query($sql);
    if ($result === false) {
        die("Ошибка выполнения запроса: " . $connection->error);
    }
    if ($result->num_rows === 0) { // если запрос пустой, то добавляем
        $append = "INSERT INTO new_name (name) VALUES ('$fromPlayer')";
        echo "Это новое слово \n";
        if ($connection->query($append) !== TRUE) {
            echo "Ошибка при добавлении данных: " . $connection->error;
        }
    }
    else {  // иначе сообщаем, что данное слово есть
        echo "Такое уже есть \n";
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
            echo 'Первый ход за компьютером';
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
    if ($result -> num_rows == 0) {
        endGame();
    }
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
        $delSQL = "delete from $temporaryTable where name = '$nameCity'";
        $del = $connection->query($delSQL);
        if ($del === false) {
            die("Ошибка удаления записи: " . $connection->error);
        } else {
            echo "Запись успешно удалена. \n";
        }
    }
}

function endGame() {
    echo "Я не знаю больше городов. Вы победили! \n Выхотите начать сначала? Yes \ No \n";
    $restart = readline();
    if ($restart == 'Yes') {
        crossRoad();
    }
    else {
        die('До скорых встреч!!');
    }

}

function logGame($connection, $name, $status, $body){
    $date = date('Y-m-d H:i:s'); // дата формата год - месяц - день
    $remark = "INSERT INTO table_log(`date`, `name_player`, `status`, `body`) VALUES ('$date', '$name', '$status', '$body')";
    $result = $connection -> query($remark);
    if ($result === false) {
        die("Ошибка удаления записи: " . $connection->error);
    }
    echo "$status $date $name $body \n";
}

function crossRoad() {  // функция распределения (перекресток)
    echo 'Кто ты?' . "\n";
    $name = readline();
    echo "Привет, $name. Поиграем?" . "\n";
    $status = 'INFO';
    $connection = connectToDataBase();
    logGame($connection, $name, $status, "$name подключился");
    $temporaryTable = copyTableCity($connection, $name);
    logGame($connection, $name, $status, "Таблица для $name успешно создана");
    $chooseStep = firstStep();
    if ($chooseStep == 1) {
        logGame($connection, $name, $status, "Игру начинает $name");
        $flag = true;
        while ($flag){
            $fromPlayer = enterCity();
            logGame($connection, $name, $status, "$name выбирает $fromPlayer");
            $chr = mb_substr($fromPlayer, -1);
            newName($connection, $temporaryTable, $fromPlayer);
            delName($connection, $temporaryTable, $fromPlayer);
            $fromPC = answerTable($connection, $temporaryTable, $chr);
            logGame($connection, $name, $status, "Компьютер выбирает $fromPC");
            delName($connection, $temporaryTable, $fromPC);
        }

    }
    else {
        logGame($connection, $name, $status, "Игру начинает компьютер");
        $rusChr = 'абвгдеёжзиклмнопрстуфхцчшщ';
        $randomIndex = rand(0, mb_strlen($rusChr) - 1);
        $startChr = mb_substr($rusChr, $randomIndex, 1, 'UTF-8');
        $fromPC = answerTable($connection, $temporaryTable, $startChr);
        logGame($connection, $name, $status, "Компьютер выбирает $fromPC");
        delName($connection, $temporaryTable, $fromPC);
        $flag = true;
        while ($flag){
            $fromPlayer = enterCity();
            logGame($connection, $name, $status, "$name выбирает $fromPlayer");
            $chr = mb_substr($fromPlayer, -1);
            newName($connection, $temporaryTable, $fromPlayer);
            delName($connection, $temporaryTable, $$fromPlayer);
            $fromPC = answerTable($connection, $temporaryTable, $chr);
            logGame($connection, $name, $status, "Компьютер выбирает $fromPC");
            delName($connection, $temporaryTable, $fromPC);
        }

    }
}

crossRoad();
?>
