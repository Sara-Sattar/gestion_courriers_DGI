/**
 * Recherche live AJAX pour les listes (même comportement que Accueil).
 *
 * Sur le conteneur : data-liste-recherche data-url="..." data-target="#table-wrapper"
 * Sur chaque champ : data-param="recherche" | data-param="statut" | …
 * Bouton effacer : data-clear-filtre (dans un parent data-filtre-effacable)
 */
(function () {
    function debounce(fn, delay) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function syncBoutonEffacer(champ) {
        const wrap = champ.closest('[data-filtre-effacable]');
        if (!wrap) {
            return;
        }

        const bouton = wrap.querySelector('[data-clear-filtre]');
        if (!bouton) {
            return;
        }

        const aValeur = (champ.value || '').trim() !== '';
        bouton.hidden = !aValeur;
    }

    function initListeRecherche(root) {
        const url = root.dataset.url;
        const targetSelector = root.dataset.target || '#table-wrapper';
        const target = document.querySelector(targetSelector);

        if (!url || !target) {
            return;
        }

        const champs = root.querySelectorAll('[data-param]');

        const fetchResults = () => {
            const params = new URLSearchParams();

            champs.forEach((champ) => {
                const cle = champ.dataset.param;
                const valeur = (champ.value || '').trim();
                if (cle && valeur !== '') {
                    params.set(cle, valeur);
                }
            });

            fetch(url + (params.toString() ? '?' + params.toString() : ''), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'text/html',
                },
            })
                .then((response) => response.text())
                .then((html) => {
                    target.innerHTML = html;
                })
                .catch((error) => console.error('Erreur lors de la recherche:', error));
        };

        const fetchDebounced = debounce(fetchResults, 300);

        champs.forEach((champ) => {
            syncBoutonEffacer(champ);

            const evenement = champ.tagName === 'SELECT' || champ.type === 'date' || champ.type === 'hidden'
                ? 'change'
                : 'input';
            const handler = evenement === 'input' ? fetchDebounced : fetchResults;

            champ.addEventListener(evenement, () => {
                syncBoutonEffacer(champ);
                handler();
            });
        });

        root.querySelectorAll('[data-clear-filtre]').forEach((bouton) => {
            bouton.addEventListener('click', () => {
                const wrap = bouton.closest('[data-filtre-effacable]');
                const champ = wrap ? wrap.querySelector('[data-param]') : null;
                if (!champ) {
                    return;
                }

                champ.value = '';
                syncBoutonEffacer(champ);
                fetchResults();
                champ.focus();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-liste-recherche]').forEach(initListeRecherche);
    });
})();
