<?php
$is_live = false;
if ($is_live) {
    $conn = new mysqli("localhost", "", "", "clientzone");
}else {
    $conn = new mysqli("localhost", "root", "", "clientzone_test");
}