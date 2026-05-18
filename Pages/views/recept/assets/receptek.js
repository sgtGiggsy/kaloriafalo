let szurtorol = document.getElementById('szurtorol');

szurtorol.addEventListener('click', e => {
    e.preventDefault();
    document.querySelectorAll('input[name="cimke[]"]').forEach(cb => {
        cb.checked = false;
    });
    szurtorol.closest('form').submit();
});