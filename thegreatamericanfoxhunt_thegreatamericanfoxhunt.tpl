{OVERALL_GAME_HEADER}

<div id='TGAFH-playArea' class='TGAFH-PlayArea'>
	<div id='TGAFH-hand' class='TGAFH-Hand TGAFH-Hunters'></div>
	<div id='TGAFH-purchase' class='TGAFH-Purchase'></div>
	<div id='TGAFH-huntArea' class='TGAFH-HuntArea'>	</div>
	<div id='TGAFH-countryChoice' class='TGAFH-CountryChoice'></div>
</div>
<div id='TGAFH-inventory' class='TGAFH-Inventory'>	</div>

<script type="text/javascript">
	var TGAFHhunter = '<div id="TGAFH-hunter-${id}" class="TGAFH-Hunter" card_id="${id}" player_id="${player}" country="${country}" value="${value}"><div class="TGAFH-HunterContainer"></div></div>';
	var TGAFHanimalDeck = '<div class="TGAFH-deck TGAFH-Animal"></div>';
	var TGAFHanimal = '<div id="TGAFH-animal-${id}" class="TGAFH-Animal TGAFH-${animal}-${value}" card_id="${id}" animal="${animal}" value="${value}"></div>';
	var TGAFHhunt = '\
<div id="TGAFH-hunt-${id}" class="TGAFH-Hunt">\n\
	<div style="margin-right:10px;"></div>\n\
	<div id="TGAFH-hunters-${id}" class="TGAFH-Hunters" prey="${id}"></div>\n\
</div>';
</script>

{OVERALL_GAME_FOOTER}
