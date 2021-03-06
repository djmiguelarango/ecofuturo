<?php
$product = '';
if(isset($_GET['pr']) && isset($_GET['ide'])){
	$product = base64_decode($_GET['pr']);
} else {
	echo '<meta http-equiv="refresh" content="0;url=index.php" >';
}

include('header.inc.php');
?>
<script type="text/javascript">
if(history.forward(1)){location.replace(history.forward(1))}

$(document).ready(function(e) {
    $(".fancybox").fancybox({
		
	});
});
</script>
<div id="content-main">
	<section id="main">
<?php
$ide = trim(base64_decode($_GET['ide']));
$idc = NULL;
$cp = false;
$type = base64_encode('PRINT');
$category = NULL;
$pr = base64_encode($product);
$swDE = false;

$titleSlip = 'Slip de Cotización';
$titleSlip2 = '';
$titleCert = 'Certificado';
$titleCert2 = '';

if($token === TRUE){
	$sqlIs = '';
	switch($product){
		case 'DE':
			$titleSlip2 = 'Slip Vida en Grupo';
			$titleCert = 'Certificado Desgravamen';
			$titleCert2 = 'Certificado Vida en Grupo';
			
			$sqlIs = 'select 
				sde.id_emision as ide,
				sdc.id_cotizacion as idc,
				sde.prefijo,
				sde.no_emision,
				sde.id_compania,
				sde.certificado_provisional as cp,
				sde.modalidad,
				sde.monto_solicitado,
				sde.moneda,
				stc.valor_boliviano as tipo_cambio,
				sum(if(sdd.vg = true, 1, 0)) as vg_no
			from 
				s_de_em_cabecera as sde
					inner join 
				s_de_cot_cabecera as sdc ON (sdc.id_cotizacion = sde.id_cotizacion)
					inner join
				s_de_em_detalle as sdd ON (sdd.id_emision = sde.id_emision)
					inner join 
				s_tipo_cambio as stc ON (stc.id_tc = sde.id_tc)
			where sde.id_emision = "'.$ide.'"
			;';
			break;
		case 'AU':
			$sqlIs = 'select 
					sae.id_emision as ide,
					sac.id_cotizacion as idc, 
					sae.prefijo,
					sae.no_emision,
					sae.id_compania,
					sae.certificado_provisional as cp
				from s_au_em_cabecera as sae
					inner join s_au_cot_cabecera as sac on (sac.id_cotizacion = sae.id_cotizacion)
				where sae.id_emision = "'.$ide.'"
			;';
			break;
		case 'TRD':
			$sqlIs = 'select 
					stre.id_emision as ide,
					strc.id_cotizacion as idc, 
					stre.prefijo,
					stre.no_emision,
					stre.id_compania,
					stre.certificado_provisional as cp
				from s_trd_em_cabecera as stre
					inner join s_trd_cot_cabecera as strc on (strc.id_cotizacion = stre.id_cotizacion)
				where stre.id_emision = "'.$ide.'"
			;';
			break;
		case 'TRM':
			$sqlIs = 'select 
					stre.id_emision as ide,
					strc.id_cotizacion as idc, 
					stre.prefijo,
					stre.no_emision,
					stre.id_compania,
					stre.certificado_provisional as cp
				from s_trm_em_cabecera as stre
					inner join s_trm_cot_cabecera as strc on (strc.id_cotizacion = stre.id_cotizacion)
				where stre.id_emision = "'.$ide.'"
			;';
			break;
	}
	
	$rsIs = $link->query($sqlIs,MYSQLI_STORE_RESULT);
	if($rsIs->num_rows === 1){
		$rowIs = $rsIs->fetch_array(MYSQLI_ASSOC);
		$ide = base64_encode($rowIs['ide']);
		$idc = base64_encode($rowIs['idc']);
		$cp = (boolean)$rowIs['cp'];
		
		if ($cp === true) {
			$category = base64_encode('CP');
			$titleCert = 'Certificado Provisional';
		} else {
			$category = base64_encode('CE');
			if ($product === 'DE') {
				$titleCert = 'Certificado Desgravamen';
			}
		}
?>
<h3 id="issue-title">Póliza <?=$rowIs['prefijo'] . '-' . $rowIs['no_emision'];?></h3>

<a href="certificate-detail.php?idc=<?=$idc;?>&cia=<?=
	base64_encode($rowIs['id_compania']);?>&type=<?=
	$type;?>&pr=<?=$pr;?>" class="fancybox fancybox.ajax view-detail">
	Ver <?=$titleSlip;?></a>

<a href="certificate-detail.php?ide=<?=$ide;?>&type=<?=
	$type;?>&pr=<?=$pr;?>&category=<?=$category;?>" 
	class="fancybox fancybox.ajax view-detail">Ver <?=$titleCert;?></a>
<?php if ($product === 'DE'): ?>
	<?php if ((int)$rowIs['vg_no'] > 0): ?>
<a href="certificate-detail.php?ide=<?=$ide;?>&type=<?=
	$type;?>&pr=<?=$pr;?>&category=<?=base64_encode('VGC');?>" 
	class="fancybox fancybox.ajax view-detail">Ver Certificado Vida Grupo</a>
	<?php endif ?>
<?php endif ?>
<?php
	} else {
		echo 'Usted no puede visualizar los Cetificados';
	}
} else {
	include('index-content.inc.php');
}
?>
	</section>
</div>	
<?php
include('footer.inc.php');
?>