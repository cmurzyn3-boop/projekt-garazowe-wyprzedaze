// script.js
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-input');
    const categorySelect = document.getElementById('category-select');
    const resultsContainer = document.getElementById('results-container');

    if (searchInput && categorySelect && resultsContainer) {
        function fetchResults() {
            const query = searchInput.value;
            const category = categorySelect.value;

            fetch(`search_ajax.php?search=${encodeURIComponent(query)}&category=${encodeURIComponent(category)}`)
                .then(response => response.text())
                .then(html => {
                    resultsContainer.innerHTML = html;
                });
        }

        searchInput.addEventListener('input', fetchResults);
        categorySelect.addEventListener('change', fetchResults);
    }
});