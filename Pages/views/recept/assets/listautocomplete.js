var iter = iterator;
document.querySelectorAll('.autocomplete').forEach(createAutocomplete);
function parseInput(value) {
    const match = value.match(/^(\d+\s*[a-zA-Z]+)\s*(.*)$/);

    if (!match) {
        return {
            prefix: '',
            query: value
        };
    }

    return {
        prefix: match[1], // pl: "20 dkg"
        query: match[2]   // pl: "csirkemell"
    };
}

function selectItem(itemName, prefix) {
    input.value = prefix
        ? prefix + ' ' + itemName
        : itemName;
}

function normalizePrefix(prefix) {
    return prefix.replace(/(\d+)([a-zA-Z]+)/, '$1 $2');
}

function createAutocomplete(input) {
    // API meghívása ha a felhasználó megáll a bevitellel egy pillanatra
    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(async () => {
            //const val = input.value.trim();
            const { prefix, query } = parseInput(input.value.trim());
            mennyimertek = normalizePrefix(prefix);

            if (query.length < 2) return;

            if(controller) controller.abort();
            controller = new AbortController();

            const res = await fetch(RootPath + `/api/alapanyag/kereses/${encodeURIComponent(query)}`, {
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
            div.textContent = mennyimertek + " " + item.alapanyag;

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
        input.value = mennyimertek + " " + item.alapanyag;
        hidden.value = item.alapanyag_id;
        mennyiseg.value = mennyimertek;

        dropdown.innerHTML = '';

        addNextRow();
    }

    // Következő mezőre ugrás
    function addNextRow() {
        iter++;
        const container = document.getElementById('alapanyagok');

        const row = document.createElement('div');
        row.classList.add('alapanyag-sor');

        const input = document.createElement('input');
        input.type = 'text';
        input.classList.add('autocomplete');

        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'alapanyagok[' + iter + '][alapanyag]';

        const mennyiseg = document.createElement('input');
        mennyiseg.type = 'hidden';
        mennyiseg.name = 'alapanyagok[' + iter + '][mennyiseg]';

        row.appendChild(input);
        row.appendChild(hidden);
        row.appendChild(mennyiseg);

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
                iter--;
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
    var mennyimertek;

    const row = input.closest('.alapanyag-sor');
    const hidden = row.querySelector('[name="alapanyagok[' + iter + '][alapanyag]"]');
    const mennyiseg = row.querySelector('[name="alapanyagok[' + iter + '][mennyiseg]"]');

    const dropdown = document.createElement('div');
    dropdown.classList.add('autocomplete-list');
    document.body.appendChild(dropdown);

}