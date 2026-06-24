<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Brits Realty</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= url('assets/css/style.css'); ?>">
    <link rel="icon" href="<?= url('favicon.ico'); ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?= url('favicon.ico'); ?>" type="image/x-icon">
</head>
<body>

<header class="site-header">
    <div class="container header-container">
        <a class="site-logo" href="<?= url('index.php'); ?>">
            <img src="<?= url('favicon.ico'); ?>" alt="Brits Realty Logo" class="nav-image">
            Brits Realty
        </a>

        <button 
            type="button"
            class="menu-toggle" 
            id="menuToggle" 
            aria-label="Open navigation menu"
            onclick="document.getElementById('siteNav').classList.toggle('show')"
        >
            ☰
        </button>

        <nav class="site-nav" id="siteNav">
            <a href="<?= url('index.php'); ?>">Home</a>
            <a href="<?= url('properties.php'); ?>">Properties</a>
            <a href="<?= url('mortgage-calculator.php'); ?>">Mortgage Calculator</a>
            <a href="<?= url('contact.php'); ?>">Contact</a>

            <?php if (isLoggedIn() && !isAgentOrAdmin()): ?>
                <a href="<?= url('dashboard.php'); ?>">Dashboard</a>
                <a href="<?= url('favourites.php'); ?>">Favourites</a>
            <?php endif; ?>

            <?php if (isAgentOrAdmin()): ?>
                <a href="<?= url('admin/dashboard.php'); ?>">Admin</a>
            <?php endif; ?>

            <?php if (isLoggedIn()): ?>
                <a href="<?= url('logout.php'); ?>">Logout</a>
            <?php else: ?>
                <a href="<?= url('register.php'); ?>">Register</a>
                <a href="<?= url('login.php'); ?>">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main>