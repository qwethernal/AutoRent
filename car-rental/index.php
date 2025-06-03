<?php

include 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ru', 'es'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

include 'includes/header.php'; 
?>

<main class="container">
    <section class="hero text-center py-5">
        <h1><?= htmlspecialchars($translations[$_SESSION['lang']]['welcome'] ?? 'Welcome') ?></h1>
        <a href="cars.php" class="btn btn-primary btn-lg"><?= htmlspecialchars($translations[$_SESSION['lang']]['book_now'] ?? 'Book Now') ?></a>
    </section>

    <section class="featured-cars py-5">
        <h2 class="text-center mb-4"><?= htmlspecialchars($translations[$_SESSION['lang']]['featured_cars'] ?? 'Featured Cars') ?></h2>
        <div class="row">
            <?php
            $lang = $_SESSION['lang'];
            $sql = "SELECT *, description_{$lang} as localized_description FROM cars WHERE is_available = 1 LIMIT 3";
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()):
                    $description = !empty($row['localized_description']) 
                        ? $row['localized_description'] 
                        : ($row['description_ru'] ?? 'No description available');
            ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="assets/images/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['brand'] . ' ' . $row['model']) ?>" height="275">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($row['brand'] . ' ' . $row['model']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($row['year']) ?> <?= htmlspecialchars($translations[$_SESSION['lang']]['year_suffix'] ?? '') ?></p>
                        <p class="card-text"><?= htmlspecialchars($description) ?></p>
                        <p class="price"><?= htmlspecialchars($row['price_per_day']) ?> <?= htmlspecialchars($translations[$_SESSION['lang']]['price_per_day'] ?? '/day') ?></p>
                        <a href="booking.php?car_id=<?= $row['id'] ?>" class="btn btn-primary"><?= htmlspecialchars($translations[$_SESSION['lang']]['book_now'] ?? 'Book Now') ?></a>
                    </div>
                </div>
            </div>
            <?php 
                endwhile;
            } else {
                echo '<div class="col-12 text-center"><p>' . htmlspecialchars($translations[$_SESSION['lang']]['no_cars_available'] ?? 'No cars available') . '</p></div>';
            }
            ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>