<?php
// index.php (ROOT) - Always show landing page first
// Users must sign in or register before accessing home.php

header('Location: landing.php');
exit();
?>