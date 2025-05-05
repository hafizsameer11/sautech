<?php
$is_live = false;
if ($is_live) {
    $conn = new mysqli("localhost", "clientzone_user", "S@utech2024!", "clientzone");
}else {
    $conn = new mysqli("localhost", "root", "", "clientzone_test");
}