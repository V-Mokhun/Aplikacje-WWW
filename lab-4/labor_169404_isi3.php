<?php

$nr_indeksu = '169404';
$nrGrupy = '3';
echo "Volodymyr Mokhun $nr_indeksu grupa $nrGrupy <br /><br />";

echo "a) Metoda include() i require_once():<br />";
include 'vars.php';
echo "vars.php: $var1 <br />";
require_once 'config.php';
echo "config.php: $db_host <br />";
echo "<br /><br />";

echo "b) Warunki if, else, elseif, switch:<br />";
$age = 20;
if ($age < 18) {
    echo "Niepełnoletni";
} elseif ($age >= 18 && $age < 65) {
    echo "Dorosły";
} else {
    echo "Senior";
}

$day = date('l');
switch ($day) {
    case 'Monday':
        echo "<br />Początek tygodnia";
        break;
    case 'Friday':
        echo "<br />Koniec tygodnia";
        break;
    default:
        echo "<br />Środek tygodnia";
}
echo "<br /><br />";

echo "c) Pętla while() i for():<br />";
$i = 1;
while ($i <= 5) {
    echo "$i ";
    $i++;
}
echo "<br />";
for ($j = 1; $j <= 5; $j++) {
    echo "$j ";
}
echo "<br /><br />";

echo "d) Typy zmiennych \$_GET, \$_POST, \$_SESSION:<br />";
echo "GET: " . (isset($_GET['param']) ? $_GET['param'] : "brak") . "<br />";
echo "POST: " . (isset($_POST['data']) ? $_POST['data'] : "brak") . "<br />";
session_start();
$_SESSION['user'] = 'Volodymyr';
echo "SESSION: " . $_SESSION['user'] . "<br />";
