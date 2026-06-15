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
</head>
<body>

<header class="site-header">
    <div class="container nav-wrapper">
        <a href="<?= url('index.php'); ?>" class="logo">Brits Realty</a>

        <button class="menu-toggle" id="menuToggle">☰</button>

        <nav class="main-nav" id="mainNav">
            <a href="<?= url('index.php'); ?>">Home</a>
            <a href="<?= url('properties.php'); ?>">Properties</a>
            <a href="<?= url('contact.php'); ?>">Contact</a>

            <?php if (isLoggedIn()): ?>
                <a href="<?= url('favourites.php'); ?>">Favourites</a>

                <?php if (isAgentOrAdmin()): ?>
                    <a href="<?= url('admin/dashboard.php'); ?>">Admin</a>
                <?php endif; ?>

                <a href="<?= url('logout.php'); ?>">Logout</a>
            <?php else: ?>
                <a href="<?= url('register.php'); ?>">Register</a>
                <a href="<?= url('login.php'); ?>">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main>