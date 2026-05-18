const images = document.querySelectorAll('.receptgallery img');
const receptoverlay = document.getElementById('receptgalleryoverlay');
const overlayImg = document.getElementById('overlayImg');
const closeBtn = document.getElementById('closeBtn');

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