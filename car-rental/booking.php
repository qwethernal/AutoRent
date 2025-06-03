<?php
include 'includes/config.php';
include 'includes/header.php';

if (!isset($_GET['car_id']) || !is_numeric($_GET['car_id'])) {
    echo '<div class="container py-3"><div class="alert alert-danger text-center">' . $translations[$_SESSION['lang']]['invalid_car_id'] . '</div></div>';
    include 'includes/footer.php';
    exit;
}

$car_id = (int)$_GET['car_id'];
$lang = $_SESSION['lang'];

$stmt = $conn->prepare("SELECT *, description_{$lang} as localized_description FROM cars WHERE id = ? AND is_available = 1");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo '<div class="container py-3"><div class="alert alert-danger text-center">' . $translations[$_SESSION['lang']]['car_not_found'] . '</div></div>';
    include 'includes/footer.php';
    exit;
}

$car = $result->fetch_assoc();
$stmt->close();

$description = !empty($car['localized_description']) ? $car['localized_description'] : $car['description_ru'];

$success = false;
$errors = [];
$booking_id = null;
$total_price = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    if (empty($name) || empty($email) || empty($phone) || empty($start_date) || empty($end_date)) {
        $errors[] = $translations[$lang]['required_fields'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = $translations[$lang]['invalid_email'];
    }
    
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    if ($start < $today) {
        $errors[] = $translations[$lang]['date_past_error'];
    }
    
    if ($start >= $end) {
        $errors[] = $translations[$lang]['date_error'];
    }

    if (empty($errors)) {
        $check_stmt = $conn->prepare("SELECT id FROM bookings WHERE car_id = ? AND status != 'cancelled' AND ((start_date <= ? AND end_date > ?) OR (start_date < ? AND end_date >= ?) OR (start_date >= ? AND end_date <= ?))");
        $check_stmt->bind_param("issssss", $car_id, $start_date, $start_date, $end_date, $end_date, $start_date, $end_date);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = $translations[$lang]['car_unavailable'];
        }
        $check_stmt->close();
    }

    if (empty($errors)) {
        $days = $start->diff($end)->days;
        $total_price = $days * $car['price_per_day'];
        
        try {
            $insert_stmt = $conn->prepare("INSERT INTO bookings (car_id, customer_name, customer_email, customer_phone, start_date, end_date, total_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            
            if ($insert_stmt) {
                $insert_stmt->bind_param("isssssd", $car_id, $name, $email, $phone, $start_date, $end_date, $total_price);
                
                if ($insert_stmt->execute()) {
                    $booking_id = $conn->insert_id;
                    $success = true;
                } else {
                    $errors[] = $translations[$lang]['error_booking'] . $insert_stmt->error;
                }
                $insert_stmt->close();
            } else {
                $errors[] = $translations[$lang]['error_booking'] . $conn->error;
            }
        } catch (Exception $e) {
            $errors[] = $translations[$lang]['error_booking'] . $e->getMessage();
        }
    }
}
?>

<style>
@media (max-width: 768px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .py-5 {
        padding-top: 2rem !important;
        padding-bottom: 2rem !important;
    }
    
    .car-card {
        margin-bottom: 2rem;
    }
    
    .car-image {
        height: 250px !important;
        border-radius: 10px;
        margin-bottom: 1rem;
    }
    
    .car-info h3 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
        color: #333;
    }
    
    .car-details p {
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    
    .price-highlight {
        font-size: 1.3rem !important;
        font-weight: bold;
        color: #0066cc;
        padding: 0.5rem;
        background: #f8f9fa;
        border-radius: 8px;
        text-align: center;
        margin: 1rem 0;
    }
    
    .booking-form {
        background: #fff;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-top: 1rem;
    }
    
    .booking-form h3 {
        font-size: 1.3rem;
        margin-bottom: 1.5rem;
        color: #333;
        text-align: center;
    }
    
    .form-label {
        font-weight: 600;
        color: #555;
        margin-bottom: 0.5rem;
    }
    
    .form-control {
        padding: 0.75rem;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        margin-bottom: 1rem;
    }
    
    .form-control:focus {
        border-color: #0066cc;
        box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.25);
    }
    
    .date-inputs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin: 1rem 0;
    }
    
    .required-star {
        color: #dc3545;
        font-weight: bold;
    }
    
    .btn-primary {
        background-color: #0066cc;
        border-color: #0066cc;
        padding: 0.875rem 2rem;
        font-size: 1.1rem;
        border-radius: 8px;
        width: 100%;
        margin-top: 1rem;
    }
    
    .btn-primary:hover {
        background-color: #0052a3;
        border-color: #0052a3;
    }
    
    .alert {
        border-radius: 8px;
        margin-bottom: 1.5rem;
        padding: 1rem;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }
    
    .success-card {
        background: #fff;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        margin: 1rem 0;
    }
    
    .success-header {
        text-align: center;
        color: #28a745;
        margin-bottom: 2rem;
    }
    
    .success-header h3 {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }
    
    .booking-details, .rental-details {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        margin: 1rem 0;
    }
    
    .booking-details h5, .rental-details h5 {
        color: #333;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        border-bottom: 2px solid #dee2e6;
        padding-bottom: 0.5rem;
    }
    
    .detail-item {
        margin-bottom: 0.75rem;
    }
    
    .detail-label {
        font-weight: 600;
        color: #555;
    }
    
    .detail-value {
        color: #333;
    }
    
    .total-price {
        font-size: 1.2rem;
        font-weight: bold;
        color: #0066cc;
    }
    
    .success-actions {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-top: 2rem;
    }
    
    .success-actions .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
    }
    
    .success-note {
        background: #e3f2fd;
        padding: 1rem;
        border-radius: 8px;
        margin: 1.5rem 0;
        text-align: center;
        color: #1565c0;
        font-weight: 500;
    }
}

