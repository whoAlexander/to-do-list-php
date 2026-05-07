<?php

date_default_timezone_set("America/Argentina/Buenos_Aires");

$dias = [
    "Domingo",
    "Lunes",
    "Martes",
    "Miércoles",
    "Jueves",
    "Viernes",
    "Sábado"
];

$meses = [
    "Enero",
    "Febrero",
    "Marzo",
    "Abril",
    "Mayo",
    "Junio",
    "Julio",
    "Agosto",
    "Septiembre",
    "Octubre",
    "Noviembre",
    "Diciembre"
];

$diaSemana = $dias[date("w")];
$dia = date("d");
$mes = $meses[date("n") - 1];
$año = date("Y");


?>