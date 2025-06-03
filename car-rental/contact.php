<?php 
include 'includes/config.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);
    
    $errors = [];
    if (empty($name)) $errors[] = $translations[$_SESSION['lang']]['required_fields'] ?? 'Имя обязательно';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = $translations[$_SESSION['lang']]['invalid_email'] ?? 'Корректный email обязателен';
    if (empty($message)) $errors[] = $translations[$_SESSION['lang']]['message'] . ' ' . ($translations[$_SESSION['lang']]['required_fields'] ?? 'обязательно');
    
    if (strlen($name) > 255) $errors[] = $translations[$_SESSION['lang']]['name'] . ' ' . ($translations[$_SESSION['lang']]['too_long'] ?? 'слишком длинное (максимум 255 символов)');
    if (strlen($email) > 255) $errors[] = $translations[$_SESSION['lang']]['email'] . ' ' . ($translations[$_SESSION['lang']]['too_long'] ?? 'слишком длинный (максимум 255 символов)');
    if (strlen($message) > 5000) $errors[] = $translations[$_SESSION['lang']]['message'] . ' ' . ($translations[$_SESSION['lang']]['too_long_message'] ?? 'слишком длинное (максимум 5000 символов)');
    
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO contacts (name, email, message, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Ошибка подготовки запроса: " . $conn->error);
            }
            
            $stmt->bind_param("sss", $name, $email, $message);
            
            if ($stmt->execute()) {
                $success = $translations[$_SESSION['lang']]['message_sent'] ?? 'Сообщение успешно отправлено! Мы свяжемся с вами в ближайшее время.';
                $_POST = [];
                
            } else {
                $errors[] = $translations[$_SESSION['lang']]['error_occurred'] ?? 'Произошла ошибка при отправке сообщения. Попробуйте еще раз.';
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            error_log("Ошибка при сохранении контакта: " . $e->getMessage());
            $errors[] = $translations[$_SESSION['lang']]['error_occurred'] ?? 'Произошла техническая ошибка. Попробуйте еще раз позже.';
        }
    }
}
?>

<style>
.contact-page {
    padding: 2rem 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
}

.contact-container {
    padding: 0 1rem;
    max-width: 1200px;
    margin: 0 auto;
}

.contact-header {
    text-align: center;
    margin-bottom: 3rem;
    animation: fadeInUp 0.6s ease-out;
}

.contact-header h1 {
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 1rem;
    position: relative;
}

.contact-header h1::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
    border-radius: 2px;
}

.contact-header p {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
    color: #6c757d;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}

.contact-grid {
    display: grid;
    gap: 2rem;
    align-items: start;
}

@media (max-width: 768px) {
    .contact-grid {
        grid-template-columns: 1fr;
    }
    
    .contact-page {
        padding: 1rem 0;
    }
    
    .contact-container {
        padding: 0 0.5rem;
    }
    
    .contact-header {
        margin-bottom: 2rem;
    }
}

@media (min-width: 769px) {
    .contact-grid {
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
    }
    
    .contact-page {
        padding: 3rem 0;
    }
    
    .contact-header {
        margin-bottom: 4rem;
    }
}

.contact-info {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: 2rem;
    box-shadow: var(--box-shadow);
    height: fit-content;
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s ease-out;
}

.contact-info::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
}

.contact-info h3 {
    color: var(--primary-color);
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding: 1.25rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: var(--border-radius);
    border: 1px solid #e9ecef;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.contact-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
    opacity: 0;
    transition: var(--transition);
}

.contact-item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(0, 102, 204, 0.15);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.contact-item:hover::before {
    opacity: 1;
}

.contact-item:last-child {
    margin-bottom: 0;
}

.contact-details {
    flex: 1;
}

.contact-label {
    font-weight: 700;
    color: #2c3e50;
    font-size: 1rem;
    margin-bottom: 0.5rem;
    display: block;
}

.contact-value {
    color: #6c757d;
    font-size: 1rem;
    line-height: 1.5;
}

.contact-value a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
}

.contact-value a:hover {
    color: var(--primary-hover);
    text-decoration: underline;
}

.contact-form {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: 2rem;
    box-shadow: var(--box-shadow);
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s ease-out 0.2s both;
}

.contact-form::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, var(--success-color) 0%, #20c997 100%);
}

.contact-form h3 {
    color: var(--success-color);
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.75rem;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-control {
    border: 2px solid #e9ecef;
    border-radius: var(--border-radius);
    padding: 1rem;
    font-size: 1rem;
    transition: var(--transition);
    background-color: #fafafa;
    min-height: 44px;
    width: 100%;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.25);
    background-color: white;
    outline: none;
}

