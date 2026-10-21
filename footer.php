    </main> <!-- Закрываем основной контейнер контента -->
    
    
    <footer class="site-footer-new">
        <div class="footer-container">
            
            <!-- Колонка 1: Логотип и описание -->
            <div class="footer-col">
                <a href="/" class="footer-logo">
                    <i class="fa-solid fa-utensils"></i> RecipeFinder
                </a>
                <p class="footer-desc">Ваш помощник на кухне. Находите вдохновение, готовьте с удовольствием и удивляйте близких.</p>
            </div>

            <!-- Колонка 2: Навигация -->
            <div class="footer-col">
                <h3>Навигация</h3>
                <ul class="footer-links">
                    <li><a href="/">Главная</a></li>
                    <li><a href="/recipes.php">Все рецепты</a></li>
                    <li><a href="/ingredients.php">Подбор по ингредиентам</a></li>
                    <li><a href="/cart.php">Избранное</a></li>
                </ul>
            </div>

            <!-- Колонка 3: Контакты и соцсети -->
            <div class="footer-col">
                <h3>Свяжитесь с нами</h3>
                <ul class="footer-links">
                    <li><a href="mailto:hello@recipefinder.ru"><i class="fa-solid fa-envelope"></i> hello@recipefinder.ru</a></li>
                </ul>
                <div class="footer-socials">
                    <a href="#" aria-label="ВКонтакте"><i class="fa-brands fa-vk"></i></a>
                    <a href="#" aria-label="Telegram"><i class="fa-brands fa-telegram"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>

        </div>
        
        <!-- Нижняя строка с копирайтом -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; <?= date('Y') ?> RecipeFinder. Все права защищены.</p>
                <p>Сделано с <i class="fa-solid fa-heart" style="color: #e63946;"></i> для любителей готовить.</p>
            </div>
        </div>
    </footer>

    <!-- Скрипт для пересчета порций и лайков оставляем -->
    <script src="/assets/js/app.js"></script>
</body>
</html>