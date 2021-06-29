/********************************************
 * REVOLUTION 6.1.0 EXTENSION - ACTIONS
 * @version: 6.1.0 (19.08.2019)
 * @requires rs6.main.js
 * @author ThemePunch
*********************************************/
(function($,undefined) {
"use strict";
var 
	_R = jQuery.fn.revolution;	

///////////////////////////////////////////
// 	EXTENDED FUNCTIONS AVAILABLE GLOBAL  //
///////////////////////////////////////////
jQuery.extend(true,_R, {	
	checkActions : function(layer,id) {					
		if (layer===undefined) moduleEnterLeaveActions(id); else checkActions_intern(layer,id);
	}
});

//////////////////////////////////////////
//	-	INITIALISATION OF ACTIONS 	-	//
//////////////////////////////////////////
var moduleEnterLeaveActions = function(id) {
	if (!_R[id].moduleActionsPrepared && _R[id].c[0].getElementsByClassName('rs-on-sh').length>0) {		
		_R[id].c.on('tp-mouseenter',function() {		
			_R[id].mouseoncontainer = true;			
			var key = _R[id].pr_next_key!==undefined ? _R[id].pr_next_key : _R[id].pr_processing_key!==undefined ? _R[id].pr_processing_key : _R[id].pr_active_key!==undefined ? _R[id].pr_active_key : _R[id].pr_next_key,
				li;
			if (key==="none" || key===undefined) return;		
			key = _R.gA(_R[id].slides[key],"key");		
			if (key!==undefined && _R[id].layers[key]) for (li in _R[id].layers[key]) if (_R[id].layers[key][li].className.indexOf("rs-on-sh")>=0) _R.renderLayerAnimation({layer:jQuery(_R[id].layers[key][li]), frame:"frame_1", mode:"trigger", id:id});				
			for (li in _R[id].layers.static) if (_R[id].layers.static[li].className.indexOf("rs-on-sh")>=0) _R.renderLayerAnimation({layer:jQuery(_R[id].layers.static[li]), frame:"frame_1", mode:"trigger", id:id});		
		});

		_R[id].c.on('tp-mouseleft',function() {	
			_R[id].mouseoncontainer = true;			
			var key = _R[id].pr_next_key!==undefined ? _R[id].pr_next_key : _R[id].pr_processing_key!==undefined ? _R[id].pr_processing_key : _R[id].pr_active_key!==undefined ? _R[id].pr_active_key : _R[id].pr_next_key,
				li;
			if (key==="none" || key===undefined) return;		
			key = _R.gA(_R[id].slides[key],"key");		
			if (key!==undefined && _R[id].layers[key]) for (li in _R[id].layers[key]) if (_R[id].layers[key][li].className.indexOf("rs-on-sh")>=0) _R.renderLayerAnimation({layer:jQuery(_R[id].layers[key][li]), frame:"frame_999", mode:"trigger", id:id});				
			for (li in _R[id].layers.static) if (_R[id].layers.static[li].className.indexOf("rs-on-sh")>=0) _R.renderLayerAnimation({layer:jQuery(_R[id].layers.static[li]), frame:"frame_999", mode:"trigger", id:id});		
		});		
	}
	_R[id].moduleActionsPrepared = true;
}

var checkActions_intern = function(layer,id) {
	var actions = _R.gA(layer[0],"actions"),
		_L = layer.data();
	actions = actions.split("||");
	layer.addClass("rs-waction");	
	_L.events = _L.events===undefined ? [] : _L.events;		
	//GET THROUGH THE EVENTS AND COLLECT INFORMATIONS
	for (var ei in actions) {		
		if (!actions.hasOwnProperty(ei)) continue;
		var event = getEventParams(actions[ei].split(";"));
		_L.events.push(event);
						
		// LISTEN TO ESC TO EXIT FROM FULLSCREEN		
		if (!_R[id].fullscreen_esclistener && (event.action=="exitfullscreen" || event.action=="togglefullscreen")) {				
			jQuery(document).keyup(function(e) {
			     if (e.keyCode == 27 && jQuery('#rs-go-fullscreen').length>0) layer.trigger(event.on);				   
			});
			_R[id].fullscreen_esclistener = true;
		}
		
		var targetlayer = event.layer == "backgroundvideo" ? jQuery("rs-bgvideo") : event.layer == "firstvideo" ? jQuery("rs-slide").find('.rs-layer-video') : jQuery("#"+event.layer);

		// NO NEED EXTRA TOGGLE CLASS HANDLING
		if (jQuery.inArray(event.action,["toggleslider","toggle_mute_video","toggle_global_mute_video","togglefullscreen"])!=-1) _L._togglelisteners=true;

		// COLLECT ALL TOGGLE TRIGGER TO CONNECT THEM WITH TRIGGERED LAYER
		switch (event.action) {
			case "togglevideo": jQuery.each(targetlayer,function() { updateToggleByList(jQuery(this),'videotoggledby', layer[0].id);}); break;
			case "togglelayer": jQuery.each(targetlayer,function() { 
				updateToggleByList(jQuery(this),'layertoggledby', layer[0].id); jQuery(this).data('triggered_startstatus',event.togglestate);
			});break;
			case "toggle_global_mute_video":
			case "toggle_mute_video":
				jQuery.each(targetlayer,function() { updateToggleByList(jQuery(this),'videomutetoggledby', layer[0].id);});
			break;							
			case "toggleslider":
				if (_R[id].slidertoggledby == undefined) _R[id].slidertoggledby = [];
				_R[id].slidertoggledby.push(layer[0].id);
			break;
			case "togglefullscreen":								
				if (_R[id].fullscreentoggledby == undefined) _R[id].fullscreentoggledby = [];
				_R[id].fullscreentoggledby.push(layer[0].id);													
			break;
		}		
	}
	
		
	_R[id].actionsPrepared = true;
	layer.on("click mouseenter mouseleave",function(e) {	

		for (var i in _L.events) {
			if (!_L.events.hasOwnProperty(i)) continue;
			if (_L.events[i].on!==e.type) continue;		

			var event = _L.events[i];			
						
			if (event.on==="click" && layer.hasClass("tp-temporarydisabled")) return false;
			var targetlayer = event.layer == "backgroundvideo" ? jQuery(_R[id].slides[_R[id].pr_active_key]).find("rs-sbg-wrap rs-bgvideo") : event.layer == "firstvideo" ? jQuery(_R[id].slides[_R[id].pr_active_key]).find(".rs-layer-video").first() : jQuery("#"+event.layer),
				tex = targetlayer.length>0;				
			switch (event.action) {
				case "nextframe":
				case "prevframe":				
				case "gotoframe":
				case "togglelayer":
				case "toggleframes":
				case "startlayer":
				case "stoplayer":				
					if (targetlayer[0]===undefined) continue;
					
					var _ = _R[id]._L[targetlayer[0].id],
						frame=event.frame,
						tou = "triggerdelay";

					if (e.type==="click" && _.clicked_time_stamp !==undefined && ((new Date().getTime() - _.clicked_time_stamp)<300)) return;
					if (e.type==="mouseenter" && _.mouseentered_time_stamp !==undefined && ((new Date().getTime() - _.mouseentered_time_stamp)<300)) return;
					if (e.type==="mouseleave" && _.mouseleaveed_time_stamp !==undefined && ((new Date().getTime() - _.mouseleaveed_time_stamp)<300)) return;

					clearTimeout(_.triggerdelayIn);
					clearTimeout(_.triggerdelayOut);
					clearTimeout(_.triggerdelay);								
					
				 	if (e.type==="click") _.clicked_time_stamp = new Date().getTime();
				 	if (e.type==="mouseenter") _.mouseentered_time_stamp = new Date().getTime();
				 	if (e.type==="mouseleave") _.mouseleaveed_time_stamp = new Date().getTime();

				 	if (event.action==="nextframe" || event.action==="prevframe") {
				 		_.forda = _.forda===undefined ? getFordWithAction(_) : _.forda;
				 		var inx = jQuery.inArray(_.currentframe,_.ford);				 		
				 		if (event.action==="nextframe") inx++;
				 		if (event.action==="prevframe") inx--;				 		
				 		while (_.forda[inx]!=="skip" && inx>0 && inx<_.forda.length-1) {				 		
				 			if (event.action==="nextframe") inx++;
				 			if (event.action==="prevframe") inx--;
				 			inx = Math.min(Math.max(0,inx),_.forda.length-1);				 			
				 		}				 		
				 		frame = _.ford[inx];				 		 					 		
				 	}
				 	if (jQuery.inArray(event.action,["toggleframes","togglelayer","startlayer","stoplayer"])>=0) {				 	
				 		
					
					 	_.triggeredstate = event.action==="startlayer" || (event.action==="togglelayer" && _.currentframe!=="frame_1") || (event.action==="toggleframes" && _.currentframe!==event.frameN);
					 	frame = _.triggeredstate ? event.action==="toggleframes" ? event.frameN : "frame_1" : event.action==="toggleframes" ? event.frameM : "frame_999";
					 	tou = _.triggeredstate ? "triggerdelayIn" : "triggerdelayOut";
					 	
					 	if (!_.triggeredstate) {
					 		if (_R.stopVideo) _R.stopVideo(targetlayer,id);
					 		_R.unToggleState(_.layertoggledby);
					 	} else {
					 		_R.toggleState(_.layertoggledby);
					 	}
					 }
					var pars = 	{layer:targetlayer, frame:frame, mode:"trigger", id:id};
					
					if (event.children===true) {
						pars.updateChildren = true;
						pars.fastforward = true;											
					}
									 	
				 	if (_R.renderLayerAnimation) {
				 		clearTimeout(_[tou]);				 		
				 		_[tou] = setTimeout(function(_) {				 							 			
				 			_R.renderLayerAnimation(_);
				 		},(event.delay*1000),pars);
				 	}				 					 	
				break;
				case "playvideo": if (tex) _R.playVideo(targetlayer,id);break;
				case "stopvideo": if (tex && _R.stopVideo) _R.stopVideo(targetlayer,id);break;
				case "togglevideo": if (tex) if (!_R.isVideoPlaying(targetlayer,id)) _R.playVideo(targetlayer,id); else if (_R.stopVideo) _R.stopVideo(targetlayer,id);break;
				case "mutevideo": if (tex) _R.Mute(targetlayer,id,true);break;
				case "unmutevideo":	if (tex && _R.Mute) _R.Mute(targetlayer,id,false);break;
				case "toggle_mute_video": if (tex) if (_R.Mute(targetlayer,id)) _R.Mute(targetlayer,id,false); else if (_R.Mute) _R.Mute(targetlayer,id,true); /*layer.toggleClass('rs-tc-active');*/break;
				case "toggle_global_mute_video":						
					var pvl = _R[id].playingvideos != undefined && _R[id].playingvideos.length>0;
					if (pvl)
					    if (_R[id].globalmute)
							jQuery.each(_R[id].playingvideos,function(i,layer) { if (_R.Mute) _R.Mute(layer,id,false);});									 	
					    else 	    					    	
							jQuery.each(_R[id].playingvideos,function(i,layer) { if (_R.Mute) _R.Mute(layer,id,true);});							 						    
				    _R[id].globalmute = !_R[id].globalmute;						    
					//layer.toggleClass('rs-tc-active');
				break;

				// DELAYED ACTION CHECK
				default:						
					punchgs.TweenLite.delayedCall(event.delay,function(targetlayer,id,event,layer) {							
						switch(event.action) {
							case "openmodal": 
								event.modalslide = event.modalslide===undefined ? 0 : event.modalslide;
								if (window.RS_60_MODALS===undefined || jQuery.inArray(event.modal, window.RS_60_MODALS) ==-1) {	
									var data = {action:'revslider_ajax_call_front', client_action:'get_slider_html', token:_R[id].ajaxNonce,alias:event.modal,usage:"modal"};							
									jQuery.ajax({type:'post',url:_R[id].ajaxUrl,dataType:'json',data:data,
							            success:function(ret, textStatus, XMLHttpRequest) {
							                if(ret.success == true) {
							                	jQuery('body').append(ret.data);
							                	setTimeout(function() {
							                		jQuery(document).trigger('RS_OPENMODAL_'+event.modal,event.modalslide);
							                	},49);
							                }
							            },
							            error:function(e) { console.log("Modal Can not be Loaded"); console.log(e);}
							        });							
								} else {								
									jQuery(document).trigger('RS_OPENMODAL_'+event.modal,event.modalslide);
								}
							break;
							case "closemodal": _R.revModal(id,{mode:"close"});break;
							case "callback": eval(event.callback);	break;
							case "simplelink":	window.open(event.url,event.target);break;
							case "simulateclick": if (targetlayer.length>0) targetlayer.click();break;
							case "toggleclass": if (targetlayer.length>0) targetlayer.toggleClass(event.classname);break;
							case "scrollbelow":	
							case "scrollto":								
								layer.addClass("tp-scrollbelowslider");								
								var doc = jQuery(document),
									off= event.action==="scrollbelow" ? (getOffContH(_R[id].fullScreenOffsetContainer) || 0) - (parseInt(event.offset,0) || 0) || 0 : 0-(parseInt(event.offset,0) || 0),
									c =  event.action==="scrollbelow" ? _R[id].c : jQuery('#'+event.id),
									ctop = c.length>0 ? c.offset().top : 0,
									sobj = {_y: (window.pageYOffset!==document.documentElement.scrollTop) ? window.pageYOffset!==0 ? window.pageYOffset :document.documentElement.scrollTop : window.pageYOffset };
								
								ctop += event.action==="scrollbelow" ? jQuery(_R[id].slides[0]).height() : 0;

								punchgs.TweenLite.to(sobj,event.speed/1000,{_y:(ctop-off), ease:event.ease, onUpdate:function() { doc.scrollTop(sobj._y); /* document.documentElement.scrollTop = sobj._y*/}});
							break;
							
							case "jumptoslide":									
								switch (event.slide.toLowerCase()) {
									case "+1":
									case "next":
										_R[id].sc_indicator="arrow";
										_R[id].sc_indicator_dir = 0;
										_R.callingNewSlide(id,1,_R[id].sliderType==="carousel");					
									break;
									case "previous":
									case "prev":
									case "-1":									
										_R[id].sc_indicator="arrow";
										_R[id].sc_indicator_dir = 1;
										_R.callingNewSlide(id,-1,_R[id].sliderType==="carousel");																		
									break;
									case "first":
										_R[id].sc_indicator="arrow";
										_R[id].sc_indicator_dir = 1;
										_R.callingNewSlide(id,0,_R[id].sliderType==="carousel");
									break;
									case "last":
										_R[id].sc_indicator="arrow";
										_R[id].sc_indicator_dir = 0;
										_R.callingNewSlide(id,(_R[id].slideamount-1),_R[id].sliderType==="carousel");									
									break;
									default:
										var ts = jQuery.isNumeric(event.slide) ?  parseInt(event.slide,0) : event.slide;										
										_R.callingNewSlide(id,ts,_R[id].sliderType==="carousel");									
									break;
								}												
							break;
							
							case "toggleslider":
								_R[id].noloopanymore=0;								
								if (_R[id].sliderstatus=="playing") {
									_R[id].c.revpause();
									_R[id].forcepaused = true;
									_R.unToggleState(_R[id].slidertoggledby);
								}
								else {
									_R[id].forcepaused = false;
									_R[id].c.revresume();	
									_R.toggleState(_R[id].slidertoggledby);							
								}
							break;
							case "pauseslider":								
								_R[id].c.revpause();	
								_R.unToggleState(_R[id].slidertoggledby);						
							break;
							case "playslider":			
								_R[id].noloopanymore=0;					
								_R[id].c.revresume();	
								_R.toggleState(_R[id].slidertoggledby);				
							break;
							
							
							case "gofullscreen":
							case "exitfullscreen":
							case "togglefullscreen":	
                var gf;
								if (jQuery('.rs-go-fullscreen').length>0 && (event.action=="togglefullscreen" || event.action=="exitfullscreen")) {
									jQuery('.rs-go-fullscreen').removeClass("rs-go-fullscreen");
									gf = _R[id].c.closest('rs-fullwidth-wrap').length>0 ? _R[id].c.closest('rs-fullwidth-wrap') : _R[id].c.closest('rs-module-wrap');
									_R[id].minHeight  = _R[id].oldminheight;
									_R[id].infullscreenmode = false;
									_R[id].c.revredraw();					
									jQuery(window).trigger("resize");
									_R.unToggleState(_R[id].fullscreentoggledby);

								} else 
								if (jQuery('.rs-go-fullscreen').length==0 && (event.action=="togglefullscreen" || event.action=="gofullscreen")) {
									gf = _R[id].c.closest('rs-fullwidth-wrap').length>0 ? _R[id].c.closest('rs-fullwidth-wrap') : _R[id].c.closest('rs-module-wrap');
									gf.addClass("rs-go-fullscreen");				
									_R[id].oldminheight = _R[id].minHeight;
									_R[id].minHeight = jQuery(window).height();							
									_R[id].infullscreenmode = true;				
									_R[id].c.revredraw();				
									jQuery(window).trigger("resize");
									_R.toggleState(_R[id].fullscreentoggledby);						
								}	
								
							break;
							
							default:_R[id].c.trigger('layeraction',[event.action, layer, event]);break;
						}
					},[targetlayer,id,event,layer]);					
				break;
			}	
		} // GET THROUGH THE EXISITNG EVENTS ON THIS ELEMENTS				
	});	
};

/*
GET FRAME ORDERS WITH ACTIONS
*/
function getFordWithAction(_) {
	var neworder = [];
	for (var i in _.ford) {
		if (_.frames[_.ford[i]].timeline.waitoncall) 
			neworder.push(_.ford[i]);
		else
			neworder.push("skip");
	}
	return neworder;
}
/*
HELPER TO CACHE TOGGLER TRIGGERERS
 */
function updateToggleByList(j,w,id) {
	var _ = j.data(w);
	if (_ === undefined) _ = [];	
	_.push(id);
	j.data(w,_);
}
/*
BUILD ACTION OBJECT FOR LAYER
 */
function getEventParams(_) {
	var r = { on:"click",
			  delay:0,
			  ease:"Power2.easeOut",
			  speed:400
			 };
	for (var i in _) {
		
		// needed for new default (custom values from AddOn Action)
		if(!_.hasOwnProperty(i)) continue;		
		var s = _[i].split(":");		
		switch (s[0]) {
			case "modal": r.modal = s[1];break;
			case "ms": r.modalslide = s[1];break;
			case "m": r.frameM = s[1];break;
			case "n": r.frameN = s[1];break;
			case "o": r.on = (s[1]==="click" || s[1]==="c" ? "click" : s[1]==="ml" || s[1]==="mouseleave" ? "mouseleave" : s[1]==="mouseenter" || s[1]==="me" ? "mouseenter" : s[1]); break;
			case "d": r.delay = parseInt(s[1],0)/1000; r.delay = r.delay==="NaN" || isNaN(r.delay) ? 0 : r.delay; break;
			case "a": r.action = s[1];break;
			case "f": r.frame = s[1];break;
			case "slide": r.slide = s[1];break;
			case "layer": r.layer = s[1];break;
			case "sp": r.speed = parseInt(s[1],0);break;
			case "e": r.ease = s[1];break;
			case "ls": r.togglestate = s[1];break;
			case "offset": r.offset = s[1];break;
			case "call": r.callback = s[1];break;
			case "url": r.url = ""; for (var ii=1;ii<s.length;ii++) r.url += s[ii]+(ii===s.length-1 ? "" : ":");break;
			case "target": r.target = s[1];break;
			case "class": r.classname = s[1];break;
			case "ch": r.children = (s[1]=="true" || s[1]==true || s[1]=="t" ? true : false);break;
			default: if(s[0].length>0 && s[0]!=="") r[s[0]] = s[1];
		}
	}	
	return r;
}


var getOffContH = function(c) {
	if (c==undefined) return 0;		
	if (c.split(',').length>1) {
		var oc = c.split(","),
			a =0;
		if (oc)
			jQuery.each(oc,function(index,sc) {
				if (jQuery(sc).length>0)
					a = a + jQuery(sc).outerHeight(true);							
			});
		return a;
	} else {
		return jQuery(c).height();
	}
	return 0;
};

})(jQuery);