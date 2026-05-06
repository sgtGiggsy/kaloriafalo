<?php
use Kaloriafalo\Classes\Settings;

?><script type="text/javascript" nonce="<?=Settings::$nonce?>">
	const policy = trustedTypes.createPolicy('default', {
		createHTML: (input) => input
	});
    const urlParams = new URLSearchParams(window.location.search);
    <?php foreach(Settings::$PHPvarsToJS as $key => $value)
    {
		if(is_array($value))
		{
			?>const <?=$key?> = [<?php
			$cval = count($value);
			for($i = 0; $i < $cval; $i++)
			{
				echo '"' . $value[$i] . '"';
				if($i < $cval - 1)
				echo ', ';
			}
			?>] <?php
		}	
		else
		{
			?>const <?=$key?> = '<?=$value?>'; <?php
		}

    }
?></script><?php
foreach(Settings::$jsfiles as $js)
{
	?><script src="<?=$RootPath?>/<?=$js?>"></script><?php
}

if(isset($swaltoaster))
{
	?><script nonce="<?=Settings::$nonce?>">
		Swal.fire({
			title: "<?= $swaltoaster['title'] ?>",
			text: "<?= $swaltoaster['text'] ?>",
			icon: "<?= $swaltoaster['icon'] ?>",
		});
	</script><?php
}

if(defined('SWALMIXIN'))
{
	?><script nonce="<?=Settings::$nonce?>">
		toaster.fire({
			icon: "<?= SWALMIXIN['icon'] ?>",
			title: "<?= SWALMIXIN['title'] ?>"
		});
	</script><?php
}

if(Settings::$admin && false/*&& olvasatlanUzenofal()*/)
{
	?><script nonce="<?=Settings::$nonce?>">
		let uzenet = Swal.mixin({
			toast: true,
			position: "top-end",
			showConfirmButton: false,
			timer: 3000,
			timerProgressBar: true,
			didOpen: (toast) => {
				toast.onmouseenter = Swal.stopTimer;
				toast.onmouseleave = Swal.resumeTimer;
			}
		})
		uzenet.fire({
			icon: "<?= "warning" ?>",
			title: "<?= "Új üzenőfal üzenet" ?>",
			html: `<a href="./uzenofal">Kattints ide az olvasáshoz!</a>`
		});
	</script><?php
}