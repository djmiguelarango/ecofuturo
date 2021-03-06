<h3>Seguro de Desgravamen - Tenemos las siguientes ofertas</h3>
<h4>Escoge el plan que mas te convenga</h4>
<section style="text-align:center;">
<script type="text/javascript">
$(document).ready(function(e) {
    $('.f_cot_save').validateForm({
		method: 'GET',
		action: 'DE-save-share.php'
	});
});
</script>
<?php
require_once('sibas-db.class.php');
$link = new SibasDB();
$idc = $link->real_escape_string(trim(base64_decode($_GET['idc'])));

$sqlCia = 'select 
	sdc.id_cotizacion,
	sec.id_ef_cia,
	sdc.id_prcia,
	scia.id_compania as idcia,
	scia.nombre as cia_nombre,
	scia.logo as cia_logo,
	sdc.monto as valor_asegurado,
	sdc.moneda,
	sdc.tasa as t_tasa_final,
	sdc.modalidad
from
	s_de_cot_cabecera as sdc
		inner join
	s_entidad_financiera as sef ON (sef.id_ef = sdc.id_ef)
		inner join
	s_ef_compania as sec ON (sec.id_ef = sef.id_ef)
		inner join
	s_compania as scia ON (scia.id_compania = sec.id_compania)
where
	sdc.id_cotizacion = "'.$idc.'"
		and sef.id_ef = "'.base64_decode($_SESSION['idEF']).'"
		and sef.activado = true
		and sec.producto = "DE"
		and scia.activado = true
;';
// echo $sqlCia;
$rsCia = $link->query($sqlCia,MYSQLI_STORE_RESULT);
if($rsCia->num_rows > 0){
	$nForm = 0;
	$tipo_cambio = $link->get_rate_exchange(true);
	while ($rowCia = $rsCia->fetch_array(MYSQLI_ASSOC)) {
		if ($rowCia['modalidad'] !== null) {
			$sqlPe = 'select 
				spe.id_pr_extra,
				spe.id_ef_cia,
				spe.rango as pr_rango,
				spe.pr_hospitalario,
				spe.pr_vida,
				spe.pr_cesante,
				spe.pr_prima
			from
				s_de_producto_extra as spe
					inner join
				s_ef_compania as sec ON (sec.id_ef_cia = spe.id_ef_cia)
			where
				sec.id_ef_cia = "'.$rowCia['id_ef_cia'].'"
					and sec.activado = true
			;';
			// echo $sqlPe;
			if (($rsPe = $link->query($sqlPe, MYSQLI_STORE_RESULT)) !== false) {
				if ($rsPe->num_rows > 0) {
					while ($rowPe = $rsPe->fetch_array(MYSQLI_ASSOC)) {
						$swPe = false;
						$rank = json_decode($rowPe['pr_rango'], true);
						
						switch ($rowCia['moneda']) {
						case 'BS':
							if ($rowCia['valor_asegurado'] >= $rank[0] && $rowCia['valor_asegurado'] <= $rank[1]) {
								$swPe = true;
							}
							break;
						case 'USD':
							if ($rowCia['valor_asegurado'] >= $rank[2] && $rowCia['valor_asegurado'] <= $rank[3]) {
								$swPe = true;
							}
							break;
						}
						
						if ($swPe === true) {
							$nForm += 1;
							resultQuote($rowCia, false, $token, $rowPe, $nForm);
						}
					}
				}
			}
		} else {
			resultQuote($rowCia, true, $token);
		}
	}
}
?>
</section>
<br>
<br>

<div class="contact-phone">
	Todas las ofertas tienen las mismas condiciones, elige la compañía de tu elección<br><br>
	* Para cualquier duda o consulta, contacta a la Línea Gratuita de Sudamericana S.R.L. 800-10-3070
</div>
<?php
// --
function resultQuote ($rowCia, $modality, $token, $rowPe = null, $nForm = 0) {
?>
<div class="result-quote" style="height:300px;">
	<div class="rq-img">
		<img src="images/<?=$rowCia['cia_logo'];?>" alt="<?=$rowCia['cia_nombre'];?>" title="<?=$rowCia['cia_nombre'];?>">
	</div>
	<span class="rq-tasa">
		Tasa Desgravamen: 
		<?= number_format($rowCia['t_tasa_final'], 2, '.', ',') ;?> %
	</span>
	
	<a href="certificate-detail.php?idc=<?=
		base64_encode($rowCia['id_cotizacion']);?>&cia=<?=
		base64_encode($rowCia['idcia']);?>&pr=<?=
		base64_encode('DE');?>&type=<?=base64_encode('PRINT');?>" 
		class="fancybox fancybox.ajax btn-see-slip">Ver slip de Cotización</a>
<?php if ($token === true): ?>
	<?php if ($modality === true): ?>
	<a href="de-quote.php?ms=<?=$_GET['ms'];?>&page=<?=$_GET['page'];?>&pr=<?=
		base64_encode('DE|05');?>&idc=<?=$_GET['idc'];?>&flag=<?=
		md5('i-new');?>&cia=<?=base64_encode($rowCia['idcia']);?>" 
		class="btn-send">Emitir</a>	
	<?php endif ?>
<?php endif ?>
</div>
<?php
}

?>