/*!
 * REVOLUTION 6.0.0 EDITOR LAYERACTION JS
 * @version: 1.0 (01.07.2019)
 * @author ThemePunch
*/

;RVS.LIB.ACTION_WITH_TRGT = [];
RVS.LIB.ACTIONTYPES = {};

(function() {
	var xmn = ["X","M","N"];


	/*
	INITIALISE THE BASIC LISTENERS, INPUT MANAGEMENTS ETC
	*/
	RVS.F.initLayerActions = function() {
		initActions();
		initLocalInputBoxes();
		initLocalListeners();
	};


	/*
	LAYER ACTIONS
	*/
	RVS.F.openLayerActions = function() {
		if (RVS.selLayers.length>0) {
			RVS.S.actionIdx=undefined;
			jQuery('#no_action_selected').show();
			jQuery('#action_inputs, .la_settings').hide();

			RVS.F.initActionsOfLayers();
			RVS.F.RSDialog.create({modalid:'rbm_layer_action', bgopacity:0.5});

		} else {
			RVS.F.showInfo({content:RVS_LANG.noLayersSelected, type:"warning", showdelay:0, hidedelay:3, hideon:"", event:"" });
		}
	};

	/*
	SELECT LAYER ACTION
	*/
	RVS.F.selectLayerAction = function() {

		jQuery('.actionselected').removeClass("actionselected");
		if (RVS.S.actionIdx===undefined || RVS.S.actionIdx<0 || RVS.L[RVS.selLayers[0]].actions.action.length==0) {
			jQuery('#action_inputs, .la_settings').hide();
			jQuery('#no_action_selected').show();
			return false;
		} else {
			jQuery('#action_inputs').show();
			jQuery('.la_settings, #no_action_selected').hide();

			if (RVS.LIB.ACTIONTYPES[RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].action]!==undefined)
				jQuery(RVS.LIB.ACTIONTYPES[RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].action].inputs).show();

			if (RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].layer_target!==undefined)
				RVS.S.actionTrgtLayerId = RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].layer_target || "none";

			if (jQuery.inArray(RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].action,["scroll_under","scrollto"])>=0) {
				if (RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].action_easing===undefined) RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].action_easing="Power1.easeInOut";
				if (RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].action_speed===undefined) RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].action_speed="1000ms";
			}

			if (RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].action==="link") {
				if (RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].link_type===undefined) RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].link_type="a";
			}

			RVS.F.updateEasyInputs({container:jQuery('#rbm_layer_action'), path:RVS.S.slideId+".layers."+RVS.selLayers[0]+".", trigger:"init"});
			RVS.F.upadteLayerTargetDropDowns({action:RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].action, targetid:RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].layer_target});
			RVS.F.updateSlideList({action:RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].action, targetid:RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].jump_to_slide});
			RVS.F.updateLinkTypes({action:RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].action, linktype:RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].link_type});

			// IF TARGET ID IS NOT STATIC
			if (RVS.S.actionTrgtLayerId!==undefined && RVS.S.actionTrgtLayerId.indexOf("static-")>=0) {
				var _GL = RVS.SLIDER.staticSlideId!==undefined && RVS.SLIDER[RVS.SLIDER.staticSlideId]!==undefined ? RVS.SLIDER[RVS.SLIDER.staticSlideId].layers : undefined,
					satli = RVS.S.actionTrgtLayerId.replace("static-","");
				if (_GL!==undefined && _GL[satli]!==undefined) {

					//RVS.H[satli].w.addClass("actionselected");
					if (RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].action==="toggle_layer") {
						if (RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].toggle_layer_type==="visible")
							_GL[satli].timeline.frames.frame_1.timeline.actionTriggered = false;
						else
							_GL[satli].timeline.frames.frame_1.timeline.actionTriggered = true;
						_GL[satli].timeline.frames.frame_999.timeline.actionTriggered = true;
					}
					jQuery('#overtake_frame_1_control')[0].checked = _GL[satli].timeline.frames.frame_1.timeline.actionTriggered;
					jQuery('#overtake_frame_999_control')[0].checked = _GL[satli].timeline.frames.frame_999.timeline.actionTriggered;
					RVS.F.turnOnOffVisUpdate({input:jQuery('#overtake_frame_1_control')});
					RVS.F.turnOnOffVisUpdate({input:jQuery('#overtake_frame_999_control')});
					jQuery('#la_triggerMemory').val(_GL[satli].actions.triggerMemory).trigger("change.select2RS");
					RVS.F.updatePlayFrameXOnlyOnAction(null,"X");
					RVS.F.updatePlayFrameXOnlyOnAction(null,"N");
					RVS.F.updatePlayFrameXOnlyOnAction(null,"M");
				}
			} else {
				if (RVS.L[RVS.S.actionTrgtLayerId]!==undefined) {

					RVS.H[RVS.S.actionTrgtLayerId].w.addClass("actionselected");
					if (RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].action==="toggle_layer") {
						if (RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].toggle_layer_type==="visible")
							RVS.L[RVS.S.actionTrgtLayerId].timeline.frames.frame_1.timeline.actionTriggered = false;
						else
							RVS.L[RVS.S.actionTrgtLayerId].timeline.frames.frame_1.timeline.actionTriggered = true;
						RVS.L[RVS.S.actionTrgtLayerId].timeline.frames.frame_999.timeline.actionTriggered = true;
					}
					jQuery('#overtake_frame_1_control')[0].checked = RVS.L[RVS.S.actionTrgtLayerId].timeline.frames.frame_1.timeline.actionTriggered;
					jQuery('#overtake_frame_999_control')[0].checked = RVS.L[RVS.S.actionTrgtLayerId].timeline.frames.frame_999.timeline.actionTriggered;
					RVS.F.turnOnOffVisUpdate({input:jQuery('#overtake_frame_1_control')});
					RVS.F.turnOnOffVisUpdate({input:jQuery('#overtake_frame_999_control')});
					jQuery('#la_triggerMemory').val(RVS.L[RVS.S.actionTrgtLayerId].actions.triggerMemory).trigger("change.select2RS");
					RVS.F.updatePlayFrameXOnlyOnAction(null,"X");
					RVS.F.updatePlayFrameXOnlyOnAction(null,"N");
					RVS.F.updatePlayFrameXOnlyOnAction(null,"M");
				}
			}
			jQuery('#layer_action_fake').html(RVS.LIB.ACTIONTYPES[RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].action].name);
		}
	};

	/*
	CHECK IF A LAYER FRAME IS TRIGGERED BY ANY OTHER LAYER
	*/
	RVS.F.layerFrameTriggered = function(_) {
		var uid = parseInt(_.layerid,0);
		return RVS.L[uid].timeline.frames[_.frame].timeline.actionTriggered;
	};

	RVS.F.layerFrameTriggeredBy = function(o) {
		var uid = parseInt(o.layerid,0),
			triggeredby = [],
			isstatic = (""+RVS.S.slideId).indexOf("static")>=0,
			DS_RND = o.src==undefined &&  isstatic ? RVS.SLIDER.slideIDs.length-1 : -1,
			DS_DONE = false;

		while (!DS_DONE) {
			var layers = o.src===undefined ? DS_RND===-1 ? RVS.L : RVS.SLIDER[RVS.SLIDER.slideIDs[DS_RND]].layers : o.src;
			for (var i in layers) {
				if ((o.all===undefined && triggeredby.length>0) || !layers.hasOwnProperty(i)) continue;
				if (layers[i].actions !==undefined) {
					for (var j in layers[i].actions.action)	{
						if((o.all===undefined && triggeredby.length>0) || !layers[i].actions.action.hasOwnProperty(j) || layers[i].actions.action[j].layer_target===undefined ) continue;

						if ((isstatic && layers[i].actions.action[j].layer_target==="static-"+uid) ||
							(isstatic && RVS.SLIDER.slideIDs[DS_RND]===RVS.S.slideId && parseInt(layers[i].actions.action[j].layer_target,0) === parseInt(uid,0)) ||
							(!isstatic && parseInt(layers[i].actions.action[j].layer_target,0) === parseInt(uid,0))) {
							var  ac = layers[i].actions.action[j].action;
						  	if ((ac==="start_in" && (o.frame==="any" || o.frame==="frame_1")) ||
						  		(ac==="start_out" && (o.frame==="any" || o.frame==="frame_999"))  ||
						  		(ac==="toggle_layer" && (o.frame==="any" || o.frame==="frame_1" || o.frame==="frame_999")) ||
						  		(ac==="toggle_frames" && (o.frame==="any" || layers[i].actions.action[j].gotoframeM===o.frame || layers[i].actions.action[j].gotoframeN===o.frame)) ||
						  		(ac==="start_frame" && (o.frame==="any" || layers[i].actions.action[j].gotoframe===o.frame))) {
									triggeredby.push({icon:RVS.F.getLayerIcon(layers[i].type), action:ac,  uid : parseInt(layers[i].uid,0), alias:layers[i].alias, slide:o.src===undefined &&  DS_RND!==-1 ? RVS.SLIDER.slideIDs[DS_RND] : RVS.S.slideId});
						  		}
						}
					}
				}
			}
			DS_RND--;
			DS_DONE = DS_RND<0;
		}

		if (triggeredby.length===0) triggeredby.push({uid:"", alias:""})

		if (o.all)
			return triggeredby;
		else
			return triggeredby[0];
	};


	RVS.F.updateLayerToggleActionWaits = function() {
		var _GL = RVS.SLIDER.staticSlideId!==undefined && RVS.SLIDER[RVS.SLIDER.staticSlideId]!==undefined ? RVS.SLIDER[RVS.SLIDER.staticSlideId].layers : undefined;
		for (var i in RVS.L) {
			if(!RVS.L.hasOwnProperty(i)) continue;
			if (RVS.L[i].actions !==undefined) {
				for (var j in RVS.L[i].actions.action)	{
					if(!RVS.L[i].actions.action.hasOwnProperty(j)) continue;
					if (RVS.L[i].actions.action[j].layer_target!==undefined && RVS.L[i].actions.action[j].action==="toggle_layer") {
						var tgt = RVS.L[i].actions.action[j].layer_target,
							static = _GL!==undefined && (""+tgt).indexOf("static-")>=0;
						tgt = static ? tgt.replace("static-","") : tgt;
						if ((static && _GL[tgt]!== undefined) || (!static &&RVS.L[tgt]!==undefined)) {
							var res = !(RVS.L[i].actions.action[j].toggle_layer_type==="visible");
							if (static) {
								_GL[tgt].timeline.frames.frame_1.timeline.actionTriggered = res;
								_GL[tgt].timeline.frames.frame_999.timeline.actionTriggered = true;
							} else {
								RVS.L[tgt].timeline.frames.frame_1.timeline.actionTriggered = res;
								RVS.L[tgt].timeline.frames.frame_999.timeline.actionTriggered = true;
							}
						 }
					}
				}
			}
		}
		RVS.F.updateAllLayerFrames();
	}


	/*
	BUILD LAYER ACTION LIST AND DEPENDENCIES
	*/
	RVS.F.initActionsOfLayers = function() {
		var l = RVS.L[RVS.selLayers[0]],
			ldw = jQuery('#layer_depending_wrap'),
			depends = RVS.F.layerFrameTriggeredBy({all:true,layerid:RVS.selLayers[0],frame:"any"}),
			header = '<i class="lwa_icon material-icons">'+RVS.F.getLayerIcon(l.type)+'</i><span class="lwa_layername">'+l.alias+'</span>';

		ldw[0].innerHTML = "";

		jQuery('#layer_with_action_wrap').removeClass("opendeps");

		if (depends.length>0) header += '<span class="drop_dependencies">'+RVS_LANG.triggeredby+'<i class="material-icons">arrow_drop_up</i></span>';

		for (var i in depends) {
			if(!depends.hasOwnProperty(i)) continue;
			if (depends[i].uid!==undefined && depends[i].action!==undefined)
				ldw.append('<li data-id="'+depends[i].uid+'" class="layer_depending_on"><i class="material-icons ldo_icon">'+depends[i].icon+'</i><span class="ldo_layername">'+depends[i].alias+(depends[i].slide!==RVS.S.slideId ? " ("+depends[i].slide+")" : "")+'</span><span class="ldo_actionname">'+RVS_LANG["layeraction_"+depends[i].action]+'</span></li>');
		}
		document.getElementById('add_action_to_layername').innerHTML = l.alias;
		document.getElementById('layer_with_action').innerHTML = header;
		RVS.F.updateEasyInputs({container:jQuery('#layer_width_action_inner_wrap'), path:RVS.S.slideId+".layers."+RVS.selLayers[0]+".", trigger:"init"});
		RVS.F.buildActionList();
	};

	RVS.F.buildActionList = function() {
		var sla = jQuery('#selected_layer_actions'),
			l = RVS.L[RVS.selLayers[0]];
		jQuery('.actionDependent').removeClass("actionDependent");
		jQuery('.actionselected').removeClass("actionselected");
		// BUILD ACTION LIST
		sla[0].innerHTML = "";
		for (var i in l.actions.action) {
			if(!l.actions.action.hasOwnProperty(i)) continue;
			var li =  '<li class="single_layer_action '+(i==RVS.S.actionIdx ? "selected" : "")+'">',
				targetid = getActionTarget(l.actions.action[i]);
			if (targetid!=-1) {
				if (RVS.L[targetid]!==undefined) {
					li += '<i class="sla_icon material-icons">'+RVS.F.getLayerIcon(RVS.L[targetid].type)+'</i><span class="sla_layername">'+RVS.L[targetid].alias+'</span>';
					RVS.H[targetid].w.addClass("actionDependent");
				}
				else
				if (l.actions.action[i].layer_target==="backgroundvideo" || l.actions.action[i].layer_target==="firstvideo")
					li += '<i class="sla_icon material-icons">videocam</i><span class="sla_layername">'+RVS_LANG[l.actions.action[i].layer_target]+'</span>';
				else
					li += '<i class="sla_icon material-icons">error_outline</i><span class="sla_layername">'+RVS_LANG.noLayersSelected+'</span>';
			} else
			if (RVS.LIB.ACTIONTYPES[l.actions.action[i].action]!==undefined)
				li += '<i class="sla_icon material-icons">'+RVS.LIB.ACTIONTYPES[l.actions.action[i].action].icon+'</i><span class="sla_layername"></span>';
			else
				li += '<i class="sla_icon material-icons">extension</i><span class="sla_layername"></span>';

			if (RVS.LIB.ACTIONTYPES[l.actions.action[i].action]!==undefined && RVS_LANG["layeraction_"+l.actions.action[i].action]!==undefined)
				li += '<span class="sla_actionname">'+RVS_LANG["layeraction_"+l.actions.action[i].action]+'</span>';
			else
				li += '<span class="sla_actionname">'+l.actions.action[i].action+'</span>';

			li += '<div class="single_layer_toolbar"><i class="material-icons duplicate_single_layer_action">content_copy</i><i class="material-icons delete_single_layer_action">delete_forever</i></div>';
			li += '</li>';
			sla.append(li);
 		}
 		//LAYER HAS NO ACTION YET
 		if (l.actions.action.length===0) {
 			jQuery('#layeraction_list').show();
 			jQuery('#selected_layer_actions').hide();
 			jQuery('#addactiontolayer').hide();
 		} else {
 			jQuery('#layeraction_list').hide();
 			jQuery('#selected_layer_actions').show();
 			jQuery('#addactiontolayer').show();
 		}
 		updateActionScrollbars();
	};

	function getSliderIdFromAlias(alias) {
		var found = false;
		for (var i in RVS.LIB.SLIDERS) if (RVS.LIB.SLIDERS.hasOwnProperty(i)) {
			if (found!==false) continue;
			if (RVS.LIB.SLIDERS[i].alias===alias) found = RVS.LIB.SLIDERS[i].id;
		}
		return found;
	}

	/*
	UPDATE THE LIST OF SLIDES AFTER SELECTING MODAL MODULE
	 */
	RVS.F.refreshModalSlides = function() {

		if(RVS.L[RVS.selLayers[0]].actions.action.length) {
			if (RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].action=="open_modal" && RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].openmodal!==undefined) {
				var sid = getSliderIdFromAlias(RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].openmodal);
				RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].openmodalId = sid;
				if (sid===false) return;
				var op = '';
				RVS.F.ajaxRequest('get_slides_by_slider_id',{id:sid},function(response){
					if (response.success) {
						for (var sid in response.slides) if (response.slides.hasOwnProperty(sid)) op += '<option value="rs-'+response.slides[sid].id+'">'+response.slides[sid].title+'</option>';
						var gf = jQuery('#la_open_modalslide');
						gf[0].innerHTML = op;

						if (RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].modalslide!==undefined) gf[0].value = RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].modalslide;
						//UPDATE SELECT LIST
						gf.select2RS({
							minimumResultsForSearch:"Infinity",
							placeholder:"Select From List"
						});
					}
				});

			}
		}
	}

	/*
	UPDATE THE FRAMES LIST AFTER SELECTING LAYER
	*/
	RVS.F.refreshFrameLists = function() {
		for (var i in xmn) if (xmn.hasOwnProperty(i)) {
			if (RVS.L[RVS.S.actionTrgtLayerId]===undefined) return;
			var op ="",
				gf=jQuery('#la_gotoframe'+xmn[i]),
				frameorder = RVS.L[RVS.S.actionTrgtLayerId].timeline.frameOrder,
				frames = RVS.L[RVS.S.actionTrgtLayerId].timeline.frames;
			op += '<option value="frame_1">'+frames.frame_1.alias+' ( '+RVS_LANG.frstframe+')'+'</option>';
			for (var frameid in frameorder) {
				if(!frameorder.hasOwnProperty(frameid)) continue;
				var frame = frameorder[frameid].id;
				if (frame!=="frame_0" && frame!=="frame_1" && frame!=="frame_999")
					op += '<option value="'+frame+'">'+frames[frame].alias+' ('+frameid+'.Frame )'+'</option>';
			}
			op += '<option value="frame_999">'+frames.frame_999.alias+' ( '+RVS_LANG.lastframe+')'+'</option>';
			gf[0].innerHTML = op;

			//CHECK IF FRAME EXISTS, AND SELECT
			if (RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx]["gotoframe"+(xmn[i]==="X" ? "" : xmn[i])] && frames[RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx]["gotoframe"+(xmn[i]==="X" ? "" : xmn[i])]]!==undefined)
				gf[0].value = RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx]["gotoframe"+(xmn[i]==="X" ? "" : xmn[i])];
			else {
				RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx]["gotoframe"+(xmn[i]==="X" ? "" : xmn[i])] = "frame_1";
				gf[0].value = RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx]["gotoframe"+(xmn[i]==="X" ? "" : xmn[i])];
			}


			//UPDATE SELECT LIST
			gf.select2RS({
				minimumResultsForSearch:"Infinity",
				placeholder:"Select From List"
			});

			//UPDATE PLAY FRAME ONLY INPUT
			RVS.F.updatePlayFrameXOnlyOnAction(null,xmn[i]);
		}

	};

	RVS.F.updatePlayFrameXOnlyOnAction = function(a,b) {

		b = b===undefined ? "X" : typeof b ==="object" ? b.eventparam : b;

		if (RVS.L[RVS.S.actionTrgtLayerId]===undefined) return;

		var inp = jQuery('#overtake_frame'+b+'_control'),
			frames = RVS.L[RVS.S.actionTrgtLayerId].timeline.frames,
			gf = RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx]["gotoframe"+(b==="N" || b==="M" ? b : "")];
		if (gf && frames[gf]!==undefined) {
			inp[0].dataset.r = "timeline.frames."+gf+".timeline.actionTriggered";
			inp[0].checked = RVS.L[RVS.S.actionTrgtLayerId].timeline.frames[gf].timeline.actionTriggered;
			RVS.F.turnOnOffVisUpdate({input:inp});
		}
	};

	/*
	UPDATE DROP DOWN OF TARGET LAYER SELECT
	*/
	function simplyfyActionSelect(state) {
		if (!state.id) return state.text;

		var static = state.id.indexOf('static-')>=0,
			_id = state.id.replace('static-',''),
			_GL = RVS.SLIDER.staticSlideId!==undefined && RVS.SLIDER[RVS.SLIDER.staticSlideId]!==undefined ? RVS.SLIDER[RVS.SLIDER.staticSlideId].layers : undefined,
			icon = _id==="backgroundvideo" || _id==="firstvideo" ? "videocam" : static && _GL!==undefined ? _GL[_id] !==undefined ? RVS.F.getLayerIcon(_GL[_id].type) : "" : RVS.L[_id] !==undefined ? RVS.F.getLayerIcon(RVS.L[_id].type) : "";
		return jQuery('<span><i class="icon_in_dropdown material-icons">'+icon+'</i>'+state.text+'</span>');
	}

	RVS.F.upadteLayerTargetDropDowns = function(_) {
		// SET UP DROPDOWN BOXES
		var lalt = jQuery('#la_layer_target'),
			showmedia = RVS.LIB.ACTIONTYPES[_.action]!==undefined ? RVS.LIB.ACTIONTYPES[_.action].media : false;

		lalt[0].innerHTML="";

		if (showmedia) {
			lalt[0].innerHTML += '<option value="backgroundvideo">'+RVS_LANG.backgroundvideo+'</option>';
			lalt[0].innerHTML += '<option value="firstvideo">'+RVS_LANG.videoactiveslide+'</option>';
		}
		for (var l in RVS.L) {
			if(!RVS.L.hasOwnProperty(l)) continue;
			if (RVS.L[l].type!==undefined &&RVS.L[l].type!=="zone" && (!showmedia || (RVS.L[l].type==="video" || RVS.L[l].type==="audio")))
				lalt[0].innerHTML += '<option value="'+RVS.L[l].uid+'">'+RVS.L[l].alias+'</option>';
		}
		if (RVS.S.slideId.indexOf("static_")===-1 && RVS.SLIDER.staticSlideId!==undefined && RVS.SLIDER[RVS.SLIDER.staticSlideId]!==undefined) {
			lalt[0].innerHTML += '<optgroup label="'+RVS_LANG.globalLayers+'">';
			var _GL = RVS.SLIDER[RVS.SLIDER.staticSlideId].layers;
			for (var l in _GL) {
				if(!_GL.hasOwnProperty(l)) continue;
				if (_GL[l].type!==undefined &&_GL[l].type!=="zone" && (!showmedia || (_GL[l].type==="video" || _GL[l].type==="audio")))
					lalt[0].innerHTML += '<option value="static-'+_GL[l].uid+'">'+_GL[l].alias+'</option>';
			}
			lalt[0].innerHTML +=  '</optgroup>';
		}
		lalt.val(_.targetid).trigger("change.select2RS").select2RS({minimumResultsForSearch:"Infinity", templateResult:simplyfyActionSelect, templateSelection:simplyfyActionSelect});

		if (_.targetid===undefined || RVS.L[_.targetid]===undefined) return;
		if (jQuery.inArray(RVS.L[_.targetid].type,["group","row","column"])>=0 && jQuery.inArray(_.action,["start_in","start_out","start_frame","next_frame","prev_frame","toggle_layer","toggle_frames"])>=0) {

			jQuery('#la_settings_childrentimelines').show();
		}
		else
			jQuery('#la_settings_childrentimelines').hide();
	};

	RVS.F.updateLinkTypes = function(_) {
		//disable_action_ongroups
		var lalt = jQuery('#la_link_type');
		lalt[0].innerHTML = "";
		lalt.append('<option value="jquery">'+RVS_LANG.jquerytriggered+'</option>');
		if (jQuery.inArray(RVS.L[RVS.selLayers[0]].type,["group","column","row"])==-1) lalt.append('<option value="a">'+RVS_LANG.atriggered+'</option>');
		lalt.val(_.linktype).trigger("change.select2RS").select2RS({minimumResultsForSearch:"Infinity"});
	};

	/*
	CREATE SINGLE ACTION GROUPS IN ACCORDION FORM
	*/
	RVS.F.createActionGroup = function(obj) {

		var groupadded = jQuery('#'+obj.id).length!==0,
			group = !groupadded ? jQuery('<div id="'+obj.id+'" class="lal_group"></div>') : jQuery('#'+obj.id);

		obj.title = RVS_LANG[obj.id]===undefined ? obj.title ? obj.title : obj.id : RVS_LANG[obj.id];


		//ADD GROUP HEADER, IF NO GROUP ADDED YET
		if (!groupadded) group.append('<div class="lal_group_header"><i class="material-icons">'+obj.icon+'</i>'+obj.title+'<i class="material-icons accordiondrop">arrow_drop_down</i></div>');

		//ADD ACTIONS TO THE GROUP
		for (var i in obj.actions) {

			if(!obj.actions.hasOwnProperty(i)) continue;
			var a = obj.actions[i];

			if (a.alias!==undefined) RVS_LANG["layeraction_"+a.val] = a.alias;

			var	title = a.title!==undefined ? a.title : RVS_LANG['layeraction_'+a.val]===undefined ? a.val : RVS_LANG['layeraction_'+a.val];
			a.inputs = a.inputs===undefined ? "" : a.inputs;
			a.inputs = 	(a.layerTarget===true ? "#la_settings_layertarget"+ (a.inputs.length>0 ? ", "+a.inputs:"") : a.inputs);
			group.append('<div data-val="'+a.val+'" id="layeraction_picker_'+a.val+'" data-inputs="'+a.inputs+'" class="lal_group_member" data-val="'+a.val+'"><i class="material-icons">'+obj.icon+'</i>'+title+'</div>');
			if (a.layerTarget) RVS.LIB.ACTION_WITH_TRGT.push(a.val);
			RVS.LIB.ACTIONTYPES[a.val] = {inputs:a.inputs, name:title, icon:obj.icon, layerTarget:a.layerTarget, media:a.media};
		}

		//ADD GROUP TO THE ACTION CONTAINER IF NOT YET ADDED
		if (!groupadded) jQuery('#layeraction_list').append(group);
	};

	/*
	UPDATE SLIDE LIST
	*/
	RVS.F.updateSlideList = function(_) {
		var sel = jQuery('#la_jump_to_slide');
		var opts = '<option value="first">'+RVS_LANG.firstslide+'</option>';
		opts += '<option value="last">'+RVS_LANG.lastslide+'</option>';
		opts += '<option value="next">'+RVS_LANG.nextslide+'</option>';
		opts += '<option value="previous">'+RVS_LANG.previousslide+'</option>';
		for (var i in RVS.SLIDER.slideIDs) {
			if(!RVS.SLIDER.slideIDs.hasOwnProperty(i)) continue;
			var id = RVS.SLIDER.slideIDs[i]+"";
			if (id.indexOf("static_")===-1)  opts += '<option value='+id+'>'+RVS.SLIDER[id].slide.title+'</option>';
		}
		sel[0].innerHTML = opts;
		sel.val(_.targetid).trigger("change.select2RS").select2RS({minimumResultsForSearch:"Infinity", templateResult:simplyfyActionSelect, templateSelection:simplyfyActionSelect});

	};



	/********************************
			INIT ACTIONS
	*********************************/
	function initActions() {
		// LINK ACTIONS
		RVS.F.createActionGroup({icon:"link", id:"layeraction_group_link", actions:[
			{val:"link", inputs:"#la_settings_link"},
			{val:"callback", inputs:"#la_settings_callback"},
			{val:"scrollto", inputs:"#la_settings_scroll_to,#la_settings_scroll_under"},
			{val:"scroll_under", inputs:"#la_settings_scroll_under"}]});

		// SLIDE ACTIONS
		RVS.F.createActionGroup({icon:"code", id:"layeraction_group_slide", actions:[{val:"jumpto", inputs:"#la_settings_jumpto"},  {val:"next"}, {val:"prev"},{val:"pause"},{val:"resume"},{val:"toggle_slider"},{val:"close_modal"},{val:"open_modal", inputs:"#la_settings_modal"}]});


		// LAYER ACTIONS
		RVS.F.createActionGroup({icon:"layers", id:"layeraction_group_layer", actions:[
			{val:"start_in", inputs:"#la_settings_layer_actions, #la_settings_layer_actions_in, #la_settings_childrentimelines", layerTarget:true},
			{val:"start_out", inputs:"#la_settings_layer_actions, #la_settings_layer_actions_out, #la_settings_childrentimelines", layerTarget:true},
			{val:"start_frame", inputs:"#la_settings_layer_actions, #la_settings_layer_actions_frame, #la_settings_childrentimelines", layerTarget:true},
			{val:"next_frame", inputs:"#la_settings_layer_actions, #la_settings_childrentimelines", layerTarget:true},
			{val:"prev_frame", inputs:"#la_settings_layer_actions, #la_settings_childrentimelines", layerTarget:true},
			{val:"toggle_layer", inputs:"#la_settings_layer_actions, #la_settings_layer_toggle_actions, #la_settings_childrentimelines", layerTarget:true},
			{val:"toggle_frames", inputs:"#la_settings_layer_actions, #la_settings_layer_actions_frameXY,  #la_settings_childrentimelines", layerTarget:true}
		]});

		// MEDIA ACTIONS
		RVS.F.createActionGroup({icon:"videocam", id:"layeraction_group_media", actions:[
			{val:"start_video", layerTarget:true,media:true},
			{val:"stop_video", layerTarget:true,media:true},
			{val:"toggle_video", layerTarget:true,media:true},
			{val:"mute_video", layerTarget:true,media:true},
			{val:"unmute_video", layerTarget:true,media:true},
			{val:"toggle_mute_video", layerTarget:true,media:true},
			{val:"toggle_global_mute_video"}
		]});

		// FULLSCREEN ACTIONS
		RVS.F.createActionGroup({icon:"fullscreen", id:"layeraction_group_fullscreen", actions:[{val:"togglefullscreen"}, {val:"gofullscreen"}, {val:"exitfullscreen"}]});

		// ADVANCED ACTIONS
		RVS.F.createActionGroup({icon:"layers", id:"layeraction_group_layer", actions:[{val:"simulate_click", layerTarget:true}, {val:"toggle_class", inputs:"#la_settings_class", layerTarget:true}]});

		RVS.DOC.trigger("extendLayerActionGroups");
	}

	/********************************
	INIT LOCAL INPUT BOX FUNCTIONS
	*********************************/
	function initLocalInputBoxes() {

	}

	/********************************
	 	SOME INTERNAL FUNCTIONS
	*********************************/
	function getActionTarget(action) {
		var result = jQuery.inArray(action.action,RVS.LIB.ACTION_WITH_TRGT) != -1 ? parseInt(action.layer_target,0) : -1;
		return result==-1 || isNaN(result) ? -1 : result;
	}

	function updateActionScrollbars() {
		jQuery('#layeraction_list').RSScroll({
			wheelPropagation:false,
			suppressScrollX:true,
			minScrollbarLength:100
		});
		jQuery('#layeractions_overview_innerwrap').RSScroll({
			wheelPropagation:false,
			suppressScrollX:true,
			minScrollbarLength:100
		});
	}

	function addNewAction(action) {
		var lastaction = RVS.L[RVS.selLayers[0]].actions.action.length,
			newactions = RVS.F.safeExtend(true,{},RVS.L[RVS.selLayers[0]].actions);
		newactions.action.push({action:action,tooltip_event:"click", link_open_in:"_self", link_follow:"nofollow"});
		RVS.F.updateSliderObj({path:RVS.S.slideId+'.layers.'+RVS.selLayers[0]+'.actions',val:newactions});

		RVS.S.actionIdx = lastaction;
		RVS.F.buildActionList();
		RVS.F.selectLayerAction();
		RVS.F.refreshFrameLists();
		RVS.F.refreshModalSlides();
		RVS.DOC.trigger('layer_action_selected');
	}

	/*
	INIT CUSTOM EVENT LISTENERS FOR TRIGGERING FUNCTIONS
	*/
	function initLocalListeners() {

		// OPEN LAYER ACTIONS
		var doc = RVS.DOC.on('openLayerActions',RVS.F.openLayerActions);

		RVS.DOC.on('updatePlayFrameXOnlyOnAction',RVS.F.updatePlayFrameXOnlyOnAction);

		// SHOW HIDE DEPENDENCIES ON LAYER ACTION
		RVS.DOC.on('click','.drop_dependencies',function() {
			jQuery('#layer_with_action_wrap').toggleClass("opendeps");
		});

		//CLICK ON CLOSE IN DIFFERENT STATES
		RVS.DOC.on('click','#rbm_layer_action .rbm_close',function() {
			if (jQuery('#rbm_layer_action').hasClass("inpickermode")) {
				jQuery('#rbm_layer_action').removeClass("inpickermode");
				jQuery('#layeraction_list').hide();
				jQuery('#layer_action_type').show();
			} else {
				jQuery('.actionDependent').removeClass("actionDependent");
				RVS.F.RSDialog.close();
			}
			for (var i in RVS.L) if (RVS.L.hasOwnProperty(i)) {
				if (RVS.L[i].actions && RVS.L[i].actions.action.length>0)
					jQuery('#tllayerlist_element_'+RVS.S.slideId+'_'+RVS.L[i].uid).addClass("actionmarked");
				else
					jQuery('#tllayerlist_element_'+RVS.S.slideId+'_'+RVS.L[i].uid).removeClass("actionmarked");
			}
			RVS.F.updateAllLayerFrames();
		});

		// CREATE NEW LAYER ACTION
		RVS.DOC.on('click','#addactiontolayer',function() {
			addNewAction("link");
			return false;
		});

		// DUPLICATE NEW LAYER ACTION
		RVS.DOC.on('click','.duplicate_single_layer_action',function() {
			var li = jQuery(this).closest('.single_layer_action'),
				lastaction = RVS.L[RVS.selLayers[0]].actions.action.length,
				newactions = RVS.F.safeExtend(true,{},RVS.L[RVS.selLayers[0]].actions);
			newactions.action.push(RVS.L[RVS.selLayers[0]].actions.action[li.index()]);
			RVS.F.updateSliderObj({path:RVS.S.slideId+'.layers.'+RVS.selLayers[0]+'.actions',val:newactions});
			RVS.S.actionIdx = lastaction;
			setTimeout(function() {
				RVS.F.buildActionList();
				RVS.F.selectLayerAction();
				RVS.F.refreshFrameLists();
				RVS.F.refreshModalSlides();
			},50);
			return false;
		});

		// DELETE LAYER ACTION
		RVS.DOC.on('click','.delete_single_layer_action',function() {
			var li = jQuery(this).closest('.single_layer_action'),
				/* lastaction = RVS.L[RVS.selLayers[0]].actions.action.length-1, */
				newactions = RVS.F.safeExtend(true,{},RVS.L[RVS.selLayers[0]].actions);


			newactions.action.splice(li.index(),1);
			RVS.S.actionIdx = 0;
			RVS.F.updateSliderObj({path:RVS.S.slideId+'.layers.'+RVS.selLayers[0]+'.actions',val:newactions});
			setTimeout(function() {
				RVS.F.buildActionList();
				RVS.F.selectLayerAction();
				RVS.F.refreshFrameLists();
				RVS.F.refreshModalSlides();
				RVS.DOC.trigger('layer_action_selected');
			},50);
			return false;
		});

		// PICK LAYER ACTION
		RVS.DOC.on('click','#layer_action_type',function() {
			jQuery('#rbm_layer_action').addClass("inpickermode");
			jQuery('#layeraction_list').show();
			jQuery(this).hide();
			jQuery('#layeraction_list .lal_group_member').removeClass("selected");
			jQuery('#layeraction_picker_'+RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx].action).addClass("selected");
		});

		// PICK A NEW LAYER ACTION
		RVS.DOC.on('click','.lal_group_member',function() {
			jQuery('#rbm_layer_action').removeClass("inpickermode");
			jQuery('#layer_action_type').show();
			if (RVS.S.actionIdx===undefined || RVS.L[RVS.selLayers[0]].actions.action[RVS.S.actionIdx]===undefined) {
				addNewAction(this.dataset.val);
				jQuery('#action_inputs .la_settings').hide();
				jQuery(RVS.LIB.ACTIONTYPES[this.dataset.val].inputs).show();
				jQuery('#layeraction_list').hide();
			} else {
				RVS.F.updateSliderObj({path:RVS.S.slideId+'.layers.'+RVS.selLayers[0]+'.actions.action.'+RVS.S.actionIdx+'.action',val:this.dataset.val});
				jQuery('#action_inputs .la_settings').hide();
				jQuery(RVS.LIB.ACTIONTYPES[this.dataset.val].inputs).show();
				jQuery('#layeraction_list').hide();
				RVS.F.buildActionList();
				RVS.F.selectLayerAction();
				RVS.F.refreshFrameLists();
				RVS.F.refreshModalSlides();
			}
			RVS.DOC.trigger('layer_action_selected');

		});

		// CLICK ON A SINGLE LAYER ACTION
		RVS.DOC.on('click','.single_layer_action', function(e) {
			RVS.S.actionIdx = jQuery(this).index();
			jQuery('.single_layer_action').removeClass("selected");
			jQuery(this).addClass("selected");
			RVS.F.selectLayerAction();
			RVS.F.refreshFrameLists();
			RVS.F.refreshModalSlides();
			RVS.DOC.trigger('layer_action_selected');
		});

		// CLICK ON ACCORDION, SHOUD OPEN/CLOSE THE ACTION ACCORDIONS
		RVS.DOC.on('click','.lal_group_header',function() {
			var group = jQuery(this).closest('.lal_group');
			group.toggleClass("closed");
		});

		RVS.DOC.on('refreshActionView',function() {
			RVS.F.buildActionList();
			RVS.F.selectLayerAction();
			RVS.F.refreshFrameLists();
			RVS.F.refreshModalSlides();
		});

		RVS.DOC.on('refreshSlideLists',function() {
			RVS.F.refreshModalSlides();
			console.log("update modal slide id also");
		});

		RVS.DOC.on('refreshLayerToggleState',function() {

			if (jQuery('#toggle_layer_type').val()=="visible")
				RVS.L[RVS.S.actionTrgtLayerId].timeline.frames.frame_1.timeline.actionTriggered = false;
			else
				RVS.L[RVS.S.actionTrgtLayerId].timeline.frames.frame_1.timeline.actionTriggered = true;

			RVS.F.buildActionList();
			RVS.F.selectLayerAction();
			RVS.F.refreshFrameLists();
			RVS.F.refreshModalSlides();
		});


	}

})();