@media (max-width: 576px) {
    .container {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    
    .car-image {
        height: 200px !important;
        margin-bottom: 1.5rem;
    }
    
    .booking-form {
        padding: 1rem;
    }
    
    .date-inputs {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    
    .success-card {
        padding: 1.5rem;
        margin: 0.5rem 0;
    }
    
    .booking-details, .rental-details {
        padding: 1rem;
    }
}

@media (min-width: 769px) {
    .car-card {
        display: flex;
        gap: 2rem;
        align-items: start;
    }
    
    .car-image-container {
        flex: 0 0 50%;
    }
    
    .car-info-container {
        flex: 1;
    }
    
    .booking-form {
        max-width: 600px;
    }
    
    .success-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
    
    .success-actions {
        flex-direction: row;
        justify-content: center;
        gap: 1rem;
    }
}

.price-calculation {
    transition: all 0.3s ease;
}

.price-calculation .alert {
    border-left: 4px solid #0066cc;
    background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
}

.loading {
    opacity: 0.6;
    pointer-events: none;
}

.spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<div class="container py-3">
    <?php if ($success): ?>
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="success-card">
                    <div class="success-header">
                        <h3><?= $translations[$lang]['booking_confirmed'] ?></h3>
                        <p class="text-muted mb-0"><?= $translations[$lang]['bron_number'] ?>: #<?= $booking_id ?></p>
                    </div>
                    
                    <div class="success-details">
                        <div class="booking-details">
                            <h5><?= $translations[$lang]['booking_details'] ?></h5>
                            <div class="detail-item">
                                <span class="detail-label"><?= $translations[$lang]['customer_name'] ?>:</span>
                                <span class="detail-value"><?= htmlspecialchars($_POST['name']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><?= $translations[$lang]['customer_email'] ?>:</span>
                                <span class="detail-value"><?= htmlspecialchars($_POST['email']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><?= $translations[$lang]['customer_phone'] ?>:</span>
                                <span class="detail-value"><?= htmlspecialchars($_POST['phone']) ?></span>
                            </div>
                        </div>
                        
                        <div class="rental-details">
                            <h5><?= $translations[$lang]['rent_details'] ?></h5>
                            <div class="detail-item">
                                <span class="detail-label"><?= $translations[$lang]['car'] ?>:</span>
                                <span class="detail-value"><?= $car['brand'] ?> <?= $car['model'] ?> (<?= $car['year'] ?>)</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><?= $translations[$lang]['start_date'] ?>:</span>
                                <span class="detail-value"><?= date('d.m.Y', strtotime($_POST['start_date'])) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><?= $translations[$lang]['end_date'] ?>:</span>
                                <span class="detail-value"><?= date('d.m.Y', strtotime($_POST['end_date'])) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><?= $translations[$lang]['rental_period'] ?>:</span>
                                <span class="detail-value"><?= $start->diff($end)->days ?> <?= $translations[$lang]['days'] ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><?= $translations[$lang]['total_price'] ?>:</span>
                                <span class="detail-value total-price"><?= number_format($total_price, 2) ?> €</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="success-note">
                        <?= $translations[$lang]['we_will_let_you_know']?>
                    </div>
                    
                    <div class="success-actions">
                        <a href="cars.php" class="btn btn-primary"><?= $translations[$lang]['other_cars'] ?></a>
                        <a href="index.php" class="btn btn-outline-primary"><?= $translations[$lang]['on_main'] ?></a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="car-card">
            <div class="car-image-container">
                <img src="assets/images/<?= $car['image'] ?>" class="img-fluid car-image" alt="<?= $car['brand'] ?> <?= $car['model'] ?>">
            </div>
            
            <div class="car-info-container">
                <div class="car-info">
                    <h3><?= $car['brand'] ?> <?= $car['model'] ?></h3>
                    <div class="car-details">
                        <p><strong><?= $translations[$lang]['year'] ?>:</strong> <?= $car['year'] ?></p>
                        <p><strong><?= $translations[$lang]['description'] ?>:</strong> <?= $description ?></p>
                    </div>
                    <div class="price-highlight">
                        <?= $car['price_per_day'] ?><?= $translations[$lang]['price_per_day'] ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="booking-form">
            <h3><?= $translations[$lang]['booking_details'] ?></h3>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong><?= $translations[$lang]['errors'] ?></strong>
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="booking-form-inner">
                <div class="mb-3">
                    <label class="form-label">
                         <?= $translations[$lang]['customer_name'] ?> 
                        <span class="required-star">*</span>
                    </label>
                    <input type="text" name="name" class="form-control" 
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
                           placeholder="<?= $translations[$lang]['enter_name'] ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">
                        <?= $translations[$lang]['customer_email'] ?> 
                        <span class="required-star">*</span>
                    </label>
                    <input type="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           placeholder="<?= $translations[$lang]['example_email'] ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">
                        <?= $translations[$lang]['customer_phone'] ?> 
                        <span class="required-star">*</span>
                    </label>
                    <input type="tel" name="phone" class="form-control" 
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" 
                           placeholder="<?= $translations[$lang]['phone_placeholder'] ?>" required>
                </div>
                
                <div class="date-inputs">
                    <div>
                        <label class="form-label">
                            <?= $translations[$lang]['start_date'] ?> 
                            <span class="required-star">*</span>
                        </label>
                        <input type="date" name="start_date" class="form-control" 
                               value="<?= $_POST['start_date'] ?? '' ?>" 
                               min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div>
                        <label class="form-label">
                            <?= $translations[$lang]['end_date'] ?> 
                            <span class="required-star">*</span>
                        </label>
                        <input type="date" name="end_date" class="form-control" 
                               value="<?= $_POST['end_date'] ?? '' ?>" 
                               min="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                
                <div id="price-calculation" class="price-calculation"></div>
                
                <button type="submit" class="btn btn-primary">
                    <span class="btn-text"><?= $translations[$lang]['submit_booking'] ?></span>
                    <span class="spinner d-none"></span>
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pricePerDay = <?= $car['price_per_day'] ?>;
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    const priceCalculation = document.getElementById('price-calculation');
    const form = document.querySelector('.booking-form-inner');
    const submitBtn = form?.querySelector('button[type="submit"]');
    
    const translations = {
        date_end_after_start: "<?= $translations[$lang]['date_end_after_start'] ?>",
        price_calculation: "<?= $translations[$lang]['price_calculation'] ?>",
        calculation_days: "<?= $translations[$lang]['calculation_days'] ?>"
    };
    
    function calculatePrice() {
        if (startDate?.value && endDate?.value) {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            
            if (start >= end) {
                priceCalculation.innerHTML = `
                    <div class="alert alert-warning">
                        ${translations.date_end_after_start}
                    </div>
                `;
                return;
            }
            
            const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
            const total = days * pricePerDay;
        } else {
            priceCalculation.innerHTML = '';
        }
    }
    
    if (startDate && endDate) {
        startDate.addEventListener('change', function() {
            endDate.min = this.value;
            calculatePrice();
        });
        
        endDate.addEventListener('change', calculatePrice);
        
        calculatePrice();
    }
    
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            const btnText = submitBtn.querySelector('.btn-text');
            const spinner = submitBtn.querySelector('.spinner');
            
            btnText.style.display = 'none';
            spinner.classList.remove('d-none');
            submitBtn.disabled = true;

            setTimeout(() => {
                if (!form.checkValidity()) {
                    btnText.style.display = 'inline';
                    spinner.classList.add('d-none');
                    submitBtn.disabled = false;
                }
            }, 100);
        });
    }
    
    const phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value;
            if (value.length > 0 && !value.startsWith('+')) {
                if (value.match(/^[0-9]/)) {
                    e.target.value = '+372 ' + value;
                }
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>