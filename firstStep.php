<?php
function firstStep() {
    echo 'Me - твой ход, You - мой ход, Random - случайный выбор' . "\n";
    $mark = 0; // 0 - рандом, 1 - ход игрока, 2 - ход компьютера
    $decision = fgets(STDIN);  // возможно это не нужно, будет приниматься от кнопки
    if ($decision == 'Me') {
        echo 'Ты начинаешь';
        $mark = 1;
        // nameFunction($mark);  // передаем нужной функции флаг с ходом игрока или компьютера
    }
    elseif($decision == 'You'){
        echo 'Первый ход за копмпьютером';
        $mark = 2;
        // nameFunction($mark);
    }
    elseif($decision = 'Random'){
        $mark = rand(1, 2);
        if ($mark == 1){
            echo 'Ты начинаешь';
        // nameFunction($mark);
        }
        else {
            echo 'Первый ход за копмпьютером';
            // nameFunction($mark);
        }
    }
}
firstStep();
?>