{*
* Okom3pom
*
* Module Vip Card for Prestashop 1.6.x.x
*
* @author    Okom3pom <contact@okom3pom.com>
* @copyright 2008-2018 Okom3pom
* @license   Free
*}

<!-- MODULE Okom VIP -->
<div class="well well-sm">

	{if $is_vip == false || $exprired == true}
		<div class="row">
				<div class="col-sm-3">
					<img class="img-responsive" width="200"src='{$modules_dir}/okom_vip/img/vip.png' alt='{l s='Devenez client Vip' mod='okom_vip'}'>
				</div>
			    <div class="col-sm-8">

				<h3>{l s='Pas encore membre VIP, profitez d\'avantages avec notre carte VIP !' mod='okom_vip'}</h3>
				{l s='Livraison Offerte dès 25€' mod='okom_vip'}<br/>
				{l s='Commande prioritaire' mod='okom_vip'}<br/>
				{l s='Des offres de folies réservées aux membres VIP' mod='okom_vip'}<br/><br/>
				<a class="button button-small btn btn-default" href="{$vip_product_url}"><span>{l s='Devenir membre VIP !' mod='okom_vip'}</span></a><br/><br/>
			</div>
		</div>
	{else}
	
		<div style="text-align: center"><h3>{l s='Votre carte VIP et avantages expire dans' mod='okom_vip'} </h3></div>
		<div id="countdownvip"></div>
		{assign var="date_vf" value="-"|explode:$customer_vip['vip_end']}
		{assign var="day" value=" "|explode:$date_vf[2]}
		{assign var="hms" value=":"|explode:$day[1]} 
		<script type="text/javascript">
			$(function(){
				var ts = new Date({$date_vf[0]} ,{$date_vf[1]-1} , {$day[0]}, {$hms[0]}, {$hms[1]} , 00 );
				var newYear = true;		
				if((new Date()) > ts){
					ts = (new Date()).getTime() + 10*24*60*60*1000;
					newYear = false;
				}			
				$('#countdownvip').countdown({
					timestamp	: ts,
					callback	: function(days, hours, minutes, seconds){
						var message = "";
						message += days + " jour" + ( days == 1 ? '':'s' ) + ", ";
						message += hours + " heure" + ( hours==1 ? '':'s' ) + ", ";
						message += minutes + " minute" + ( minutes==1 ? '':'s' ) + " et ";
						message += seconds + " seconde" + ( seconds==1 ? '':'s' );
					}
				});		
			});
		</script>

	{/if}
</div>
<!-- END : Okom VIP  -->
