const quill = new Quill('#' + selectorname, {
    theme: 'snow',
    placeholder: 'Írj valamit...',
    modules: {
        toolbar: [
            ['bold', 'italic', 'underline'],
            [{ 'header': [1, 2, false] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link']
        ]
    }
});