/*!
 * REVOLUTION 6.0.0 BUILDER JS
 * @version: 1.0 (01.07.2019)
 * @author ThemePunch
*/

/**********************************
	-	GLOBAL VARIABLES	-
**********************************/
;window.RVS = window.RVS === undefined ? {} : window.RVS;
RVS.F = RVS.F === undefined ? {} : RVS.F;
RVS.ENV = RVS.ENV === undefined ? {} : RVS.ENV;
RVS.LIB = RVS.LIB === undefined ? {} : RVS.LIB;
RVS.V = RVS.V === undefined ? {} : RVS.V;
RVS.S = RVS.S === undefined ? {} : RVS.S;
RVS.C = RVS.C === undefined ? {} : RVS.C;
RVS.WIN = RVS.WIN === undefined ? jQuery(window) : RVS.WIN;
RVS.DOC = RVS.DOC === undefined ? jQuery(document) : RVS.DOC;


/**********************************
	-	REVBUILDER ADMIN	-
********************************/
(function() {

	var errorMessageID = null,
		successMessageID = null,
		ajaxLoaderID = null,
		ajaxHideButtonID = null,
		slideanimlist,sliderlist,_ease,undoRetSteps=0,ignoreEvents,resizeTimeout,mouseInfoBoxOn,draggingelements,listOfContents,socList,clipboardselectors,globalpresetsinit,vimeoPlayer,videoPlayerTimer,downloadExportLayerCombo;


	RVS.F.initAdmin = function() {
		// ADDONS SELECT 2 REFERENCES AFTER CHNAGE FROM SELECT2 TO SELECT2RS
		try{ jQuery.fn.select2 = jQuery.fn.select2===undefined ? jQuery.fn.select2RS : jQuery.fn.select2; } catch(e) {}
		try{ $.fn.select2 = $.fn.select2===undefined ?  $.fn.select2RS : $.fn.select2;} catch(e) {}

		RVS.screen = "d";
		RVS.S.bckpGrp = false;
		RVS.S.winh = RVS.WIN.height();
		RVS.S.winw = RVS.WIN.width();
		RVS.ENV.youtubeargs ="hd=1&wmode=opaque&showinfo=0&rel=0;";
		RVS.ENV.vimeoargs = "title=0&byline=0&portrait=0&api=1";
		RVS.V.sizes = ["d","n","t","m"];
		RVS.V.sizesold = ["desktop","notebook","tablet","mobile"];
		RVS.ENV.grid_sizes = {d:1240, n:1024, t:778, m:480, none:"none"};
		RVS.ENV.globVerOffset = 75;
		RVS.S.shrink = {d:1, m:0.625, n:0.82580645, t:0.75};
		RVS.S.mP = {top:0, left:0};
		RVS.S.redoList = [],
		RVS.S.undoList = [],
		RVS.S.layer_wrap_offset = { x:0,y:0}; // Current Delta Horizontal and Vertical between Layer Content and Wrapping UL Container
		RVS.S.navOffset = {thumbs:{top:0, bottom:0,left:0,right:0}, tabs:{top:0,bottom:0,left:0,right:0}};
		RVS.S.rb_ScrollX = 0;
		RVS.S.rb_ScrollY = 0;
		RVS.eMode = RVS.eMode===undefined ? {top:"", menu:"", mode:""} : RVS.eMode;
		RVS.S.respInfoBar = {}; // Responsive Infos in ToolBar over Fields
		RVS.V.timeline_height = "auto";
		RVS.V.timeline_minHeight = 275;
		RVS.V.timeline_minOpenHeight = 275;
		RVS.S.editorSize = {w:RVS.WIN.width()-335, h:RVS.WIN.height()-RVS.V.timeline_minHeight-65};
		RVS.S.click = {x:0,y:0};



		//JS HOOKS
		RVS.JHOOKS = {
			createLayerListElement : [],
			redrawSlideBG : [],
			prepareOneSlide : [],
			layerListElementClicked : [],
			updateFrameOptionsVisual : [],
			changeLayerAnimation : [],
			defaultFrame : []
		};


		RVS.S.DaD = {};
		RVS.S.DaD.dragdelta = {x:0,y:0};	// Latest Dragging Delta from Start till Current Time

		/************************************************
			-	GLOBAL RESIZING / MOUSE MANAGEMENT	-
		*************************************************/
		RVS.WIN.on("resize",function() {
			var _w = RVS.WIN.width(),
				_h = RVS.WIN.height();
			if (RVS.S.winw==_w && RVS.S.winh==_h) return;
			RVS.S.winh = _h;
			RVS.S.winw = _w;
			RVS.S.editorSize.w = RVS.S.winw-335;
			RVS.S.editorSize.h = RVS.S.winh-RVS.V.timeline_minHeight-65;

			clearTimeout(resizeTimeout);
			resizeTimeout = setTimeout(function() {
				RVS.DOC.trigger("windowresized");
			},25);
		});

		RVS.DOC.on('mousemove','#builderView', function(event){
			RVS.S.mP.top=event.pageY;
			RVS.S.mP.left=event.pageX;
			if (mouseInfoBoxOn)
				punchgs.TweenLite.set(RVS.C.mouseInfo,{top:(RVS.S.mP.top-40),left:(RVS.S.mP.left+40),display:"block"});

			if (RVS.S.builderHover!==undefined && RVS.S.builderHover!==false)
				RVS.F.setRulerMarkers({x:RVS.S.mP.left,y:RVS.S.mP.top});

		});

	}
	/****************************************************************************+
		-	SET / GET OBJECT VALUES IN THE MAIN OBJECT STRUCTURE RECURSIVE 	-
	*****************************************************************************/
	// get deep value from path
	function getDeepValue(obj, path) {
		if(typeof path === 'string')
			path = path.split('.');
		if(path.length > 1) {
			var prop = path.shift();
			return obj.hasOwnProperty(prop) ? getDeepValue(obj[prop], path) : undefined;
		}
		return obj.hasOwnProperty(path[0]) ? obj[path[0]] : undefined;
	}

	// write deep value from path
	function writeDeepPath(obj, path, val) {

		if(typeof path !== 'string') return;

		var paths = path.split('.'),
			len = paths.length,
			total = len - 1,
			data = obj;

		if(!len) return;
		for(var i = 0; i < len; i++) {
			if(i < total) data = data[paths[i]];
			else data[paths[i]] = val;
		}

	}

	//***************************************************
	// GET THE VALUE OF THE OBJECT DEFINED BY THE PATH //
	//***************************************************
	RVS.F.getDeepVal = function(_) {
		if (_.path.indexOf('#targetlayer#')>=0 && (RVS.S.actionTrgtLayerId===undefined || RVS.S.actionTrgtLayerId==="none")) return;
		//Find ScreenSize based Values
		_.screen = _.screen===undefined ? RVS.screen : _.screen;
		var	path = _.path.replace('#size#',_.screen).replace('#slide#',RVS.S.slideId).replace('#curslidetrans#',RVS.S.slideTrans).replace('#actionindex#',RVS.S.actionIdx).replace('#targetlayer#',RVS.S.actionTrgtLayerId).replace('#frame#','timeline.frames.'+RVS.S.keyFrame).replace('#framekey#',RVS.S.keyFrame);

		// replaces eval call below
		return getDeepValue(RVS.SLIDER, path);

	};

	//***************************************************
	// CORE UPDATE FUNCTION FOR THE OBJECT STRUCTURES //
	//***************************************************


	RVS.F.updateSliderObj = function(_) {
		if (_.path.indexOf('#targetlayer#')>=0 && (RVS.S.actionTrgtLayerId===undefined || RVS.S.actionTrgtLayerId==="none")) return;
		//Find ScreenSize based Values
		_.path = _.path.replace('#size#',RVS.screen).replace('#slide#',RVS.S.slideId).replace('#curslidetrans#',RVS.S.slideTrans).replace('#actionindex#',RVS.S.actionIdx).replace('#targetlayer#',RVS.S.actionTrgtLayerId).replace('#frame#','timeline.frames.'+RVS.S.keyFrame).replace('#framekey#',RVS.S.keyFrame);
		var keys = _.path.split(".");

		// UPDATE OBJECT PATH
		try {
			// these replace eval calls below
			_.old = getDeepValue(RVS.SLIDER, _.path);
			writeDeepPath(RVS.SLIDER, _.path, _.val);
			_.lastkey = keys[keys.length-1];
		}
		catch(e) { console.log("Object Path Does Not Exists:"+_.path); }

		// UPDATE RESPOSNIVE VALUES ON LAYERS

		if ( _.ignoreResponsive!==true && (keys[keys.length-1]==="v" || keys[keys.length-2]==="v")) {

			_.uid = _.uid===undefined ? keys[jQuery.inArray("layers",keys)+1] : _.uid;
			if (_.editedPath===undefined) {
				_.editedPath = "";
				var i =0;
				while (keys[i]!=="v" && i<keys.length) { _.editedPath += keys[i]+"."; i++;}
				_.editedPath += 'e';
			}

			var evalEpath = '["'+(_.editedPath.split('.')).join('"]["')+'"]';
			try{
				if (_.wasEdited!==undefined && _.undoRedo==="undo")
					writeDeepPath(RVS.SLIDER, _.editedPath, _.wasEdited);
				else {
					_.wasEdited = getDeepValue(RVS.SLIDER, _.editedPath);
					writeDeepPath(RVS.SLIDER, _.editedPath, true);

				}
			} catch(e) { console.log(e);}
			RVS.F.intelligentUpdate({calcShrink:true, iii:RVS.F.isIntelligentInherited(_.uid), key:(keys[keys.length-1]==="v" ? keys[keys.length-3] : keys[keys.length-4]), index:keys[keys.length-1], uid:_.uid});
		}

		// CALL EVENT
		if (_.evt!==undefined && ignoreEvents!==true) RVS.DOC.trigger(_.evt,_.evtparam);
		// BACKUP THE CHANGES
		if (_.ignoreBackup!==true) 	RVS.F.backup(_);

	};

	RVS.F.updateLayerObj = function(_) {
		var newBGroup="none";
		if (RVS.S.bckpGrp===false && RVS.selLayers.length>1) {
			newBGroup = _.path;
			var obj = {path:_.path,icon:"layers",lastkey:"layer", type:"layer"};
			obj.id = "MultipleLayers";

			RVS.F.openBackupGroup(undoRedoTranslate(obj));
		}

		for (var lid in RVS.selLayers) {
			if(!RVS.selLayers.hasOwnProperty(lid)) continue;
			if (_.evt!==undefined) {
				_.evtparam = _.evtparam===undefined ? {} : _.evtparam;
				_.evtparam.layerid = RVS.selLayers[lid];
			}
			RVS.F.updateSliderObj({path:RVS.S.slideId+".layers."+RVS.selLayers[lid]+"."+_.path,val:_.val,evt:_.evt, evtparam:_.evtparam,  uid:RVS.selLayers[lid], ignoreBackup:_.ignoreBackup});
			if (_.ignoreRedraw!==true) RVS.F.drawHTMLLayer({uid:RVS.selLayers[lid]});
		}
		if (newBGroup!=="none")
			RVS.F.closeBackupGroup({id:"MultipleLayers"});
	};
/************************************
 - BACKUP / RESTORE / UNDO / REDO -
*************************************/

	function setUndoRedoContainers() {
		RVS.C.undo = RVS.C.undo===undefined ? jQuery('#undolist') : RVS.C.undo;
		RVS.C.redo = RVS.C.redo===undefined ? jQuery('#redolist') : RVS.C.redo;
	}

	/**
	REDO AND UNDO GROUPS WITH MORE THAN 1 STEP IN THE SAME TIME
	**/
	RVS.F.openBackupGroup = function(obj) {
		setUndoRedoContainers();
		obj.steps = [];
		RVS.S.bckpGrp = obj;
		RVS.S.bckpGrp.chngamount = 0;
	};

	RVS.F.closeBackupGroup = function(obj) {
		if (!obj.ignore) {
			RVS.S.bckpGrp.close=true;
			RVS.F.backup(RVS.S.bckpGrp);
		}
	};

	RVS.F.ignoreEventsOpen = function() {
		ignoreEvents = true;
	};

	RVS.F.ignoreEventsClose = function() {
		ignoreEvents = false;
	};

	/**
	RECORD BACKUP STEP
	**/
	RVS.F.backup = function(obj) {

		if (obj.force!==true && RVS.S.bckpGrp.close!==true && obj.val === obj.old) return;
		setUndoRedoContainers();
		if (RVS.S.bckpGrp != false && RVS.S.bckpGrp.close!==true) {
			RVS.S.bckpGrp.steps.push(obj);
			RVS.S.bckpGrp.chngamount++;
		} else {

			RVS.F.clearRedoList();
			var res = RVS.S.bckpGrp!=false ? {icon:RVS.S.bckpGrp.icon, txt:RVS.S.bckpGrp.txt, lastkey:RVS.S.bckpGrp.chngamount} : obj.icon!==undefined && obj.txt!==undefined ? {icon:obj.icon, txt:obj.txt, lastkey:obj.lastkey} : undoRedoTranslate(obj);
			obj.stepMarkup = '<li id="" class="undoredostep toolbar_listelement">';
			obj.stepMarkup += '<i class="material-icons">'+res.icon+'</i>';
			obj.stepMarkup += '<span style="display:inline-block;min-width:150px;">'+res.txt+'</span>';
			//if (RVS.S.bckpGrp!=false) obj.stepMarkup +='(Multi Steps)';
			obj.stepMarkup += '</li>';
			RVS.S.undoList.push(obj);
			if (RVS.S.undoList.length>30) {
				RVS.S.undoList.splice(0,1);
				RVS.C.undo.find('li').first().remove();
			}
			RVS.C.undo.append(obj.stepMarkup);
			RVS.S.bckpGrp = false;
		}
		undoRetSteps = undoRetSteps + 1;
		RVS.S.need_to_save = true;
		//jQuery('#noactiondone_undo').remove();
	};

	RVS.F.clearRedoList = function() {
		RVS.S.redoList = [];
		RVS.C.redo.html("");
	};
	/**
	UNDO THE LAST STEP(S)
	**/
	RVS.F.undo = function(obj) {
		var todo;

		if (obj===undefined || obj.step>=1) {
			obj.step = obj.step===undefined ? 1 : obj.step;
			if (RVS.S.undoList.length>obj.step-1) {

				for (var i=0;i<obj.step;i++) {
					var step = RVS.S.undoList.pop();
					RVS.S.redoList.push(step);
					todo = makeUndoRedoStep({todo:todo, step:step, valkey:"old"});
				}
				updateScenesBackup({todo:todo});
				RVS.F.showInfo({content:"Succesfully Undone "+obj.step+" Steps.", type:"success", showdelay:0, hidedelay:1, hideon:"", event:"" });

			}
		}
		RVS.F.updateBackupList();
	};
	/**
	REDO THE LAST STEP(S)
	**/
	RVS.F.redo = function(obj) {
		var todo;
		if (obj===undefined || obj.step>=1) {
			obj.step = obj.step===undefined ? 1 : obj.step;
			if (RVS.S.redoList.length>obj.step-1) {
				for (var i=0;i<obj.step;i++) {
					var step = RVS.S.redoList.pop();
					RVS.S.undoList.push(step);
					todo = makeUndoRedoStep({todo:todo, step:step, valkey:"val"});
				}
				updateScenesBackup({todo:todo});
				RVS.F.showInfo({content:"Succesfully Redone "+obj.step+" Steps.", type:"success", showdelay:0, hidedelay:1, hideon:"", event:"" });
			}

		}
		RVS.F.updateBackupList();
	};
	/**
	REWRITE UNDO/REDO LIST
	**/
	RVS.F.updateBackupList = function() {
		setUndoRedoContainers();
		RVS.C.undo.html("");
		RVS.C.redo.html("");
		for (var ui in RVS.S.undoList) if (RVS.S.undoList.hasOwnProperty(ui)) {
			if (RVS.S.undoList.length>0 && RVS.S.undoList[ui]!==undefined)
				RVS.C.undo.append(RVS.S.undoList[ui].stepMarkup);
		}
		for (var ri in RVS.S.redoList) if (RVS.S.redoList.hasOwnProperty(ri)) {
			if (RVS.S.redoList.length>0 && RVS.S.redoList[ri]!==undefined)
				RVS.C.redo.prepend(RVS.S.redoList[ri].stepMarkup);
		}
		jQuery('#undoredowrap').RSScroll("update");
	};


	// REDRAW SCENES AS GOOD AS POSSIBLE AFTER REDO / UNDO PROCESS
	function updateScenesBackup(obj) {

		if (obj.todo.SliderSettings===true) {
			RVS.F.sliderUpdateAllFields(true);
			jQuery('.sliderinput').trigger('init');
			//updateAllOnOff();
		}
		obj.todo.slide = obj.todo.forceSelectSlide!==undefined ? obj.todo.forceSelectSlide : obj.todo.slide;
		obj.todo.slide = obj.todo.slide===undefined ? RVS.S.slideId : obj.todo.slide;
		obj.todo.slide = RVS.F._inArray(obj.todo.slide,RVS.SLIDER.slideIDs)>=0 ? obj.todo.slide : RVS.SLIDER.slideIDs[0];

		if (obj.todo.SlideSettings===true) {
			if (jQuery('.slide_list_element.sortable_slide_list_element').length===0)
				obj.todo.mode = "sliderlayout";
			else {

				RVS.F.setSlideFocus({slideid:obj.todo.slide});
				obj.todo.LayerSettings = false;
			}
		}


		if (obj.todo.SlideList===true) RVS.F.reSortSlides();

		if (obj.todo.mode!==undefined) RVS.F.mainMode({mode:obj.todo.mode,set:true, slide:obj.todo.slide}); obj.todo.LayerSettings = false;

		if (obj.todo.mode==="slidelayout" && !obj.todo.layerAndSlideMode)
			if (obj.todo.layer!==undefined)
				RVS.DOC.trigger('changeToLayerMode');
			else
				RVS.DOC.trigger('changeToSlideMode');


		// LAYER SETTINGS CHANGED
		if (obj.todo.LayerSettings===true ) RVS.F.updateAllLayerFrames();

		//REBUILD LAYER LIST
		if (obj.todo.rebuildLayerList || obj.todo.LayerSorting===true) {
			if (obj.todo.mode!=="slidelayout" || obj.todo.forceRebuildLayerList) {
				RVS.H = {};
				RVS.F.buildLayerLists({force:true});
				RVS.F.updateLayerToggleActionWaits();
			}
			RVS.F.reOrderHTMLLayers();

		}

		//UPDATE FRAMES ON LAYERS IF NEEDED
		if (obj.todo.framesToAdd!==undefined) {
			for (var i in obj.todo.framesToAdd) {
				if(!obj.todo.framesToAdd.hasOwnProperty(i)) continue;
				RVS.F.addLayerFrameOnDemand(RVS.L[obj.todo.framesToAdd[i].layerid], obj.todo.framesToAdd[i].el, obj.todo.framesToAdd[i].newframe);
				RVS.F.getFrameOrder({layerid:obj.todo.framesToAdd[i].layerid});
				RVS.F.updateFramesZIndexes({layerid:obj.todo.framesToAdd[i].layerid});
			}
			RVS.DOC.trigger('updateKeyFramesList');
		}

		//UPDATE FRAME SIZES & RENDER ANIMS
		if (obj.todo.framesToRedraw!==undefined) {
			for (var i in obj.todo.framesToRedraw) {
				if(!obj.todo.framesToRedraw.hasOwnProperty(i)) continue;
				RVS.F.updateLayerFrames({layerid:obj.todo.framesToRedraw[i]});
				RVS.F.renderLayerAnimation({layerid:obj.todo.framesToRedraw[i]});
			}
		}

		if (obj.todo.framesToReorder!==undefined) {
			for (var i in obj.todo.framesToReorder) {
				if(!obj.todo.framesToReorder.hasOwnProperty(i)) continue;
				RVS.F.getFrameOrder({layerid:obj.todo.framesToReorder[i].layerid});
				RVS.F.updateFramesZIndexes({layerid:obj.todo.framesToReorder[i].layerid});
			}
			RVS.DOC.trigger('updateKeyFramesList');
		}

		if (obj.todo.lastSelectedLayers!==undefined)
			for (var i in obj.todo.lastSelectedLayers) {
				if(!obj.todo.lastSelectedLayers.hasOwnProperty(i)) continue;
				if (RVS.L[obj.todo.lastSelectedLayers[i]]!==undefined) RVS.F.selectLayers({id:obj.todo.lastSelectedLayers[i],action:"add"});
			}



		//RELOAD IMAGE SOURCES IF NEEDED
		for (var i in obj.todo.updateLayerImageSrcList) {
			if(!obj.todo.updateLayerImageSrcList.hasOwnProperty(i)) continue;
			RVS.F.updateLayerImageSrc({},{layerid:obj.todo.updateLayerImageSrcList[i]});
		}


		RVS.DOC.trigger("SceneUpdatedAfterRestore");

	}

	// CHECK ALL THE PROCESSES WE NEED TO REDO AFTER REDO/UNDO STEP
	function checkUndoRedoTodos(obj) {
		var  n = jQuery.isNumeric(obj.path);
		if (obj.backupType!==undefined) {
			obj.todo.SlideList=true;
			obj.todo.SliderSettings=true;
			obj.todo.SlideSettings=true;
			obj.todo.LayerSettings=true;
			obj.todo.LayerSorting=true;
			obj.todo.layerAndSlideMode=false;

		}

		//SLIDER SETTINGS HAS BEEN CHANGED
		if (!n && obj.path.indexOf('settings')===0) {
			obj.todo.SliderSettings = true;
			obj.todo.mode="sliderlayout";
		}

		//LAYER SETTINGS HAS BEEN CHANGED
		if (!n && obj.path.indexOf('.layers.')>=0 && obj.path.indexOf('.layers.')<=6) {
			obj.todo.lastSelectedLayers = RVS.selLayers;
			obj.todo.LayerSettings = true;
			var _split = obj.path.split("."),
				_index = jQuery.inArray('layers',_split);
			obj.todo.slide = _split[(_index-1)];
			obj.todo.layer = _split[(_index+1)];
			obj.todo.mode = "slidelayout";

		}
		// IF SLIDE OR LAYERS ARE IN GAME, WE NEED TO RESET CACHE FOR THOSE ELEMENTS
		if (!n && obj.path.indexOf('.slide.')>=0 && obj.path.indexOf('.slide.')<=6){
			obj.todo.SlideSettings = true;
			var _split = obj.path.split("."),
				_index = jQuery.inArray('slide',_split);
			obj.todo.slide = _split[(_index-1)];
			obj.todo.mode = "slidelayout";
		}

		if (!n &&
				(obj.path.indexOf('.slide.timeline.delay')>=0 ||
				 obj.path.indexOf('.slide.timeline.duration.0')>=0)
			)
			obj.todo.layerAndSlideMode = true;

		if (obj.path==='slideIDs') obj.todo.SlideList = true;

		if (obj.groupid==="layersorting" || obj.groupid==="layersorting_layermovement") {
			obj.todo.LayerSorting = true;
			obj.todo.slide = obj.path.split(".")[0];
		}
		return obj.todo;
	}


	/*
	REDO/UNDO LAST STEP(S)
	*/
	function makeUndoRedoStep(obj) {
		var step = obj.step,
			todo = obj.todo === undefined ? {SlideList:false,SliderSettings:false,SlideSettings:false,LayerSettings:false,LayerSorting:false} : obj.todo,
			evts = [];
		todo.updateLayerImageSrcList = todo.updateLayerImageSrcList===undefined ? [] : todo.updateLayerImageSrcList;
		todo.slides = todo.slides===undefined ? [] : todo.slides;
		//UNDO/REDO GROUP OF CHANGES
		if ( step.chngamount!==undefined) {
			// 1 OR MORE STEPS UNDO/REDO
			for (var stepindex in step.steps) {
				if(!step.steps.hasOwnProperty(stepindex)) continue;
				var substep = step.steps[stepindex];
				if (obj.valkey==="old")
					var substep = step.steps[step.chngamount-(stepindex)-1];

				if (substep.backupType!==undefined){

					if (substep.bckpGrType==="addlayer" || substep.bckpGrType==="removelayer" || substep.bckpGrType==="layerTemplateAnimation") todo.forceRebuildLayerList=true;

					switch (substep.backupType) {
						case "layerFrames":
							if (jQuery.isEmptyObject(substep[obj.valkey])) {
							} else {
								RVS.SLIDER[substep.slide].layers[substep.layer].timeline.frames = RVS.F.safeExtend(true,{},substep[obj.valkey]); //UNDO/REDO FULL BACKUP
								todo.framesToRedraw = todo.framesToRedraw===undefined ? [] : todo.framesToRedraw;
								todo.framesToRedraw.push(substep.layer);
							}
						break;
						case "clipPath":
							if (jQuery.isEmptyObject(substep[obj.valkey])) {
							} else {
								RVS.SLIDER[substep.slide].layers[substep.layer].timeline.clipPath = RVS.F.safeExtend(true,{},substep[obj.valkey]); //UNDO/REDO FULL BACKUP
								todo.framesToRedraw = todo.framesToRedraw===undefined ? [] : todo.framesToRedraw;
								todo.framesToRedraw.push(substep.layer);
							}
						break;
						case "singleFrame":
							if (jQuery.isEmptyObject(substep[obj.valkey])) {
							} else {
								RVS.SLIDER[substep.slide].layers[substep.layer].timeline.frames[substep.frame] = RVS.F.safeExtend(true,{},substep[obj.valkey]); //UNDO/REDO FULL BACKUP
								todo.framesToRedraw = todo.framesToRedraw===undefined ? [] : todo.framesToRedraw;
								todo.framesToRedraw.push(substep.layer);
							}
						break;
						case "layerLoop":
							if (jQuery.isEmptyObject(substep[obj.valkey])) {
							} else {
								RVS.SLIDER[substep.slide].layers[substep.layer].timeline.loop = RVS.F.safeExtend(true,{},substep[obj.valkey]); //UNDO/REDO FULL BACKUP
								todo.framesToRedraw = todo.framesToRedraw===undefined ? [] : todo.framesToRedraw;
								todo.framesToRedraw.push(substep.layer);
							}
						break;
						case "frame":

							if (jQuery.isEmptyObject(substep[obj.valkey])) {
								delete RVS.SLIDER[substep.slide].layers[substep.layer].timeline.frames[substep.frame];
								jQuery('#'+substep.slide+"_"+substep.layer+"_"+substep.frame).remove();
								todo.framesToReorder = todo.framesToReorder===undefined ? [] : todo.framesToReorder;
								todo.framesToReorder.push({layerid:substep.layer});
							} else {
								RVS.SLIDER[substep.slide].layers[substep.layer].timeline.frames[substep.frame] = RVS.F.safeExtend(true,{},substep[obj.valkey]); //UNDO/REDO FULL BACKUP
								todo.framesToAdd = todo.framesToAdd===undefined ? [] : todo.framesToAdd;
								todo.framesToAdd.push({layerid:substep.layer, el: jQuery('#tllayerlist_element_'+substep.slide+'_'+substep.layer), newframe:substep.frame});
							}
						break;
						case "slide":
							if (jQuery.isEmptyObject(substep[obj.valkey])) {
								delete RVS.SLIDER[substep.path];
								substep.cache = jQuery('#slide_list_element_'+substep.path).removeClass("selected").detach();
								todo.forceSelectSlide = substep.beforeSelected;
							}
							else {
								RVS.SLIDER[substep.path] = RVS.F.safeExtend(true,{},substep[obj.valkey]); //UNDO/REDO FULL BACKUP
								if (substep.cache!==undefined) jQuery('#slidelist').append(substep.cache);
								if (jQuery.inArray(substep.bckpGrType,["addnewslide","duplicateslide"])>=0)
									todo.forceSelectSlide = substep.path;
								else
									todo.forceSelectSlide = substep.beforeSelected;

							}
							todo.mode = "slidelayout";
						break;
						case "layer":
							if (jQuery.isEmptyObject(substep[obj.valkey])) {

								delete RVS.SLIDER[(substep.slideid!==undefined ? substep.slideid : RVS.S.slideId)].layers[substep.path];
								jQuery('#_lc_'+(substep.slideid!==undefined ? substep.slideid : RVS.S.slideId)+'_'+substep.path+'_').remove();
							} else
								RVS.SLIDER[(substep.slideid!==undefined ? substep.slideid : RVS.S.slideId)].layers[substep.path] = RVS.F.safeExtend(true,{},substep[obj.valkey]); //UNDO/REDO FULL BACKUP

							todo.rebuildLayerList = true;
							todo.layer = true;
						break;
						case "full":
							RVS.SLIDER = RVS.F.safeExtend(true,{},substep[obj.valkey]); //UNDO/REDO FULL BACKUP
						break;
					}
				} else {
					RVS.F.updateSliderObj({path:substep.path,val:substep[obj.valkey], ignoreBackup:true,  ignoreResponsive: substep.ignoreResponsive, wasEdited:substep.wasEdited, editedPath:substep.editedPath, undoRedo:(obj.valkey==="old" ? "undo" : "redo") });// UNDO / REDO SINGLE STEP
				}
				if (substep.evt==="updatelayerimagesrc" && jQuery.inArray(todo.updateLayerImageSrcList.indexOf,substep.uid)===-1) todo.updateLayerImageSrcList.push(substep.uid);
				todo = checkUndoRedoTodos({todo:todo, path:substep.path, groupid:step.id});
			}
			if (jQuery.inArray(todo.slide,todo.slides)===-1) todo.slides.push(todo.slide);
			if (step.id=="layersorting_layermovement") todo.forceRebuildLayerList=true;

		} else {

			// VERY 1 STEP UNDO	/ REDO
				if (step.backupType!==undefined){
					switch (step.backupType) {
						case "slide":
							if (jQuery.isEmptyObject(step[obj.valkey]))
								delete RVS.SLIDER[step.path];
							else
								RVS.SLIDER[step.path] = RVS.F.safeExtend(true,{},step[obj.valkey]); //UNDO/REDO FULL BACKUP
						break;
						case "full":
							RVS.SLIDER = RVS.F.safeExtend(true,{},step[obj.valkey]); //UNDO/REDO FULL BACKUP
						break;
					}
				} else {
					RVS.F.updateSliderObj({path:step.path,val:step[obj.valkey],ignoreBackup:true, evt:step.evt, evtparam:step.evtparam,  undoRedo:(obj.valkey==="old" ? "undo" : "redo"),  ignoreResponsive: step.ignoreResponsive, wasEdited:step.wasEdited, editedPath:step.editedPath}); // UNDO / REDO SINGLE STEP
				}
				todo = checkUndoRedoTodos({todo:todo, path:step.path});
				if (jQuery.inArray(todo.slide,todo.slides)===-1) todo.slides.push(todo.slide);
		}

		return todo;
	}

	/*
	VISIBLE TEXT AND ICON IN REDO/UNDO LIST
	*/
	function undoRedoTranslate(obj) {

		if (obj!==undefined && obj.type===undefined) obj.type = obj.path.indexOf(RVS.S.slideId+".layers.")>=0 ? "layer" : obj.type;

		var _ = obj.path,
			_addon = _.indexOf('addOns') >= 0 ? _.split('addOns.')[1] : "none",
			r,
			i = obj.type==="layer" ? "layers" : "undo",
			lastkey = obj.path.split(".");

		lastkey = lastkey[lastkey.length-1];

		r = _addon!=="none" ? _addon.replace("revslider-","").replace("-addon."," "+(obj.type==="layer" ? "layer" : "Slide")+" ") :
			_.indexOf('settings.size.width')>=0 ? 'Layer Container Width' :
			_.indexOf('settings.size.height')>=0 ? 'Layer Container Height' :
			_.indexOf('settings.size.minHeight')>=0 ? 'Slider Min Height' :
			_.indexOf('settings.size.maxWidth')>=0 ? 'Slider Max Width' :
			_.indexOf('settings.size')>=0 ? 'Slider Size Settings' :
			_.indexOf('settings.carousel')>=0 ? 'Carousel Settings' :
			_.indexOf('slide.bg')>=0 ? 'Slide Background' :
			_.indexOf('.fontWeight')>=0 ? 'Font Weight' :
			_.indexOf('.fontSize')>=0 ? 'Font Size' :
			_.indexOf('.fontFamily')>=0 ? 'Font Family' :
			_.indexOf('.fontStyle')>=0 ? 'Font Style' :
			_.indexOf('.textTransform')>=0 ? 'Text Transform' :
			_.indexOf('.idle.selectable')>=0 ? 'Layer Markable' :
			_.indexOf('.textDecoration')>=0 ? 'Text Decoration' :
			_.indexOf('slide.attributes')>=0 ? 'Slide Attributes' :
			_.indexOf('.media')>=0 && obj.type==="layer" ? 'Media ('+lastkey+')' :
			_.indexOf('alias')>=0 && (_.indexOf('.layers')>=0  || obj.type==="layer") ? 'Layer Alias' :
			_.indexOf('slide.timeline.duration')>=0 ? 'Slide Transition Speed':
			_.indexOf('timeline.frames') >= 0 ? 'Layer Animation ('+lastkey+')' :
			_ === "slideIDs" ? 'Slide Order Change' :
				_;

		i = _addon!=="none" ? 'extension' :
			_.indexOf('settings.size.width')>=0 ? 'open_with' :
			_.indexOf('settings.size.height')>=0 ? 'open_with' :
			_.indexOf('settings.size.minHeight')>=0 ? 'vertical_align_bottom' :
			_.indexOf('settings.size.maxWidth')>=0 ? 'trending_flat' :
			_.indexOf('settings.size')>=0 ? 'exposure' :
			_.indexOf('settings.carousel')>=0 ? 'view_carousel' :
			_.indexOf('slide.bg')>=0 ? 'image' :
			_.indexOf('.fontWeight')>=0 ? 'font_download' :
			_.indexOf('.fontSize')>=0 ? 'text_format' :
			_.indexOf('.fontFamily')>=0 ? 'translate' :
			_.indexOf('.fontStyle')>=0 ? 'format_italic' :
			_.indexOf('.textTransform')>=0 ? 'text_fields' :
			_.indexOf('.idle.selectable')>=0 ? 'select_all' :
			_.indexOf('.textDecoration')>=0 ? 'text_format' :
			_.indexOf('slide.attributes')>=0 ? 'speaker_notes' :
			_.indexOf('.media')>=0 && obj.type==="layer" ? 'videocam' :
			_.indexOf('alias')>=0 && (_.indexOf('.layers')>=0  || obj.type==="layer") ? 'title' :
			_.indexOf('slide.timeline.duration')>=0 ? 'timelapse' :
			_.indexOf('timeline.frames') >= 0 ? 'theaters' :
			_ === "slideIDs" ? 'sort' :
				i;

		return {txt:r,icon:i, lastkey:obj.lastkey};
	}




/****************************************************************************+
	-	ICON LIBRARY BUILDER FOR QUICK PICK OF ICONS 	-
*****************************************************************************/
	RVS.F.showIconPicker = function(_) {
		// CREATE CONTAINER IF NEEDED
		RVS.F.container = RVS.F.container===undefined ? jQuery('<div id="rs_iconselector"><div class="rs_iconselector_header"><input type="text" placeholder="'+RVS_LANG.searcforicon+'" id="rs_iconselector_search"><i class="material-icons closers_iconselector">close</i></i></div>') : RVS.F.container;
		if (RVS.F.list===undefined) {
			RVS.F.list = jQuery('<div id="rs_iconselector_inner"></div>');
			RVS.F.container.append(RVS.F.list);
		}
		// APPEND TO THE RIGHT CONTAINER
		if (_.parent!==undefined) jQuery(_.parent).append(RVS.F.container);

		// ADD CLASS IF NEEDED
		if (_.classlist!==undefined) RVS.F.container[0].className= _.classlist;

		RVS.F.insertinto = jQuery(_.insertinto);
		RVS.S.icon_closeafterpick = _.closeafterpick;
		RVS.S.icon_shortreturn = _.shortreturn;

		if (_.insertinto==="#ta_layertext" && RVS.F.insertinto[0].style.display==="none")
			RVS.F.insertinto = jQuery('#ta_toggletext');

		// LOAD LIBRARY IF NOT YET DONE AND SHOW CONTAINER
		if (RVS.LIB.OBJ==undefined || RVS.LIB.OBJ.items===undefined || RVS.LIB.OBJ.items.fonticons===undefined)
			RVS.F.openObjectLibrary({types:["fonticons"],filter:"all", selected:["fonticons"], event:"showIconToTextLayerForm", silent:true});
		else
			RVS.DOC.trigger('showIconToTextLayerForm');
	};
	RVS.F.initIconPicker = function() {
		// CREATE LISTENERS (RUN ONLY 1x)
		if (!RVS.F.initialized) {
			RVS.F.initialized = true;

			// SHOW ICON TO TEXT LAYER FORM LISTENER
			RVS.DOC.on('showIconToTextLayerForm',function(){
				RVS.F.open = true;
				// BUILD LIST IF NOT READY YET
				if (!RVS.V.buildIconPicker) {
					RVS.V.buildIconPicker=true;
					for (var i in RVS.LIB.OBJ.items.fonticons) {
						if(!RVS.LIB.OBJ.items.fonticons.hasOwnProperty(i)) continue;
						var icon = RVS.LIB.OBJ.items.fonticons[i];
						if (icon.tags===undefined) continue;
						if (RVS.C[icon.tags[0]]===undefined) {
							RVS.C[icon.tags[0]] = jQuery('<div id="font_icon_subcontainer_'+icon.tags[0]+'" class="font_icon_subcontainer"><div class="font_icon_subcontainer_title">'+icon.tags[0]+'</div></div>');
							RVS.F.list.append(RVS.C[icon.tags[0]]);
						}
						if (icon.tags[0]==="MaterialIcons")
							RVS.C[icon.tags[0]].append('<i data-title="'+icon.title+'" data-fonticon="true" class="material-icons">'+icon.handle.replace(".","")+'</i>');
						else
							RVS.C[icon.tags[0]].append('<i data-title="'+icon.title+'" data-fonticon="true" class="'+icon.handle.replace(".","")+'"></i>');
					}
				}
				RVS.F.container.show();
				RVS.F.list.RSScroll({wheelPropagation:false, suppressScrollX:true});
			});

			// LISTEN TO CLOSE CLICK
			RVS.DOC.on('click','.closers_iconselector', function() {
				RVS.F.open = false;
				RVS.F.container.hide();
			});

			// LISTEN TO INPUT FIELD
			RVS.DOC.on('keyup','#rs_iconselector_search',function() {
				if (this.value.length>1) {
					RVS.F.list.scrollTop(0);
					var inpval = (this.value).toLowerCase();
					RVS.F.list.find('i').each(function() {
						if ((this.dataset.title.toLowerCase()).indexOf(inpval)>=0)
							this.style.display="inline-block";
						else
							this.style.display="none";
					});
				} else {
					RVS.F.list.find('i').each(function() {
						this.style.display="inline-block";
					});
				}
			});


			//BODY LISTENER; CLOSE IF IT IS OPENEND AND CLICKED OUTSIDE
			jQuery('body').on('click',function(e) {
				if (RVS.F.open) {
					if (e.target.id==="rs_iconselector_search") {

					} else {
						if (e.target.dataset.fonticon) {
							if (RVS.S.icon_shortreturn)
								RVS.F.insertinto.val(e.target.className);
							else
								RVS.F.insertinto.val(RVS.F.insertinto.val()+'<i class="'+e.target.className+'">'+(e.target.className==="material-icons" ? e.target.innerHTML : "")+'</i>');
							RVS.F.insertinto.trigger("change");
							if (RVS.S.icon_closeafterpick) {
								RVS.F.open = false;
								RVS.F.container.hide();
							}

						} else
						if (jQuery(e.target).closest('#rs_iconselector_inner').length>0)
						{
							//CLICK WITHIN THE CONTAINER, IGNORE
						} else {
							RVS.F.open = false;
							RVS.F.container.hide();
							return false;
						}
					}

				}
			});
		} // END OF INITIALISED CHECK
	};

	// ADD ICON TO LAYER CONTENT
	RVS.DOC.on('addIcontoTextLayer',function(e,origEvent) {
		//Initialise the Mini Icon Selector
		RVS.F.initIconPicker();
		var d = origEvent.event.currentTarget.dataset;
		RVS.F.showIconPicker({parent:d.iconparent, classlist:d.classlist, insertinto:d.insertinto, closeafterpick:d.closeafterpick, shortreturn:d.shortreturn});
	});

	RVS.F.addBodyClickListener = function(_) {

		// IF WE WANT TO LIMIT THE ONLY CLICkABLE ELEMENTS....
		jQuery('body').on('click.revbuilderbodyclick',function(e) {
			if (RVS.S.waitOnFeedback!==undefined && RVS.S.waitOnFeedback.allowed!==undefined) {
				var clickonallow = false;
				for (var i in RVS.S.waitOnFeedback.allowed) {
					if(!RVS.S.waitOnFeedback.allowed.hasOwnProperty(i)) continue;
					clickonallow = (clickonallow === true || jQuery.inArray(RVS.S.waitOnFeedback.allowed[i],e.target.classList)>=0) ? true : false;
				}

				if (clickonallow) {

				} else {
					if (RVS.S.waitOnFeedback.closeEvent!==undefined)
						RVS.DOC.trigger(RVS.S.waitOnFeedback.closeEvent);
					RVS.S.waitOnFeedback = undefined;
					jQuery('body').unbind('click.revbuilderbodyclick');
					return false;
				}
			}
		});
	};


	/****************************************************************************+
		-	FIND UNDEFINED ATTRIBUTES IN OBJECTS 	-
	*****************************************************************************/
	RVS.F.findUndefineds = function(obj,par) {
		var _par ="";
		par = par===undefined ? "ROOT" : par;
		 for(var key in obj)
		    {
		        if(!obj.hasOwnProperty(key)) continue;
				if(typeof(obj[key]) == "object"){
		        	_par = par + "."+key;
		            RVS.F.findUndefineds(obj[key],_par);
		        }
		        else{
		        	if (obj[key]===undefined)
		        		console.log(par+'.'+key+'='+par+'.'+key+'===undefined ? "" : '+par+'.'+key+";");
		            	//console.log(key": " + obj[key]+"   "+par);
		        }
		    }
	};

	/************************************************************************
		-	DRAG ME FUNCTION 	-
		POSITION IS NOT CHANGED BY DRAGGABLE BUT BY ELEMENT DRAW FUNCTIONS
	************************************************************************/
	/**  WHERE  **/
	RVS.F.dragMe = function(obj) {
		obj.element.data('dragstart',{top:"auto",left:"auto",right:"auto",bottom:"auto"});
		obj.element.draggable({

			start:function(event,ui) {
				RVS.F.openBackupGroup({id:"elementmovement",txt:"Move "+obj.element.attr('id'),icon:"open_with"});
				RVS.F.updateContentDeltas();
				RVS.S.click.y = event.clientY;
				RVS.S.click.x = event.clientX;
				draggingelements = [];
				// RECORD THE CURRENT AND SELECTED ELEMENT POSITION AND RELATIV DEPNDENCIES
				obj.mem = {x:0,y:0};
				obj.revert = { 	x: this.style.left==="auto" ? -1 : 1,
								y: this.style.top==="auto" ? -1 : 1};
				if (obj.input!==undefined && obj.input.x!==undefined) obj.mem.x = parseInt(obj.input.x.val(),0);
				if (obj.input!==undefined && obj.input.y!==undefined) obj.mem.y = parseInt(obj.input.y.val(),0);
				obj.attribute = obj.attribute===undefined ? {x:"",y:""} : obj.attribute;
				obj.attribute.x = obj.attribute.x==="" || obj.attribute.x===undefined ? obj.input.x.data('r') : obj.attribute.x;
				obj.attribute.y = obj.attribute.y==="" || obj.attribute.y===undefined ? obj.input.y.data('r') : obj.attribute.y;
				obj.pos = {x:0,y:0};
				// FILL THE DRAGGINGELEMENT ARRAY WITH ALL SELECTED ELEMENT ATTRIBUTES
				draggingelements.push(obj);
				RVS.C.vW.removeClass("mode__slidelayout");
				RVS.C.vW.addClass("mode__navlayout");
				RVS.F.openSettings({forms:obj.forms,uncollapse:true});
			},
			drag:function(event,ui) {
				RVS.S.DaD.dragdelta.x =(event.clientX - RVS.S.click.x);
				RVS.S.DaD.dragdelta.y =(event.clientY - RVS.S.click.y);
				for (var elementindex in draggingelements) {
					if(!draggingelements.hasOwnProperty(elementindex)) continue;
					positionFieldUpdate(draggingelements[elementindex]);
				}
				ui.position={};
			},
			stop: function(event,ui) {
				RVS.F.closeBackupGroup({id:"elementmovement",txt:"Move "+obj.element.attr('id'),icon:"open_with"});

			}

		});
	};

	function positionFieldUpdate(obj) {
		obj.pos.x = obj.mem.x+ obj.revert.x*(RVS.S.DaD.dragdelta.x);
		obj.pos.y = obj.mem.y+ obj.revert.y*(RVS.S.DaD.dragdelta.y);
		if (obj.updateInput) {
			if (obj.input!==undefined && obj.input.x!==undefined) obj.input.x.val(obj.pos.x);
			if (obj.input!==undefined && obj.input.y!==undefined) obj.input.y.val(obj.pos.y);
		}
		RVS.F.updateSliderObj({path:obj.attributeRoot+obj.attribute.x,val:obj.pos.x});
		RVS.F.updateSliderObj({path:obj.attributeRoot+obj.attribute.y,val:obj.pos.y});
		if (obj.callEvent!==undefined) {
			jQuery('body').trigger(obj.callEvent,obj.callEventParam);
		}

	}




/**************************************************
	-	FORMULAR SHOW/HIDE FUNCTIONS IN POSITION -
***************************************************/

	// PUT THE FORMULAR IN POSIION BASED ON ITS PREDEFINED ATTRIBUTES AND CURRENT STATUS
	RVS.F.updateFormPositions = function(obj) {
		var jc = obj.jf.closest('.form_collector'),
			tc = obj.jf.closest('#the_right_toolbar_inner'),
			d= jc.data();
		if (obj.uncollapse===true || obj.uncollapse==="true") {
			var alltoclose = tc.find('.form_collector:visible .formcontainer');
			if (jc.attr('id')!=="form_collector_layerlist") {
				for (var ci=0; ci<alltoclose.length;ci++) {
					if (alltoclose[ci].id!=="form_layerlist")
						jQuery(alltoclose[ci]).addClass("collapsed");
				}
			}
			obj.jf.removeClass("collapsed");
		}
		if (obj.jf[0]!==undefined && obj.jf[0].dataset!==undefined) {
			if (obj.jf[0].dataset.unselect!==undefined) jQuery(obj.jf[0].dataset.unselect).removeClass("selected");
			if (obj.jf[0].dataset.select!==undefined) jQuery(obj.jf[0].dataset.select).addClass("selected");
		}
		// SHOW UNDERLAYS TO COVER ALL OTHER FUNCTIONS
		if (d && d.underlay!==undefined) {
			punchgs.TweenLite.set('#the_container',{filter:'blur(5px)'});
			jQuery(d.underlay).show();
		}
		RVS.DOC.trigger('scrollUpdates');
	};

	/*
	SHOW FORM AND SELECT SUBMENU IF NECCESSARY
	*/
	RVS.F.showForms = function(forms,uncollapse) {

		var submenus = forms.split(":"),
			selected;


		RVS.eMode.lo_container = RVS.eMode.lo_container===undefined ? document.getElementById('mmbw_loptions') : RVS.eMode.lo_container;
		RVS.eMode.sticky_container = RVS.eMode.sticky_container===undefined ? document.getElementById('settings_sticky_info') : RVS.eMode.sticky_container;
		RVS.eMode.stickyLeft = RVS.eMode.stickyLeft===undefined ? document.getElementById('settings_sticky_left') : RVS.eMode.stickyLeft;
		RVS.eMode.stickyRight = RVS.eMode.stickyRight===undefined ? document.getElementById('settings_sticky_right') : RVS.eMode.stickyRight;
		// If Preselector Exists, only run the Show Info if Preselector covers the LayoutMode
		if (submenus[0].indexOf('*sliderlayout*')>=0 && RVS.S.vWmode!=="sliderlayout") {
			RVS.F.mainMode({mode:"sliderlayout"});
			RVS.eMode.top = "slider";
			selected = jQuery('.general_submodule_trigger.selected');
		}

		if (submenus[0].indexOf('*navlayout*')>=0 && RVS.S.vWmode!=="navlayout") {
			RVS.F.mainMode({mode:"navlayout"});
			RVS.eMode.top = "navigation";
			selected = jQuery('.nav_submodule_trigger.selected');
		}

		if (submenus[0].indexOf('*slidelayout*')>=0 && RVS.S.vWmode!=="slidelayout") {
			RVS.F.mainMode({mode:"slidelayout"});
			RVS.eMode.top = "slide";
			selected = jQuery('.slide_submodule_trigger.selected');
		}


		if (submenus[0].indexOf('*mode__slidestyle*')>=0) {
			RVS.DOC.trigger('changeToSlideMode');
			RVS.eMode.top = "slide";
			selected  = jQuery('.slide_submodule_trigger.selected');
		}


		if (submenus[0].indexOf('*mode__slidecontent*')>=0) {
			RVS.DOC.trigger('changeToLayerMode');
			RVS.eMode.top = "layer";
			selected = jQuery('.layer_submodule_trigger.selected');
		}


		if (selected!==undefined && selected.length>=1 && selected.data("forms")!==undefined) RVS.eMode.menu = selected.data("forms")[0];

		for (var i in submenus) {
			if(!submenus.hasOwnProperty(i)) continue;
			submenus[i] = submenus[i].replace("*sliderlayout*","");
			submenus[i] = submenus[i].replace("*navlayout*","");
			submenus[i] = submenus[i].replace("*slidelayout*","");
			submenus[i] = submenus[i].replace("*mode__slidestyle*","");
			submenus[i] = submenus[i].replace("*mode__slidecontent*","");
		}


		if (submenus[0]!=="") RVS.eMode.menu = submenus[0];
		RVS.F.updateFormPositions({jf:jQuery(submenus[0]),focus:true,uncollapse:uncollapse});
		//jQuery(submenus[submenus.length-1]).click();		// KRIKI ??
		RVS.eMode.preMode = RVS.eMode.mode;

		// IF ANIMATION MODE IN LAYERS SELECTED, NEED TO RESET AMOUNT OF SELECTED LAYERS
		if (RVS.eMode.top==="layer" && RVS.eMode.menu==="#form_layer_content" && RVS.selLayers.length===1 && RVS.L[RVS.selLayers[0]].type==="audio") RVS.F.checkForAudioLayer();

		if (RVS.eMode.top==="layer" && (RVS.eMode.menu==="#form_layer_animation" || RVS.eMode.menu==="#form_layer_loop")) {
			if (RVS.selLayers.length>=1) RVS.F.selectLayers({id:RVS.L[RVS.selLayers[0]].uid,overwrite:true, action:"add"});
			RVS.eMode.mode = "animation";
			RVS.eMode.lo_container.className = "mmbw_animation";
			RVS.eMode.sticky_container.className = "sticky_in_animation";
			RVS.F.animationMode(true);
			RVS.TL.TL.addClass("inAnimationMode");
			RVS.C.rb.addClass("inAnimationMode");
		} else

		if (RVS.eMode.top==="layer" && RVS.eMode.menu==="#form_layer_hover" && RVS.eMode.mode!=="hover") {
			RVS.eMode.mode = "hover";
			RVS.eMode.lo_container.className = "mmbw_hover";
			RVS.eMode.sticky_container.className = "sticky_in_animation";
			RVS.F.animationMode(false);
			if (RVS.S.shwLayerAnim) {
				RVS.S.shwLayerAnim = false;
				RVS.F.changeSwitchState({el:document.getElementById("layer_simulator"),state:"play"});
				RVS.F.changeSwitchState({el:document.getElementById("layer_simulator_loop"),state:"play"});
			}
			RVS.F.updateSelectedLayersIdleHover();
			RVS.TL.TL.removeClass("inAnimationMode");
			RVS.C.rb.removeClass("inAnimationMode");
		} else

		if (RVS.eMode.mode!=="idle") {
			RVS.eMode.mode="idle";
			RVS.eMode.lo_container.className = "mmbw_idle";
			RVS.eMode.sticky_container.className = "";
			RVS.F.animationMode(false);
			if (RVS.S.shwLayerAnim) {
				RVS.S.shwLayerAnim = false;
				RVS.F.changeSwitchState({el:document.getElementById("layer_simulator"),state:"play"});
				RVS.F.changeSwitchState({el:document.getElementById("layer_simulator_loop"),state:"play"});
			}
			RVS.F.updateSelectedLayersIdleHover();
			RVS.TL.TL.removeClass("inAnimationMode");
			RVS.C.rb.removeClass("inAnimationMode");
		}

		if (RVS.eMode.mode==="idle" && RVS.S.keyFrame!=='idle') {
			RVS.S.keyFrame="idle";
			RVS.F.animationMode(false);
		}

		// REDRAW ELEMENTS IN IDLE MODE IF NOT ANY MORE IN HOVER AND ELEMENT IS SELECTED
		if (RVS.eMode.preMode==="hover" && RVS.eMode.mode!=="hover")
				for (var l in RVS.selLayers) {
					if(!RVS.selLayers.hasOwnProperty(l)) continue;
					RVS.F.drawHTMLLayer({uid:RVS.selLayers[l]});
				}

		if (RVS.eMode.top==="slide" && RVS.eMode.menu=="#form_slide_loops")	{
			RVS.TL.TL.addClass('slideloopedit');
			RVS.TL.slideLoopEdit = true;
		} else
		if (RVS.TL.slideLoopEdit) {
			RVS.TL.TL.removeClass('slideloopedit');
			RVS.TL.slideLoopEdit = false;
		}



		RVS.F.checkForFixedScroll();

		RVS.DOC.trigger('editorViewModeChange');

		clearTimeout(RVS.eMode.stickytimer);
		RVS.eMode.stickytimer = setTimeout(function(selected) {
			selected = selected===undefined ?
				RVS.eMode.top==="slider" ? jQuery('.general_submodule_trigger.selected') :
				RVS.eMode.top==="navigation" ? jQuery('.nav_submodule_trigger.selected') :
				RVS.eMode.top==="slide" ? jQuery('.slide_submodule_trigger.selected') :
				jQuery('.layer_submodule_trigger.selected') : selected;
			var gso = selected.find('.gso_title')[0];

			if (gso!==undefined) {
				RVS.eMode.stickyLeft.innerHTML = gso.innerHTML;
				RVS.eMode.stickyLeft.classList.remove("purple");
				if (gso.dataset.stickycolor!==undefined && gso.dataset.stickycolor==="purple") RVS.eMode.stickyLeft.className += " purple";
			}


			RVS.eMode.stickyRight.innerHTML = RVS_LANG["sticky_"+RVS.eMode.top];
		},50,selected);


	};

	/*
	OPEN SETTINGS BASED ON TRIGGER BUTTON, ID, JQUERY ELEMENT OR ELEMENT
	*/
	RVS.F.openSettings = function(obj) {

		// CALLING BUTTON GIVEN
		if (obj.btn!==undefined && obj.forms===undefined) {
			obj.forms = obj.btn.data('forms');
			obj.forms = obj.forms===undefined ?
				obj.btn.closest('.markable').length>0 ?
					obj.btn.closest('.markable').data('forms') : [] : obj.forms;
		}
		// ARRAY OF FORMS GIVEN
		if (obj.forms!==undefined) {
			if (typeof obj.forms==="string")
				RVS.F.showForms(obj.forms,obj.uncollapse);
			else
			for (var f in obj.forms) {
				if(!obj.forms.hasOwnProperty(f)) continue;
				RVS.F.showForms(obj.forms[f],obj.uncollapse);
			}

		}
	};


	// Change The Main Layout (Show, hide Windows we dont need)
	RVS.F.mainMode = function(obj) {
		RVS.C.vW.removeClass("mode__sliderlayout");
		RVS.C.vW.removeClass("mode__slidelayout");
		RVS.C.vW.removeClass("mode__navlayout");
		RVS.S.vWmode = obj.mode;
		if (obj.ignoreReDraw!==true) RVS.DOC.trigger("beforeLayoutModeChange");
		switch (obj.mode) {
			case "navlayout":
				RVS.C.vW.addClass("mode__navlayout");
				RVS.F.redrawAllNavigationContainer();
			break;
			case "sliderlayout":
				jQuery('#theslidermodule').addClass("selected");
				RVS.C.vW.addClass("mode__sliderlayout");
				RVS.F.redrawAllNavigationContainer();
			break;
			case "slidelayout":
				RVS.C.vW.addClass("mode__slidelayout");
				//if (slidestyle) RVS.C.vW.addClass("mode__slidestyle");

				if (obj.slide!==undefined) {
					RVS.F.setSlideFocus({slideid:obj.slide});
				} else {
					RVS.F.updateAllHTMLLayerPositions();
				}
			break;
		}

		// OPEN THE FORMS WE NEED FOR THIS MODE
		if (obj.set && obj.forms!==undefined)
			RVS.F.openSettings({forms:obj.forms, uncollapse:obj.uncollapse});



	};



/****************************************************************************
	-	UPDATE THE INPUT BOXES ANY ART FROM OBJECT VALUES ON DEMAND 	-
*****************************************************************************/
	RVS.F.updateEasyInput = function(obj) {

		obj.nval = obj.nval==='false' ? false : obj.nval==='true' ? true : obj.nval;
		obj.path = obj.path===undefined ? "settings." : obj.path;


		var commonvalue = undefined,
			tempval = "",
			ty = obj.el.type,
			ds = obj.el.dataset;



		switch (ty) {
			case "checkbox":
			case "text":
			case "textarea":
			case "select-one":
			case "select-multiple":

				if (!obj.multiselection) {
					commonvalue = obj.nval===undefined ? RVS.F.getDeepVal({path:obj.path+obj.el.dataset.r}) : obj.nval;
				} else {
					for (var li in RVS.selLayers) {
						if(!RVS.selLayers.hasOwnProperty(li)) continue;
						tempval = obj.nval===undefined ? RVS.F.getDeepVal({path:obj.path+RVS.selLayers[li]+'.'+obj.el.dataset.r}) : obj.nval;
						if (commonvalue==undefined || commonvalue==tempval)
							commonvalue = tempval;
						else
							commonvalue = ds.multiplaceholder!==undefined ? ds.multiplaceholder : "";
					}
				}



				if (commonvalue===undefined && obj.el.dataset.default!==undefined ) commonvalue =  obj.el.dataset.default;

				if (ty === "select-one") {
					if(obj.el.className.indexOf('setboxes')>=0) RVS.F.checkAvailableTagS2({select:jQuery(obj.el),val:commonvalue});
					obj.el.value = commonvalue;
					jQuery(obj.el).trigger("change.select2RS");
				}

				if (ty === "select-multiple") {

					if(!jQuery.isArray(commonvalue)) commonvalue=commonvalue.split(",");
					jQuery(obj.el).val(commonvalue);
					jQuery(obj.el).trigger("change.select2RS");
				}


				// SET / UNSET SELECTED CONTAINERS BASED ON THE CURRENT VALUE
				if (ds.unselect!==undefined || ds.select!==undefined) RVS.F.setUnsetSelected({unselect:ds.unselect, select:ds.select, val:obj.el.value, rval:ds.rval, prval:ds.prval, prvalif:ds.prvalif});

				if (ty === "checkbox")
					obj.el.checked = commonvalue===undefined ? undefined : commonvalue === "false" || commonvalue === false ? false : true;
				else {
					obj.el.value = commonvalue;
					obj.el.history = obj.el.value;
				}

				obj.el.value = obj.el.id==="layer_action_type" ? RVS_LANG["layeraction_"+obj.el.value] : obj.el.value;

				obj.el.value= obj.el.value===undefined || obj.el.value==="undefined" ? "" : obj.el.value;
				/*if (obj.el.dataset.suffix)
					obj.el.value = commonvalue + obj.el.dataset.suffix;	*/

				if (ty==="text" && obj.el.className.indexOf('my-color-field')>=0) jQuery(obj.el).rsColorPicker("refresh");

			break;
			case "radio":
				if (!obj.multiselection)
					 commonvalue = obj.nval===undefined ? obj.el.value === RVS.F.getDeepVal({path:obj.path+obj.el.dataset.r}) : obj.nval;
				else
					for (var li in RVS.selLayers) {
						if(!RVS.selLayers.hasOwnProperty(li)) continue;
						tempval = obj.nval===undefined ? obj.el.value === RVS.F.getDeepVal({path:obj.path+RVS.selLayers[li]+'.'+obj.el.dataset.r}) : obj.nval;
						if (commonvalue==undefined || commonvalue==tempval)
							commonvalue = tempval;
						else
							commonvalue = "";
					}
				obj.el.checked = commonvalue;
				// SET / UNSET SELECTED CONTAINERS BASED ON THE CURRENT VALUE
				if (ds.unselect!==undefined || ds.select!==undefined)
					if (obj.el.checked.checked)
						RVS.F.setUnsetSelected({unselect:ds.unselect, select:ds.select, val:obj.el.checked, rval:ds.rval, prval:ds.prval, prvalif:ds.prvalif});


			break;
		}

	};

	RVS.F.updateEasyInputs = function(obj) {
		obj.path = obj.path===undefined ? "settings." : obj.path;
		// EASY INITS (GO THROUGH IN LOOP AND INITIALISE THE INPUTS)
		jQuery(obj.container).find('.easyinit').each(function() {
			RVS.F.updateEasyInput({el:this, path:obj.path, multiselection:obj.multiselection});
			if (obj.trigger==="init" || obj.visualUpdate) {
				var jt = jQuery(this);
				if (obj.trigger==="init") jt.trigger("init");
				if (this.type==="checkbox") RVS.F.turnOnOffVisUpdate({input:jt});

			}
		});
	};

	// Shorthand set Input to a predefined Value (Input Field and after Object Path)
	RVS.F.setInputTo = function(obj) {


		jQuery.each(obj.field,function(i,e) {
			e = jQuery(e);
			RVS.F.updateEasyInput({el:e[0],nval:obj.val, path:obj.path});
			if (e[0].type==="checkbox") RVS.F.turnOnOffVisUpdate({input:e});
			e.trigger("change");
		});

	};

	// GENERATE IMAGES BY WP IF NOT GENERATED YET
	RVS.F.generateAttachmentMetaData = function() {
		if (RVS.ENV.create_img_meta) RVS.F.ajaxRequest('generate_attachment_metadata', {}, function(){},true,true);
	};


/******************************************************************+*
	-	INITIALISE, UPDATE, REINIT THE INPUT AND SELECT BOXES 	-
********************************************************************/
	RVS.F.updateInputBoxes = function() {
		// 1ST Initialisation of The Select Boxes
		jQuery('.tos2, .slideinput, .sliderinput').trigger('init');
	};


	RVS.F.reInitInputBoxes = function() {
		// 1ST Initialisation of The Select Boxes
		jQuery('.tos2, .slideinput, .sliderinput').trigger('init');
	};

	RVS.F.checkAvailableTagS2 = function(obj) {
		if (obj.val===undefined || obj.select===undefined) return false;
		if (obj.select.find('option[value="'+obj.val+'"]').length>0) return false;

		obj.select.append('<option value="'+obj.val+'">'+obj.val+'</option>');
		obj.select.trigger("change.select2RS").select2RS({tags:true});
		return true;
	};

	RVS.F.removeAllOptionsS2 = function(obj) {
		if (obj===undefined || obj.select===undefined) return;
		obj.select.find('option').remove();
		if (obj.select.hasClass("nosearchbox")) obj.select.trigger("change.select2RS").select2RS({minimumResultsForSearch:"Infinity",placeholder:"Enter or Select"});
		else
		if (obj.select.hasClass("setboxes")) obj.select.trigger("change.select2RS").select2RS({tags:true,placeholder:"Enter or Select"});
	};

	RVS.F.addOptionS2 = function(obj) {
		if (obj.val===undefined || obj.select===undefined) return false;
		if (obj.select.find('option[value="'+obj.val+'"]').length>0) return false;

		obj.select.append('<option value="'+obj.val+'">'+obj.txt+'</option>');
		if (obj.select.hasClass("nosearchbox")) obj.select.trigger("change.select2RS").select2RS({minimumResultsForSearch:"Infinity",placeholder:"Enter or Select"});
		else
		if (obj.select.hasClass("setboxes")) obj.select.trigger("change.select2RS").select2RS({tags:true,placeholder:"Enter or Select"});
	};

	RVS.F.addOrSelectOption = function(obj) {
		if (obj.val===undefined || obj.select===undefined) return false;
		if (obj.select.find('option[value="'+obj.val+'"]').length>0 && obj.selected!==false) {
			obj.select.val(obj.val).trigger('change');
		} else {
			if (obj.selected!==false)
				obj.select.append('<option selected value="'+obj.val+'">'+obj.val+'</option>');
			else
				obj.select.append('<option value="'+obj.val+'">'+obj.val+'</option>');
			obj.select.select2RS({
				minimumResultsForSearch:"Infinity",
				placeholder:"Select From List"
			});
		}

	};



	/* enable / disable an Option in a Select 2 Box **/
	RVS.F.setS2Option = function(obj) {

		if (obj===undefined) return;

		if (obj.enableValue!==undefined ) obj.select.find('option[value="'+obj.enableValue+'"]').removeAttr('disabled');
		if (obj.disableValue!==undefined) obj.select.find('option[value="'+obj.disableValue+'"]').attr('disabled','disabled');
		if (obj.selectValue!==undefined) obj.select.val(obj.selectValue);


		if (obj.select.hasClass("nosearchbox"))
			obj.select.trigger("change.select2RS").select2RS({
				minimumResultsForSearch:"Infinity",
				placeholder:"Enter or Select"
			});
		else
		if (obj.select.hasClass("setboxes"))
			obj.select.trigger("change.select2RS").select2RS({
				tags:true,
				placeholder:"Enter or Select"
			});

		// UPDATE THE SELETED ELEMENTS BASED ON THE OBJECT VALUES
		if (obj.update===true) RVS.F.updateEasyInput({el:obj.select[0],path:obj.path});

	};


	/* enable / disable an Option in a Select 2 Box **/
	RVS.F.setRadio = function(obj) {

		if (obj===undefined || obj.radio===undefined || obj.radioValue===undefined) return;
		var radio = jQuery('input:radio[name="'+obj.radio+'"]').filter('[value="'+obj.radioValue+'"]');
		radio.attr('checked', true);
		if (obj.change===true) radio.trigger('change');

		// UPDATE THE SELETED ELEMENTS BASED ON THE OBJECT VALUES
		if (obj.update===true) RVS.F.updateEasyInput({el:obj.select[0],path:obj.path});

	};

	// create function, it expects 2 values.
	RVS.F.insertAfter = function(newElement,targetElement) {
	    // target is what you want it to go after. Look for this elements parent.
	    var parent = targetElement.parentNode;

	    // if the parents lastchild is the targetElement...
	    if (parent.lastChild == targetElement) {
	        // add the newElement after the target element.
	        parent.appendChild(newElement);
	    } else {
	        // else the target has siblings, insert the new element between the target and it's next sibling.
	        parent.insertBefore(newElement, targetElement.nextSibling);
	    }
	};

	RVS.F.minMaxCheck = function(_) {
		_.v = _.v!=="wrong" && _.max!==undefined ? Math.min(_.v,_.max) : _.v;
		_.v = _.v!=="wrong" && _.min!==undefined ? Math.max(_.v,_.min) : _.v;
		return _.v;
	};

	// CHECK THE NUMMERIC INPUT FIELD, AND ALLOW ONLY VALUES AND SUFFIXES WITH CORRECT CONTENT
	RVS.F.checkNumInput = function(_) {

		// Split Numeric from Text first
		var valisnum = jQuery.isNumeric(_.val),
			_n = valisnum ? _.val : _.val.replace(/[^\d||-]+/g,''),
			_t = valisnum ? "" : _.val.replace(/\d+/,''),
			_oldt = _.history!==undefined ? _.history.replace(/\d+/,'') : "px",
			_a = _.allowed!==undefined ?_.allowed.toLowerCase().split(",") : ["px"],
			_s = "";
		_n = _n==="" ? "wrong" : _n;
		_t = _t.toLowerCase();



		// CHECK RANDOM VALUES
		if ((jQuery.inArray("random",_a)>=0  && _t[0]==="{" && _t[_t.length-1]==="}") || (jQuery.inArray("cycle",_a)>=0  && _t[0]==="[" && _t[_t.length-1]==="]")) {
			_.val = _.val.replace(/[^[\d||%||\-||{||}||.||,||\[||\]]+/g,'');
			return _.val;
		} else
		if (jQuery.inArray("#/#",_a)>=0  && _t[0]==="#" && _t[_t.length-1]==="#" && _.val[2]==="/")
			return _.val;
		else {
			for (var i in _a) {
				if(!_a.hasOwnProperty(i)) continue;
				if (_t == _a[i] || _t == "-"+_a[i]) _s = _a[i];
			}
			if (_n==="wrong" && _s==="" && _.val.length>0) {

				return "badvalue";
			}

			_n = _n==="wrong" ? 0 : _n;

			if (_s==="%" || _s==="px" || _s==="ms" || _s==="deg" || _s==="char" || _s==="-%" || _s==="-px" || _s==="-ms" || _s==="-deg") {

				return RVS.F.minMaxCheck({v:_n,min:_.min,max:_.max}) + _s;
			}
			else
			if (_s!=="") {

				return _s.toLowerCase(); //charAt(0).toUpperCase() + _s.slice(1).toLowerCase();
			}
			else
			if (_.val.length<=1 || _.val == _n) {

				if (jQuery.inArray("none",_a)>=0 && _n===0) return "none";
				_oldt = jQuery.inArray(_oldt,_a)>=0 && (_oldt==="px" || _oldt==="%" || _oldt==="ms" || _oldt==="deg" || _oldt==="char")? _oldt : jQuery.inArray("px",_a)>=0 ? "px" : jQuery.inArray("ms",_a)>=0  ? "ms" : jQuery.inArray("%",_a)>=0  ? "%" : jQuery.inArray("deg",_a)>=0  ? "deg" : jQuery.inArray("char",_a)>=0  ? "char" : "";
				return RVS.F.minMaxCheck({v:_n,min:_.min,max:_.max})+_oldt;
			} else {

				return "badvalue";
			}
		}

	};

	RVS.F.prepareOneInputWithPresets = function(t) {
		var _t = jQuery(t);
		if (!_t.parent().hasClass("input_presets_wrap")) {
			_t.wrap('<div class="input_presets_wrap"></div>');

			var _p = _t.parent(),
				list = jQuery('<div class="input_presets"></div>'),
				vals = t.dataset.presets_val.split("!"),
				txts = t.dataset.presets_text.split("!"),
				extcl = "";
			_p.append('<i class="material-icons input_presets_dropdown">more_vert</i>');

			for (var i in txts) {
				if(!txts.hasOwnProperty(i)) continue;
				 extcl = (txts[i].indexOf("$$"))>=0 ? "ipwborder" : "";
				 txts[i] = txts[i].replace("$R$",'<i class="material-icons">shuffle</i>')
				 					.replace("$C$",'<i class="material-icons">create</i>')
				 					.replace("$I$",'<i class="material-icons">system_update_alt</i>')
				 					.replace("$SC$",'<i class="material-icons">fullscreen_exit</i>')
				 					.replace("$SR$",'<i class="material-icons">arrow_back</i>')
				 					.replace("$SB$",'<i class="material-icons">arrow_upward</i>')
				 					.replace("$ST$",'<i class="material-icons">arrow_downward</i>')
				 					.replace("$SL$",'<i class="material-icons">arrow_forward</i>')
				 					.replace("$CL$",'<i class="material-icons">remove_circle_outline</i>')
				 					.replace("$LI$",'<i class="material-icons">link</i>')
				 					.replace("$LO$",'<i class="material-icons">local_offer</i>')
				 					.replace("$CY$",'<i class="material-icons">import_export</i>');

				list.append('<div class="input_preset '+extcl+'" data-iid="'+t.id+'" data-r="'+t.dataset.r+'" data-val="'+vals[i]+'">'+txts[i]+'</div>');

			}
			_p.append(list);
		}
	};

	RVS.F.initInputsWithPresets = function(selector) {
		if (selector===undefined)
			jQuery('.input_with_presets').each(function() {
				RVS.F.prepareOneInputWithPresets(this);
			});
	};


	/* MOVE CURSOR TO CLICK POSITION */
	function moveCaretToEnd(el) {
	    if (typeof el.selectionStart == "number") {
	        el.selectionStart = el.selectionEnd = el.value.length;
	    } else if (typeof el.createTextRange != "undefined") {
	        el.focus();
	        var range = el.createTextRange();
	        range.collapse(false);
	        range.select();
	    }
	}

/*******************************************************
-	UPDATE SELECT OPTIONS WITH SLIDERS, PAGES	-
********************************************************/
	// CREATE CUSTOM SELECT INNER OPTIONS
	function createOptionsOfArray(_) {
		var ret = '<option value="none">'+RVS_LANG.none+'</option>';

		for (var i in _.array) {
			if(!_.array.hasOwnProperty(i)) continue;
			if ((_.filter===undefined || _.filter==="all" || _.filter === _.array[i].type)	&& (_.subfilter===undefined || _.subfilter==="all" || _.subfilter===_.array[i].subtype))
				ret += '<option '+(_.preselected===_.array[i][_.type] ? "selected" : "")+' value="'+_.array[i][_.type]+'">'+_.array[i].title+'</option>';
		}
		return ret;
	}

	//BUILD THE SELECT LISTS WITH THE PAGES, SLIDER AS REQUESTED
	RVS.F.createSelectOptions = function(_) {
		listOfContents = listOfContents===undefined ? {} : listOfContents;

		if (listOfContents[_.ctype]===undefined) {
			listOfContents[_.ctype]=[];
			//ctype  => sliders, pages, posttypes
			RVS.F.ajaxRequest("get_list_of", {type:_.ctype}, function(response){
				if (response.pages) {
					for (var i in response.pages) {
						if(!response.pages.hasOwnProperty(i)) continue;
						listOfContents[_.ctype].push({id:i, slug:response.pages[i].slug, title:response.pages[i].title});
					}
				}
				if (response.sliders) {
					for (var i in response.sliders) {
						if(!response.sliders.hasOwnProperty(i)) continue;
						listOfContents[_.ctype].push({id:i, slug:response.sliders[i].slug, title:response.sliders[i].title, type:response.sliders[i].type, subtype:response.sliders[i].subtype});
					}
				}
				if (response.posttypes) {
					for (var i in response.posttypes) {
						if(!response.posttypes.hasOwnProperty(i)) continue;
						listOfContents[_.ctype].push({slug:response.posttypes[i].slug, title:response.posttypes[i].title});
					}
				}
				_.select.innerHTML = createOptionsOfArray({array:listOfContents[_.ctype], type:_.select.dataset.valuetype, preselected:_.select.value, filter:_.select.dataset.filter, subfilter:_.select.dataset.subfilter});
				jQuery(_.select).select2RS({minimumResultsForSearch:"Infinity",placeholder:"Select From List"});
				RVS.F.updateSelectsWithSpecialOptions();
			},undefined,undefined,RVS_LANG.updateselects+'<br><span style="font-size:17px; line-height:25px;">"'+RVS_LANG.buildingSelects+'"</span>');



		} else {

			_.select.innerHTML = createOptionsOfArray({array:listOfContents[_.ctype], type:_.select.dataset.valuetype, preselected:_.select.value,filter:_.select.dataset.filter, subfilter:_.select.dataset.subfilter});
			jQuery(_.select).select2RS({minimumResultsForSearch:"Infinity",placeholder:"Select From List"});
			RVS.F.updateSelectsWithSpecialOptions();
		}
	};
	// GET CUSTOM POST TYPES
	RVS.F.getCustomPostTypes = function(callback) {
		if (RVS.LIB.POST_TYPES===undefined) {
			RVS.LIB.POST_TYPES=[];
			//ctype  => sliders, pages, posttypes
			RVS.F.ajaxRequest("get_list_of", {type:"posttypes"}, function(response){
				if (response.posttypes) {
					for (var i in response.posttypes) {
						if(!response.posttypes.hasOwnProperty(i)) continue;
						RVS.LIB.POST_TYPES.push({slug:response.posttypes[i].slug, title:response.posttypes[i].title, tax:response.posttypes[i].tax});
					}
				}
				if (callback) callback();
				return RVS.LIB.POST_TYPES;
			});
		} else	{
			if (callback) callback();
			return RVS.LIB.POST_TYPES;
		}
	};

	// GO SEQUENTIEL THROUGH THE ELEMENTS TO SURELY ADD ALL OPTIONS TO THE LISTS ON SIMILAR REQUESTS
	RVS.F.updateSelectsWithSpecialOptions = function() {
		if (socList===undefined)
			socList = { listofselects : document.getElementsByClassName('select_of_customlist'), curindex : 0};
		else
			socList.curindex++;

		if (socList.curindex>=socList.listofselects.length) {
			delete socList;
			return true;
		} else {
			RVS.F.createSelectOptions({ctype:socList.listofselects[socList.curindex].dataset.ctype, select:socList.listofselects[socList.curindex]});
		}
	};



/*******************************************************

-	BIG SHOWDOWN ! INITIALISE ALL INPUT EVENTS	-

********************************************************/

	RVS.F.initTpColorBoxes = function(_) {
		// Create ColorPicker Inputs
		jQuery(_).rsColorPicker({

			init: function(inputWrap, inputElement, cssColor, widgetSettings) {

				// hide the real input and replace with a fake input for visual purposes
				var ghost = jQuery('<input type="text" class="layerinput">').appendTo(inputWrap);

				// if the original input is hidden in any way, RevSlider admin won't include it in the input fields to update for some reason
				inputElement.data('ghost', ghost).hide();

			},

			onRefresh: function(inputElement, cssColor) {

				// update the ghost input field
				inputElement.data('ghost').val(cssColor);

			},

			onEdit:function(currentInput, cssColor) {

				// update the ghost input field
				currentInput.data('ghost').val(cssColor);

				RVS.DOC.trigger('coloredit', [currentInput, cssColor]);

			},

			/*
				new, value has officially changed
			*/
			change:function(currentInput, cssColor, gradient) {

				// update the ghost input field
				currentInput.data('ghost').val(cssColor);

				/*

					Inside the "colorEditLayer" function, this is handled like this:
					inp.val(gradient || val);

					^ if the color is a gradient, the gradient arg will be a JSON string
					  otherwise, the gradient arg will be undefined, and we can use the cssColor as the official value instead

				*/
				RVS.DOC.trigger('coloredit', [currentInput, cssColor, gradient, true]);

			},

			cancel:function(currentInput, cssColor) {

				// update the ghost input field
				currentInput.data('ghost').val(cssColor);

				RVS.DOC.trigger('colorcancel', [currentInput, cssColor]);

			}

		});
	};

	RVS.F.createWPMLOptions = function(_) {
		var _h="";
		if (typeof RS_WPML_LANGS!=="undefined" && RS_WPML_LANGS!==undefined) {
			for (var i in RS_WPML_LANGS) {
				if(!RS_WPML_LANGS.hasOwnProperty(i)) continue;
				_h += '<option value="'+i+'" data-src="'+RS_WPML_LANGS[i].image+'" data-select2RS-id="'+i+'">'+RS_WPML_LANGS[i].title+'</option>';
			}
			_.innerHTML = _h;
		}

	};
	// BUILD EASE OPTIONS FOR SELECTBOXES
	RVS.F.createEaseOptions = function(_) {
		_ = _.innerHTML===undefined ? _[0] : _;

		if (_ease===undefined || _ease==="") {
			// WRITE EASING SELECTS
			_ease = '<option value="default">Default</option>';
			_ease += '<option value="Linear.easeNone">Linear.easeNone</option>';
			_ease += '<option value="Power0.easeIn">Power0.easeIn</option>';
			_ease += '<option value="Power0.easeInOut">Power0.easeInOut</option>';
			_ease += '<option value="Power0.easeOut">Power0.easeOut</option>';
			_ease += '<option value="Power1.easeIn">Power1.easeIn</option>';
			_ease += '<option value="Power1.easeInOut">Power1.easeInOut</option>';
			_ease += '<option value="Power1.easeOut">Power1.easeOut</option>';
			_ease += '<option value="Power2.easeIn">Power2.easeIn</option>';
			_ease += '<option value="Power2.easeInOut">Power2.easeInOut</option>';
			_ease += '<option value="Power2.easeOut">Power2.easeOut</option>';
			_ease += '<option value="Power3.easeIn">Power3.easeIn</option>';
			_ease += '<option value="Power3.easeInOut">Power3.easeInOut</option>';
			_ease += '<option value="Power3.easeOut">Power3.easeOut</option>';
			_ease += '<option value="Power4.easeIn">Power4.easeIn</option>';
			_ease += '<option value="Power4.easeInOut">Power4.easeInOut</option>';
			_ease += '<option value="Power4.easeOut">Power4.easeOut</option>';
			_ease += '<option value="Back.easeIn">Back.easeIn</option>';
			_ease += '<option value="Back.easeInOut">Back.easeInOut</option>';
			_ease += '<option value="Back.easeOut">Back.easeOut</option>';
			_ease += '<option value="Bounce.easeIn">Bounce.easeIn</option>';
			_ease += '<option value="Bounce.easeInOut">Bounce.easeInOut</option>';
			_ease += '<option value="Bounce.easeOut">Bounce.easeOut</option>';
			_ease += '<option value="BounceLite">Bounce Lite</option>';
			_ease += '<option value="BounceSolid">Bounce Solid</option>';
			_ease += '<option value="BounceStrong">Bounce Strong</option>';
			_ease += '<option value="BounceExtrem">Bounce Extrem</option>';
			_ease += '<option value="Circ.easeIn">Circ.easeIn</option>';
			_ease += '<option value="Circ.easeInOut">Circ.easeInOut</option>';
			_ease += '<option value="Circ.easeOut">Circ.easeOut</option>';
			_ease += '<option value="Elastic.easeIn">Elastic.easeIn</option>';
			_ease += '<option value="Elastic.easeInOut">Elastic.easeInOut</option>';
			_ease += '<option value="Elastic.easeOut">Elastic.easeOut</option>';
			_ease += '<option value="Expo.easeIn">Expo.easeIn</option>';
			_ease += '<option value="Expo.easeInOut">Expo.easeInOut</option>';
			_ease += '<option value="Expo.easeOut">Expo.easeOut</option>';
			_ease += '<option value="Sine.easeIn">Sine.easeIn</option>';
			_ease += '<option value="Sine.easeInOut">Sine.easeInOut</option>';
			_ease += '<option value="Sine.easeOut">Sine.easeOut</option>';
			_ease += '<option value="SlowMo.ease">SlowMo.ease</option>';
			_ease += '<option value="SFXBounceLite">SFX - Bounce Lite</option>';
			_ease += '<option value="SFXBounceSolid">SFX - Bounce Solid</option>';
			_ease += '<option value="SFXBounceStrong">SFX - Bounce Strong</option>';
			_ease += '<option value="SFXBounceExtrem">SFX - Bounce Extrem</option>';
		}
		_.innerHTML = _ease;
		if (_.dataset!==undefined && _.dataset.inherit===true) _.innerHTML += '<option value="inherit">Inherit</option>'+thisease;
	};

	// CREATE SLIDE ANIM OPTIONS  SELECTBOXES
	RVS.F.createSlideAnimOptions = function(_) {
		_ = _.innerHTML===undefined ? _[0] : _;
		if (slideanimlist===undefined || slideanimlist==="") {
			for (var i in RVS.LIB.SLIDEANIMS) {
				if(!RVS.LIB.SLIDEANIMS.hasOwnProperty(i)) continue;
				var newgroup = '<optgroup label="'+RVS.LIB.SLIDEANIMS[i].alias+'">';
				for (var j in RVS.LIB.SLIDEANIMS[i]) {
					if(!RVS.LIB.SLIDEANIMS[i].hasOwnProperty(j)) continue;
					if (j!=="alias") newgroup += '<option value="'+j+'">'+RVS.LIB.SLIDEANIMS[i][j]+'</option>';
				}
				slideanimlist += newgroup+"</optgoup>";
			}
		}
		_.innerHTML = slideanimlist;
	};

	// CREATE SLIDE ANIM OPTIONS  SELECTBOXES
	RVS.F.createSliderListOptions = function(_) {
		_ = _.innerHTML===undefined ? _[0] : _;
		if (sliderlist===undefined || sliderlist==="") {
			sliderlist="";
			for (var i in RVS.LIB.SLIDERS) if(RVS.LIB.SLIDERS.hasOwnProperty(i)) sliderlist += '<option value="'+RVS.LIB.SLIDERS[i].alias+'">'+RVS.LIB.SLIDERS[i].title+'</option>';
		}

		_.innerHTML = sliderlist;
	};

	// LANGUAGE FLAGS ADDITION
	function formatState (state) {
	  if (!state.id)
	  	return state.text;
	  else
	  	return jQuery('<span><img src="'+jQuery(state.element)[0].dataset.src+'" class="wpml-img-flag" />' + state.text + '</span>');
	}



	RVS.F.initialiseGlobalBoxes = function() {
		// BUIL Slide Anim Selects
		jQuery('.tos2.slideAnimSelect').each(function() {
			RVS.F.createSlideAnimOptions(this);
		});

		// BUILD SLIDER LISTS
		jQuery('.tos2.selectsliderlist').each(function() {
			RVS.F.createSliderListOptions(this);
		});

		// BUILD EASING LISTS
		jQuery('.tos2.easingSelect').each(function() {
			RVS.F.createEaseOptions(this);
		});

		// CREATE WPML LISTS
		jQuery('.tos2.wpml_lang_selector').each(function() {
			RVS.F.createWPMLOptions(this);
		});

		// Create SelectBoxes without SearchBox
		jQuery('.tos2.nosearchbox').select2RS({
			minimumResultsForSearch:"Infinity",
			placeholder:"Select From List"
		});

		jQuery('.tos2.searchbox').select2RS({
			placeholder:"Enter or Select"
		});

		// Create SelectBoxes without SearchBox
		jQuery('.tos2.setboxes').select2RS({
			tags:true,
			placeholder:"Enter or Select"
		});

		jQuery('.tos2.wpml_lang_selector').select2RS({
			minimumResultsForSearch:"Infinity",
			placeholder:"Select From List",
			templateResult:formatState
		});

		RVS.DOC.on('click','#add_on_management',RVS.F.openAddonModal);
		// INIT ADDON MODAL LISTENER
		RVS.DOC.on('openAddonModal',RVS.F.openAddonModal);

	}


	RVS.F.initialiseInputBoxes = function() {

		RVS.F.initialiseGlobalBoxes();

		//Initialise Color Boxes
		RVS.F.initTpColorBoxes('.my-color-field');

		//Initialise the Predefined DropDowns for Special Fields
		RVS.F.initPreDrops();

		RVS.F.initInputsWithPresets();
		RVS.DOC.on('keyup click focus', '.livechange', function(e) {
			var	cT = e.currentTarget,
				ds = cT.dataset;
			RVS.S.inputField = cT.id;
			RVS.S.inputFieldCursorAt = cT.selectionStart;
			if (ds!==undefined && ds.evt==undefined) jQuery('body').trigger(ds.evt,{event:e,val:this.value,eventparam:ds.evtparam});
		});

		// RECORD CURRENT FOCUSED FIELD
		RVS.DOC.on('focus','textarea, input', function(e) {
			RVS.S.inFocus = e.currentTarget;
			RVS.S.inFocusValue = e.currentTarget.value;
		});

		// RECORD LAST BLURRED FIELD
		RVS.DOC.on('blur','textarea, input', function(e) {
			RVS.S.inFocus = "none";
		});

		// CLOSE DROPPABLE WINDOW IF NEEDED
		RVS.DOC.on('click','#filedrop_close',RVS.F.browserDroppable.close);

		// CLOSE DROPPABLE WINDOW IF NEEDED
		RVS.DOC.on('click','#fullpage_close',RVS.F.fullPageInfo.close);


		/*
		 ACTIVATE THE PLUGIN FROM INFO BOX
		 */
		 RVS.DOC.on('click','#rbmas_activateplugin',function() {
	 		var code = jQuery('#rbmas_purchasekey').val();
	 		RVS.F.ajaxRequest('activate_plugin', {code:code},function(response) {
	 			if (response.success) {
	 				RVS.ENV.activated = true;
	 				RVS.ENV.code = code;
	 				jQuery('.rbmas_close').click();
	 				if (RVS===undefined || RVS.F.updateDraw===undefined) return;
	 				RVS.F.updateDraw();
					RVS.F.isActivated();
					RVS.F.notifications();
	 			}
	 		});
		 });

		// Add Listeners to all SelectBoxes
		RVS.DOC.on('change init update focus','.tos2, .basicinput, .slideinput, .sliderinput, .globalinput, .layerinput, .navinput, .navstyleinput, .indeplayerinput, .actioninput, .targetlayeractioninput',function(e) {

			var	cT = e.currentTarget,
				ds = cT.dataset,
				v = cT.type==="checkbox" ? this.checked : this.value,
				jt = jQuery(this);

			// QUALITY CHECK

			if (ds.numeric) {
				v = RVS.F.checkNumInput({val:v, allowed:ds.allowed, history:ds.history, min:ds.min, max:ds.max});
				if (v==="badvalue") {
					jQuery(cT).addClass("badvalue");
					return;
				} else {
					cT.className = cT.className.replace("badvalue","");
				}
				cT.value=v;
				ds.history=cT.value;
			}

			if (ds.sanitize==="true") {
				v = RVS.F.sanitize_input(v);
			}

			if (cT.type==="select-multiple") {
				v = [];
				jQuery.each(cT.selectedOptions,function() {
					v.push(this.value);
				});

			}

			// CHAIN REACTIONS

			//Trigger SHOW HIDE ELEMENTS
			if (ds.showhide!==undefined || ds.show!==undefined || ds.hide!==undefined) {
				if (cT.type!=="radio" || cT.checked) {
					RVS.F.triggerShowHideDep(cT,v);
				}
			}

			// SET / UNSET SELECTED CONTAINERS BASED ON THE CURRENT VALUE
			if (ds.unselect!==undefined || ds.select!==undefined) {
				if (cT.type!=="radio" ||  cT.checked) {
					RVS.F.setUnsetSelected({unselect:ds.unselect, select:ds.select, val:v, rval:ds.rval, prval:ds.prval, prvalif:ds.prvalif});
				}
			}

			if (ds.setclasson!==undefined || ds.class!==undefined) {
				if (cT.type!=="radio" || cT.checked)
					RVS.F.setUnsetClass({container:ds.setclasson, class:ds.class, inversclass:ds.inversclass, val:v, rval:ds.rval});
			}

			//DISABLE, ENABLE CONTAINERS
			if (ds.disable!==undefined || ds.enable!==undefined) {
				if (cT.type!=="radio" || cT.checked)
					RVS.F.setEnableDisable({disable:ds.disable, enable:ds.enable, val:v});
			}

			//MAKE CONTAINERS AVAILABLE, UNAVAILABLE
			if (ds.available!==undefined || ds.unavailable!==undefined)
				if (cT.type!=="radio" ||  cT.checked)
					RVS.F.setUnAvailable({unavailable:ds.unavailable, available:ds.available, val:v});

			//UPDATE OTHER INPUTS
			if (ds.change!==undefined) {

				if (ds.changewhen!==undefined && ((v===ds.changewhen) || (v===true && ds.changewhen==="true") ||  (v===false && ds.changewhen==="false")))
					RVS.F.setInputTo({field:jQuery(ds.change), val:ds.changeto, path:ds.path});

				if (ds.changewhennot!==undefined && (v!==ds.changewhennot))
					if (!(((v===false || v==="false") && ds.changewhennot+""==="false") || ((v===true || v==="true") && ds.changewhennot+""==="true")))
						RVS.F.setInputTo({field:jQuery(ds.change), val:ds.changeto, path:ds.path});
			}

			// UPDATE A TEXT OF ANY CONTAINER WITH THE NEW VALUE
			if (ds.updatetext!==undefined) 	jQuery(ds.updatetext).text(v);

			// WHAT EVENT
			switch (e.type) {
				case "change":

					if (ds.r!==undefined) {


						// Check Value against some Function, and update it before pushing it into Object and Undo
						/*if (ds.valcheck!==undefined) {
							var precall = eval(ds.valcheck);
							if (typeof precall == 'function') {
								v = precall(v);
								if (ds.numeric) {
									v = RVS.F.checkNumInput({val:v, allowed:ds.allowed, history:ds.history, min:ds.min, max:ds.max});
									this.value = v;
									ds.history=this.value = v;
								}
							}
						}*/


						// replaces block above
						// Check Value against some Function, and update it before pushing it into Object and Undo
						if (ds.valcheck!==undefined && RVS.F.hasOwnProperty(ds.valcheck) && typeof RVS.F[ds.valcheck] === 'function') {
							var v = RVS.F[ds.valcheck](v);
							if (ds.numeric) v = RVS.F.checkNumInput({val:v, allowed:ds.allowed, history:ds.history, min:ds.min, max:ds.max});
							this.value = v;
							ds.history = v;

						}

						//Update Value of Object in Slide Settings
						if (jt.hasClass("slideinput"))
							RVS.F.updateSliderObj({path:RVS.S.slideId+".slide."+ds.r,val:v,evt:ds.evt, evtparam:ds.evtparam});

						//Update Value of Object in Navigation Presets
						if (jt.hasClass("navstyleinput")) {
							RVS.F.updateSliderObj({path:ds.r+"-def",val:true});
							jQuery("#"+e.currentTarget.id+'-def').attr('checked','checked');
							RVS.F.turnOnOff(jQuery("#"+e.currentTarget.id+'-def').closest('.tponoffwrap'),false);
							RVS.F.updateSliderObj({path:ds.r,val:v,evt:ds.evt, evtparam:ds.evtparam});
						}

						//Update Value of Object in Global Settings
						if (jt.hasClass("globalinput")) RVS.F.updateSliderObj({path:ds.r,val:v,evt:ds.evt, evtparam:ds.evtparam});


						//Update Value of Object in Slider Settings
						if (jt.hasClass("sliderinput")) RVS.F.updateSliderObj({path:"settings."+ds.r,val:v,evt:ds.evt, evtparam:ds.evtparam});


						//LAYER INPUT CHANGE, ALL LAYERS NEED TO BE UPDATES
						if (jt.hasClass("layerinput"))
							if (ds.updateviaevt!=='true' && ds.updateviaevt!=true)
								RVS.F.updateLayerObj({path:ds.r,val:v,evt:ds.evt, evtparam:ds.evtparam});


						//ACTION INPUT CHANGE
						if (jt.hasClass("actioninput")) {
							RVS.F.updateSliderObj({path:RVS.S.slideId+".layers."+RVS.selLayers[0]+"."+ds.r,val:v,evt:ds.evt, evtparam:ds.evtparam});
						}

						//ACTION's TARGET LAYER INPUT CHANGE
						if (jt.hasClass("targetlayeractioninput")) {
							if (RVS.S.actionTrgtLayerId!==undefined && RVS.S.actionTrgtLayerId.indexOf("static-")>=0)
								RVS.F.updateSliderObj({path:RVS.SLIDER.staticSlideId+".layers."+RVS.S.actionTrgtLayerId.replace("static-","")+"."+ds.r,val:v,evt:ds.evt, evtparam:ds.evtparam});
							else
								RVS.F.updateSliderObj({path:RVS.S.slideId+".layers."+RVS.S.actionTrgtLayerId+"."+ds.r,val:v,evt:ds.evt, evtparam:ds.evtparam});
						}

						//LAYER INPUT CHANGE, FULL PATH IS DEFINED, SINGLE LAYER UPDATE
						if (jt.hasClass("indeplayerinput"))
							RVS.F.updateSliderObj({path:RVS.S.slideId+".layers."+ds.r,val:v,evt:ds.evt, evtparam:ds.evtparam});


						// Values with Navigation Style Fields should change to Custom Preset
						if (jt.hasClass("presetToCustom")) {
							jQuery('#sr_'+ds.evtparam+'_style_preset').val("").trigger("change.select2RS");
							RVS.SLIDER.settings.nav[ds.evtparam].preset="";
						}

						//Trigger Also Other Inputs based on the Change
						if (this.dataset.triggerinp!==undefined)
							RVS.F.triggerInput({inp:this.dataset.triggerinp, dep:v, val:this.dataset.triggerinpval, when:this.dataset.triggerwhen, whennot:this.dataset.triggerwhennot});
					}

					// STANDARD INPUTS
					/*
					if (jt.hasClass("basicinput")) {
						//RVS.F.turnOnOff(jQuery("#"+e.currentTarget.id).closest('.tponoffwrap'),false);
					}
					*/

					if (jt.hasClass("callEvent")) {
						jQuery('body').trigger(ds.evt,{event:e,val:v,eventparam:ds.evtparam});
					}

				break;

				case "focusin":
					//Check if Focus Based Event need to be triggered
					if (ds.focusevt!==undefined) RVS.DOC.trigger(ds.focusevt,{event:e,val:v,eventparam:ds.focusevtparam});

					//Input Element has Focused
					if (ds.responsive!==undefined) {
						RVS.S.respInfoBar.visible = true;
						RVS.F.showFieldResponsiveValues(this);
					}


					// Put Cursor to the Right Position, Or Select all Input
					if (ds.cursortoclick=="true")
						 window.setTimeout(function() {
					        moveCaretToEnd(this);
					    }, 1);
					 else
						jt.select();
				break;
			}
		});

		// BLUR AND CLICK ON PRESETS FOR INPUT FIELDS
		RVS.DOC.on('mouseleave','.input_presets_wrap',function() {	jQuery(this).removeClass("infocus");});

		// INPUT PRESET HANDLER (SET PRESETS LISTED ON INPUT FIELD)
		RVS.DOC.on('click','.input_preset',function() {
			if (this.dataset.val==="###metapicker###") {
				RVS.DOC.trigger('addMetaToLayer',{eventparam:"#"+this.dataset.iid});
			} else {
				var inp = jQuery('#'+this.dataset.iid);
				inp.val(this.dataset.val);
				inp.trigger("change");
				inp.focus();
			}
		});

		RVS.DOC.on('click','.show_more_toggle',function() {
			jQuery(this).toggleClass("showlesson");
			jQuery(this.dataset.toggle).toggle();
		});



		// ICON SWITCHER TRIGGERING INPUT FIELD
		RVS.DOC.on('click','.icon_switcher',function() {
			if (this.className.indexOf('icsw_on')>=0)
				RVS.F.setInputTo({field:jQuery(this.dataset.ref), val:false});
			else
				RVS.F.setInputTo({field:jQuery(this.dataset.ref), val:true});
		});

		// TRIGGER EVENT ON CLICK
		RVS.DOC.on('click','.triggerEvent',function(e) {
			var ds = this.dataset;
			jQuery('body').trigger(ds.evt,{event:e,eventparam:ds.evtparam});
		});


		RVS.DOC.on('click','.vs-item',function() {
			jQuery(this.parentNode).find('.vs-item').removeClass("selected");
			this.className += " selected";
			showHideGroups({hide:this.dataset.hide, show:this.dataset.show, showprio:this.dataset.showprio});
		});

		// CLICK ON BUTTONS
		RVS.DOC.on('click','.screen_selector, .toolkit_selector, .eventcaller, .form_opener_btn, .collectortab, .opensettingstrigger, .extendval, .openmodaltrigger',function(e) {
			if (e.target.className.indexOf("tponoff")>=0 || this.className.indexOf("ssnotavailable")>=0) {
			} else {
				if (this.dataset.triggerinp!==undefined) jQuery(this.dataset.triggerinp).val(this.dataset.triggerinpval).trigger("change");
				if (this.dataset.forms!==undefined) RVS.F.openSettings({forms:jQuery(this).data('forms'), uncollapse:this.dataset.collapse});
				if (this.dataset.unselect!==undefined) jQuery(this.dataset.unselect).removeClass("selected");
				if (this.dataset.select!==undefined) jQuery(this.dataset.select).addClass("selected");

				if(this.dataset.extendval!==undefined) {
					var inp = jQuery(this.dataset.inp);
					inp.val(inp.val()+" "+this.dataset.extendval).trigger("change");
				}

				if (this.dataset.screenicon!==undefined) {
					jQuery('#screen_selector_ph_icon').html(this.dataset.screenicon);
					jQuery('#screen_selector_ph_icon_sr').html(this.dataset.screenicon);
				}

				if (this.dataset.evt!==undefined && (this.className.indexOf('callEvent')>=0 || this.className.indexOf('eventcaller')>=0))
					jQuery('body').trigger(this.dataset.evt,this.dataset.evtparam);

				if (this.dataset.modal!==undefined) RVS.F.RSDialog.create({modalid:this.dataset.modal});
			}

			//Stop All Animation
			RVS.DOC.trigger('previewStopLayerAnimation');

		});



		//CLIPBOARD FUNCTIONS
		//jQuery('.copyclipboard').each(function() {
		if (jQuery('.copyclipboard').length>0 && typeof RSClipboard!=="undefined") {
			var clipboard = new RSClipboard('.copyclipboard');
		    clipboard.on('success', function(e) {
		    	punchgs.TweenLite.fromTo(jQuery(e.trigger),0.4,{autoAlpha:0},{autoAlpha:1,ease:punchgs.Power3.easeInOut});
		    });
		    clipboard.on('error', function(e) {
		    	e = jQuery(e.trigger);
		    	e.addClass("errorcopy");
		    	setTimeout(function() {
		    		e.removeClass("errorcopy");
		    	},400);
		    });
		  }
		//})

		// DATEPICKER INITIALISATION
		if (jQuery('.inputDatePicker').length>0)
			jQuery('.inputDatePicker').datepicker({
				dateFormat : 'dd-mm-yy 00:00'
			});

		// REINITIALISE THE INPUT BOXES !
		RVS.F.reInitInputBoxes();

		//Value Changes throug Keyboard
		RVS.DOC.on('keydown',".valueduekeyboard",function(e,d) {
			var code = (e.keyCode ? e.keyCode : e.which),
				dist = e.currentTarget.dataset.steps!=undefined ? parseFloat(e.currentTarget.dataset.steps):1,
				min = e.currentTarget.dataset.min!=undefined ? e.currentTarget.dataset.min : -99999,
				max = e.currentTarget.dataset.max!=undefined ? e.currentTarget.dataset.max : 99999,
				cv = parseFloat(e.currentTarget.value) || 0;
			if (e.shiftKey) dist = dist*10;

			switch(code) {
				case 38:
					e.currentTarget.value = Math.min(cv+dist,max);
					if (e.currentTarget.value !== Math.round((e.currentTarget.value))) e.currentTarget.value = (Math.round(e.currentTarget.value*100))/100;
				break;
				case 40:
					e.currentTarget.value = Math.max(cv-dist,min);
					if (e.currentTarget.value !== Math.round((e.currentTarget.value))) e.currentTarget.value = (Math.round(e.currentTarget.value*100))/100;
				break;
			}

			if (code===38 || code===40) jQuery(e.currentTarget).trigger("change");
		});

		RVS.DOC.on('keyup','.losefocusonenter',function(e,d){
			if (e.keyCode=== 13) {
				jQuery(document.activeElement).blur();
			}
		});

		// HANDLE ENTER AND ESC BUTTONS ON INPUT FIELDS IN FOCUS TO BE ABLE TO REVERT TO OLD VALUES
		RVS.DOC.on('keyup','input, textarea',function(e,d) {
			if (e.keyCode===13)
				RVS.S.inFocusValue = this.value;
		});

		RVS.DOC.on('keyup keydown',function(e,d){
			if (e.keyCode=='9') jQuery('#builderView').scrollTop(0);
		});

		RVS.DOC.on('keydown',function(e,d){
			if (e.keyCode=='27') {
				if (RVS.S.inFocus!==undefined && RVS.S.inFocus!=="none"  &&  RVS.S.inFocus.value!==RVS.S.inFocusValue) {
					RVS.S.inFocus.value = RVS.S.inFocusValue;
					if (RVS.S.inFocus.dataset.evt!==undefined) {
						RVS.DOC.trigger(RVS.S.inFocus.dataset.evt,{val:RVS.S.inFocusValue,eventparam:RVS.S.inFocus.dataset.evtparam});
					}
				}
				if (RVS.S.inFocus!==undefined && RVS.S.inFocus!=="none") jQuery(RVS.S.inFocus).trigger("blur");
			}
			if (e.keyCode=='32' && (RVS.S.inFocus==="none" || RVS.S.inFocus===undefined)) {
				RVS.F.toggleTimeLine();
				return false;
			}

		});

		// RESET VALUE TO DEFAULT
		RVS.DOC.on('click','.resettodefault',function() {
			var btn = jQuery(this),
				ds = btn.data(),
				target = jQuery(ds.target);
				//update Object
				if (ds.r!==undefined)
					if (btn.hasClass("layerinput"))
						RVS.F.updateLayerObj({path:ds.r,val:ds.default,evt:ds.evt, evtparam:ds.evtparam});
					else
						RVS.F.updateSliderObj({path:ds.r,val:ds.default,evt:ds.evt, evtparam:ds.evtparam});
				else {
					//update input:
					if (target!==undefined) {
						target.val(ds.default);
						target.trigger("change");
					}

					if (ds.evt!==undefined) RVS.DOC.trigger(ds.evt,ds.evtparam);
				}

		});

		// Get Image from Media Library
		RVS.DOC.on('click','.getImageFromMediaLibrary',function() {
			var btn = jQuery(this),
				ds = btn.data(),
				target = jQuery(ds.target),
				multiple = this.dataset.multiple==="true" || this.dataset.multiple==true;

			RVS.F.openAddImageDialog(RVS_LANG.choose_image,function(urlImage, imageID){
				//update Object
				if (ds.r!==undefined)
					if (btn.hasClass("layerinput")) {
						//LAYER INPUT UPDATE
						RVS.F.openBackupGroup({id:"UpdateLayerImage",txt:"Update Layer Image",icon:"photo"});
						if (ds.r.indexOf(".media")>=0) RVS.F.updateLayerObj({path:"media.lastLibrary",val:"medialibrary"});
						RVS.F.updateLayerObj({path:ds.r,val:urlImage,evt:ds.evt, evtparam:ds.evtparam});

						if (ds.rid!==undefined) RVS.F.updateLayerObj({path:ds.rid,val:imageID});
						if (ds.rty!==undefined) RVS.F.updateLayerObj({path:ds.rty,val:"medialibrary"});
						RVS.F.closeBackupGroup({id:"UpdateLayerImage"});
					}
					else {
						// SLIDE INPUT UPDATE
						RVS.F.openBackupGroup({id:"UpdateSlideImage",txt:"Update Slide Image",icon:"photo"});
						RVS.F.updateSliderObj({path:ds.r,val:urlImage,evt:ds.evt, evtparam:ds.evtparam});
						if (ds.rid!==undefined) RVS.F.updateSliderObj({path:ds.rid,val:imageID});
						if (ds.rty!==undefined) RVS.F.updateSliderObj({path:ds.rty,val:"medialibrary"});
						RVS.F.closeBackupGroup({id:"UpdateSlideImage"});
					}
				else {
					//update input:
					if (target!==undefined) {
						target.val(urlImage);
						target.trigger("change");
					}

					ds.evtparam = ds.evtparam===undefined ? {} : ds.evtparam;
					ds.evtparam.urlImage = urlImage;
					if (ds.evt!==undefined) RVS.DOC.trigger(ds.evt,ds.evtparam);
				}
			},multiple);
		});

		RVS.DOC.on('click','.removePosterImage',function() {
			var btn = jQuery(this),
				ds = btn.data(),
				target = jQuery(ds.target),
				multiple = this.dataset.multiple==="true" || this.dataset.multiple==true;

			//update Object
			if (ds.r!==undefined)
				if (btn.hasClass("layerinput")) {
					//LAYER INPUT UPDATE
					RVS.F.openBackupGroup({id:"UpdateLayerImage",txt:"Update Layer Image",icon:"photo"});
					if (ds.r.indexOf(".media")>=0) RVS.F.updateLayerObj({path:"media.lastLibrary",val:"medialibrary"});
					RVS.F.updateLayerObj({path:ds.r,val:"",evt:ds.evt, evtparam:ds.evtparam});

					if (ds.rid!==undefined) RVS.F.updateLayerObj({path:ds.rid,val:""});
					if (ds.rty!==undefined) RVS.F.updateLayerObj({path:ds.rty,val:"medialibrary"});
					RVS.F.closeBackupGroup({id:"UpdateLayerImage"});
				}
				else {
					// SLIDE INPUT UPDATE
					RVS.F.openBackupGroup({id:"UpdateSlideImage",txt:"Update Slide Image",icon:"photo"});
					RVS.F.updateSliderObj({path:ds.r,val:"",evt:ds.evt, evtparam:ds.evtparam});
					if (ds.rid!==undefined) RVS.F.updateSliderObj({path:ds.rid,val:""});
					if (ds.rty!==undefined) RVS.F.updateSliderObj({path:ds.rty,val:"medialibrary"});
					RVS.F.closeBackupGroup({id:"UpdateSlideImage"});
				}
			else {
				//update input:
				if (target!==undefined) {
					target.val("");
					target.trigger("change");
				}

				ds.evtparam = ds.evtparam===undefined ? {} : ds.evtparam;
				ds.evtparam.urlImage = "";
				if (ds.evt!==undefined) RVS.DOC.trigger(ds.evt,ds.evtparam);
			}

		});

		RVS.DOC.on('click','.getVideoFromMediaLibrary',function() {
			var btn = jQuery(this),
				ds = btn.data(),
				target = jQuery(ds.target),
				islayer = this.className.indexOf("layerinput")>=0;
				RVS.F.openAddVideoDialog(RVS_LANG.choose_video,function(urlVideo, videoID){
					//update Object
					if (ds.r!==undefined) {

						RVS.F.openBackupGroup({id:"updateVideo",txt:"Update Video from Media Library",icon:"videocam"});
						if (ds.rid!==undefined) RVS.F.updateSliderObj({path:ds.rid,val:videoID});
						RVS.F.updateSliderObj({path:ds.r,val:urlVideo,evt:ds.evt, evtparam:ds.evtparam});
						RVS.F.closeBackupGroup({id:"updateVideo"});
					}
					else {
						//update input:
						if (target!==undefined) {
							target.val(urlVideo);
							RVS.F.openBackupGroup({id:"updateVideo",txt:"Update Video from Media Library",icon:"videocam"});
							target.trigger("change");
							if (ds.rid!==undefined && !islayer) RVS.F.updateSliderObj({path:ds.rid,val:videoID});
							if (RVS.selLayers.length>0 && islayer) {
								RVS.F.updateLayerObj({path:"media.mediaType",val:(ds.mediatype!==undefined ? ds.mediatype : "html5")});
								if (ds.rid!==undefined && islayer) RVS.F.updateLayerObj({path:ds.rid,val:videoID});
								RVS.F.updateEasyInputs({container:jQuery('.layer_settings_collector'), path:RVS.S.slideId+".layers.", trigger:"init", multiselection:true});
							}
							RVS.F.closeBackupGroup({id:"updateVideo"});
						}

						if (ds.evt!==undefined) RVS.DOC.trigger(ds.evt,ds.evtparam);

					}
				});
		});

		// Get Image from Media Library
		RVS.DOC.on('click','.getVideoFromObjectLibrary',function() {
			var btn = jQuery(this),
				ds = btn.data();
			ds.targetType = btn.hasClass("layerinput") ? "layer" : "slide";
			RVS.F.openObjectLibrary({types:["videos"],filter:"all", selected:["videos"], data:ds, success:{video:"updateVideoSrcFromLibrary"}});
		});


		RVS.DOC.on('updateVideoSrcFromLibrary',function(e,ds){

			if (ds.r!==undefined)
				if (ds.targetType==="layer") {
					RVS.F.openBackupGroup({id:"videofromobjlibrary",txt:"Video from OBJ Library",icon:"videocam",lastkey:"mp4Url"});
					RVS.F.updateLayerObj({path:"media.lastLibrary",val:"objectlibrary"});
					RVS.F.updateLayerObj({path:"media.mediaType",val:"html5"});
					RVS.F.updateLayerObj({path:"media.posterUrl",val:ds.img});
					RVS.F.updateLayerObj({path:"media.mp4Url",val:ds.video,evt:ds.evt, evtparam:ds.evtparam});
					RVS.F.closeBackupGroup({id:"videofromobjlibrary"});
					RVS.F.updateEasyInputs({container:jQuery('.layer_settings_collector'), path:RVS.S.slideId+".layers.", trigger:"init", multiselection:true});

				}
				else {
					RVS.F.openBackupGroup({id:"videofromobjlibrary",txt:"Video from OBJ Library",icon:"videocam",lastkey:"mp4Url"});
					RVS.F.updateSliderObj({path:RVS.S.slideId+".slide.bg.image",val:ds.img});
					RVS.F.updateSliderObj({path:RVS.S.slideId+".slide.bg.imageId",val:undefined});
					RVS.F.updateSliderObj({path:RVS.S.slideId+".slide.bg.mpeg",val:ds.video,evt:ds.evt, evtparam:ds.evtparam});
					RVS.F.closeBackupGroup({id:"Vvideofromobjlibrary"});
					RVS.F.updateEasyInputs({container:jQuery('.slide_settings_collector'), path:RVS.S.slideId+".slide.", trigger:"init"});
				}
			else {
				//update input:
				if (ds.target!==undefined) {
					ds.target.val(ds.img);
					ds.target.trigger("change");
				}

				if (ds.evt!==undefined) RVS.DOC.trigger(ds.evt,ds.evtparam);
			}
		});


		// Get Image from Media Library
		RVS.DOC.on('click','.getImageFromObjectLibrary',function() {
			var btn = jQuery(this),
				ds = btn.data();
			ds.targetType = btn.hasClass("layerinput") ? "layer" : "slide";
			RVS.F.openObjectLibrary({types:["images","objects"],filter:"all", selected:["images"], data:ds, success:{image:"updateImageSrcFromLibrary"}});
		});


		RVS.DOC.on('click','.getImageFromStream',function() {
			var ds = this.dataset;
			ds.targetType = this.className.indexOf("layerinput") >=0 ? "layer" : "slide";
			if (ds.r!==undefined)
				if (ds.targetType==="layer") {
					RVS.F.openBackupGroup({id:"UpdateLayerImage",txt:"Update Layer Image",icon:"photo"});
					RVS.F.updateLayerObj({path:"media.lastLibrary",val:"stream"});
					RVS.F.updateLayerObj({path:ds.rid,val:"stream"});
					RVS.F.updateLayerObj({path:ds.r,val:RVS.ENV.img_ph_url,evt:ds.evt, evtparam:ds.evtparam});
					RVS.F.closeBackupGroup({id:"UpdateLayerImage"});

				}
				else {
					RVS.F.openBackupGroup({id:"UpdateSlideImage",txt:"Update Slide Image",icon:"photo"});
					RVS.F.updateSliderObj({path:ds.rid,val:"stream"});
					RVS.F.updateSliderObj({path:ds.r,val:RVS.ENV.img_ph_url,evt:ds.evt, evtparam:ds.evtparam});
					RVS.F.closeBackupGroup({id:"UpdateSlideImage"});
				}
			else {
				//update input:
				if (ds.target!==undefined) {
					ds.target.val("stream");
					ds.target.trigger("change");
				}

				if (ds.evt!==undefined) RVS.DOC.trigger(ds.evt,ds.evtparam);
			}
		});

		RVS.DOC.on('updateImageSrcFromLibrary',function(e,ds){
			if (ds.r!==undefined)
				if (ds.targetType==="layer") {

					RVS.F.openBackupGroup({id:"UpdateLayerImage",txt:"Update Layer Image",icon:"photo"});
					if (ds.rty!==undefined) RVS.F.updateLayerObj({path:ds.rty,val:"objectlibrary"});
					RVS.F.updateLayerObj({path:"media.lastLibrary",val:"objectlibrary"});
					if (ds.rid!==undefined) RVS.F.updateLayerObj({path:ds.rid,val:"objectlibrary"});
					RVS.F.updateLayerObj({path:ds.r,val:ds.img,evt:ds.evt, evtparam:ds.evtparam});
					RVS.F.closeBackupGroup({id:"UpdateLayerImage"});

				}
				else {
					RVS.F.openBackupGroup({id:"UpdateSlideImage",txt:"Update Slide Image",icon:"photo"});
					if (ds.rty!==undefined) RVS.F.updateSliderObj({path:ds.rty,val:"objectlibrary"});
					if (ds.rid!==undefined) RVS.F.updateSliderObj({path:ds.rid,val:"objectlibrary"});
					RVS.F.updateSliderObj({path:ds.r,val:ds.img,evt:ds.evt, evtparam:ds.evtparam});
					RVS.F.closeBackupGroup({id:"UpdateSlideImage"});
				}
			else {
				//update input:
				if (ds.target!==undefined) {
					ds.target.val(ds.img);
					ds.target.trigger("change");
				}

				if (ds.evt!==undefined) RVS.DOC.trigger(ds.evt,ds.evtparam);
			}
		});



		// Get YouTube Thumb
		RVS.DOC.on('click','.getImageFromYouTube',function() {
			var btn = jQuery(this),
				ds = btn.data(),
				target = jQuery(ds.target),
				youtubeid = RVS.F.getDeepVal({path:ds.f}),
				urlImage ="https://img.youtube.com/vi/"+youtubeid+"/sddefault.jpg";

			if (ds.r!==undefined) {
				RVS.F.openBackupGroup({id:"UpdateLayerImage",txt:"Update Layer Image",icon:"photo"});
				RVS.F.updateSliderObj({path:ds.r,val:urlImage,evt:ds.evt, evtparam:ds.evtparam});
				if (ds.rid!==undefined) RVS.F.updateSliderObj({path:ds.rid,val:"objectlibrary"});
				RVS.F.closeBackupGroup({id:"UpdateLayerImage"});
			} else {
				//update input:
				if (target!==undefined) {
					target.val(urlImage);
					target.trigger("change");
				}

				if (ds.evt!==undefined) RVS.DOC.trigger(ds.evt,ds.evtparam);
			}
		});


		// INITIALISE THE FORM 1 AND FORM 2 MENUS
		var bodies = jQuery('body').on('click','.form_menu_level_1_li, .form_menu_level_2_li',function() {

			var _ = jQuery(this),
				fmi = _.closest('.form_menu_inside');
			_.siblings('li').removeClass("selected");
			_.addClass("selected");
			if (_.hasClass("form_menu_level_1_li")) {
				//fmi.find('.form_inner').hide().removeClass("open");
			} else
				fmi.find('.form_level_2_inner').hide().removeClass("open");

			var openform = jQuery(_.data('target'));
			openform.show().addClass("open");
			RVS.F.updateFormPositions({jf:_.closest('.formcontainer'), uncollapse:true});

			if (openform.data('evt')!==undefined)
				RVS.DOC.trigger(openform.data('evt'),openform.data('evtparam'));
		});

		function uncollapseParent(_) {
			if (_.data('trigger')!==undefined) jQuery(_.data('trigger')).click();
			_.closest('.formcontainer').removeClass("collapsed");
			RVS.DOC.trigger('scrollUpdates');
			RVS.DOC.trigger('accordionaction');
		}

		function collapseParent(_) {
			if (_.closest('.form_inner').length>0)
				_.closest('.form_inner').removeClass("open");
			else
				_.closest('.formcontainer').addClass("collapsed");


			RVS.DOC.trigger('scrollUpdates');
			RVS.DOC.trigger('accordionaction');
		}

		// LISTEN TO ACCORDION FUNCTIONS
		bodies.on('click','.form_intoaccordion',function() {
			var _ = jQuery(this),
				fi = _.closest('.form_inner'),
				fc = _.closest('.formcontainer');
			if ((fi.length>0 && fi.hasClass("open")) || (fi.length===0 && !fc.hasClass("collapsed"))) {
				collapseParent(_);return false;
			} else {
				uncollapseParent(_);
			}
		});

		bodies.on('mouseenter','.form_intoaccordion',function() {
			this.parentNode.dataset.hovered = "on";
		});
		bodies.on('mouseleave','.form_intoaccordion',function() {
			this.parentNode.dataset.hovered = "off";
		});

		// SELECT BOX TRIGGERED BY EXTERNAL ELEMENTS
		bodies.on('click','.triggerselect', function() {
			var btn = jQuery(this),
				d = btn.data(),
				sel = jQuery(d.select);

			if (sel!==undefined && sel.length>0)
				sel.val(d.val);
			sel.trigger("change");
		});

		// SELECT BOX TRIGGERED BY EXTERNAL ELEMENTS
		bodies.on('click','.navaligntrigger', function() {

			var btn = jQuery(this),
				d = btn.data(),
				selects = d.select.split(","),
				vals = d.val.split(",");

			RVS.F.openBackupGroup({id:"NavigationAlign",txt: d.type+" Align",icon:"navigation",lastkey:"navigation"});
			RVS.F.ignoreEventsOpen();
			for (var i in selects) {
				if(!selects.hasOwnProperty(i)) continue;
				var sel = jQuery(selects[i]);
				if (sel!==undefined && sel.length>0) {
					if (vals.length>0)
						sel.val(vals[i]);
					else
						sel.val(vals[0]);
					sel.trigger("change");
				}
			}
			RVS.F.ignoreEventsClose();
			RVS.F.closeBackupGroup({id:"NavigationAlign"});
			RVS.DOC.trigger('sliderNavPositionUpdate',d.type);
		});

		// BRING FORM CONTAINER TO THE TOP
		/*jQuery('body').on('click','.formcontainer',function(){
			var jf = jQuery(this);
			RVS.F.updateFormPositions({jf:jf,uncollapse:true});
			punchgs.TweenLite.set(jQuery('.form_collector'),{zIndex:1000});
			punchgs.TweenLite.set(jQuery(this).closest('.form_collector'),{zIndex:1005});
		});*/

		//LISTEN ON CLICK OF META SUBMENU
		RVS.DOC.on('click','.mdl_group_wrap_menuitem',function() {
			jQuery('.mdl_group_wrap_menuitem.selected, .mdl_group_wrap.selected').removeClass("selected");
			jQuery(this).addClass("selected");
			jQuery("#"+this.dataset.show).addClass("selected");
			jQuery('#meta_rbm_content').scrollTop(0).RSScroll("update");
		});



		RVS.F.updateMetaTranslate();

		RVS.DOC.trigger('extendmetas');

		// LISTEN TO SMART SUB NAviGATION ELEMENTS
		RVS.DOC.on('click','.ssmbtn',function() {
			var inside = jQuery(this.dataset.inside);
			inside.find('.ssmbtn.selected, .ssm_content.selected').removeClass("selected");
			this.className +=" selected";
			jQuery(this.dataset.showssm).addClass("selected");
			if (this.dataset.evt!==undefined) RVS.DOC.trigger(this.dataset.evt, this.dataset.evtparam);
		});

		RVS.DOC.on('click','.input_presets_dropdown',function() {
			jQuery('.input_presets_wrap.infocus').removeClass("infocus");
			this.parentElement.className +=" infocus";
		});

		RVS.DOC.on('blur','input',function() {
			if (RVS.S.respInfoBar.toolbar) {
				RVS.S.respInfoBar.visible = false;
				RVS.S.respInfoBar.toolbar[0].style.display = "none";
			}
		});



	};

/*******************************************************************************
	-	UPDATE META TRANSLATE TABLE TO REPLACE META WITH FAKE CONTENT 	-
********************************************************************************/
	// UPDATE POST CATEGORIE LISTS BASED ON THE CURRENT SELECTED POST TYPES
	RVS.F.updateMetaTranslate = function(obj) {
		RVS.LIB.META = {};
		jQuery('.mdl_group_member').each(function() {
			var v = this.dataset.val.split(":");
			v = v.length>1 ? v[0] + ".*?}}" : v[0];
			RVS.LIB.META[v] = jQuery(this).find('.mdl_placeholder_content').text();
		});
	};

/*****************************************************
	-	POST CATEGORIES UPDATE BASED ON POST TYPE 	-
*****************************************************/
	// UPDATE POST CATEGORIE LISTS BASED ON THE CURRENT SELECTED POST TYPES
	RVS.F.updatePostCategories = function(obj) {
		if (!jQuery.isArray(obj.postTypes))
			obj.postTypes = obj.postTypes.split(",");
		if (!jQuery.isArray(obj.postTypes)) {
			var nA = [];
			nA.push(obj.postTypes);
			obj.postTypes = nA;

		}
		obj.categories.html("");
		jQuery(obj.postTypes).each(function(index,postType){
			var objCats = RVS.LIB.POST_TYPES_CAT[postType];
			//var flagFirst = true;
			for(var catIndex in objCats){
				if(!objCats.hasOwnProperty(catIndex)) continue;
				var catTitle = objCats[catIndex];
				//add option to cats select
				var opt = new Option(catTitle, catIndex);
				if(catIndex.indexOf("option_disabled") == 0){
					jQuery(opt).prop("disabled","disabled");
				}
				if (obj.categories.find('option[value="'+catIndex+'"]').length==0) obj.categories.append(opt);
			}
		});
		RVS.F.setS2Option({select:obj.categories,update:true});
	};


/********************************************************************************
	-	SHOW / HIDE CONTAINERS DEPENDING ON INPUT / RADIO / SELECT CHANGES 	-
*********************************************************************************/

	// Show Field Responsive Values
	RVS.F.showFieldResponsiveValues = function(_) {
		RVS.S.respInfoBar.field = _ !==undefined ? jQuery(_) : RVS.S.respInfoBar.field;

		if (RVS.eMode.top!=="slider")
			if (RVS.selLayers.length!==1 || RVS.S.respInfoBar.field===undefined || !RVS.S.respInfoBar.visible) return;

		if (!RVS.S.respInfoBar.toolbar) {
			RVS.S.respInfoBar.toolbar = jQuery('<div id="responsive_infos_toolbar"></div>');
			jQuery('#the_right_toolbar').append(RVS.S.respInfoBar.toolbar);
		}



		var _sizes = 0, _h="";
		_h += '<span class="int_inher_title">'+RVS_LANG.intinheriting+'</span>';
		for (var i in RVS.V.sizes) {
			if(!RVS.V.sizes.hasOwnProperty(i)) continue;
			if (RVS.V.sizes[i]!==RVS.screen && RVS.SLIDER.settings.size.custom[RVS.V.sizes[i]]) {
				_h += '<div style="white-space:nowrap">';

				_h += '<i class="material-icons">';
				_h += RVS.V.sizes[i] == "d" ? 'desktop_mac' : RVS.V.sizes[i] == "n" ? 'laptop' : RVS.V.sizes[i] == "t" ? 'tablet_android' : 'phone_iphone';
				_h += '</i>';
				_h += '<span class="responsive_info_value">'+RVS.F.getDeepVal({path:RVS.eMode.top==="slider" ? "settings."+RVS.S.respInfoBar.field[0].dataset.r : RVS.S.slideId+".layers."+RVS.selLayers[0]+"."+RVS.S.respInfoBar.field[0].dataset.r,screen:RVS.V.sizes[i]})+'</span>';
				_h += '</div>';
				_sizes++;
			}
		}

		if (_sizes>0) {
			RVS.S.respInfoBar.toolbar[0].style.display = "block";
			RVS.S.respInfoBar.toolbar[0].innerHTML = _h;
			var o = RVS.S.respInfoBar.field.offset(),
				w = RVS.S.respInfoBar.toolbar.width(),
				l = -85;
			punchgs.TweenLite.set(RVS.S.respInfoBar.toolbar,{top:(o.top ), left:l});
		} else {
			RVS.S.respInfoBar.visible = false;
			RVS.S.respInfoBar.toolbar[0].style.display = "none";
		}
	};

	RVS.F.initCopyClipboard = function(selector) {
		if (jQuery(selector).length>0 && jQuery.inArray(selector,clipboardselectors)==-1) {
			clipboardselectors = clipboardselectors===undefined ? [] : clipboardselectors;
			clipboardselectors.push(selector);
			var clipboard = new RSClipboard(selector);
		    clipboard.on('success', function(e) {
		    	jQuery(':focus').blur();
		    	RVS.F.showInfo({content:"Copied To Clipboard", type:"success", showdelay:0, hidedelay:1, hideon:"", event:"" });
		    	punchgs.TweenLite.fromTo(jQuery(e.trigger),0.4,{autoAlpha:0},{autoAlpha:1,ease:punchgs.Power3.easeInOut});

		    });
		    clipboard.on('error', function(e) {
		    	e = jQuery(e.trigger);
		    	e.addClass("errorcopy");
		    	setTimeout(function() {
		    		e.removeClass("errorcopy");
		    	},400);
		    });
		  }
	};

	RVS.F.triggerShowHideDep = function(obj,rv) {
		if (obj.dataset.show!==undefined && obj.dataset.hide!==undefined && (obj.type!=="radio" || obj.checked))
			showHideGroups({hide:obj.dataset.hide, show:obj.dataset.show, val:rv, showprio:obj.dataset.showprio});
		else
			showHideDep({target:obj.dataset.showhide, nortarget:obj.dataset.hideshow, value: (rv===undefined ? obj.value : rv), depend:obj.dataset.showhidedep });
	};

	RVS.F.triggerInput = function(obj) {
		if (obj.inp===undefined) return;
		var inps = obj.inp.split(",");
		obj.when = obj.when==="true" ? true : obj.when==="false" ? false : obj.when;
		obj.whennot = obj.whennot==="true" ? true : obj.whennot==="false" ? false : obj.whennot;

		for (var inp in inps) {
			if(!inps.hasOwnProperty(inp)) continue;
			if (obj.val!==undefined) {
				obj.val = obj.val.replace("*val*",obj.dep);
				if (obj.when!==undefined && obj.dep==obj.when) {
					jQuery(inps[inp].replace("*val*",obj.dep)).val(obj.val).trigger("change");
				}
				else
				if (obj.whennot!==undefined && obj.dep!=obj.whennot)
					jQuery(inps[inp].replace("*val*",obj.dep)).val(obj.val).trigger("change");
				else
				if (obj.when===undefined && obj.whennot===undefined)
					jQuery(inps[inp].replace("*val*",obj.dep)).val(obj.val).trigger("change");
			}
			else
				jQuery(inps[inp].replace("*val*",obj.dep)).trigger("change");
		}
	};

	RVS.F.switchButtonInit = function(obj) {
		obj = obj===undefined ? {} : obj;
		obj.container = obj.container===undefined ? '#builderView' : obj.container;
		obj.init = obj.init===undefined ? true : obj.init;

		if (obj.init) {
			//SWITCH BUTTONS AND EVENT CALLING FROM THEM
			jQuery(obj.container).find('.switch_button').each(function() {
				var btn = jQuery(this),
					ds = this.dataset;
				if (!btn.hasClass("activeswitch")) {
					btn.addClass("activeswitch");
					RVS.F.changeSwitchState({el:this,state:ds.start_state});
				}

				btn.on('click',function() {
					RVS.F.changeSwitchState({el:this,callEvent:true});
				});
			});

		}
	};

	RVS.F.changeSwitchState = function(obj) {
		if (obj.el===null) return;
		var ds=obj.el.dataset,
			btn = jQuery(obj.el),
			states = ds.states.split(",");

		ds.state = obj.state!==undefined ? obj.state : ds.state===states[0] ? states[1] : states[0];
		btn.find('.switch_button_state').html(ds[ds.state+'_state']);
		btn.find('.switch_button_icon').html(ds[ds.state+'_icon']);

		if (obj.callEvent!==undefined) {
			RVS.DOC.trigger(ds[ds.state]);
		}

	};



	function showHideDep(obj) {
		var _ = jQuery(obj.target),
			_nnor = jQuery(obj.nortarget),
			dep = obj.depend==="true" ? true : obj.depend==="false" ? false : obj.depend;


		if (typeof dep === "string" && dep.indexOf("!!")>=0) {
			dep = dep.replace("!!","");
			if (obj.value!==dep) {
				_.show().removeClass("showhide_hidden");
				_nnor.hide().addClass("showhide_hidden");
			} else {
				_.hide().addClass("showhide_hidden");
				_nnor.show().removeClass("showhide_hidden");
			}
		} else {
			if (obj.value===dep) {
				_.show().removeClass("showhide_hidden");
				_nnor.hide().addClass("showhide_hidden");
			} else {
				_.hide().addClass("showhide_hidden");
				_nnor.show().removeClass("showhide_hidden");
			}
		}
	}

	function showHideGroups(obj) {
		if (obj.val!==undefined) {
			obj.show = obj.show.replace(/\*val\*/g,obj.val).replace(/ /g,"");
			obj.hide = obj.hide.replace(/\*val\*/g,obj.val).replace(/ /g,"");
		}
		if (obj.showprio==="hide") {
			jQuery(obj.show).show();
			jQuery(obj.hide).hide();
		} else {
			jQuery(obj.hide).hide();
			jQuery(obj.show).show();
		}
	}



	/***************************************************************************************
		CREATE A PRESET LIST CONTAINER, AND RETURN THE HTML MARKUP, CREATE LISTENERS ETC.
	****************************************************************************************/

	RVS.F.createPresets = function(_) {
		_.groupid = _.groupid===undefined ? "preset_list_"+Math.round(Math.random()*100000) : _.groupid;

		var h = '<div id="'+_.groupid+'" class="presets_liste">',
			prefix = _.prefix===undefined ? "" : _.prefix+"_";

		h += '	<div class="presets_liste_head"><span class="presets_liste_title">'+_.title+'</span><i class="right-divided-icon material-icons">arrow_drop_down</i></div>';
		h += '	<div class="presets_liste_inner">';

		for (var i in _.groups) {
			if(!_.groups.hasOwnProperty(i)) continue;
			h += '<div class="presetssgroup">';
			h += '	<div class="presetssgroup_head"><span class="presetssgroup_name">'+_.groups[i].title+'</span><div class="animation_drop_arrow"><i class="material-icons">arrow_drop_down</i></div></div>';
			h += '	<div class="presets_listelements">';
			if (i==="custom")
				h += '<div data-evt="'+_.customevt+'" data-key="custom" class="'+prefix+'presetelement presets_listelement dark_btn"><span class="cla_custom_name">Save Current Template</span><input type="text" value="custom" class="cla_entername"><div class="custom_layer_animation_toolbar"><i data-evt="'+_.customevt+'" class="cla_answer_yes material-icons">done</i><i data-evt="'+_.customevt+'" class="cla_answer_no material-icons">close</i><i class="add_custom_layeranimation material-icons">add</i></div></div>';

			for (var j in _.groups[i].elements) {
				if(!_.groups[i].elements.hasOwnProperty(j)) continue;
				h += i==="custom" ?
					'<div data-key="'+prefix+j+'" data-custom="true" data-evt="'+_.customevt+'" class="presets_listelement dark_btn"><span class="cla_custom_name">'+_.groups[i].elements[j].title+'</span><div class="cla_message">'+RVS_LANG.overwritetemplate+'</div><input data-evt="'+_.customevt+'" type="text" value="'+_.groups[i].elements[j].title+'" class="cla_entername"><div class="custom_layer_animation_toolbar"><i data-evt="'+_.customevt+'" class="cla_answer_yes material-icons">done</i><i data-evt="'+_.customevt+'" class="cla_answer_no material-icons">close</i><i data-evt="'+_.customevt+'" class="edit_custom_layeranimation material-icons">edit</i><i data-evt="'+_.customevt+'" class="save_custom_layeranimation material-icons">save</i><i data-evt="'+_.customevt+'" class="delete_custom_layeranimation material-icons">delete</i></div></div>' :
					'<div data-key="'+prefix+j+'" class="'+prefix+'presetelement presets_listelement dark_btn">'+_.groups[i].elements[j].title+'</div>';
			}

			h += '	</div>';
			h += '</div>';
		 }

		 h += '	</div>';
		 h += '</div>';

		 // CLICK ON AN ELEMENT, CUSTOM SAVE OR TRIGGER CALLBACK
		 RVS.DOC.on('click', '#'+_.groupid+' .presets_listelement', function() {
			if (this.dataset.key==="custom") {
				var clse = jQuery(this);
				this.dataset.mode="create";
				clse.addClass("cla_showentername");
				clse.find('input').focus().select();
				RVS.S.waitOnFeedback = { allowed:["cla_entername", "cla_answer_yes","cla_answer_no"], closeEvent:"hideCustomLayerNameEntering"};
				RVS.F.addBodyClickListener();
				return false;
			} else
			if (this.className.indexOf('cla_showentername')>=0) {

			} else
			_.onclick(this.dataset.key,this.dataset.custom);
		 });

		 // LISTEON ON OPEN/ CLOSE MENUS
		 if (!globalpresetsinit) {
			RVS.DOC.on('click','.presets_liste_head',function() {
				jQuery(this.parentElement).toggleClass("open");
			});

			RVS.DOC.on('click','.presetssgroup_head',function() {
				var isopen = this.parentElement.className.indexOf('open')>=0;
				jQuery(this).closest('.presets_liste').find('.presetssgroup.open').removeClass("open");
				if (!isopen)
					this.parentElement.className +=' open';
			});
			globalpresetsinit = true;
		 }

		 return h;

	};






	/*************************************************
		CREATE META DATA LIST
	**************************************************/
	RVS.F.createMetaGroups = function(obj) {

		var groupadded = jQuery('#'+obj.id).length!==0,
			group = !groupadded ? jQuery('<div id="'+obj.id+'" class="mdl_group"></div>') : jQuery('#'+obj.id);

		obj.title = RVS_LANG[obj.id]===undefined ? obj.id : RVS_LANG[obj.id];

		//ADD GROUP HEADER, IF NO GROUP ADDED YET
		if (!groupadded) group.append('<div class="mdl_group_header"><i class="material-icons">'+obj.icon+'</i>'+obj.title+'<i class="material-icons accordiondrop">arrow_drop_down</i></div>');

		//ADD ACTIONS TO THE GROUP
		for (var i in obj.actions) {
			if(!obj.actions.hasOwnProperty(i)) continue;
			var a = obj.actions[i],
				title = a.title!==undefined ? a.title : RVS_LANG['metadata_'+a.val]===undefined ? a.val : RVS_LANG['metadata_'+a.val];
			a.inputs = a.inputs===undefined ? "" : a.inputs;
			a.inputs = 	(a.layerTarget===true ? "#la_settings_layertarget"+ (a.inputs.length>0 ? ", "+a.inputs:"") : a.inputs);
			group.append('<div data-val="'+a.val+'" id="metadata_picker_'+a.val+'" data-inputs="'+a.inputs+'" class="mdl_group_member" data-val="'+a.val+'"><i class="material-icons">'+obj.icon+'</i>'+title+'</div>');
			if (a.layerTarget) RVS.LIB.ACTION_WITH_TRGT.push(a.val);
			RVS.LIB.ACTIONTYPES[a.val] = {inputs:a.inputs, name:title, icon:obj.icon, layerTarget:a.layerTarget, media:a.media};
		}

		//ADD GROUP TO THE ACTION CONTAINER IF NOT YET ADDED
		if (!groupadded) jQuery('#layeraction_list').append(group);
	};


/*************************************************
	SELECT / UNSELECT REFERENCED CONTAINERS
**************************************************/

	RVS.F.setUnsetSelected = function(obj) {

		if (obj.unselect!==undefined) jQuery(obj.unselect).removeClass("selected");
		if (obj.select!==undefined) {
			obj.val = obj.val!==undefined ? obj.val.replace(/\s/g,"-") : obj.val;
			//If Parent Content based Selection should happen !
			if (obj.prval!=undefined && RVS.selLayers.length>0 && RVS.L[RVS.selLayers[0]]!==undefined &&  (obj.prvalif===undefined || RVS.L[RVS.selLayers[0]].type===obj.prvalif))
					obj.val = RVS.F.getDeepVal({path:obj.prval.replace('#parentlayer#',RVS.L[RVS.selLayers[0]].group.puid)}) || "";


			var selector = obj.select.replace('*val*',obj.val),
				rval = obj.rval!==undefined ? RVS.F.getDeepVal({path:obj.rval}) : "";

			selector = selector.replace('*RVAL*',rval);
			jQuery(selector).addClass("selected");
		}
	};

/*************************************************
	SELECT / UNSELECT REFERENCED CONTAINERS
	RVS.F.setUnsetClass({container:ds.setclasson, class:ds.class, val:v, rval:ds.rval});
**************************************************/

	RVS.F.setUnsetClass = function(obj) {
		if (obj.class!==undefined && obj.container!==undefined) {
			if ((obj.rval!==undefined && obj.rval===obj.val) || obj.val) {
				jQuery(obj.container).addClass(obj.class);
				if (obj.inversclass) jQuery(obj.container).removeClass(obj.inversclass);
			}
			else {
				jQuery(obj.container).removeClass(obj.class);
				if (obj.inversclass) jQuery(obj.container).addClass(obj.inversclass);
			}
		}
	};

/*************************************************
	DISABLE / ENABLE REFERENCED CONTAINERS
**************************************************/

	RVS.F.setEnableDisable = function(obj) {
		if (obj.enable!==undefined) jQuery(obj.enable.replace('*val*',obj.val)).removeClass("disablecontainer");
		if (obj.disable!==undefined) jQuery(obj.disable.replace('*val*',obj.val)).addClass("disablecontainer");
	};

/********************************************************
	MAKE AVAILABLE / UNAVAILABLE REFERENCED CONTAINERS
*********************************************************/
	RVS.F.setUnAvailable = function(obj) {
		if (obj.available!==undefined) jQuery(obj.available.replace('*val*',obj.val)).removeClass("unavailablecontainer");
		if (obj.unavailable!==undefined) jQuery(obj.unavailable.replace('*val*',obj.val)).addClass("unavailablecontainer");
	};


	/***********************************************************
		-	PREDEFINED DROP DOWNS FOR SPECIAL FIELDS	-
	***********************************************************/
	 RVS.F.initPreDrops = function(el) {
		if (el===undefined) {
			jQuery('.predrop_wrap').each(function() {
				var pd = jQuery(this);
				if (!pd.hasClass("inited")) {

					pd.append('<div class="predrop"><ul class="predrop_ul"></ul></div>');

					var _d = pd.data(),
						pul = pd.find('.predrop_ul');
					/*
					if (_d.unitselector_r!==undefined) {
						var li = jQuery('<li class="predrop_li predrop_unitselector"></li>')
							_units = _d.units.split(",");
						for (var ui in _units) {
							li.append('<div class="radiooption '+pd.attr('id')+'_rops" id="'+pd.attr('id')+'_'+ui+'"><input data-select="#'+pd.attr('id')+'_'+ui+'" data-unselect=".'+pd.attr('id')+'_rops" data-r="'+_d.unitselector_r+'" type="radio" class="'+_d.class+'" value="'+_units[ui]+'" data-show="" data-hide="">'+_units[ui]+'</div>')
							pul.append(li);
						}
					}*/
				}
			});

		}
	 };
	/***/




	/**********************************
		- 	FULLPAGE INFO 	-
	***********************************/
	RVS.F.fullPageInfo = {
		init:function(_) {
				var fullpage = jQuery('<div id="fullpageinfo"><div id="fullpageinfo_zone">'+_.content+'<div id="fullpage_close"><i class="material-icons">close</i></div></div></div>');
				jQuery('#wpwrap').addClass("blurred");
				jQuery('body').append(fullpage);
				punchgs.TweenLite.fromTo(fullpage,0.4,{autoAlpha:0,scale:0.9},{autoAlpha:1,scale:1,ease:punchgs.Power3.EaseInOut});
			},
		close:function() {
				jQuery('#fullpageinfo').remove();
				jQuery('#wpwrap').removeClass("blurred");
		}
	};
	/**********************************
		- 	BROWSER DROPPABLE 	-
	***********************************/
	RVS.F.browserDroppable = {
		init :function(_) {
				_.textblock = '<div id="filedrop">';
				_.textblock += '	<form id="filedrop_zone">';
				_.textblock += '		<div class="filedrop_state_idle">';
				_.textblock += '			<input class="uploadfileinput" type="file" name="files[]" id="file" data-multiple-caption="{count} files selected" multiple />';
				_.textblock += '			<i class="big_filedrop_icon material-icons">file_download</i>';
				_.textblock += '			<div class="filedrop_line_1">'+RVS_LANG.dragAndDropFile+'</div>';
				_.textblock += '			<div class="filedrop_line_2">'+RVS_LANG.or+'</div>';
				_.textblock += '			<label for="file"  class="filedrop_clickbtn">'+RVS_LANG.clickToChoose+'</label>';
				_.textblock += '		</div>';
				_.textblock += '		<div class="filedrop_state_drop">';
				_.textblock += '			<i class="big_filedrop_icon material-icons">file_download</i>';
				_.textblock += '			<div class="filedrop_line_1">'+RVS_LANG.releaseToUpload+'</div>';
				_.textblock += '			<div class="filedrop_line_2">'+RVS_LANG.moduleZipFile+'</div>';
				_.textblock += '		</div>';
				_.textblock += '		<div class="filedrop_state_process">';
				_.textblock += '			<i id="file_upload_processicon" class="rotating big_filedrop_icon material-icons">autorenew</i>';
				_.textblock += '			<div id="file_upload_mininfo" class="filedrop_line_1">'+RVS_LANG.importing+'</div>';
				_.textblock += ' 			<div id="importing_processing_files">';
				_.textblock += '			</div>';
				_.textblock += '		</div>';
				_.textblock += '		<div id="filedrop_close"><i class="material-icons">close</i></div>';
				_.textblock += '	</form>';
				_.textblock += '</div>';

				var filedrop = jQuery(_.textblock),
					form = filedrop.find('#filedrop_zone');

				punchgs.TweenLite.fromTo(filedrop,0.3,{autoAlpha:0,scale:0.9},{autoAlpha:1,scale:1,ease:punchgs.Power3.EaseInOut});
				jQuery('#wpwrap').addClass("blurred");
				jQuery('body').append(filedrop);


				form.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
					e.preventDefault();
					e.stopPropagation();
				 })
				 .on('dragover dragenter', function(e) {
					form[0].className="is-dragover";
				 })
				 .on('dragleave dragend drop', function() {
					form[0].className="";
				 })
				 .on('drop', function(e) {
					form[0].className="is-processing";
					jQuery('#importing_processing_files').html('');
					for (var i in e.originalEvent.dataTransfer.files) {
						if(!e.originalEvent.dataTransfer.files.hasOwnProperty(i)) continue;
						if (jQuery.type(e.originalEvent.dataTransfer.files[i])=="object") {
							var txt = e.originalEvent.dataTransfer.files[i].name+" ("+Math.round(e.originalEvent.dataTransfer.files[i].size/1024)+"kb)";
							jQuery('#importing_processing_files').append('<div id="fileprocessing_'+i+'" class="filedrop_line_2">'+txt+'<i class="material-icons fileupload_status"></i><span class="fileupload_message"></span></div>');
						}
					}
					RVS.F.uploadFiles({form:form,files:e.originalEvent.dataTransfer.files,fileindex:0,report:'#fileprocessing_',success:_.success});
				 });

				 jQuery('#file').on('change', function(e) {
					form[0].className="is-processing";
					for (var i in e.target.files) {
						if(!e.target.files.hasOwnProperty(i)) continue;
						if (jQuery.type(e.target.files[i])=="object") {
							var txt = e.target.files[i].name+" ("+Math.round(e.target.files[i].size/1024)+"kb)";
							jQuery('#importing_processing_files').append('<div id="fileprocessing_'+i+'" class="filedrop_line_2">'+txt+'<i class="material-icons fileupload_status"></i><span class="fileupload_message"></span></div>');
						}
					}
					RVS.F.uploadFiles({form:form,files:e.target.files,fileindex:0,report:'#fileprocessing_',success:_.success});


				 });
			},
		close:function() {
				jQuery('#filedrop').remove();
				jQuery('#wpwrap').removeClass("blurred");
		}
	};

	RVS.F.uploadFiles = function(_) {
		_.fileindex = _.fileindex === undefined ? 0 : _.fileindex;
		 jQuery(_.report+_.fileindex).find('.fileupload_status').html('autorenew').addClass("rotating");
		 var form_data = new FormData();
		form_data.append('import_file', _.files[_.fileindex]);
		form_data.append('action', RVS.ENV.plugin_dir+'_ajax_action');
		form_data.append('client_action', 'import_slider');
		form_data.append('nonce', RVS.ENV.nonce);
		form_data.append('form_key', window.FORM_KEY);


		jQuery.ajax({
			url: ajaxurl,
			type: 'post',
			contentType: false,
			processData: false,
			data: form_data,
			success: function (response) {
				response = JSON.parse(response);
				if (response.success) {
					jQuery(_.report+_.fileindex).find('.fileupload_status').html('done').removeClass("rotating").addClass("doneupload");
					if (_.success!==undefined) RVS.DOC.trigger(_.success,response);
				} else {
					_.anyError = true;
					jQuery(_.report+_.fileindex).find('.fileupload_status').html('priority_high').removeClass("rotating").addClass("errorupload");
					jQuery(_.report+_.fileindex).find('.fileupload_message').html(response.message);
				}
				_.fileindex++;
				if (_.files.length>_.fileindex) {
					RVS.F.uploadFiles(_);
				} else {
					if (_.anyError!==true) {
						jQuery('#file_upload_mininfo').html(RVS_LANG.successImportFile);
						jQuery('#file_upload_processicon').removeClass("rotating").addClass("done").html('done');
						setTimeout(function() {
							RVS.F.browserDroppable.close();
						},500);
					} else {
						jQuery('#file_upload_mininfo').html(RVS_LANG.importReport);
						jQuery('#file_upload_processicon').removeClass("rotating").addClass("error").html('error');
					}

				}

			},
			error: function (response) {
				_.anyError = true;
				jQuery(_.report+_.fileindex).find('.fileupload_status').html('priority_high').removeClass("rotating").addClass("errorupload");
				jQuery(_.report+_.fileindex).find('.fileupload_message').html(response.message);
				_.fileindex++;
				if (_.files.length>_.fileindex) {
					RVS.F.uploadFiles(_);
				} else {
					if (_.anyError!==true) {
						jQuery('#file_upload_mininfo').html(RVS_LANG.successImportFile);
						jQuery('#file_upload_processicon').removeClass("rotating").addClass("done").html('done');
						setTimeout(function() {
							RVS.F.browserDroppable.close();
						},500);
					} else {
						jQuery('#file_upload_mininfo').html(RVS_LANG.importReport);
						jQuery('#file_upload_processicon').removeClass("rotating").addClass("error").html('error');
					}

				}

			}

		});
	};


	/*********************************************
		- 	SAVE SLIDE,SLIDER,STATIC SLIDE 	-
	*********************************************/

	RVS.F.convertIDStoTxt = function() {
		for (var i in RVS.SLIDER.slideIDs) if(RVS.SLIDER.slideIDs.hasOwnProperty(i)) RVS.SLIDER.slideIDs[i] = ""+RVS.SLIDER.slideIDs[i];
		for (var i in RVS.SLIDER.inWork) if(RVS.SLIDER.inWork.hasOwnProperty(i)) RVS.SLIDER.inWork[i] = ""+RVS.SLIDER.inWork[i];
	}

	RVS.F.saveSlides = function(_) {
		if (_.index < _.slides.length) {
			_.order = _.order===undefined ? 0 : _.order;
			_.order++;
			var workindex = jQuery.inArray(_.slides[_.index]+"",RVS.SLIDER.inWork);
			if (workindex>=0) {
				var params = JSON.stringify(RVS.F.simplifySlide(RVS.SLIDER[_.slides[_.index]].slide)),
					layers = JSON.stringify(RVS.F.simplifyAllLayer(RVS.SLIDER[_.slides[_.index]].layers));
				// need a hook for the backups addon to push the session_id onto the saved data
				var options = {slider_id:RVS.ENV.sliderID, slide_id:_.slides[_.index], params:params, layers:layers, slide_order:_.order};
				RVS.DOC.trigger('rs_save_slide_params', [options]);
				RVS.F.ajaxRequest('save_slide', options, function(response){
					if(response.success) {
						_.index++;
						RVS.F.saveSlides(_);
					}
				},undefined,undefined,RVS_LANG.saveslide+'<br><span style="font-size:17px; line-height:25px;">"'+RVS.SLIDER[_.slides[_.index]].slide.title+'"</span>');
			} else {
				_.index++;
				RVS.F.saveSlides(_);
			}
		} else {
			RVS.SLIDER.inWork = RVS.SLIDER.inWork===undefined ? [] : RVS.SLIDER.inWork;
			RVS.SLIDER.inWork.push(RVS.S.slideId);
			RVS.S.need_to_save = false;
			if (_.trigger!==undefined) _.trigger();
		}
	};

	RVS.F.convertArrayToObjects = function() {
		RVS.SLIDER.settings.nav.arrows.presets = Object.assign({},RVS.SLIDER.settings.nav.arrows.presets);
		RVS.SLIDER.settings.nav.bullets.presets = Object.assign({},RVS.SLIDER.settings.nav.bullets.presets);
		RVS.SLIDER.settings.nav.thumbs.presets = Object.assign({},RVS.SLIDER.settings.nav.thumbs.presets);
		RVS.SLIDER.settings.nav.tabs.presets = Object.assign({},RVS.SLIDER.settings.nav.tabs.presets);
	};



	RVS.F.saveSliderSettings = function() {
		var params = JSON.stringify(RVS.SLIDER.settings),
			slideids = RVS.SLIDER.slideIDs.slice(),
			staticindex = -1;
			for (var si in slideids) {
				if(!slideids.hasOwnProperty(si)) continue;
				if ((""+slideids[si]).indexOf("static")>=0) staticindex = si;
			}
			slideids.splice(staticindex,1);
		RVS.F.ajaxRequest('save_slider', {slider_id:RVS.ENV.sliderID, params:params, slide_ids:/*RVS.SLIDER.slideIDs*/slideids}, function(response){
			if (response.success && response.missing!==undefined && response.missing.length>0) RVS.F.saveSlides({index:0,slides:RVS.SLIDER.slideIDs,works:response.missing});
		},undefined,undefined,RVS_LANG.saveslide+'<br><span style="font-size:17px; line-height:25px;">'+RVS_LANG.slidersettings+'</span>');
	};


	RVS.F.getAllSliderDatas = function() {
		RVS.F.convertIDStoTxt();
		var r = { slider : JSON.stringify(RVS.SLIDER.settings)},
			slideids = RVS.SLIDER.slideIDs.slice();
		for (var si in slideids) {
			if(!slideids.hasOwnProperty(si)) continue;

			r[slideids[si]] = {
					params : JSON.stringify(RVS.F.simplifySlide(RVS.SLIDER[slideids[si]].slide)),
					layers : JSON.stringify(RVS.F.simplifyAllLayer(RVS.SLIDER[slideids[si]].layers))
			}
		}
		return r;
	}

	/**********************************
		- 	ADDONS MODAL FUNCTIONS 	-
	***********************************/

	function fcMarkup(_) {
		var h = '<div class="form_collector '+_.a+'" data-type="'+_.b+'" data-pcontainer="#'+_.c+'" data-offset="#rev_builder_wrapper">';
			h +='<div id="'+_.d+'"  data-select="'+_.f+_.slug+'" class="formcontainer form_menu_inside collapsed">';
			h +='<div class="collectortabwrap"><div id="" class="collectortab form_menu_inside" data-forms=\'["#'+_.d+'"]\'>'+_.title+'</div></div>';
			h +='<div id="'+_.e+'" class="form_inner open"></div>';
			h +='</div>';
			h +='</div>';
		return h;
	}
	// EXTEND ADDON CONTAINERS
	RVS.F.addOnContainer =  {
		create : function(_) {

			if (!_.slug || !_.icon || !_.alias) return;

			var h="";
			if (_.slider===true) {
				jQuery('#gst_sl_collector').append('<div id="gst_sl_'+_.slug+'" data-select="#gst_sl_'+_.slug+'" data-unselect=".general_submodule_trigger" class="general_submodule_trigger opensettingstrigger" style="display:none" data-collapse="true" data-forms=\'["#form_module_'+_.slug+'"]\'><i class="material-icons">'+_.icon+'</i><span class="gso_title">'+_.alias+'</span></div>');
				h += fcMarkup({a:"slider_general_collector", f:"#gst_sl_",  b:"sliderconfig", c:"slider_settings", d:"form_module_"+_.slug, title:_.title, e:"form_slidergeneral_"+_.slug, slug:_.slug});
			}
			if (_.layer===true) {
				jQuery('#gst_layer_collector').append('<div id="gst_layer_'+_.slug+'" data-select="#gst_layer_'+_.slug+'" data-unselect=".layer_submodule_trigger" class="layer_submodule_trigger opensettingstrigger" style="display:none" data-collapse="true" data-forms=\'["#form_layer_'+_.slug+'"]\'><i class="material-icons">'+_.icon+'</i><span class="gso_title">'+_.alias+'</span></div>');
				h += fcMarkup({a:"layer_settings_collector", f:"#gst_layer_", b:"layersconfig", c:"layer_settings", d:"form_layer_"+_.slug, title:_.title, e:"form_layerinner_"+_.slug, slug:_.slug});
			}
			if (_.slide===true) {
				jQuery('#slide_menu_gso_wrap').append('<div id="gst_slide_'+_.slug+'" data-select="#gst_slide_'+_.slug+'" data-unselect=".slide_submodule_trigger" class="slide_submodule_trigger opensettingstrigger" style="display:none" data-collapse="true" data-forms=\'["#form_slide_'+_.slug+'"]\'><i class="material-icons">'+_.icon+'</i><span class="gso_title">'+_.alias+'</span></div>');
				h += fcMarkup({a:"slide_settings_collector", f:"#gst_slide_", b:"slideconfig", c:"slide_settings", d:"form_slide_"+_.slug, title:_.title, e:"form_slidegeneral_"+_.slug, slug:_.slug});
			}

			jQuery('#the_right_toolbar_inner').append(h);
		}
	};

	// CREATE ADDONS ELEMENT
	RVS.F.buildSingleAddonElement = function(addon,slug) {
		if (addon===undefined || addon==="" || addon===0) return "";

		var markup = '<div id="ale_'+slug+'" data-ref="'+slug+'" class="rs_ale">';
		markup += '<div class="rs_alethumb"><div class="rs_alecbg" style="'+(addon.logo.color!==undefined && addon.logo.color!=="" && addon.installed!==false ? 'background-color:'+addon.logo.color : '')+'">';
		if (addon.logo.img==="")
			markup += '<div class="rs_alethumb_title">'+addon.logo.text+'</div>';
		markup += '</div>';
		if (addon.logo.img!=="")
			markup += '<div class="rs_alethumb_img" style="background-image:url('+addon.logo.img+')"></div>';
		if (!addon.installed || !addon.active) {
			if (!addon.installed)
				markup += '<div class="rs_ale_notinstalled">'+RVS_LANG.notinstalled+'</div>';
			else
				markup += '';
			markup += '<div class="rs_alethumb_notinstalledimg" style="background-image:url('+addon.logo.img+')"></div>';
		}

		var showenabled = addon.active && RVS.LIB.ADDONS[slug].enable ? "block" : "none",
			enabledtxt = RVS_LANG.enabled;
		markup += (RVS.ENV.addOns_to_update[slug]!==undefined && RVS.ENV.addOns_to_update[slug].updated!==true ) || addon.installed<addon.available ? '<div class="rs_ale_actionneeded" style="display:block">'+RVS_LANG.actionneeded+'</div>' : '<div class="rs_ale_enabled" style="display:'+showenabled+'">'+enabledtxt+'</div>';
		markup += '</div>';
		markup += '<div class="rs_ale_title">'+addon.title+'</div>';
		markup += '</div>';

		return markup;
	};

	// CREATE ADDONS LIST BASED ON OBJECT
	RVS.F.buildAddonList = function(_,mode) {
		var markup = "";
		RVS.LIB.ADDONS = RVS.LIB.ADDONS===undefined ? {} : RVS.LIB.ADDONS;
		for (var slug in _) {
			if(!_.hasOwnProperty(slug)) continue;
			RVS.LIB.ADDONS[slug] = RVS.LIB.ADDONS[slug]===undefined ? {} : RVS.LIB.ADDONS[slug];
			//if (jQuery.inArray(_[slug].slug,["revslider-gallery-addon", "revslider-rel-posts-addon", "revslider-sharing-addon", "revslider-maintenance-addon", "revslider-404-addon","revslider-login-addon","revslider-prevnext-posts-addon","revslider-featured-addon","revslider-backup-addon"])>=0) _[slug].global = true;
			RVS.LIB.ADDONS[slug].enable = RVS.S.ovMode ? _[slug].global ? RVS.LIB.ADDONS[slug].enable : undefined : _[slug].global ? RVS.LIB.ADDONS[slug].enable : RVS.SLIDER.settings.addOns[slug]!==undefined ? RVS.SLIDER.settings.addOns[slug].enable : false;
			RVS.LIB.ADDONS[slug].enable = RVS.LIB.ADDONS[slug].enable===0 || RVS.LIB.ADDONS[slug].enable==="0" ? false : RVS.LIB.ADDONS[slug].enable===1 || RVS.LIB.ADDONS[slug].enable==="1" ? true : RVS.LIB.ADDONS[slug].enable===0 || RVS.LIB.ADDONS[slug].enable;
			if (mode!=="update") markup += RVS.F.buildSingleAddonElement(_[slug],slug);
			RVS.LIB.ADDONS_LIST[slug] = RVS.F.safeExtend(true,{},_[slug]);
		}
		if (mode!=="update") {
			jQuery('#rbm_addonlist').append(markup);
			RVS.F.RSDialog.center();
		}
	};


	RVS.F.loadAddonList = function(slug,mode,callme) {
		RVS.LIB.ADDONS_LIST = {};
		// GET THE LATEST AVAILABLE LIST
		RVS.F.ajaxRequest('get_addon_list', {}, function(response){
			if (response.success) {

				// sometimes response.addons can equal "[false]"
				if(response.addons && Array.isArray(response.addons) && response.addons.length === 1 && response.addons[0] === false) return;

				RVS.F.buildAddonList(response.addons,mode);
				if (slug && mode!=="update") RVS.F.showAddonInfos(slug);
				if (mode==="update") callme();
			}
		});
	};

	RVS.F.loadCSS = function(url) {
		var element = document.createElement("link");
		element.setAttribute("rel", "stylesheet");
		element.setAttribute("type", "text/css");
		element.setAttribute("href", url);
		document.getElementsByTagName("head")[0].appendChild(element);
	};

	// OPEN ADDONS MODAL WINDOW
	RVS.F.openAddonModal = function() {

		// 1ST TIME RUNNING
		if (!RVS.LIB.ADDONS_LIST) {
			// 	RESET THE MMODAL LIST
			RVS.F.loadAddonList();

			// ADD LISTENERS
			RVS.DOC.on('click','#rbm_addons .rbm_close',function() {
				RVS.F.RSDialog.close();
			});

			RVS.DOC.on('click','.rs_ale',function() {
				if (RVS.ENV.activated=="false" || RVS.ENV.activated==false) {
					RVS.F.showRegisterSliderInfo();
					return;
				}
				jQuery('.rs_ale.selected').removeClass("selected");
				this.className +=" selected";
				RVS.F.showAddonInfos(this.dataset.ref);
			});


			//INSTALL ADDON
			RVS.DOC.on('click','.ale_i_installaddon',function() {
				var slug = this.dataset.slug;
				RVS.F.ajaxRequest('activate_addon', {addon:slug}, function(response){
					if(response.success) {
						RVS.LIB.ADDONS_LIST[slug].installed=true;
						jQuery('#ale_'+slug+' .rs_ale_notinstalled').remove();

						RVS.F.showAddonInfos(slug);
					}
				},undefined,undefined,RVS_LANG.addon+'<br><span style="font-size:17px; line-height:25px;">"'+RVS_LANG.installingaddon+'"</span>');
			});

			//ACTIVATE ADDON
			RVS.DOC.on('click','.ale_i_activateaddon',function() {
				if (RVS.ENV.activated!=="true" && RVS.ENV.activated!==true) {
					//MESSAGE ABOUT REGISTERING
					return;
				}
				var slug = this.dataset.slug,
					varslug = slug.replace(/-/g, '_'),
					coloraddonthmb = jQuery('#ale_'+slug+' .rs_alethumb_img');

				// IF NOT IN OVERVIEW AND NOT GLOBAL, WE CAN ENABLE IT AFTER INSTALL/ACTIVATE
				if (!RVS.LIB.ADDONS_LIST[slug].global && !RVS.S.ovMode) {
					RVS.SLIDER.settings.addOns[slug] = RVS.SLIDER.settings.addOns[slug]===undefined ? {} : RVS.SLIDER.settings.addOns[slug];
					RVS.SLIDER.settings.addOns[slug].enable = true;
					RVS.LIB.ADDONS[slug].enable = true;

				}

				RVS.F.ajaxRequest('activate_addon', {addon:slug}, function(response){
					if(response.success) {

						RVS.LIB.ADDONS_LIST[slug].active=true;
						// GET BRICKS AND OTHER VALUES LOADED VIA AJAX FROM ADDON
						window[varslug] = response[slug];

						// handle global AddOns
						if(typeof revbuilder !== 'undefined' && !RVS.SLIDER.settings.addOns.hasOwnProperty(slug)) window[varslug].enabled = true;
						else window[varslug].enabled = RVS.F._d(RVS.F._truefalse(window[varslug].enabled), (!RVS.S.ovMode ? RVS.SLIDER.settings.addOns[slug].enable : false));

						// SHOW THE ICON COLORED
						punchgs.TweenLite.fromTo(coloraddonthmb, 2, {zIndex:"13", clip:"rect(95px 95px 95px 95px)"},{clip:"rect(0px 190px 190px 0px)"});

						// SHOW THE ENABLED BUTTON
						jQuery('#ale_'+slug+' .rs_ale_enabled').show();

						// SHOW NEW VALUES OF ADDON IN THE PANEL
						RVS.F.showAddonInfos(slug);

						// UPDATE ALREADY CREATED OBJECT LIBRARY ELEMENTS
						RVS.F.addonInstalledOnDemand(slug);

						//  LOAD JS AND CSS FILES OR SHOW RESTART MESSAGES
						RVS.F.loadCSS(RVS.ENV.wp_plugin_url+slug+'/admin/assets/css/'+slug+'-admin.css');
						jQuery.getScript(RVS.ENV.wp_plugin_url+slug+'/admin/assets/js/'+slug+'-admin.js',function() {
							RVS.F.showAddonInfos(slug);

							// IF LOCAL ADDON WITHIN A SLIDER, WE NEED TO INITIALISE IT
							/*
								social and backups AddOns need an init triggered here
							*/
							// changed this:
							//if (!RVS.S.ovMode && !RVS.LIB.ADDONS_LIST[slug].global && RVS.LIB.ADDONS[slug].enable) RVS.DOC.trigger(slug+"_init");
							// to this:
							if (!RVS.S.ovMode && RVS.LIB.ADDONS[slug].enable) RVS.DOC.trigger(slug+"_init");

						}).fail(function(a,b,c) {console.log(c);});
					}
				},undefined,undefined,RVS_LANG.addon+'<br><span style="font-size:17px; line-height:25px;">"'+RVS_LANG.activatingaddon+'"</span>');
				//return false;
			});

			RVS.DOC.on('click','#check_addon_updates',function() {
				RVS.F.ajaxRequest('check_for_updates',{},function(response) {
					if (response.success) {
						RVS.ENV.latest_version = response.version;
						delete RVS.LIB.ADDONS_LIST;
						document.getElementById('rbm_addonlist').innerHTML = "";
						RVS.F.loadAddonList();
					}
				});
			});

			// ENABLE ADDON
			RVS.DOC.on('click','.ale_i_enableaddon',function() {
				var slug = this.dataset.slug;
				// SLIDER BASED ADD ONS ENABLE
				if (RVS.LIB.ADDONS_LIST[slug].global!==true) {
					RVS.SLIDER.settings.addOns[slug] = RVS.SLIDER.settings.addOns[slug]===undefined ? {} : RVS.SLIDER.settings.addOns[slug];
					RVS.SLIDER.settings.addOns[slug].enable = true;
					RVS.LIB.ADDONS[slug].enable=true;
					RVS.DOC.trigger(slug+"_init");
					RVS.F.showAddonInfos(slug);
					jQuery('#ale_'+this.dataset.slug+' .rs_ale_enabled').show();
				} else {
				// GLOBAL ADD ON ENABLE
					RVS.F.ajaxRequest('wp_ajax_enable_'+slug, {}, function(response){
						RVS.LIB.ADDONS[slug].enable=true;
						RVS.DOC.trigger(slug+"_init");
						RVS.F.showAddonInfos(slug);
						jQuery('#ale_'+slug+' .rs_ale_enabled').show();
					},undefined,undefined,RVS_LANG.addon+'<br><span style="font-size:17px; line-height:25px;">"'+RVS_LANG.enablingaddon+'"</span>');
				}
			});

			// DISABLE ADDON
			RVS.DOC.on('click','.ale_i_disableaddon',function() {
				var slug = this.dataset.slug;
				// SLIDER BASED ADD ONS DISABLE
				if (!RVS.LIB.ADDONS_LIST[slug].global) {
					RVS.SLIDER.settings.addOns[slug].enable = false;
					RVS.DOC.trigger(slug+"_init");
					RVS.LIB.ADDONS[slug].enable=false;
					RVS.F.showAddonInfos(slug);
					jQuery('#ale_'+this.dataset.slug+' .rs_ale_enabled').hide();
				} else {
					RVS.F.ajaxRequest('wp_ajax_disable_'+slug, {}, function(response){
						RVS.LIB.ADDONS[slug].active=false;
						RVS.LIB.ADDONS[slug].enable=false;
						RVS.DOC.trigger(slug+"_init");
						RVS.F.showAddonInfos(slug);
						jQuery('#ale_'+slug+' .rs_ale_enabled').hide();
					},undefined,undefined,RVS_LANG.addon+'<br><span style="font-size:17px; line-height:25px;">"'+RVS_LANG.disablingaddon+'"</span>');
				}
			});

			// UPDATE ADDON
			RVS.DOC.on('click','.ale_i_updateaddon',function() {
				var slug = this.dataset.slug;
				RVS.F.ajaxRequest('activate_addon', {addon:slug, update:true}, function(response){
					if(response.success) {
						if (RVS.ENV.addOns_to_update[slug]!==undefined && RVS.ENV.addOns_to_update[slug].updated!==true) RVS.ENV.addOns_to_update[slug].updated = true;
						delete RVS.LIB.ADDONS_LIST;
						document.getElementById('rbm_addonlist').innerHTML = "";
						RVS.F.loadAddonList(slug);
						/*RVS.LIB.ADDONS_LIST[slug].installed=true;
						jQuery('#ale_'+slug+' .rs_ale_notinstalled').remove();
						RVS.F.showAddonInfos(slug);*/

					}
				},undefined,undefined,RVS_LANG.addon+'<br><span style="font-size:17px; line-height:25px;">"'+RVS_LANG.updatingaddon+' '+slug+'"</span>');
			});

			//LISTEN ON SAVE BUTTON
			RVS.DOC.on('click','#rbm_configpanel_savebtn',function() {
				RVS.DOC.trigger('save_'+this.dataset.slug);
			});
		}

		RVS.F.RSDialog.create({modalid:'rbm_addons', bgopacity:0.85});
		jQuery('#rbm_addonlist, #rbm_addon_details').RSScroll({ suppressScrollX:true});

	};

	RVS.F.showAddonInfos = function(slug) {

		//RVS.ENV.revision = "4.0.0";

		//document.getElementById('rbm_addon_details').innerHTML = "";
		var markup ='<div class="rbm_addon_details_inner">',
			addon = RVS.LIB.ADDONS_LIST[slug],
			path,
			enabletxt = addon.active ? addon.global ?  RVS_LANG.enableglobaladdon : RVS_LANG.enableaddon : addon.global ? RVS_LANG.activateglobaladdon : RVS_LANG.activateaddon,
			disabletxt = addon.global ? RVS_LANG.disableglobaladdon : RVS_LANG.disableaddon;
		markup += '<div class="div20"></div>';
		markup += '<div class="ale_i_title">'+addon.title+'</div>';
		markup += '<div class="ale_i_content">'+addon.line_1+' '+addon.line_2+'</div>';

		markup += '<div class="div20"></div>';

		if (RVS.S.ovMode && !addon.global && addon.active && addon.installed)
			markup += '<div class="basic_action_button_inactive autosize basic_action_button" data-slug="'+addon.slug+'" data-global="'+addon.global+'"><i class="material-icons">error_outline</i>'+RVS_LANG.addonOnlyInSlider+'</div>';
		else
		if (RVS.ENV.revision<addon.version_from)
			markup += '<div class="ale_i_errorbutton basic_action_button autosize"><i class="material-icons">error_outline</i>'+RVS_LANG.checkforrequirements+'</div>';
		else

		if (!addon.installed)
			markup += '<div class="ale_i_installaddon basic_action_coloredbutton autosize basic_action_button" data-slug="'+addon.slug+'" data-global="'+addon.global+'"><i class="material-icons">get_app</i>'+RVS_LANG.install_and_activate+'</div>';
		else
		if (!addon.active)
			markup += '<div class="ale_i_activateaddon basic_action_coloredbutton autosize basic_action_button" data-slug="'+addon.slug+'" data-global="'+addon.global+'"><i class="material-icons">power_settings_new</i>'+enabletxt+'</div>';
		else
		if (!RVS.LIB.ADDONS[slug].enable) {
			if (RVS.S.ovMode && !addon.global)
				markup += '';
			else
				markup += '<div class="ale_i_enableaddon basic_action_coloredbutton autosize basic_action_button" data-global="'+addon.global+'" data-slug="'+addon.slug+'"><i class="material-icons">power_settings_new</i>'+enabletxt+'</div>';
		} else
			markup += '<div class="ale_i_disableaddon basic_action_coloredbutton autosize basic_action_button" data-global="'+addon.global+'" data-slug="'+addon.slug+'"><i class="material-icons">remove_circle_outline</i>'+disabletxt+'</div>';

		markup += '</div>';
		markup += '<div class="ale_i_line"></div>';
		markup +='<div class="rbm_addon_details_inner">';

		// VERSION DETAILS
		markup += '<row>';
		markup += '<onehalf>';
		markup += '<div class="ale_i_title">'+RVS_LANG.installedversion+'</div>';
		// ADDON INSTALLED ??
		if (addon.installed===false)
			markup += '<div class="ale_i_content">'+RVS_LANG.notinstalled+'</div>';
		else
			markup += '<div class="ale_i_content">'+addon.installed+'</div>';
		markup += '</onehalf>';
		markup += '<onehalf>';
		markup += '<div class="ale_i_title">'+RVS_LANG.availableversion+'</div>';
		markup += '<div class="ale_i_content">'+addon.available+'</div>';
		markup += '</onehalf>';
		markup += '</row>';
		markup += '<div class="div20"></div>';
		markup += '<div class="ale_i_title">'+RVS_LANG.requirements+'</div>';

		// REQUIREMENT FILLED ?
		if (RVS.ENV.revision>=addon.version_from)
			markup += '<div class="ale_i_content"><i class="material-icons">check</i>'+RVS_LANG.sliderrevversion+' '+addon.version_from+'</div>';
		else
			markup += '<div class="ale_i_content ale_yellow"><i class="material-icons">error_outline</i>'+RVS_LANG.sliderrevversion+' '+addon.version_from+'</div>';

		// UPDATE AVAILABLE, UPDATE ADDON
		if (addon.installed!==false && addon.installed<addon.available) {
			markup += '<div class="div20"></div>';
			markup += '<div class="ale_i_updateaddon  basic_action_coloredbutton autosize basic_action_button" data-global="'+addon.global+'" data-slug="'+addon.slug+'"><i class="material-icons">get_app</i>'+RVS_LANG.updateNow+'</div>';
		}

		markup += '</div>';
		markup += '<div class="ale_i_line"></div>';
		markup += '<div class="form_collector" id="addon_configuration_subpanel"></div>';

		document.getElementById('rbm_addon_details').innerHTML = markup;
		RVS.F.configPanelSaveButton({show:false, slug:slug});
		if (addon.active) {
			RVS.DOC.trigger(addon.slug+'_config',{container:"addon_configuration_subpanel"});
		}
	};

	RVS.F.configPanelSaveButton = function(_) {
		var btn = document.getElementById('rbm_configpanel_savebtn');
		if (_.show===true) {
			btn.style.display="block";
			btn.dataset.slug=_.slug;
		} else {
			btn.style.display="none";
		}
	};







	/*******************************************
		- IMAGE AND VIDEO AND AUDIO LOADINGS -
	*******************************************/

	RVS.F.preloadImage = function(_) {
		var useCallback = true;
		var img = new Image();
		if (_.silent!==true) RVS.F.showWaitAMinute({fadeIn:500,text:RVS_LANG.imageisloading});
		img.onload = function() {
			if (_.slideId!==undefined && _.uid!==undefined) {
				RVS.SLIDER[_.slideId].layers[_.uid].size.originalWidth = this.width;
				RVS.SLIDER[_.slideId].layers[_.uid].size.originalHeight = this.height;
				var val = parseInt(this.height,0)===0 ? 0 : parseInt(this.width,0) / parseInt(this.height,0);
				RVS.SLIDER[_.slideId].layers[_.uid].size.aspectRatio =  RVS.F.cToResp({default:"1",val:val});
				RVS.SLIDER[_.slideId].layers[_.uid].media.loaded = true;
			}
			if (_.silent!==true) setTimeout(function() {	RVS.F.showWaitAMinute({fadeOut:500});},100);
			if (_.callback && useCallback) _.callback.call();
		};
		img.onerror = function() {

			if (_.slideId!==undefined && _.uid!==undefined) {
				RVS.SLIDER[_.slideId].layers[_.uid].media.imageUrl = RVS.ENV.img_ph_url;
				RVS.SLIDER[_.slideId].layers[_.uid].size.originalWidth = 300;
				RVS.SLIDER[_.slideId].layers[_.uid].size.originalHeight = 200;
				RVS.SLIDER[_.slideId].layers[_.uid].size.aspectRatio =  RVS.F.cToResp({default:"1",val:300/200});
				RVS.SLIDER[_.slideId].layers[_.uid].media.loaded = true;
			}

			if (_.silent!==true) setTimeout(function() {	RVS.F.showWaitAMinute({fadeOut:500});},100);
			if (_.callback && useCallback) _.callback.call();
		};
		img.onabort = function() {
			if (_.silent!==true) setTimeout(function() {	RVS.F.showWaitAMinute({fadeOut:500});},100);
			if (_.callback && useCallback) _.callback.call();
		};

		img.src = _.image;
	};

	RVS.F.createMiniPreloader = function(_) {
		jQuery('#font_minipreloader').remove();
		_.container.append(jQuery('<div id="font_minipreloader" style="position:absolute; top:5px;right:5px; width:20px:height:20px"><svg  height="20" width="20"><circle style="visibility:visible; color:#fff;" class="circle-fill" cx="10" cy="10" r="8" transform="rotate(-90 10 10)" stroke="white" stroke-width="4" fill="none" /></svg>'));
		punchgs.TweenLite.fromTo('.circle-fill', 0.5,{drawSVG:'0%'},{drawSVG: '30%',ease:punchgs.Linear.EaseNone, delay:0.2, force3D:true});
	};
	RVS.F.miniPreloaderOut = function(dontkill) {

		punchgs.TweenLite.fromTo('.circle-fill', 0.5,{drawSVG: '30%'},{drawSVG: '100%', ease:punchgs.Linear.EaseNone, force3D:true,delay:0.5});
		punchgs.TweenLite.to('#font_minipreloader', 0.6,{scale:0, transformOrigin:"50% 50%",ease:punchgs.Power3.EaseInOut, overwrite:"all", force3D:true,delay:0.7});
		if (dontkill) RVS.F.miniPreloaderKill();
	};

	RVS.F.miniPreloaderKill = function() {
		setTimeout(function() {
			jQuery('#font_minipreloader').remove();
		},2000);
	};

	RVS.F.checkVimeoID = function(_) {
		RVS.F.createMiniPreloader({container:jQuery('#video_id_wrap')});
		jQuery('#hidden_video_container').remove();
		jQuery('body').append('<div id="hidden_video_container"></div>');
		vimeoPlayer = new Vimeo.Player("hidden_video_container",_);
		videoPlayerTimer = setTimeout(function() {
			jQuery('#layer_youtubevimeo_id').addClass("badvalue");
			RVS.F.miniPreloaderOut();
		},3000);
		vimeoPlayer.ready().then(function() {
			jQuery('#layer_youtubevimeo_id').removeClass("badvalue");
			RVS.F.miniPreloaderOut();
			clearTimeout(videoPlayerTimer);
		}).catch(function(e) {
			jQuery('#layer_youtubevimeo_id').addClass("badvalue");
			RVS.F.miniPreloaderOut();
			clearTimeout(videoPlayerTimer);
		});


	};
	RVS.F.checkYouTubeID = function(_) {
		RVS.F.createMiniPreloader({container:jQuery('#video_id_wrap')});
		jQuery('#hidden_video_container').remove();
		jQuery('body').append('<div id="hidden_video_container"></div>');
		videoPlayerTimer = setTimeout(function() {
			jQuery('#layer_youtubevimeo_id').addClass("badvalue");
			RVS.F.miniPreloaderOut();
		},3000);
		window._youtubeplayer_ = new YT.Player('hidden_video_container',{
			videoId:_.id,
			events:{
				'onReady':function() {jQuery('#layer_youtubevimeo_id').removeClass("badvalue");RVS.F.miniPreloaderOut();clearTimeout(videoPlayerTimer);},
				'onError':function() { console.log("ERROR");jQuery('#layer_youtubevimeo_id').addClass("badvalue");RVS.F.miniPreloaderOut();clearTimeout(videoPlayerTimer);}
			}});



	};
	/**********************************
		-	IMAGE AND VIDEO DIALOG -
	********************************/

	RVS.F.openMageImageDialog = function(title, onInsert, isMultiple, fileType) {
		MediabrowserUtility.openDialog(
			revMageImageUploadUrl.replace('file_type', fileType ? fileType : 'image'),
			false,
			false,
			title,
			{},
			onInsert
		);
	}

	RVS.F.openAddImageDialog = function(title,onInsert,isMultiple){
		if(!title)
			title = RVS_LANG.select_image;
		RVS.F.openMageImageDialog(title, onInsert, isMultiple);
	};

	RVS.F.openAddVideoDialog = function(title,onInsert,isMultiple){
		if(!title)
			title = RVS_LANG.select_image;
		RVS.F.openMageImageDialog(title, onInsert, isMultiple, 'video');
	};

	/**********************************
		-	MOUSE INFO MANAGEMET	-
	********************************/

	RVS.F.showMouseInfo = function(obj) {
		if (RVS.C.mouseInfo===undefined) {
			RVS.C.mouseInfo = jQuery('#mouseInfoBox');
			RVS.C.mouseInfo.appendTo(jQuery('body'));
		}
		if (obj.html!==undefined) RVS.C.mouseInfo[0].innerHTML = obj.html;
		else
		if (obj.text!==undefined) RVS.C.mouseInfo[0].innerHTML = obj.text;
		mouseInfoBoxOn = true;
	};

	RVS.F.hideMouseInfo = function() {
		mouseInfoBoxOn = false;
		if (RVS.C.mouseInfo !== undefined)
			punchgs.TweenLite.set(RVS.C.mouseInfo,{display:"none"});
	};



	/****************************
		-	USEFULL THINGS  -
	*****************************/

	RVS.F.os = function() {
		    var OSName="Unknown OS";
			if (navigator.appVersion.indexOf("Win")!=-1) OSName="Windows";
			else if (navigator.appVersion.indexOf("Mac")!=-1) OSName="MacOS";
			else if (navigator.appVersion.indexOf("X11")!=-1) OSName="UNIX";
			else if (navigator.appVersion.indexOf("Linux")!=-1) OSName="Linux";
			RVS.S.OSName = OSName;
			return OSName;
	};

	RVS.F.setEditorUrl = function(id) {
		if (window.history && window.history.pushState) {
			window.lastUrlState = window.location.href;
			window.history.pushState({}, null, window.location.origin+window.location.pathname+"?id="+id);
			RVS.WIN.on('popstate',function(e) {
				window.location.href = window.lastUrlState;
			});
		}
	};



	RVS.F.getEditorUrl = function() {
		var orig = window.location.href;
		if (window.location.href.indexOf('alias=')>=0) return RVS.SLIDER.slideIDs[0];
		var id = window.location.href.split("?id=");
		id = jQuery.isArray(id) ? id[1] : RVS.SLIDER.slideIDs[0];
		id = jQuery.isNumeric(id) ? id : id.split("&")[0];
		id = jQuery.isNumeric(id) ? id : id.split("#")[0];
		return id;
	};

	RVS.F.backToOverview = function() {
		window.location.href = overviewUrl;
	};

	RVS.F.getProportionalSizes = function(_) {
		_.image.width = parseInt((_.image.width=="100%"  || _.image.width=="auto" ? _.viewPort.width : _.image.width),0);
		_.image.height = parseInt((_.image.height=="100%" || _.image.height=="auto" ? _.viewPort.height : _.image.height),0);
		_.viewPort.width = parseInt((_.viewPort.width=="100%" || _.viewPort.width=="auto" ? _.image.width : _.viewPort.width),0);
		_.viewPort.height = parseInt((_.viewPort.height=="100%" || _.viewPort.height=="auto" ? _.image.height : _.viewPort.height),0);

		var iAR = _.image.width / _.image.height,
			vAR = _.viewPort.width / _.viewPort.height,
			ret = {width:_.image.width, height:_.image.height};

		switch(_.type) {
			case "fit":
				if (iAR > vAR) {
					ret.width = _.viewPort.width;
					ret.height = _.viewPort.width / iAR;
				} else {
					ret.width = _.viewPort.height * iAR;
					ret.height = _.viewPort.height;
				}
			break;
			case "cover-proportional":
				if (iAR <= vAR) {
					ret.width = _.viewPort.width;
					ret.height = _.viewPort.width / iAR;
				} else {
					ret.width = _.viewPort.height * iAR;
					ret.height = _.viewPort.height;
				}
			break;
			case "fullwidth":

				ret.width = _.viewPort.width;
				ret.height = _.proportional ? ret.width / iAR : _.image.height;
			break;
			case "fullheight":
				ret.height = _.viewPort.height;
				ret.width = _.proportional ? ret.height / iAR : _.image.width;
			break;
			case "cover":
				ret.width = _.viewPort.width;
				ret.height = _.viewPort.height;
			break;
		}
		return ret;
	};

	RVS.F.convPercVals = function(x) {
		if (!jQuery.isNumeric(x) && x!==false && x!==undefined && x!==true && x.match(/%]/g))
			x = x.split("[")[1].split("]")[0];
		return x;
	};


	RVS.F.convertHexToRGB = function(hex) {
		hex = parseInt(((hex.indexOf('#') > -1) ? hex.substring(1) : hex), 16);
		return [hex >> 16,(hex & 0x00FF00) >> 8,(hex & 0x0000FF)];
	};

	RVS.F.sanitize_input_ws = function(raw){
		return raw.replace(/[^-0-9a-zA-Z_ -]/g,'');
	};

	RVS.F.sanitize_columns = function(raw) {
		return raw.replace(/[^-1-9+/]/g,'');
	};

	RVS.F.sanitize_input = function(raw){
		if (raw===null) return;
		return raw.replace(/ /g, '-').replace(/[^-0-9a-zA-Z_-]/g,'');
	};

	RVS.F.fontNameConvert = function(raw) {
		return raw.replace(/"/g, '');
	};


	RVS.F.sanitize_input_lc = function(raw){
		return raw.replace(/ /g, '-').replace(/[^-0-9a-z_-]/g,'');
	};

	RVS.F.parseIntPlus = function(a) {
		var b = parseInt(a,0);
		return jQuery.isNumeric(b) ? b : a;
	};

	RVS.F.htmlToText = function(raw) {
		return raw.replace(/</g, '&lt;').replace(/>/g, '&gt;');
	};


	// INSERT INTO CODEMIRROR EDITOR TEXT AT CURSOR POSITION
	RVS.F.insertTextAtCursor = function(editor, text) {
	    var doc = editor.getDoc();
	    var cursor = doc.getCursor();
	    doc.replaceRange(text, cursor);
	};

	// GET THE DIRECTION OF THE CURRENT RESIZING EVENT
	RVS.F.getResizeDirection = function(d) {
		return d.size.height < d.originalSize.height ? "height" :
		d.size.height > d.originalSize.height ? "height" :
		d.size.width < d.originalSize.width ? "width" :
		d.size.width > d.originalSize.width ? "width" :
		"none";
	};

	RVS.F.whichBGPos = function(obj) {
		if (obj.position==="custom")
			return obj.positionX+"% "+obj.positionY+"%";
		else
			return obj.position;
	};

	RVS.F.matchArray = function(a,b) {
		if (!jQuery.isArray(a) || !jQuery.isArray(b)) return false;
		if (a.length!=b.length) return false;
		var ret = true;
		for (var i in a) {
			if(!a.hasOwnProperty(i)) continue;
			if (jQuery.inArray(a[i],b)===-1) {
				ret = false;
				break;
			}
		}
		if (ret)
			for (var i in b) {
				if(!b.hasOwnProperty(i)) continue;
				if (jQuery.inArray(b[i],a)===-1) {
					ret = false;
					break;
				}
			}
		return ret;
	};

	RVS.F.mergeArrays = function(a,b) {
		for (var i in b) {
			if(!b.hasOwnProperty(i)) continue;
			if (jQuery.inArray(b[i],a)===-1) a.push(b[i]);
		}
		return a;
	};


	//Make Array of Single Elements
	RVS.F.makeArray = function(a,len) {
		if (!jQuery.isArray(a)) {
			var _ = [];
			for (var i=0;i<len;i++) {
				_.push(a);
			}
			a = _;
		}
		return a;
	};

	//Move Array Elements
	RVS.F.amove = function(arr, old_index, new_index) {
		if (!jQuery.isArray(arr)) arr = Object.values(arr);
		if (new_index >= arr.length) {
	        var k = new_index - arr.length + 1;
	        while (k--) {
	            arr.push(undefined);
	        }
	    }
	    arr.splice(new_index, 0, arr.splice(old_index, 1)[0]);
	    return arr;
	};

	RVS.F.rArray = function(a,removeItem) {
		return jQuery.grep(a, function(value) {return value != removeItem;});
	};
	RVS.F._inArray = function(a,b){
		var f = -1,
			i = 0;
		while ( i < b.length && f===-1) {
			if (b[i]==a) f = i;
			i++;
		}
		return f;
	};

	RVS.F.addT = function(a) {
		var r = 0;

		for (var i in a) {
			if(!a.hasOwnProperty(i)) continue;
			r = r + parseInt(a[i],0);
		}
		return r;
	};

	RVS.F.isVaOrPx = function(a) {
		var res = false;
		if (!jQuery.isNumeric(a) && a.indexOf("px")>=0)
			res = true;
		else
		if (jQuery.isNumeric(a))
			res = true;
		return res;
	};

	RVS.F.retWitSuf = function(a,suf) {
		if (!jQuery.isNumeric(a) && a.indexOf("px")>=0) return parseInt(a)+"px"
		else
		if (!jQuery.isNumeric(a) && a.indexOf("%")>=0) return parseInt(a)+"%"
		else
		if (!jQuery.isNumeric(a) && jQuery.isNumeric(parseInt(a,0))) return parseInt(a)+suf
		else
		if (jQuery.isNumeric(a)) return a+suf;
	}



	RVS.F.firstCharUppercase = function(a) {
		return a.substr(0,1).toUpperCase()+(a.substr(1).toLowerCase());
	};



	/*
	SET VALUE TO A OR B DEPENDING IF VALUE A EXISTS AND NOT UNDEFINED OR NULL
	*/
	RVS.F._d = function(a,b) {
		if (a===undefined || a===null)
			return b;
		else
			return a;
	};

	RVS.F._truefalse = function(v) {
		if (v==="false" || v===false || v==="off" || v===undefined || v===0 || v===-1)
			v=false;
		else
		if (v==="true" || v===true || v==="on")
			v=true;
		return v;
	};



	/*
	CREATE A 4 LEVEL OBJECT STRUCTURE
	(DESKTOP, NOTEBOOK, TABLET, MOBILE) WITH DEFAULT OR PREDEFINED VALUES
	VALUE, EDITED (true/false), UNIT (PX, %, EM...)
	*/
	RVS.F.cToResp = function(attr) {

		attr = attr===undefined ? {default:0,unit:""} : attr;
		var newObj = {},
			v = attr.default===undefined ? 0 : attr.default,
			unit = attr.unit===undefined ? "" : attr.unit;

		for (var i in RVS.V.sizes) {
			if(!RVS.V.sizes.hasOwnProperty(i)) continue;
			var s = RVS.V.sizes[i],
				sold = RVS.V.sizesold[i],
				v = jQuery.isArray(attr.val) ? attr.val : attr.val!==undefined && typeof attr.val!=='object' ? attr.val : (attr.val===undefined || typeof attr.val !=='object' || attr.val[sold]===undefined || attr.val[sold]===null) ? v : attr.val[sold];


			if (typeof v ==="object") {

				newObj[s] =  RVS.F.safeExtend(true,{},{v:v,e:false});
				if (attr!==undefined && attr.val!==undefined && attr.val[sold]!==undefined) newObj[s].e = true;
				for (var vi in v) {
					if(!v.hasOwnProperty(vi)) continue;
					newObj[s].v[vi] = unit.length>0 ? v[vi]!=="auto" && v[vi]!=="none" ? parseFloat(v[vi])+unit : v[vi] : v[vi];
					if (unit=="" && !jQuery.isNumeric(newObj[s].v[vi])) {
						if (newObj[s].v[vi].indexOf("%")>=0) {
							newObj[s].v[vi] = parseInt(newObj[s].v[vi],0)+ "%";
							//newObj[s].u = "%";
						} else
						if (newObj[s].v[vi].indexOf("px")>=0) {
							newObj[s].v[vi] = parseInt(newObj[s].v[vi],0) + "px";
							//newObj[s].u = "px";
						}
					}
				}
			} else {

				newObj[s] = RVS.F.safeExtend(true,{},{	v:unit.length>0 ? v!=="auto" && v!=="none"  && v!=="" ? parseFloat(v)+unit : v : v, e:false, u:unit});
				if (attr!==undefined && attr.val!==undefined && attr.val[sold]!==undefined) newObj[s].e = true;
				if (newObj[s].v==="" && attr.default!=="") newObj[s].v = attr.default;
				if (unit=="" && !jQuery.isNumeric(newObj[s].v) && newObj[s].v!==false && newObj[s].v!==true) {
					if (newObj[s].v.indexOf("%")>=0) {
						newObj[s].v = parseInt(newObj[s].v,0)+"%";
						//newObj[s].u = "%";
					} else
					if (newObj[s].v.indexOf("px")>=0) {
						newObj[s].v = parseInt(newObj[s].v,0)+"px";
						//newObj[s].u = "px";
					}
				}

				//newObj[s].v[vi] = newObj[s].v[vi];
			}
		}

		return newObj;
	};


	RVS.F.cToVandU = function(_) {
		var newObj = {v:_.default, u:_.u};
		newObj.v = _.val===undefined ? newObj.v : _.val;

		var i = 0;
		if (typeof newObj.v==="object") {
			// var i=0;
			for (var vi in newObj.v) {
				if(!newObj.v.hasOwnProperty(vi)) continue;
				if (!jQuery.isNumeric(newObj.v[vi])) {
					newObj.u = i==0 && newObj.v[vi].indexOf("px")>=0 ? "px" : i==0 && newObj.v[vi].indexOf("%")>=0 ? "%" : newObj.u;
					newObj.v[vi] = parseInt(newObj.v[vi],0) + newObj.u;
					i++;
				}
			}
		} else {
			if (!jQuery.isNumeric(newObj.v)) {
					newObj.u = i==0 && newObj.v.indexOf("px")>=0 ? "px" : i==0 && newObj.v.indexOf("%")>=0 ? "%" : newObj.u;
					newObj.v = parseInt(newObj.v,0) + newObj.u;
					i++;
				}
		}


		return newObj;
	};

	RVS.F.exportLayerCombo = function(_) {
		if (downloadExportLayerCombo===undefined) {
			jQuery('body').append('<a style="display:none" id="downloadExportLayerCombo" href="" download></a>');
			downloadExportLayerCombo = jQuery('#downloadExportLayerCombo');
		}
		var videoID,
			thumbID = RVS.SLIDER[RVS.S.slideId].slide.thumb.customAdminThumbSrcId,
			layers = {};

		for (var i in RVS.L) {

			if(!RVS.L.hasOwnProperty(i)) continue;

			//GET VIDEO ID
			if (RVS.L[i].type==="video" /*&& RVS.L[i].alias.toLowerCase()==="videothumb"*/) videoID = RVS.L[i].media.id;
			if (RVS.L[i].type!=="video" /*RVS.L[i].alias.toLowerCase()!=="videothumb"*/) layers[i] = RVS.F.safeExtend(true,{},RVS.L[i]);
		}

		delete layers.bottom;
		delete layers.top;
		delete layers.middle;

		//export_layer_group
		layers = JSON.stringify(RVS.F.simplifyAllLayer(layers));
		//layers = JSON.stringify(layers);



		RVS.F.ajaxRequest('export_layer_group', {videoid:videoID, thumbid:thumbID, layers:layers, title:_.title}, function(response){
			downloadExportLayerCombo[0].href = response.url;
			downloadExportLayerCombo[0].click();
		},true,true);
		return "Exporting Layer Combo File";
	};



	RVS.F.debug = function(txt) {
		console.log(txt);
	};







	//COMPRESSING THE RAW OBJECT STRUCTURE TO REDUCE FILE SIZE
	RVS.F.simplifyObject = function(emp,o) {
	 	for (var key in o) {

			if(!o.hasOwnProperty(key)) continue;

	 		if (typeof o[key]!=="object" || jQuery.isArray(o[key]))  {
	 			if (emp[key] == o[key] && key!=="text" && key!=="endWithSlide") {
	 				delete o[key];
	 			}
	 		} else {

		 		if (emp[key]!==undefined && key!=="margin" && key!=="padding") {
		 			o[key] = RVS.F.safeExtend(true,{}, RVS.F.simplifyObject(emp[key],o[key]));
		 		}

		 		// CHECK IF OBJECT IS EMPTY ?
	 			if (jQuery.isEmptyObject(o[key])) delete o[key];
	 		}
	 	}
	 	return o;
	};

	RVS.F.removeEmptyChilds = function(o) {
		var _ = {};
		for (var key in o) {
			if(!o.hasOwnProperty(key)) continue;
			if (typeof o[key]!=="object")
				_[key] = o[key];
			if (!jQuery.isEmptyObject(o[key])) _[key] = RVS.F.safeExtend(true,{},RVS.F.removeEmptyChilds(o[key]));
		}
		return _;
	};

	RVS.F.convertFraction = function(st) {

		var exp,
			tempExp;

		if(st.search('/') !== -1) {
			tempExp = st.split('/');
			if(tempExp.length === 2) exp = parseInt(tempExp[0], 10) / parseInt(tempExp[1], 10);
		}

		return exp || 1 / 3;

	};

	RVS.F.openPreivew = function(_) {
		RVS.preview = RVS.preview===undefined ? {selectedSize:"d",inited:false, c: jQuery('#rbm_preview')} : RVS.preview;
		jQuery('#wpwrap').addClass("inRS_RSpreview");

		document.getElementById('rbm_preview_moduletitle').innerHTML = _.title;
		document.getElementById('copy_shortcode_from_preview').value = '{{block class="Nwdthemes\\Revslider\\Block\\Revslider" alias="'+_.alias+'"}}';
		RVS.preview.open = true;
		if (!RVS.preview.inited) {
			RVS.preview.inited = true;
			RVS.DOC.on('click','.rbm_prev_size_sel',function() {
				jQuery('.rbm_prev_size_sel.selected').removeClass("selected");
				this.className +=" selected";
				RVS.preview.selectedSize = this.dataset.ref;
				RVS.F.updatePreviewSize();
			});
			RVS.DOC.on('click','#rbm_preview .rbm_close',function() {
				jQuery('#wpwrap').removeClass("inRS_RSpreview");
				RVS.F.RSDialog.close();
				RVS.preview.open = false;
				document.getElementById('rbm_preview_live').innerHTML = "";
			});
			RVS.F.initCopyClipboard('.copypreviewshortcode');
			RVS.DOC.on('windowresized',function() {
				if (RVS.preview.open)  RVS.F.updatePreviewSize();
			});
		}
		RVS.preview.iframe = document.createElement('iframe');
		document.getElementById('rbm_preview_live').appendChild(RVS.preview.iframe);

		RVS.F.RSDialog.create({modalid:'rbm_preview', bgopacity:0.85});
		var pars = {id:_.id}
		if (_.mode==="editor") pars.data = RVS.F.getAllSliderDatas();


		RVS.F.ajaxRequest('preview_slider',pars,function(response) {
			RVS.preview.sizes = response.size;
			RVS.preview.iframe.contentWindow.document.open();
			RVS.preview.iframe.contentWindow.document.write(response.html);
			RVS.preview.iframe.contentWindow.document.close();
			RVS.F.updatePreviewSize();
		});

	}

	RVS.F.updatePreviewSize = function() {
		if (RVS.preview===undefined || RVS.preview.sizes===undefined || RVS.preview.sizes.width==undefined || RVS.preview.sizes.height==undefined) return;
		var rw = parseInt(RVS.preview.sizes.width[RVS.preview.selectedSize],0)-1,
			rh = parseInt(RVS.preview.sizes.height[RVS.preview.selectedSize],0);

		punchgs.TweenLite.set(RVS.preview.c,{width:Math.min(parseInt(RVS.ENV.glb_slizes.d,0),RVS.S.winw), height:Math.min((rh+50),RVS.S.winh)});
		punchgs.TweenLite.set(RVS.preview.iframe,{maxHeight:"100%",maxWidth:"100%",margin:"auto",position:"relative",left:"50%",x:"-50%"});
		RVS.preview.iframe.width = Math.min(rw,RVS.S.winw);
		RVS.preview.iframe.height = rh;
		RVS.F.RSDialog.center();
	}

    RVS.F.safeExtend = function() {
        if (arguments.length == 3 && arguments[0] === true) {
            return RVS.F.extendObject(arguments[1], arguments[2]);
        }
        return jQuery.extend.apply(this, arguments);
    };

    RVS.F.extendObject = function(obj1, obj2) {
        return RVS.F.cleanExtendedObjet(RVS.F.safeExtend(true, {}, obj1, obj2), obj1, obj2);
    };

    RVS.F.cleanExtendedObjet = function(obj, obj1, obj2) {
        for (var key in obj) {
            if (
                ! obj.hasOwnProperty(key)
                || (typeof obj1 != 'undefined' && ! obj1.hasOwnProperty(key) && typeof obj2 != 'undefined' && ! obj2.hasOwnProperty(key))
                || (typeof obj1 != 'undefined' && ! obj1.hasOwnProperty(key) && typeof obj2 == 'undefined')
                || (typeof obj2 != 'undefined' && ! obj2.hasOwnProperty(key) && typeof obj1 == 'undefined')
            ) {
                delete obj[key];
            } else if (typeof obj[key] == 'object' || typeof obj[key] == 'Array') {
                obj[key] = RVS.F.cleanExtendedObjet(
                    obj[key],
                    typeof obj1 != 'undefined' && typeof obj1[key] != 'undefined' ? obj1[key] : undefined,
                    typeof obj2 != 'undefined' && typeof obj2[key] != 'undefined' ? obj2[key] : undefined
                );
            }
        }
        return obj;
    };

})();
