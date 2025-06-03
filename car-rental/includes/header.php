<?php
include 'includes/config.php';
$lang = $_SESSION['lang'] ?? 'ru';

function getLangUrl($new_lang) {
    $params = $_GET;
    $params['lang'] = $new_lang;
    
    $base_url = strtok($_SERVER['REQUEST_URI'], '?');
    return $base_url . '?' . http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=1.0, maximum-scale=5.0">
    <title><?= $translations[$lang]['title'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css?v=<?= time() ?>" rel="stylesheet">
    
    <style>

    * {
        box-sizing: border-box;
    }
    
    body {
        font-size: 16px; 
        line-height: 1.5;
        -webkit-text-size-adjust: 100%;
        -webkit-font-smoothing: antialiased;
    }

    .navbar {
        padding: 0.5rem 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .navbar-brand {
        font-size: 1.1rem;
        font-weight: bold;
    }
    
    .navbar-toggler {
        border: none;
        padding: 0.25rem 0.5rem;
        font-size: 1rem;
    }
    
    .navbar-toggler:focus {
        box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
    }
    

    @media (max-width: 991.98px) {
        .navbar-collapse {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .navbar-nav {
            width: 100%;
        }
        
        .navbar-nav .nav-link {
            padding: 0.75rem 0;
            font-size: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .navbar-nav .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .language-switcher {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }
        
        .language-switcher .btn {
            flex: 1;
            max-width: 60px;
            padding: 0.5rem;
            font-size: 0.875rem;
        }
    }

    @media (min-width: 992px) {
        .language-switcher {
            display: flex;
            gap: 0.5rem;
        }
        
        .language-switcher .btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
    }

    .container {
        max-width: 100%;
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    @media (min-width: 576px) {
        .container {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
    }
 
    main {
        padding-top: 1rem;
        padding-bottom: 2rem;
    }
    
    @media (max-width: 576px) {
        main {
            padding-top: 0.5rem;
            padding-bottom: 1rem;
        }
    }
    
 
    h1 { font-size: 1.75rem; }
    h2 { font-size: 1.5rem; }
    h3 { font-size: 1.25rem; }
    h4 { font-size: 1.125rem; }
    h5 { font-size: 1rem; }
    
    @media (max-width: 576px) {
        h1 { font-size: 1.5rem; }
        h2 { font-size: 1.375rem; }
        h3 { font-size: 1.125rem; }
        h4 { font-size: 1rem; }
        h5 { font-size: 0.9375rem; }
    }
    
    .btn {
        min-height: 44px; 
        border-radius: 6px;
        font-weight: 500;
    }
    
    .btn-sm {
        min-height: 38px;
        padding: 0.5rem 1rem;
    }
    
    .btn-lg {
        min-height: 50px;
        padding: 0.75rem 1.5rem;
        font-size: 1.125rem;
    }
    
    @media (max-width: 576px) {
        .btn {
            font-size: 1rem;
            padding: 0.75rem 1rem;
        }
    }
    
    img {
        max-width: 100%;
        height: auto;
    }
    

    .card {
        border-radius: 8px;
        border: 1px solid rgba(0,0,0,0.125);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: box-shadow 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    @media (max-width: 768px) {
        .card {
            margin-bottom: 1rem;
        }
    }
    


    .form-control, .form-select {
        min-height: 44px;
        font-size: 1rem;
        border-radius: 6px;
    }
    
    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    

    .alert {
        border-radius: 6px;
        padding: 1rem;
    }
    
    @media (max-width: 576px) {
        .alert {
            padding: 0.75rem;
            font-size: 0.9375rem;
        }
    }
    

    @media (max-width: 576px) {
        .py-5 { padding-top: 2rem !important; padding-bottom: 2rem !important; }
        .py-4 { padding-top: 1.5rem !important; padding-bottom: 1.5rem !important; }
        .mb-5 { margin-bottom: 2rem !important; }
        .mb-4 { margin-bottom: 1.5rem !important; }
    }
    

    .mobile-only { display: none; }
    .desktop-only { display: block; }
    
    @media (max-width: 768px) {
        .mobile-only { display: block; }
        .desktop-only { display: none; }
    }
    

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
    

    @media (prefers-color-scheme: dark) {
        .bg-light {
            background-color: #212529 !important;
            color: #fff;
        }
    }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <span class="d-none d-sm-inline"><?= $translations[$lang]['title'] ?></span>
            <span class="d-sm-none">AutoRent</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Переключить навигацию">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" 
                       href="index.php"><?= $translations[$lang]['home'] ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'cars.php' ? 'active' : '' ?>" 
                       href="cars.php"><?= $translations[$lang]['cars'] ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>" 
                       href="contact.php"><?= $translations[$lang]['contact'] ?></a>
                </li>
            </ul>
            
            <div class="language-switcher">
                <a href="<?= getLangUrl('ru') ?>" 
                   class="btn btn-sm <?= $lang == 'ru' ? 'btn-light' : 'btn-outline-light' ?>">RU</a>
                <a href="<?= getLangUrl('en') ?>" 
                   class="btn btn-sm <?= $lang == 'en' ? 'btn-light' : 'btn-outline-light' ?>">EN</a>
                <a href="<?= getLangUrl('et') ?>" 
                   class="btn btn-sm <?= $lang == 'et' ? 'btn-light' : 'btn-outline-light' ?>">ET</a>
            </div>
        </div>
    </div>
</nav>

<main class="flex-grow-1">