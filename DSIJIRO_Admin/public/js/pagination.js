function updatePaginationIndication(page, count) {
    if(count == 0) { count = 1; }
    document.getElementById('curr-page').innerHTML = page;
    document.getElementById('count-page').innerHTML = count;
}

function generatePagination(page, count) {
    if(count == 0) { count = 1; }
    var paginationContainer = document.querySelector('#pagination .pagination');
    paginationContainer.innerHTML = ''; // Réinitialiser le contenu

    // Fonction pour générer un item de page
    function createPageItem(pageNumber, isActive = false) {
        var activeClass = isActive ? 'active' : '';
        return `<li class="page-item ${activeClass}">
                    <button class="page-link" onclick="changePage(${pageNumber})">${pageNumber}</button>
                </li>`;
    }

    // Bouton "Précédent"
    var prevDisabled = page === 1 ? 'disabled' : '';
    var prevItem = `<li class="page-item ${prevDisabled}">
                    <button class="page-link" onclick="changePage(${page - 1})">
                        <i class="gd-angle-left"></i>
                    </button>
                    </li>`;
    paginationContainer.insertAdjacentHTML('beforeend', prevItem);

    // Afficher la première page
    paginationContainer.insertAdjacentHTML('beforeend', createPageItem(1, page === 1));

    // Si la page actuelle est plus grande que 3, ajoutez des points de suspension
    if (page > 3) {
        paginationContainer.insertAdjacentHTML('beforeend', `<li class="page-item disabled"><a class="page-link">...</a></li>`);
    }

    // Afficher les pages autour de la page actuelle
    for (var i = Math.max(2, page - 1); i <= Math.min(count - 1, page + 1); i++) {
        paginationContainer.insertAdjacentHTML('beforeend', createPageItem(i, i === page));
    }

    // Si la page actuelle est à plus de 2 pages de la fin, ajoutez des points de suspension
    if (page < count - 2) {
        paginationContainer.insertAdjacentHTML('beforeend', `<li class="page-item disabled"><a class="page-link">...</a></li>`);
    }

    // Afficher la dernière page
    if(count !== 1) {
        paginationContainer.insertAdjacentHTML('beforeend', createPageItem(count, page === count));
    }

    // Bouton "Suivant"
    var nextDisabled = page === count ? 'disabled' : '';
    var nextItem = `<li class="page-item ${nextDisabled}">
                    <button class="page-link" onclick="changePage(${page + 1})">
                        <i class="gd-angle-right"></i>
                    </button>
                    </li>`;
    paginationContainer.insertAdjacentHTML('beforeend', nextItem);
}

// Fonction pour changer de page
function changePage(newPage) {
    if (newPage >= 1 && newPage <= count) {
        page = newPage;
        generatePagination(page, count);
    }
    loadDataOfPage();
}