.form-control::placeholder {
    color: #adb5bd;
    font-size: 0.95rem;
}

textarea.form-control {
    resize: vertical;
    min-height: 120px;
    font-family: inherit;
}

.btn-submit {
    width: 100%;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
    border: none;
    color: white;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 700;
    border-radius: var(--border-radius);
    transition: var(--transition);
    box-shadow: 0 4px 15px rgba(0, 102, 204, 0.3);
    position: relative;
    overflow: hidden;
    min-height: 50px;
    cursor: pointer;
}

.btn-submit::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-submit:hover {
    background: linear-gradient(135deg, var(--primary-hover) 0%, #003366 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 102, 204, 0.4);
}

.btn-submit:hover::before {
    left: 100%;
}

.btn-submit:active {
    transform: translateY(0);
}

.alert {
    border-radius: var(--border-radius);
    padding: 1.25rem;
    margin-bottom: 2rem;
    border: none;
    position: relative;
    overflow: hidden;
    font-weight: 500;
}

.alert::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background-color: currentColor;
}

.alert-success {
    background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
    color: var(--success-color);
    border: 1px solid rgba(40, 167, 69, 0.2);
}

.alert-danger {
    background: linear-gradient(135deg, #ffeaea 0%, #f8d7da 100%);
    color: var(--danger-color);
    border: 1px solid rgba(220, 53, 69, 0.2);
}

.alert strong {
    display: block;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.error-list {
    margin: 0.5rem 0 0 0;
    padding-left: 1.5rem;
}

.error-list li {
    margin-bottom: 0.5rem;
}

.form-note {
    text-align: center;
    margin-top: 1rem;
    color: #6c757d;
    font-size: 0.9rem;
}

.success-actions {
    text-align: center;
    margin-top: 1.5rem;
}

.btn-outline-primary {
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
    background-color: transparent;
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
    display: inline-block;
}

.btn-outline-primary:hover {
    background-color: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 102, 204, 0.3);
}

.form-control.is-invalid {
    border-color: var(--danger-color);
    background-color: #fff5f5;
}

.invalid-feedback {
    color: var(--danger-color);
    font-size: 0.875rem;
    margin-top: 0.5rem;
    display: block;
}

@media (min-width: 769px) {
    .contact-info, .contact-form {
        padding: 2.5rem;
    }
    
    .contact-item:hover {
        transform: translateX(8px);
        box-shadow: 0 6px 25px rgba(0, 102, 204, 0.2);
    }
    
    .form-control {
        padding: 1.125rem;
        font-size: 1.05rem;
    }
    
    .btn-submit {
        padding: 1.25rem 2rem;
        font-size: 1.125rem;
    }
    
    .contact-header {
        transform: translateZ(0);
    }
}

@media (min-width: 1200px) {
    .contact-container {
        max-width: 1400px;
    }
    
    .contact-grid {
        gap: 4rem;
    }
    
    .contact-info, .contact-form {
        padding: 3rem;
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (prefers-color-scheme: dark) {
    .contact-page {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d30 100%);
    }
    
    .contact-info, .contact-form {
        background: #2d2d30;
        color: #f8f9fa;
    }
    
    .contact-item {
        background: linear-gradient(135deg, #3d3d40 0%, #2d2d30 100%);
        border-color: #495057;
    }
    
    .contact-item:hover {
        background: linear-gradient(135deg, #2d2d30 0%, #3d3d40 100%);
    }
    
    .form-control {
        background-color: #3d3d40;
        border-color: #495057;
        color: #f8f9fa;
    }
    
    .form-control:focus {
        background-color: #495057;
    }
}

@media (prefers-reduced-motion: reduce) {
    .contact-info, .contact-form, .contact-item {
        animation: none;
        transition: none;
    }
    
    .contact-item:hover {
        transform: none;
    }
    
    .btn-submit:hover {
        transform: none;
    }
}

:root {
    --primary-color: #0066cc;
    --primary-hover: #0052a3;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --border-radius: 8px;
    --border-radius-lg: 12px;
    --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
}
</style>

<div class="contact-page">
    <div class="contact-container">
        <div class="contact-header">
            <h1><?= $translations[$_SESSION['lang']]['contact'] ?? 'Контакты' ?></h1>
            <p><?= $translations[$_SESSION['lang']]['contact_description'] ?? 'Свяжитесь с нами любым удобным способом' ?></p>
        </div>
        
        <div class="contact-grid">
            <div class="contact-info">
                <h3><?= $translations[$_SESSION['lang']]['our_contacts'] ?? 'Наши контакты' ?></h3>
                
                <div class="contact-item">
                    <div class="contact-details">
                        <span class="contact-label"><?= $translations[$_SESSION['lang']]['address'] ?? 'Адрес' ?></span>
                        <div class="contact-value"><?= $translations[$_SESSION['lang']]['city'] ?? 'Таллинн' ?>, <?= $translations[$_SESSION['lang']]['street_address'] ?? 'ул. Автомобильная 123' ?></div>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-details">
                        <span class="contact-label"><?= $translations[$_SESSION['lang']]['phone_number'] ?? 'Номер телефона' ?></span>
                        <div class="contact-value">
                            <a href="tel:+37212345678">+372 1234 5678</a>
                        </div>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-details">
                        <span class="contact-label"><?= $translations[$_SESSION['lang']]['email_address'] ?? 'Email адрес' ?></span>
                        <div class="contact-value">
                            <a href="mailto:info@autorent.ee">info@autorent.ee</a>
                        </div>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-details">
                        <span class="contact-label"><?= $translations[$_SESSION['lang']]['working_hours'] ?? 'Часы работы' ?></span>
                        <div class="contact-value">
                            <?= $translations[$_SESSION['lang']]['working_hours_details'] ?? 'Пн-Пт: 9:00-18:00<br>Сб-Вс: 10:00-16:00' ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="contact-form">
                <h3><?= $translations[$_SESSION['lang']]['form_title'] ?? 'Напишите нам' ?></h3>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <strong><?= $translations[$_SESSION['lang']]['success'] ?? 'Успешно!' ?></strong>
                        <?= $success ?>
                    </div>
                    <div class="success-actions">
                        <a href="contact.php" class="btn-outline-primary"><?= $translations[$_SESSION['lang']]['send_another'] ?? 'Отправить еще сообщение' ?></a>
                    </div>
                <?php elseif (isset($errors) && !empty($errors)): ?>
                    <div class="alert alert-danger">
                        <strong><?= $translations[$_SESSION['lang']]['error_occurred'] ?? 'Ошибка!' ?></strong>
                        <ul class="error-list">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (!isset($success)): ?>
                    <form method="POST" novalidate>
                        <div class="form-group">
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   class="form-control" 
                                   placeholder="<?= $translations[$_SESSION['lang']]['name_placeholder'] ?? $translations[$_SESSION['lang']]['enter_name'] ?? 'Ваше имя' ?>"
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                   maxlength="255"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-control" 
                                   placeholder="<?= $translations[$_SESSION['lang']]['email_placeholder'] ?? $translations[$_SESSION['lang']]['example_email'] ?? 'your@email.com' ?>"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   maxlength="255"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <textarea id="message" 
                                      name="message" 
                                      class="form-control" 
                                      rows="5" 
                                      placeholder="<?= $translations[$_SESSION['lang']]['message_placeholder'] ?? 'Ваше сообщение...' ?>"
                                      maxlength="5000"
                                      required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <?= $translations[$_SESSION['lang']]['send_message'] ?? 'Отправить сообщение' ?>
                        </button>
                        
                        <div class="form-note">
                            * <?= $translations[$_SESSION['lang']]['all_fields_required'] ?? 'Все поля обязательны для заполнения' ?>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, textarea');
    
    const messages = {
        required: '<?= addslashes($translations[$_SESSION['lang']]['required_fields'] ?? 'Это поле обязательно') ?>',
        email: '<?= addslashes($translations[$_SESSION['lang']]['invalid_email'] ?? 'Введите корректный email') ?>',
        maxLength: '<?= addslashes($translations[$_SESSION['lang']]['max_length'] ?? 'Максимальная длина:') ?>'
    };
    
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', clearError);
    });
    
    function validateField(e) {
        const field = e.target;
        const value = field.value.trim();
        
        clearError.call(field);
        
        if (field.hasAttribute('required') && !value) {
            showError(field, messages.required);
            return false;
        }
        
        if (field.type === 'email' && value && !isValidEmail(value)) {
            showError(field, messages.email);
            return false;
        }
        
        const maxLength = field.getAttribute('maxlength');
        if (maxLength && value.length > maxLength) {
            showError(field, `${messages.maxLength} ${maxLength} символов`);
            return false;
        }
        
        return true;
    }
    
    function clearError() {
        this.classList.remove('is-invalid');
        const errorDiv = this.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) errorDiv.remove();
    }
    
    function showError(field, message) {
        field.classList.add('is-invalid');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
    
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        inputs.forEach(input => {
            if (!validateField({target: input})) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });
    
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.6s ease-out';
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.contact-item').forEach(item => {
            observer.observe(item);
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>