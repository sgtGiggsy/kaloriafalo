let bookmarks= document.querySelectorAll(".bookmark");
let csillagok = document.querySelectorAll(".csillag");
bookmarks.forEach(bookmark => {
    bookmark.addEventListener("click",function(e){
        e.preventDefault();
        const receptId = bookmark.getAttribute("data-receptid");
        const formData = new FormData();

        formData.append("recept_id", receptId);
        formData.append("csrf_token", csrf_token);

        fetch(RootPath + "/api/recept/bookmark", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            let icon = 'success'
            let message = data.data.message
            let title = 'Siker'
            if(!data.success) {
                icon = 'error'
                message = data.error.message
                title = 'Hiba'
            }
            toaster.fire({
                title: title,
                text: message,
                icon: icon
            });
            if(data.success) {
                let bookmarkicons = document.getElementById("bookmark-" + receptId);
                bookmarkicons.querySelectorAll("svg").forEach(icon => {
                    icon.classList.toggle("active");
                });
            }
        })
        .catch(error => {
            toaster.fire({
                title: "Hiba",
                text: error,
                icon: "error"
            });
        });
    })
});

csillagok.forEach(csillag => {
    csillag.addEventListener("click", function (e) {
        e.preventDefault();
        const receptId = csillag.getAttribute("data-receptid");
        const ertek = csillag.getAttribute("data-ertek")
        const formData = new FormData();

        formData.append("recept_id", receptId);
        formData.append("ertekeles", ertek);
        formData.append("csrf_token", csrf_token);

        fetch(RootPath + "/api/recept/ertekel", {
            method: "POST",
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                let icon = 'success'
                let message = 'Recept értékelése sikeresen mentve'
                let title = 'Siker'
                if(!data.success) {
                    icon = 'error'
                    message = data.error.message
                    title = 'Hiba'
                }
                toaster.fire({
                    title: title,
                    text: message,
                    icon: icon
                });
            })
            .catch(error => {
                toaster.fire({
                    title: "Hiba",
                    text: error,
                    icon: "error"
                });
            });
    })
});