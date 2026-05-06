<?php
if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="normalcontent">
    <h1>Új jelszó megadása</h1>
    <form action="./jelszocsere" method="post">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div>
            <label for="regijelszo">Jelenlegi jelszó</label>
            <input type="password"
                name="regijelszo"
                placeholder="Régi jelszó"
                id="regijelszo"
                autocomplete="current-password"
                required>
        </div>
        <div>
            <label for="ujjelszo">Új jelszó</label>
            <input type="password"
                name="ujjelszo"
                placeholder="Új jelszó"
                id="ujjelszo"
                autocomplete="new-password"
                required>
        </div>
        <div>
            <label for="ujjelszoismetles">Jelszó megerősítés</label>
            <input type="password"
                name="ujjelszoismetles"
                placeholder="Jelszó megerősítés"
                id="ujjelszoismetles"
                autocomplete="new-password"
                required>
        </div>
        <div class="submit"><input type="submit" value="Új jelszó"></div>
    </form>
</div>