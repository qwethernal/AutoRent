<?php
include 'includes/config.php';
include 'includes/header.php'; 
?>

<style>
@media (max-width: 768px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    h1 {
        font-size: 1.75rem;
        margin-bottom: 2rem !important;
    }
    
    .card {
        margin-bottom: 1.5rem;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .card-img-top {
        height: 200px !important;
        object-fit: cover;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .card-title {
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
    }
    
    .card-body h5 {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 0.75rem;
    }
    
    .card-text {
        font-size: 0.85rem;
        line-height: 1.4;
        margin-bottom: 1rem;
    }
    
    .card-footer {
        padding: 1rem;
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.75rem;
    }
    
    .price {
        font-size: 1.1rem;
        font-weight: bold;
        color: #0066cc;
        text-align: center;
        margin: 0;
    }
    
    .btn {
        width: 100%;
        padding: 0.75rem;
        font-size: 0.95rem;
        border-radius: 5px;
    }
    
    .btn-primary {
        background-color: #0066cc;
        border-color: #0066cc;
    }
    
    .btn-primary:hover {
        background-color: #0052a3;
        border-color: #0052a3;
    }
}

@media (max-width: 576px) {
    .py-5 {
        padding-top: 2rem !important;
        padding-bottom: 2rem !important;
    }
    
    h1 {
        font-size: 1.5rem;
    }
    
    .card-img-top {
        height: 180px !important;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    .card-footer {
        padding: 0.75rem;
    }
}

@media (min-width: 769px) {
    .card:hover {
        transform: translateY(-5px);
        transition: transform 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
}
</style>

<div class="container py-5">
    <h1 class="text-center mb-5"><?= $translations[$_SESSION['lang']]['cars'] ?></h1>
    
    <div class="row g-3">
        <?php
        $lang = $_SESSION['lang'];
        $sql = "SELECT *, description_{$lang} as localized_description FROM cars WHERE is_available = 1";
        $result = $conn->query($sql);
        
        while ($row = $result->fetch_assoc()):
            $description = !empty($row['localized_description']) ? $row['localized_description'] : $row['description_ru'];
            $short_description = mb_strlen($description) > 100 ? mb_substr($description, 0, 100) . '...' : $description;
        ?>
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100">
                <img src="assets/images/<?= $row['image'] ?>" class="card-img-top" alt="<?= $row['brand'] ?> <?= $row['model'] ?>">
                <div class="card-body d-flex flex-column">
                    <h4 class="card-title"><?= $row['brand'] ?> <?= $row['model'] ?></h4>
                    <h5 class="text-muted"><?= $row['year'] ?> <?= $translations[$_SESSION['lang']]['year_suffix'] ?></h5>
                    <p class="card-text flex-grow-1">
                        <span class="d-none d-md-block"><?= $description ?></span>
                        <span class="d-md-none"><?= $short_description ?></span>
                    </p>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <span class="price"><?= $row['price_per_day'] ?> <?= $translations[$_SESSION['lang']]['price_per_day'] ?></span>
                    <a href="booking.php?car_id=<?= $row['id'] ?>" class="btn btn-primary"><?= $translations[$_SESSION['lang']]['book_now'] ?></a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    
    <?php if ($result->num_rows == 0): ?>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="alert alert-info text-center">
                    <h4><?= $translations[$_SESSION['lang']]['no_cars_available'] ?? 'Нет доступных автомобилей' ?></h4>
                    <p><?= $translations[$_SESSION['lang']]['check_back_later'] ?? 'Пожалуйста, проверьте позже или свяжитесь с нами.' ?></p>
                    <a href="contact.php" class="btn btn-primary"><?= $translations[$_SESSION['lang']]['contact'] ?></a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>