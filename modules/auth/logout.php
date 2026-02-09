<?php
session_abort();
session_destroy();
header('Location: login.php');
exit();