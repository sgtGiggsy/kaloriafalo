const images = document.querySelectorAll('.receptgallery img');
const receptoverlay = document.getElementById('receptgalleryoverlay');
const overlayImg = document.getElementById('overlayImg');
const closeBtn = document.getElementById('closeBtn');

// preload (nem kötelező, de kérted)
images.forEach(img => {
    const preload = new Image();
    preload.src = img.src;
});

// kattintás
images.forEach(img => {
    img.addEventListener('click', () => {
        overlayImg.src = img.src;
        receptoverlay.classList.add('active');
    });
});

// bezárás
closeBtn.addEventListener('click', () => {
    receptoverlay.classList.remove('active');
});

// háttérre kattintás is zár
receptoverlay.addEventListener('click', (e) => {
    if (e.target === receptoverlay) {
        receptoverlay.classList.remove('active');
    }
});