<?php 
// Deixamos a variável vazia no começo para não dar erro
$result = ""; 

$num1 = $_GET["num1"];
$num2 = $_GET["num2"];
$op = $_GET["op"];

switch($op){
  case "+":
    $result = $num1 + $num2;
    break;

  case "-":
    $result = $num1 - $num2;
    break;

  case "*":
    $result = $num1 * $num2;
    break;

  case "/":
    $result = $num1 / $num2;
    break;
}
?>
