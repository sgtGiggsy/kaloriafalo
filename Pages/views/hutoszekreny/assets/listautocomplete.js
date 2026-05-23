document.querySelectorAll('.autocomplete').forEach(createAutocomplete);

function createAutocomplete(input) {
    let tippek = [];
    let activeIndex = -1;
    let controller = null;
    let debounceTimer = null;

    const row = input.closest('.alapanyag-sor');
    const hidden = row.querySelector('input[type="hidden"]');

    const dropdown = document.createElement('div');
    dropdown.classList.add('autocomplete-list');
    document.body.appendChild(dropdown);

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(async () => {
            const val = input.value.trim();

            if (!val) {
                clearDropdown();
                return;
            }

            if (controller) controller.abort();
            controller = new AbortController();

            try {
                const res = await fetch(
                    RootPath + `/api/alapanyag/kereses/${encodeURIComponent(val)}`,
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
        if (!tippek.length && e.key !== 'Backspace') return;

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
                if (activeIndex >= 0) select(tippek[activeIndex]);
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
            div.textContent = item.alapanyag;
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
        input.value = item.alapanyag;
        hidden.value = item.alapanyag_id;

        clearDropdown();
        addNextRow();
    }

    function addNextRow() {
        const container = document.getElementById('alapanyagok');

        const newRow = document.createElement('div');
        newRow.classList.add('alapanyag-sor');

        const newInput = document.createElement('input');
        newInput.type = 'text';
        newInput.classList.add('autocomplete');

        const newHidden = document.createElement('input');
        newHidden.type = 'hidden';
        newHidden.name = 'alapanyagok[]';

        newRow.appendChild(newInput);
        newRow.appendChild(newHidden);

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