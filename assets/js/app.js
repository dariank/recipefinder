document.addEventListener('DOMContentLoaded', function() {
    // Находим все кнопки лайков на странице
    const likeButtons = document.querySelectorAll('.add-to-cart-btn');

    likeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // Отменяем стандартное поведение
            e.stopPropagation(); // Не даем клику "провалиться" на саму карточку рецепта

            const btn = this;
            const recipeId = btn.getAttribute('data-id');

            // Меняем иконку СРАЗУ (визуально)
            btn.style.color = '#e63946'; // Делаем красным
            btn.innerHTML = '<i class="fa-solid fa-heart"></i>'; // Меняем на закрашенное
            btn.disabled = true; // Отключаем кнопку

            // Отправляем данные на сервер в фоне
            const formData = new FormData();
            formData.append('recipe_id', recipeId);
            formData.append('action', 'add');

            fetch('/api/cart_action.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'success') {
                    console.error('Ошибка при добавлении');
                }
            })
            .catch(error => {
                console.error('Ошибка сети:', error);
            });
        });
    });
});