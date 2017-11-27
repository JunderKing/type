<?php
$serverName = '127.0.0.1';
$userName = 'root';
$passwd = 'youxiwang';
$conn = mysqli_connect($serverName, $userName, $passwd) or die('connection error!');
$sql = 'CREATE DATABASE IF NOT EXISTS kingco_typing';
mysqli_query($conn, $sql) or die('create database error!' . mysqli_error($conn));
mysqli_select_db($conn, 'kingco_typing');
