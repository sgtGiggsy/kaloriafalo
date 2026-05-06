document.querySelectorAll('.autocomplete').forEach(createAutocomplete);

function createAutocomplete(input) {
    // API meghívása ha a felhasználó megáll a bevitellel egy pillanatra
    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(async () => {
            const val = input.value.trim();

            if(!val) {
                dropdown.innerHTML = '';
                return;
            }

            if(controller) controller.abort();
            controller = new AbortController();

            const res = await fetch(RootPath + `/api/alapanyag/kereses/${encodeURIComponent(val)}`, {
                signal: controller.signal
            });

            const json = await res.json();

            tippek = json.data || [];
            activeIndex = -1;

            render();
        }, 200);
    });

    // Billentyűzetes navigáció
    input.addEventListener('keydown', (e) => {
        if(!tippek.length && e.key !== 'Backspace')
            return;

        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                activeIndex = (activeIndex + 1) % tippek.length;
                render();
                break;

            case 'ArrowUp':
                e.preventDefault();
                activeIndex = (activeIndex - 1 + tippek.length) % tippek.length;
                render();
                break;

            case 'Enter':
                e.preventDefault();
                if (activeIndex >= 0) select(tippek[activeIndex]);
                break;

            case 'Backspace':
                handleBackspace();
                break;
        }
    });

    // Dropdown menü renderelése
    function render() {
        dropdown.innerHTML = '';
        positionDropdown();

        tippek.forEach((item, i) => {
            const div = document.createElement('div');
            div.textContent = item.alapanyag;

            if (i === activeIndex) div.classList.add('active');

            div.addEventListener('mousedown', (e) => {
                e.preventDefault(); // ne blur-öljön
                select(item);
            });

            dropdown.appendChild(div);
        });
    }

    // Elem kiválasztása
    function select(item) {
        input.value = item.alapanyag;
        hidden.value = item.slug;

        dropdown.innerHTML = '';

        addNextRow();
    }

    // Következő mezőre ugrás
    function addNextRow() {
        const container = document.getElementById('alapanyagok');

        const row = document.createElement('div');
        row.classList.add('alapanyag-sor');

        const input = document.createElement('input');
        input.type = 'text';
        input.classList.add('autocomplete');

        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'slug[]';

        row.appendChild(input);
        row.appendChild(hidden);

        container.appendChild(row);

        createAutocomplete(input);

        input.focus();
    }

    // Backspace billentyű leütésének kezelése
    function handleBackspace() {
        if (input.value === '') {
            const prevRow = row.previousElementSibling;

            if (prevRow) {
                row.remove();
                const prevInput = prevRow.querySelector('.autocomplete');
                prevInput.focus();
            }
        }
    }

    function positionDropdown() {
        const rect = input.getBoundingClientRect();

        dropdown.style.position = 'absolute';
        dropdown.style.top = `${rect.bottom + window.scrollY}px`;
        dropdown.style.left = `${rect.left + window.scrollX}px`;
        dropdown.style.width = `${rect.width}px`;
    }

    let tippek = [];
    let activeIndex = -1;
    let controller = null;
    let debounceTimer = null;

    const row = input.closest('.alapanyag-sor');
    const hidden = row.querySelector('input[type="hidden"]');

    const dropdown = document.createElement('div');
    dropdown.classList.add('autocomplete-list');
    document.body.appendChild(dropdown);

}