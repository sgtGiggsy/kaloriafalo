<?php

use Kaloriafalo\Classes\Helpers;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="normalcontent">
    <h1><?=ucfirst($this->hutoszekreny['huto']['huto_nev'])?></h1><?php
    if($this->irasjog) {
        ?><a href="<?= ROOT_PATH . '/hutoszekreny/' . $this->hutoszekreny['huto']['huto_id'] . '/szerkeszt' ?>">
            Hűtő szerkesztése
        </a>
        <?php
        Helpers::RenderArrayAsTable($this->hutoszekreny['tartalom'], 'alapanyag', 'slug');
    }
    ?><div class="autocomplete">
        <input type="text" id="ingredient" placeholder="Pl. liszt">
        <div id="suggestions" class="suggestions"></div>
    </div>

    <input type="hidden" id="ingredient_id">
</div>
<style>
    .autocomplete {
        position: relative;
        width: 250px;
    }

    .suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        border: 1px solid #ccc;
        background: #fff;
        max-height: 150px;
        overflow-y: auto;
        display: none;
    }

    .suggestions div {
        padding: 8px;
        cursor: pointer;
    }

    .suggestions div:hover {
        background: #f0f0f0;
    }
</style>
<script nonce="<?= \Kaloriafalo\Classes\Settings::$nonce ?>">
    const input = document.getElementById('ingredient');
    const suggestions = document.getElementById('suggestions');
    const hiddenInput = document.getElementById('ingredient_id');

    let debounceTimer;

    input.addEventListener('input', () => {
        const query = input.value.trim();

        clearTimeout(debounceTimer);

        if (query.length < 2) {
            suggestions.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(RootPath + `/api/alapanyagok/kereses/${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    suggestions.innerHTML = '';

                    if (!data.data.length) {
                        suggestions.style.display = 'none';
                        return;
                    }

                    data.data.forEach(item => {
                        const div = document.createElement('div');
                        div.textContent = item.alapanyag;

                        div.addEventListener('click', () => {
                            input.value = item.alapanyag;
                            hiddenInput.value = item.slug;
                            suggestions.style.display = 'none';
                        });

                        suggestions.appendChild(div);
                    });

                    suggestions.style.display = 'block';
                });
        }, 250);
    });

    // klikken kívül bezár
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.autocomplete')) {
            suggestions.style.display = 'none';
        }
    });
</script>