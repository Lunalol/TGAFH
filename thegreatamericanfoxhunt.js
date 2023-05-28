const DELAY = 500;
//
define([
	"dojo", "dojo/_base/declare", "ebg/core/gamegui", "ebg/counter"
], function (dojo, declare)
{
	return declare("bgagame.thegreatamericanfoxhunt", ebg.core.gamegui,
	{
		constructor: function ()
		{
			console.log('thegreatamericanfoxhunt constructor');
//
//			this.dontPreloadImage('United-States.png');
//			this.dontPreloadImage('Great-Britain.png');
//			this.dontPreloadImage('Japan.png');
//			this.dontPreloadImage('Ghana.png');
//			this.dontPreloadImage('Sweden.png');
//			this.dontPreloadImage('Spain.png');
//
			this.default_viewport = 'initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,width=device-width,user-scalable=no';
//
			this.ro = new ResizeObserver(entries => {
				for (let entry of entries)
					this.sort(entry.target);
			});
		},
//
// Game setup
//
		setup: function (gamedatas)
		{
			console.log("Starting game setup");
//
			this.deck = false;
//
			for (let player of Object.values(gamedatas.players))
			{
				dojo.place('<span>%</span>', `player_score_${player.id}`, 'after');
				dojo.place(`<div class='TGAFH-Player' id='TGAFH_player_${player.id}' style='background-color:#${player.color};'><span id='TGAFH_hand_${player.id}' class='TGAFH-PlayerHand' title='${_('Hunters/dogs in hand')}'></span><span id='TGAFH_discard_${player.id}' class='TGAFH-PlayerDiscard' title='${_('Discarded hunters/dogs')}'></span></div>`, `player_board_${player.id}`);
				let node = dojo.place(`<div class='TGAFH-Player' id='TGAFH_animals_${player.id}' style='background-color:#${player.color};'></div>`, `player_board_${player.id}`);
				new dijit.Tooltip({
					connectId: node, showDelay: DELAY, hideDelay: 0, position: this.defaultTooltipPosition,
					getContent: (matchedNode) =>
					{
						let html = '<div class="TGAFH-Purchase">';
						for (let animal of Object.values(player.counts))
						{
							for (let prey of Object.values(animal[2]))
							{
								html += this.format_block('TGAFHanimal', {id: prey.id, animal: prey.type, value: prey.type_arg});
							}
						}
						html += '</div>';
						return html;
					}
				});
				this.counts(player.id, player.counts);
			}
//
// Create animal deck
//
			if ('deck' in gamedatas.animals && this.deck)
			{

				for (let i = 0; i < gamedatas.animals.deck; i++)
				{
					let node = dojo.place(this.format_block('TGAFHanimalDeck'), 'TGAFH-huntArea');
					dojo.style(node, 'position', 'absolute');
					dojo.style(node, 'z-index', i);
					dojo.style(node, 'opacity', '50%');
					dojo.style(node, 'left', `${Math.random() * 60 + 10}%`);
					dojo.style(node, 'top', `${Math.random() * 60 + 10}%`);
					dojo.style(node, 'transform', `rotate(${Math.random() * 360}deg)`);
				}
			}
//
//
// Create hunt area
//
			let huntArea = dojo.place(this.format_block('TGAFHhunt', {id: 0}), 'TGAFH-huntArea');
//
			if (!this.deck) dojo.place(this.format_block('TGAFHanimalDeck', {id: 0}), huntArea, 'first');
//
			dojo.connect(huntArea.querySelector('.TGAFH-Hunters'), 'click', this, 'onHunt');
			this.ro.observe(huntArea.querySelector('.TGAFH-Hunters'));
//
			for (let prey of Object.values(gamedatas.preys))
			{
				let node = dojo.place(this.format_block('TGAFHhunt', {id: prey.id}), 'TGAFH-huntArea');
				dojo.connect(node.querySelector('.TGAFH-Hunters'), 'click', this, 'onHunt');
				this.ro.observe(node.querySelector('.TGAFH-Hunters'));
//
				dojo.place(this.format_block('TGAFHanimal', {id: prey.id, animal: prey.type, value: prey.type_arg}), `TGAFH-hunt-${prey.id}`, 'first');
//
				for (let hunter of Object.values(prey.hunters))
				{
					let node = dojo.place(this.format_block('TGAFHhunter', {id: hunter.id, player: hunter.location_arg, country: hunter.type, value: hunter.type_arg}), `TGAFH-hunters-${prey.id}`);
					dojo.addClass(node, 'TGAFH-Disabled');
				}
			}
//
			for (let animal of gamedatas.ANIMALS)
			{
				let node = dojo.place(this.format_block('TGAFHanimal', {id: 0, animal: animal.type, value: animal.type_arg}), 'TGAFH-inventory');
				dojo.setAttr(node, 'N', animal.nbr);
			}
			for (let dog of gamedatas.DOGS)
			{
				let node = dojo.place(this.format_block('TGAFHhunter', {id: 0, player: 0, country: dog.type, value: dog.type_arg}), 'TGAFH-inventory');
				dojo.setAttr(node, 'N', dog.nbr);
			}
//
			this.notif_update_decks({args: {}});
//
			this.ro.observe($('TGAFH-hand'));
			dojo.connect($('TGAFH-hand'), 'click', this, () => {
				dojo.query(`.TGAFH-Hunter.TGAFH-Selected`, 'TGAFH-hand').removeClass('TGAFH-Selected');
			});
//
			dojo.connect($('TGAFH-purchase'), 'click', this, () => {
				dojo.query(`.TGAFH-Animal.TGAFH-Selected`, 'TGAFH-purchase').removeClass('TGAFH-Selected');
			});
//
			this.setupNotifications();
//
			console.log("Ending game setup");
		},
//
// onEnteringState
//
		onEnteringState: function (stateName, state)
		{
			console.log('Entering state: ' + stateName, state.args);
//
			if (stateName === 'countryChoice')
			{
				for (let [index, country] of Object.entries(state.args.countries))
				{
					dojo.style('TGAFH-countryChoice', 'display', 'flex');
					let node = dojo.place(`<div id='TGAFH-country-${index}' country=${index} class='TGAFH-Country'><img src='${g_gamethemeurl + 'img/' + country.replace(' ', '-')}.png' style='width:100%;max-height:25vh;'/></div>`, 'TGAFH-countryChoice');
					dojo.connect(node, 'click', this, 'onCountry');
				}
			}
//
			if (state.args && '_private' in state.args)
			{
				this.country = this.gamedatas.players[this.player_id].country;
//
				if ('hand' in state.args._private)
				{
					this.hand = {};
					dojo.empty('TGAFH-hand');
					dojo.style('TGAFH-hand', 'opacity', '');
					dojo.style('TGAFH-hand', 'pointer-events', '');
//
					for (let hunter of Object.values(state.args._private.hand))
					{
						let node = dojo.place(this.format_block('TGAFHhunter', {id: hunter.id, player: hunter.location_arg, country: hunter.type, value: hunter.type_arg}), 'TGAFH-hand');
						dojo.connect(node, 'click', this, 'onHunter');
						this.hand[hunter.id] = hunter;
					}
					this.sort($('TGAFH-hand'));
				}
//
				if ('animals' in state.args._private)
				{
					dojo.style('TGAFH-hand', 'opacity', 0.75);
					dojo.style('TGAFH-hand', 'pointer-events', 'none');
					for (let animal of Object.values(state.args._private.animals))
					{
						let node = dojo.place(this.format_block('TGAFHanimal', {id: animal.id, animal: animal.type, value: animal.type_arg}), `TGAFH-purchase`);
						dojo.connect(node, 'click', this, 'onAnimal');
					}
				}
			}
//
			if (this.isCurrentPlayerActive())
			{
				if (this.gamedatas.gamestate.possibleactions.includes('hunt'))
				{
					this.addActionButton('TGAFHhunt', _('Hunt'), 'hunt');
					dojo.addClass('TGAFHhunt', 'disabled');
				}
				if (this.gamedatas.gamestate.possibleactions.includes('purchase'))
				{
					this.addActionButton('TGAFHpurchase', _('Purchase dog(s)'), 'purchase');
					dojo.addClass('TGAFHpurchase', 'disabled');
					dojo.place(`<span style='font-size:small;vectical-align:middle;margin:0px 5px;'>${_('(8 points per dog)')}</span>`, 'TGAFHpurchase', 'after');
				}
				if (this.gamedatas.gamestate.possibleactions.includes('pass'))
					this.addActionButton('TGAFHpass', _('Pass'), 'pass');
//
			}
		},
//
// onLeavingState
//
		onLeavingState: function (stateName)
		{
			console.log('Leaving state: ' + stateName);
//
			dojo.style('TGAFH-countryChoice', 'display', '');
			dojo.empty('TGAFH-countryChoice');
			dojo.empty('TGAFH-purchase');
		},
//
// onUpdateActionButtons
//
		onUpdateActionButtons: function (stateName, args)
		{
			console.log('onUpdateActionButtons: ' + stateName);
			//
		},
//
// Notifications
//
		setupNotifications: function ()
		{
			console.log('notifications subscriptions setup');
//
			dojo.subscribe('choose_country', this, 'notif_choose_country');
			dojo.subscribe('place_animal', this, 'notif_place_animal');
			dojo.subscribe('remove_animal', this, 'notif_remove_animal');
			dojo.subscribe('place_hunters', this, 'notif_place_hunters');
			dojo.subscribe('remove_hunters', this, 'notif_remove_hunters');
			dojo.subscribe('update_decks', this, 'notif_update_decks');
			dojo.subscribe('update_counts', this, 'notif_update_counts');
			dojo.subscribe('update_score', this, 'notif_update_score');
//
			this.notifqueue.setSynchronous('place_animal', DELAY);
			this.notifqueue.setSynchronous('remove_animal', DELAY * 4);
			this.notifqueue.setSynchronous('place_hunters', DELAY);
			this.notifqueue.setSynchronous('remove_hunters', DELAY);
		},
		notif_choose_country: function (notif)
		{
			console.log('notif_choose_country', notif.args);
//
			this.gamedatas.players[notif.args.player_id].color = notif.args.color;
//
			dojo.setStyle($(`player_name_${notif.args.player_id}`).getElementsByTagName('a')[0], 'color', '#' + notif.args.color);
			dojo.setStyle(`TGAFH_player_${notif.args.player_id}`, 'background', '#' + notif.args.color);
			dojo.setStyle(`TGAFH_animals_${notif.args.player_id}`, 'background', '#' + notif.args.color);
			dojo.query('.playername').forEach((node) => {
				if (node.innerHTML === this.gamedatas.players[notif.args.player_id].name)
				{
					dojo.setStyle(node, 'background', '');
					dojo.setStyle(node, 'color', '#' + notif.args.color);
				}
			});
		},
		notif_place_animal: function (notif)
		{
			console.log('notif_place_animal', notif.args);
//
// Create hunt area
//
			let node = dojo.place(this.format_block('TGAFHhunt', {id: notif.args.animal.id}), 'TGAFH-hunt-0', 'after');
			dojo.connect(node.querySelector('.TGAFH-Hunters'), 'click', this, 'onHunt');
			this.ro.observe(node.querySelector('.TGAFH-Hunters'));
//
// Reveal animal
//
			if (this.deck)
			{
				let nodes = dojo.query('.TGAFH-HuntArea>.TGAFH-Animal');
				let anim = this.slideToObjectPos(nodes[Math.floor(Math.random() * nodes.length)], node, 0, 0, DELAY);
				anim.onEnd = (node) => {
					dojo.destroy(node);
					dojo.place(this.format_block('TGAFHanimal', {id: notif.args.animal.id, animal: notif.args.animal.type, value: notif.args.animal.type_arg}), `TGAFH-hunt-${notif.args.animal.id}`, 'first');
				};
				anim.play();
			}
			else dojo.place(this.format_block('TGAFHanimal', {id: notif.args.animal.id, animal: notif.args.animal.type, value: notif.args.animal.type_arg}), `TGAFH-hunt-${notif.args.animal.id}`, 'first');
		},
		notif_remove_animal: function (notif)
		{
			console.log('notif_remove_animal', notif.args);
//
// Destroy hunt area (with animal)
//
			if ('player_id' in notif.args)
			{
				let node = `TGAFH-animal-${notif.args.animal.id}`;
				dojo.style(node, 'top', '0');
				dojo.style(node, 'left', '0');
				dojo.style(node, 'position', 'absolute');
				let anim = this.slideToObject(node, `player_board_${notif.args.player_id}`, DELAY * 2);
				dojo.connect(anim, 'onEnd', () => this.fadeOutAndDestroy(`TGAFH-hunt-${notif.args.animal.id}`, DELAY));
				anim.play();
			}
			else
				this.fadeOutAndDestroy(`TGAFH-hunt-${notif.args.animal.id}`, DELAY);
		},
		notif_place_hunters: function (notif)
		{
			console.log('notif_place_hunters', notif.args);
//
			dojo.empty(`TGAFH-hunters-0`);
			dojo.empty(`TGAFH-hunters-${notif.args.animal.id}`);
			for (let hunter of Object.values(notif.args.hunters))
			{
				let node = dojo.place(this.format_block('TGAFHhunter', {id: hunter.id, player: hunter.location_arg, country: hunter.type, value: hunter.type_arg}), `TGAFH-hunters-${notif.args.animal.id}`);
				dojo.addClass(node, 'TGAFH-Disabled');
			}
			this.sort($(`TGAFH-hunters-${notif.args.animal.id}`));
		},
		notif_remove_hunters: function (notif)
		{
			console.log('notif_remove_hunters', notif.args);
//
			for (let hunter of Object.values(notif.args.hunters)) dojo.destroy(`TGAFH-hunter-${hunter.id}`, DELAY);
		},
		notif_update_decks: function (notif)
		{
			console.log('notif_update_decks', notif.args);
//
			if ('animals' in notif.args) this.gamedatas.animals = notif.args.animals;
			if ('hands' in notif.args) this.gamedatas.hands = notif.args.hands;
			if ('discards' in notif.args) this.gamedatas.discards = notif.args.discards;
			if ('dogs' in notif.args) this.gamedatas.dogs = notif.args.dogs;
//
			for (let player of Object.values(this.gamedatas.players))
			{
				$(`TGAFH_hand_${player.id}`).innerHTML = player.id in this.gamedatas.hands ? this.gamedatas.hands[player.id] : 0;
				$(`TGAFH_discard_${player.id}`).innerHTML = player.id in this.gamedatas.discards ? this.gamedatas.discards[player.id] : 0;
			}
//
//			$('TGAFH-deck').style.setProperty('--N', this.gamedatas.animals.deck / 10);
		},
		notif_update_counts: function (notif)
		{
			console.log('notif_update_counts', notif.args);
//
			this.counts(notif.args.player_id, notif.args.counts);
		},
		notif_update_score(notif)
		{
			console.info("notif_update_score", notif.args);
//
			this.scoreCtrl[notif.args.player_id].setValue(notif.args.score);
		},
//
// Event
//
		onCountry: function (event)
		{
			dojo.stopEvent(event);
//
			if (this.isCurrentPlayerActive() && this.checkAction('country', true))
			{
				const country = event.currentTarget;
				this.action('country', {country: dojo.getAttr(country, 'country')});
			}
		},
		onHunter: function (event)
		{
			dojo.stopEvent(event);
//
			if (this.isCurrentPlayerActive() && this.checkAction('hunt', true))
			{
				const hunter = event.currentTarget;
//
				if (dojo.getAttr(hunter, 'card_id') in this.hand)
				{
					if (hunter.closest('.TGAFH-Hand'))
					{
						dojo.toggleClass(hunter, 'TGAFH-Selected');
//
						let hunters = dojo.query(`.TGAFH-Hunter[player_id=${this.player_id}]:not(.TGAFH-Disabled)`, 'TGAFH-huntArea');
						if (hunters.length) hunters.forEach((node) => dojo.toggleClass(node.closest('.TGAFH-Hunt'), 'TGAFH-Possible', dojo.query('.TGAFH-Selected', 'TGAFH-hand').length));
						else dojo.query(`.TGAFH-Hunt`, 'TGAFH-huntArea').toggleClass('TGAFH-Possible', dojo.query('.TGAFH-Selected', 'TGAFH-hand').length);
//
						return;
					}
//
					let huntArea = hunter.closest('.TGAFH-Hunters');
					if (huntArea)
					{
						if (dojo.getAttr(hunter, 'country') > 0 && dojo.query(`.TGAFH-Hunter[player_id=${this.player_id}][country=0]:not(.TGAFH-Disabled)`, huntArea).length)
						{
							let hunters = dojo.query(`.TGAFH-Hunter[player_id=${this.player_id}]:not([country=0])`, huntArea).length;
							if (hunters <= 1) return this.showMessage(_('Dogs can\'t hunt alone'), 'error');
						}
//
						dojo.removeClass(hunter, 'TGAFH-Selected');
						$('TGAFH-hand').appendChild(hunter);
//
						this.sort($('TGAFH-hand'));
						this.sort(huntArea);
					}
				}
				if (dojo.query(`.TGAFH-Hunter[player_id=${this.player_id}]:not(.TGAFH-Disabled):not(.TGAFH-Abandon)`, 'TGAFH-huntArea').length === 0)
				{
					dojo.query(`.TGAFH-Hunter[player_id=${this.player_id}].TGAFH-Abandon`, 'TGAFH-huntArea').removeClass('TGAFH-Abandon');
					dojo.addClass('TGAFHhunt', 'disabled', );
				}
//
			}
		},
		onAnimal: function (event)
		{
			dojo.stopEvent(event);
//
			if (this.isCurrentPlayerActive() && this.checkAction('purchase', true))
			{
				const animal = event.currentTarget;
//
				if (animal.closest('.TGAFH-Purchase')) dojo.toggleClass(animal, 'TGAFH-Selected');
//
				const dogs = Math.floor(dojo.query(`.TGAFH-Animal.TGAFH-Selected`, 'TGAFH-purchase').reduce((s, node) => {
					if (+dojo.getAttr(node, 'animal') === 5) return s + 16;
					return s + parseInt(dojo.getAttr(node, 'value'));
				}, 0) / 8);
//
				dojo.toggleClass('TGAFHpurchase', 'disabled', dogs === 0);
				if (dogs === 0) $('TGAFHpurchase').innerHTML = _('Purchase dog(s)');
				else if (dogs === 1) $('TGAFHpurchase').innerHTML = _('Purchase one dog');
				else $('TGAFHpurchase').innerHTML = dojo.string.substitute(_('Purchase ${DOGS} dogs'), {DOGS: dogs});
			}
		},
		onHunt: function (event)
		{
			dojo.stopEvent(event);
//
			if (this.isCurrentPlayerActive() && this.checkAction('hunt', true))
			{
				const huntArea = event.currentTarget;
//
				if (!dojo.hasClass(huntArea.closest('.TGAFH-Hunt'), 'TGAFH-Possible')) return;
				if (dojo.query(`.TGAFH-Hunter[player_id=${this.player_id}].TGAFH-Abandon`, huntArea).length) return;
//
				selected = dojo.query('.TGAFH-Selected', 'TGAFH-hand');
				if (selected.length === 0) return;
//
				let dogs = dojo.query('.TGAFH-Hunter.TGAFH-Selected[country=0]', 'TGAFH-hand').length;
				let hunters = dojo.query(`.TGAFH-Hunter[player_id=${this.player_id}]:not(.TGAFH-Abandon)`, huntArea).length + dojo.query(`.TGAFH-Hunter.TGAFH-Selected:not([country=0])`, 'TGAFH-hand').length;
				if (dogs > 0 && hunters === 0) return this.showMessage(_('Dogs must hunt with some hunters'), 'error');
//
				let preys = [];
				dojo.query(`.TGAFH-Hunter[player_id=${this.player_id}]:not(.TGAFH-Abandon)`, 'TGAFH-huntArea').forEach((node) => preys.push(node.closest('.TGAFH-Hunters').id));
				if (preys.length > 0 && preys[0] !== huntArea.id)
				{
					let preys = [];
					dojo.query(`.TGAFH-Hunter[player_id=${this.player_id}].TGAFH-Abandon`, 'TGAFH-huntArea').forEach((node) => preys.push(node.closest('.TGAFH-Hunters').id));
					if (preys.length > 0 && preys[0] !== huntArea.id) return;
					dojo.query(`.TGAFH-Hunter[player_id=${this.player_id}]:not(.TGAFH-Abandon)`, 'TGAFH-huntArea').addClass('TGAFH-Abandon');
				}
//
				selected.forEach((node) => {
					dojo.removeClass(node, 'TGAFH-Selected');
					huntArea.appendChild(node);
				});
				dojo.toggleClass('TGAFHhunt', 'disabled', dojo.query(`.TGAFH-Hunter[player_id=${this.player_id}]:not(.TGAFH-Disabled)`, 'TGAFH-huntArea').length === 0);
//
				this.sort(huntArea);
				this.sort($('TGAFH-hand'));
				dojo.query(`.TGAFH-Hunt`, 'TGAFH-huntArea').removeClass('TGAFH-Possible');
			}
		},
//
// Game actions
//
		hunt: function ()
		{
			if (this.isCurrentPlayerActive() && this.checkAction('hunt', true))
			{
				let nodes = dojo.query(`.TGAFH-Hunter[player_id=${this.player_id}]:not(.TGAFH-Disabled)`, 'TGAFH-huntArea');
				if (nodes.length === 0) return this.showMessage(_('You must play at least one hunter'), 'error');
//
				let dogs = dojo.query('.TGAFH-Hunter[country=0]:not(.TGAFH-Selected)', 'TGAFH-hand').length;
				let hunters = dojo.query(`.TGAFH-Hunter:not([country=0])`, 'TGAFH-hand').length;
				if (dogs > 0 && hunters === 0) return this.showMessage(_('You can\'t leave a dog alone'), 'error');
//
				const huntArea = nodes[0].closest('.TGAFH-Hunt');
				let prey = huntArea.querySelector('.TGAFH-Animal');
				if (prey && dojo.getAttr(prey, 'value'))
				{
					const value = dojo.getAttr(prey, 'value');
					const total = dojo.query(`.TGAFH-Hunter`, huntArea).reduce((s, node) => s + parseInt(dojo.getAttr(node, 'value')), 0);
					if (total < value) return this.showMessage(_('You don\'t have not enough to hunt'), 'error');
					animal = dojo.getAttr(prey, 'card_id');
				}
				else animal = 0;
//
				this.action('hunt', {animal: animal, hunters: nodes.reduce((hunters, node) => dojo.hasClass(node, 'TGAFH-Disabled') ? '' : hunters + dojo.getAttr(node, 'card_id') + ';', '')});
			}
		},
		pass: function ()
		{
			if (this.isCurrentPlayerActive() && this.checkAction('pass', true)) this.action('purchase', {animals: ''});
		},
		purchase: function ()
		{
			if (this.isCurrentPlayerActive() && this.checkAction('purchase', true))
			{
				let nodes = dojo.query(`.TGAFH-Animal.TGAFH-Selected`, 'TGAFH-purchase');
				this.action('purchase', {animals: nodes.reduce((animals, node) => animals + dojo.getAttr(node, 'card_id') + ';', '')});
			}
		},
//
// Utils
//
		counts: function (player_id, counts)
		{
			this.gamedatas.players[player_id].counts = counts;
//
			const foxes = counts.Fox[0];
			html = Object.entries(counts).reduce((html, v) => {
				return html + `<div style='text-align:center;'><div style='font-size:small;'>${_(v[0])}</div><div style='font-size:normal;font-weight:bold;${(v[1][1] - v[1][0] - foxes <= 1) ? 'color:red; font-weight:bold; ' : ''}'>${v[1][0]}/${v[1][1]}</div></div>`;
			}, '');
			$(`TGAFH_animals_${player_id}`).innerHTML = html;
		},
		sort: function (container)
		{
			container.style.setProperty('--SCALE', Math.min(1.0, container.clientWidth / 165 / (1 + dojo.query(`.TGAFH-Hunter`, container).length)));
			for (let node of dojo.query(`.TGAFH-Hunter[player_id=${this.player_id}]`, container).sort((a, b) => dojo.getAttr(a, 'value') - dojo.getAttr(b, 'value')))
				container.appendChild(node);
		},
		action: function (action, args =
		{}, success = () => {}, fail = undefined)
		{
			args.lock = true;
			this.ajaxcall(`/thegreatamericanfoxhunt/thegreatamericanfoxhunt/${action}.html`, args, this, success, fail);
		}
	});
});
