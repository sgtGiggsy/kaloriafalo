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
        prefix: match[1],
        query: match[2]
    };
}

function normalizePrefix(prefix) {
    return prefix.replace(/(\d+)([a-zA-Z]+)/, '$1 $2');
}

function createAutocomplete(input) {
    let tippek = [];
    let activeIndex = -1;
    let controller = null;
    let debounceTimer = null;
    let mennyimertek = '';

    const row = input.closest('.alapanyag-sor');

    const hidden = row.querySelector(
        '[name="alapanyagok[' + iter + '][alapanyag]"]'
    );

    const mennyiseg = row.querySelector(
        '[name="alapanyagok[' + iter + '][mennyiseg]"]'
    );

    const dropdown = document.createElement('div');
    dropdown.classList.add('autocomplete-list');
    document.body.appendChild(dropdown);

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(async () => {
            const { prefix, query } = parseInput(input.value.trim());

            mennyimertek = normalizePrefix(prefix);

            if (query.length < 2) {
                clearDropdown();
                return;
            }

            if (controller) controller.abort();
            controller = new AbortController();

            try {
                const res = await fetch(
                    RootPath + `/api/alapanyag/kereses/${encodeURIComponent(query)}`,
                    { signal: controller.signal }
                );

                const json = await res.json();

                tippek = json.data || [];
                activeIndex = -1;

                render();
            } catch (err) {
                if (err.name !== 'AbortError') {
                    clearDropdown();
                }
            }
        }, 200);
    });

    input.addEventListener('keydown', (e) => {
        if (!tippek.length && e.key !== 'Backspace' && e.key !== 'Escape') {
            return;
        }

        switch (e.key) {
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
                if (activeIndex >= 0) {
                    select(tippek[activeIndex]);
                }
                break;

            case 'Backspace':
                handleBackspace();
                break;

            case 'Escape':
                clearDropdown();
                break;
        }
    });

    input.addEventListener('blur', () => {
        setTimeout(() => {
            if (!dropdown.contains(document.activeElement)) {
                clearDropdown();
            }
        }, 0);
    });

    dropdown.addEventListener('focusout', () => {
        setTimeout(() => {
            if (
                document.activeElement !== input &&
                !dropdown.contains(document.activeElement)
            ) {
                clearDropdown();
            }
        }, 0);
    });

    window.addEventListener('scroll', positionDropdown);
    window.addEventListener('resize', positionDropdown);

    function render() {
        dropdown.innerHTML = '';

        if (!tippek.length) return;

        positionDropdown();

        tippek.forEach((item, i) => {
            const div = document.createElement('div');

            div.textContent = mennyimertek
                ? mennyimertek + ' ' + item.alapanyag
                : item.alapanyag;

            div.tabIndex = 0;

            if (i === activeIndex) {
                div.classList.add('active');
            }

            div.addEventListener('mousedown', (e) => {
                e.preventDefault();
                select(item);
            });

            div.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    select(item);
                }

                if (e.key === 'Escape') {
                    clearDropdown();
                    input.focus();
                }
            });

            dropdown.appendChild(div);
        });
    }

    function select(item) {
        input.value = mennyimertek
            ? mennyimertek + ' ' + item.alapanyag
            : item.alapanyag;

        hidden.value = item.alapanyag_id;
        mennyiseg.value = mennyimertek;

        clearDropdown();

        addNextRow();
    }

    function addNextRow() {
        iter++;

        const container = document.getElementById('alapanyagok');

        const newRow = document.createElement('div');
        newRow.classList.add('alapanyag-sor');

        const newInput = document.createElement('input');
        newInput.type = 'text';
        newInput.classList.add('autocomplete');

        const newHidden = document.createElement('input');
        newHidden.type = 'hidden';
        newHidden.name = 'alapanyagok[' + iter + '][alapanyag]';

        const newMennyiseg = document.createElement('input');
        newMennyiseg.type = 'hidden';
        newMennyiseg.name = 'alapanyagok[' + iter + '][mennyiseg]';

        newRow.appendChild(newInput);
        newRow.appendChild(newHidden);
        newRow.appendChild(newMennyiseg);

        container.appendChild(newRow);

        createAutocomplete(newInput);

        newInput.focus();
    }

    function handleBackspace() {
        if (input.value === '') {
            const prevRow = row.previousElementSibling;

            if (prevRow) {
                row.remove();

                const prevInput = prevRow.querySelector('.autocomplete');

                prevInput.focus();

                const len = prevInput.value.length;
                prevInput.setSelectionRange(len, len);

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

    function clearDropdown() {
        tippek = [];
        activeIndex = -1;
        dropdown.innerHTML = '';
    }
}