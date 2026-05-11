<?php
if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="normalcontent"><?php
    if($this->megerosit)
    {
        ?><h1>Új jelszó megadása</h1>
        <form action="<?= ROOT_PATH ?>/elfelejtettjelszo" method="post">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="megerosit" value="<?= $this->megerositokod ?>">
            <div>
                <label for="jelszo">Jelszó</label>
                <input type="password"
                    name="jelszo"
                    placeholder="Jelszó"
                    id="jelszo1"
                    autocomplete="new-password"
                    required>
            </div>
            <div>
                <label for="jelszoismetles">Jelszó megerősítés</label>
                <input type="password"
                    name="jelszoismetles"
                    placeholder="Jelszó megerősítés"
                    id="jelszoismetles"
                    autocomplete="new-password"
                    required>
            </div>
            <div class="submit"><input type="submit" value="Új jelszó"></div>
        </form><?php
    }
    else
    {
        ?><h1>Hibás megerősítőkód</h1>
        <p>Ismeretlen megerősítőkód! Ezzel a kóddal nem lehet jelszót helyreállítani.</p><?php
    }
?></div>