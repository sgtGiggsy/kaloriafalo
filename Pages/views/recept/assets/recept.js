const images = document.querySelectorAll('.receptgallery img');
const receptoverlay = document.getElementById('receptgalleryoverlay');
const overlayImg = document.getElementById('overlayImg');
const closeBtn = document.getElementById('closeBtn');
const torlesBtn = document.getElementById('torles');

images.forEach(img => {
    const preload = new Image();
    preload.src = img.src;
});

images.forEach(img => {
    img.addEventListener('click', () => {
        overlayImg.src = img.src;
        receptoverlay.classList.add('active');
    });
});

closeBtn.addEventListener('click', () => {
    receptoverlay.classList.remove('active');
});

receptoverlay.addEventListener('click', (e) => {
    if (e.target === receptoverlay) {
        receptoverlay.classList.remove('active');
    }
});

torlesBtn.addEventListener('click', (e) => {
    e.preventDefault();
    Swal.fire({
        title: "Biztos vagy benne, hogy törölni szeretnéd a receptet?",
        text: "A törölt receptet már nem tudod majd visszaállítani!",
        icon: "error",
        showCancelButton: true,
        confirmButtonColor: "var(--alertcolor)",
        cancelButtonColor: "var(--okaycolor)",
        confirmButtonText: "Igen, törlöm a receptet!",
        cancelButtonText: "Mégsem"
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement("form");
            form.method = "POST";
            form.action = torlesBtn.href;
            const token = document.createElement("input");
            token.type = "hidden";
            token.name = "csrf_token";
            token.value = csrf_token;
            const recept = document.createElement("input");
            recept.type = "hidden";
            recept.name = "recept_id";
            recept.value = recept_id;

            form.appendChild(token);
            form.appendChild(recept);

            document.body.appendChild(form);

            form.submit();
        }
    });
});