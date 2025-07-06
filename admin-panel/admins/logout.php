<?php

require "../../config/config.php";

session_unset();
session_destroy();

header("Location: " . ADMINAPPURL . "/admins/login-admins.php");
exit;
