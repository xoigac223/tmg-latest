/*!
 * REVOLUTION 6.0.0 EDITOR BUILDER JS
 * @version: 1.0 (01.07.2019)
 * @author ThemePunch
*/

RVS.S.nextscreen = "none";
RVS.S.prevscreen = "n";
RVS.S.uniqueIds = [];
RVS.S.uniqueId = 0;
RVS.S.selLayerTypes = {row:false, column:false, group:false, text:false, image:false, shape:false, object:false, button:false, audio:false, video:false, bottom:false, top:false, middle:false };
RVS.S.selElements = [];
RVS.selLayers = [];
RVS.screen = "d";
RVS.TL = {currentTime:0, c:{}};
RVS.C.rZone = {top:"", middle:"", bottom:""};
RVS.S.slideTrans = 0;

(function() {

	var redirectingtooverview,hideWPMENUTIMER;

	RVS.F.initAdmin();
	RVS.SLIDER = {};

	// LOAD THE BUILDER.PHP AND ALL ITS PARTS
	RVS.F.loadBuilder = function(_) {
		//SET URL TO THE RIGHT SLIDE ID
		var id =  (_!==undefined && _.id!==undefined) ? _.id : false;
		if (id!==false) RVS.F.setEditorUrl(id);
		RVS.V.ignoreAutoStart = true;
		if (jQuery('#builderView').length>0) jQuery('#builderView').remove();

		RVS.F.ajaxRequest('load_builder', {}, function(response) {
			jQuery('#wpbody').append(response.data);
			if (id!==false) RVS.F.loadSlider({id:id});
		});
	};

	// LOAD THE SLIDER OBJECTS
	RVS.F.loadSlider = function(_) {

		if (_.id!==undefined && _.id!=="" || _.alias!==undefined && _.alias!=="") {
			RVS.F.showWaitAMinute({fadeIn:0,text:RVS_LANG.loadingcontent});
			setTimeout(function() {

				RVS.F.ajaxRequest('get_full_slider_object', (_.alias!==undefined && _.alias!=="" ? {alias:_.alias} : {id:_.id}), function(response){

					if (response.id===undefined) {
						RVS.F.showWaitAMinute({fadeIn:500,text:RVS_LANG.redirectingtooverview});
						RVS.F.backToOverview();
					} else {
						RVS.F.showWaitAMinute({fadeIn:500,text:RVS_LANG.preparingdatas});
						RVS.SLIDER.id = response.id;
						RVS.ENV.sliderID = response.id;
						response.slider_params.alias = response.alias;
						response.slider_params.title = response.title;
			            //Init Slider SETTINGS
			            initSlider(response.slider_params);
			            for (var sindex in response.slides) {
							if(response.slides.hasOwnProperty(sindex)) {
								let slide = response.slides[sindex];
								slide.params = slide.params!==undefined && jQuery.isArray(slide.params) ? undefined : RVS.F.expandSlide(slide.params);
								initSingleSlide({slideid:slide.id,uid:slide.id, slide:slide.params,layers:slide.layers});
							}
			            }
			            initStaticLayers(response.static_slide);
			            init();
			            RVS.F.showWaitAMinute({fadeOut:500,text:RVS_LANG.preparingdatas});
			            if (response.slider_params.layout===undefined) RVS.F.openNewGuide();
			        }
		        },undefined,true);
		        RVS.F.showWaitAMinute({fadeOut:0,text:RVS_LANG.loadingcontent});
			},100);
	    }
	};

	RVS.F.addOnsBasics = function() {
		for (var i in RVS.LIB.ADDONS) {
			if(RVS.LIB.ADDONS.hasOwnProperty(i))
				RVS.SLIDER.settings.addOns[i] = RVS.SLIDER.settings.addOns[i]===undefined ? {enable:false} : RVS.SLIDER.settings.addOns[i];
		}
	};

	// INIT AND BUILD THE STATIC LAYER STRUCTURE
	var initStaticLayers = function(_) {
		var staticSettings = _.params !== undefined ? RVS.F.addSlideObj() : RVS.F.addSlideObj(_.slide);
		staticSettings.title = RVS_LANG.globalLayers;
		staticSettings.static.isstatic = true;
		if (_.params!==undefined && _.params.static!==undefined) {
			staticSettings.static.overflow = _.params.static.overflow===undefined ? "hidden" : _.params.static.overflow;
			staticSettings.static.position = _.params.static.position===undefined ? "front" : _.params.static.position;
			staticSettings.static.lastEdited = _.params.static.lastEdited===undefined ? "front" : _.params.static.lastEdited;
		}
		_.id= _.id===undefined ? RVS.ENV.sliderID : _.id;
		initSingleSlide({slideid:"static_"+_.id, uid:_.id, slide:staticSettings, layers:_.layers, order:999});
		RVS.SLIDER.staticSlideId = "static_"+_.id;
	},

	// INITIALISE SLIDES FROM DB
	initSingleSlide = function(_) {

		_.layers = _.layers===undefined ? {} : _.layers;
		_.slide = _.slide===undefined ? {} : _.slide;

		RVS.SLIDER.slideIDs = RVS.SLIDER.slideIDs===undefined ? [] :  RVS.SLIDER.slideIDs;

		// Create New Slide Object
		var newSlide = {slide:RVS.F.addSlideObj(_.slide), layers:{}, id:_.uid};

		// Add Layers to Slide Object
		for (var layerIndex in _.layers) {
			if(_.layers.hasOwnProperty(layerIndex)) {
				var layerObj = _.layers[layerIndex],
					newLayer = RVS.F.addLayerObj(RVS.F.safeExtend(true,RVS.F.addLayerObj(layerObj.type,undefined,false,true), layerObj));

				if (newLayer.type==="zone" && (newLayer.uid!=="bottom" && newLayer.uid!=="middle" && newLayer.uid!=="top")) {
					//Update Bug from Old version, Layer can be ignored !
				} else {
					if (newLayer) newSlide.layers[newLayer.uid] = newLayer;
				}
			}
		}
		// Push Slide Object to Slider Array
		RVS.SLIDER[_.slideid] = newSlide;
		RVS.SLIDER.slideIDs.push(_.slideid);
		RVS.S.slideId = _.slideid;
		RVS.F.addToSlideList({id:_.slideid});
	},


	// INITIALISE SLIDER PARAMETERS FROM DB
	initSlider = function(initParams) {
		//var initParams = jQuery.parseJSON(jsonSlider);
		RVS.SLIDER.settings = RVS.F.setSlider(initParams);
	},


	// INITIALISATION OF THE BUILDER
	init = function() {

		// GENERATE NOT GENERATED META DATAS LIKE IMAGES
		RVS.F.generateAttachmentMetaData();
		RVS.C.vW = jQuery('#builderView');
		RVS.C.rb = jQuery('#rev_builder');
		RVS.C.rb_tlw = jQuery('#rb_tlw');
		RVS.S.slideId = RVS.F.getEditorUrl();
		if (""+RVS.S.slideId.indexOf('slider-')>=0) {
			RVS.S.slideId = RVS.SLIDER.slideIDs[0];
			if (""+RVS.S.slideId.indexOf('static_')>0 && RVS.SLIDER.slideIDs.length>1)
				RVS.S.slideId = RVS.SLIDER.slideIDs[1];
		}

		//INIT GLOBAL LISTENERS
		globalListeners();

		//INIT NAVIGATION STYLE LIST
		RVS.F.initNavigation();

		//Start Basic Input Box and Select Box Listeners
		RVS.F.initialiseInputBoxes();
		//Start Slider Builder
		RVS.F.initSliderBuilder();

		//Init the TimeLine
		RVS.F.initTimeLineModules();
		RVS.F.initTimeLineConstruct();

		//Start Slide Builder
		RVS.F.initSlideBuilder();



		//Start Slide Builder
		RVS.F.initLayerTools();
		RVS.F.initLayerActions();
		RVS.F.initLayerBuilder();
		RVS.F.initLayerListBuilder();
		RVS.F.initQuickStyle();

		//Update Input Boxes for First Load
		RVS.F.updateInputBoxes();

		//Init OnOff Buttons
		RVS.F.initOnOff();

		//Init Switch Buttons
		RVS.F.switchButtonInit();

		//Init Hover And Selects
		initHoversAndSelects();

		// First Time Update Collectors
		jQuery('.form_collector').each(function(i) {
			var fc = jQuery(this);
			if (fc.attr('id')===undefined)
				fc.attr('id','form_collector_id_'+i);
		});

		RVS.F.fixTools();
		punchgs.TweenLite.fromTo(jQuery('#builderView'),0.001,{x:"100%"},{x:"0%",ease:punchgs.Power3.easeInOut});

		setTimeout(function() {

			//OPEN WITH SLIDE SETTINGS
			//RVS.F.mainMode({mode:"slidelayout", forms:["#form_slidebg"], set:true, slide:RVS.S.slideId, uncollapse:true});


			//OPEN WITH NAVIGATION
			//RVS.F.mainMode({mode:"slidelayout", forms:["*navlayout*#form_nav_arrows"], set:true, uncollapse:true,slide:RVS.S.slideId});

			//OPEN WITH SLIDERLAYOUT
			//RVS.F.mainMode({mode:"sliderlayout", forms:["*sliderlayout*#form_module_title"], set:true, uncollapse:true,slide:RVS.S.slideId});

			//OPEN WITH LAYER SETTINGS
			//RVS.F.mainMode({mode:"slidelayout", forms:["*slidelayout**mode__slidecontent*#form_layer_style"], set:true, uncollapse:true,slide:RVS.S.slideId});
			RVS.F.mainMode({mode:"slidelayout", forms:["*slidelayout**mode__slidecontent*#form_layer_content"], set:true, uncollapse:true,slide:RVS.S.slideId});

			// OPEN WITH SLIDE SETTINGS
			//RVS.F.mainMode({mode:"slidelayout", forms:["*slidelayout**mode__slidestyle*#form_slidebg"], set:true, uncollapse:true,slide:RVS.S.slideId});
			//RVS.F.mainMode({mode:"slidelayout", forms:["*slidelayout**mode__slidestyle*#form_slide_revslider-beforeafter-addon"], set:true, uncollapse:true,slide:RVS.S.slideId});
			//RVS.F.mainMode({mode:"slidelayout", forms:["*slidelayout**mode__slidestyle*#form_slide_progress"], set:true, uncollapse:true,slide:RVS.S.slideId});

			// OPEN SLIDER SETTINGS AND ADDON
			//RVS.F.mainMode({mode:"sliderlayout", forms:["*sliderlayout*#form_module_revslider-beforeafter-addon"], set:true, uncollapse:true,slide:RVS.S.slideId});


			RVS.F.mainMode({mode:"sliderlayout", forms:["*sliderlayout*#form_module_title"], set:true, uncollapse:true,slide:RVS.S.slideId});

			jQuery('body.rs-builder-mode').addClass('hideallwp');
			//Listen to Window Position and Inherit changes
			UIPresetHandling();
			UICTRLUpdate();


			//if (location.href.indexOf("id=668")>=0)
			//	RVS.F.openNavigationEditor();
			// RVS.F.mainMode({mode:"slidelayout", forms:["*navlayout*#form_nav_arrows"], set:true, uncollapse:true,slide:RVS.S.slideId});
			//	RVS.F.openObjectLibrary({types:["modules"],filter:"all", selected:["modules"], context:"editor", depth:"layers", success:{slide:"addImportedLayers"}});


		},1);

	// Drop Function for Images and Videos
	/*	jQuery('#rev_slider_ul').on('dragenter drop', function(e) {
			console.log(e);
			e.preventDefault();
			e.stopPropagation();
		 }).on('dragenter', function(e) {
			jQuery('#rev_slider_ul').className="rs-dragover-content";
		 })
		 .on('dragleave dragend drop', function() {
			jQuery('#rev_slider_ul').className="";
		 })
		 .on('drop', function(e) {
			jQuery('#rev_slider_ul').className="rs-drag-is-processing";
			jQuery('#importing_processing_files').html('');
			for (var i in e.originalEvent.dataTransfer.files) {
				if(!e.originalEvent.dataTransfer.files.hasOwnProperty(i)) continue;
				if (jQuery.type(e.originalEvent.dataTransfer.files[i])=="object") {
					var txt = e.originalEvent.dataTransfer.files[i].name+" ("+Math.round(e.originalEvent.dataTransfer.files[i].size/1024)+"kb)";
					jQuery('#importing_processing_files').append('<div id="fileprocessing_'+i+'" class="filedrop_line_2">'+txt+'<i class="material-icons fileupload_status"></i><span class="fileupload_message"></span></div>');
				}
			}
			//RVS.F.uploadFiles({form:form,files:e.originalEvent.dataTransfer.files,fileindex:0,report:'#fileprocessing_',success:_.success});
		 });*/

	};

	/*_.textblock = '<div id="filedrop">';
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
	*/

	RVS.F.fixTools = function() {
		// First Time Update Collectors
		jQuery('.form_collector').each(function(i) {
			var fc = jQuery(this);
			if (!fc.hasClass("__inmodal")) fc.appendTo(jQuery('#the_right_toolbar_inner'));
		});
	};




	/***************************

		- INTERNAL FUNCTIONS -

	***************************/

	/*
	UPDATE CTRL AND CMD
	*/
	function UICTRLUpdate() {
		if (RVS.F.os() === "MacOS") {
			jQuery('.shortcut_cmdctrl').html('⌘');
			jQuery('.shortcuttext').addClass("osx");
		}
	}

	/*
	SELECT AN ELEMENT OR JQUERY ELEMENT: MARK IT AS SELECTED AND ADD IT TO SELECTED ARRAY
	*/
	function selectElement(_) {
		// _.element - javascript element
		// _.jelement - jQuery element
		// _.id - ID of Element



		if (_===undefined || typeof _!="object" || (_.element===undefined && _.jelement===undefined && _.id===undefined)) return false;
		if (_.id!==undefined) _.jelement = jQuery('#'+_.id);
		if (_.element===undefined) _.element=_.jelement[0];
		if (_.jelement===undefined) _.jelement = jQuery(_.element);
		if (_.id===undefined) _.id = _.element.id;

		if (_.jelement===undefined) return;

		deselectAllElement(_.element.id);

		RVS.S.selElements = [];
		RVS.S.selElements.push({
					jobj : _.jelement,
					multiplemark: _.element.dataset.multiplemark,
					forms:_.jelement.data('forms'),
					id:_.element.id,

			});

		_.jelement.addClass("marked");

	}



	function UIPresetHandling() {
		jQuery('#ui_preset_toggle').click(function() {
			var _ = jQuery(this);

			if (_.hasClass("windowmode")) {
				_.removeClass("windowmode");
				RVS.F.fixTools();
			} else {
				_.addClass("windowmode");
				RVS.F.releaseDarkTools();
			}
		});
	}


	/*
	GLOBAL LISTENERS
	*/
	function globalListeners() {
		// LISTENES FOR REVERT INPUT BOXES AFTER SNAPSHOT REGENERATED
		RVS.DOC.on('revertEasyInputs',function(e,ep) {
			RVS.F.updateEasyInputs({container:ep,trigger:"init",path:"settings."});
		});

	}


	/*
	DESELECT ALL ELEMENTS EXCEPT THE ELEMENT WITH THE ID ignore_id
	*/
	function deselectAllElement(ignore_id) {

		var tempselected = [];
		for (var el in RVS.S.selElements) if (RVS.S.selElements.hasOwnProperty(el)) {
			if (RVS.S.selElements[el].id === ignore_id) {
				tempselected.push(RVS.S.selElements[el]);
			}
			else
				RVS.S.selElements[el].jobj.removeClass("marked");
		}
		RVS.S.selElements=tempselected;
		RVS.DOC.trigger('cursorselection');

		RVS.F.selectLayers({ignoreModeChange:true, overwrite:true});
	}






	/*
	INITIALISE THE HOVER AND CLICK FUNCTIONS ON ELEMENTS
	*/
	function initHoversAndSelects() {

		jQuery('#undoredowrap').RSScroll({
				wheelPropagation:false,
				suppressScrollX:true,
				minScrollbarLength:100
		});

		punchgs.TweenLite.set('#adminmenumain, #wpadminbar', {opacity:0});

		window.onbeforeunload = function (e) {
			RVS.F.showWaitAMinute({fadeIn:500,text:redirectingtooverview ? RVS_LANG.redirectingtooverview : RVS_LANG.leavingpage});
			if (RVS.S.need_to_save) {
				redirectingtooverview = false;
				RVS.F.showWaitAMinute({fadeOut:500});
				var e = e || window.event;

			    // For IE and Firefox
			    if (e) {
			        e.returnValue = RVS_LANG.leaving;
			    }

			    // For Safari
			    return RVS_LANG.leaving;
			}
		};

		RVS.DOC.on('enablePXModule',function(e,p) {
			if (p===undefined) return;
			if ((p==="slideparallax" && RVS.SLIDER.settings.parallax.set!==true && RVS.SLIDER[RVS.S.slideId].slide.effects.parallax!=='-') ||
			    (typeof p =="object" &&  RVS.L[p.layerid].effects.parallax!=='-' && RVS.SLIDER.settings.parallax.set!==true)) {
				RVS.SLIDER.settings.parallax.set=true;
				RVS.F.updateEasyInputs({container:jQuery('#form_slidergeneral_effects_scroll'), trigger:"init", visualUpdate:true});
				RVS.F.showInfo({content:RVS_LANG.parallaxsettoenabled, type:"goodtoknow", showdelay:0, hidedelay:2, hideon:"", event:"" });
			}
		});

		RVS.DOC.on('enableScrollEffectModule',function(e,p) {
			if (p===undefined) return;
			if ((typeof p !="object" && (p==="fade" || p==="blur" || p==="grayscale") && RVS.SLIDER.settings.parallax.set!==true && RVS.SLIDER[RVS.S.slideId].slide.effects[p]=='true') ||
				(typeof p =="object" && p.layerid==undefined && p.val!==undefined && p.val=='true' && RVS.SLIDER.settings.scrolleffects.set!==true) ||
				(typeof p =="object" && p.layerid!==undefined && RVS.L[p.layerid].effects.effect=='true' && RVS.SLIDER.settings.scrolleffects.set!==true)) {
				RVS.SLIDER.settings.scrolleffects.set=true;
				RVS.F.updateEasyInputs({container:jQuery('#form_slidergeneral_effects_scroll'), trigger:"init", visualUpdate:true});
				RVS.F.showInfo({content:RVS_LANG.feffectscrollsettoenabled, type:"goodtoknow", showdelay:0, hidedelay:2, hideon:"", event:"" });
			}
		});

		RVS.DOC.on('enableScrollModule',function(e,p) {
			if (p===undefined || p.layerid===undefined) return;
			if (RVS.L[p.layerid].timeline.scrollBased=='true' && RVS.SLIDER.settings.scrolltimeline.set!==true) {
				RVS.SLIDER.settings.scrolltimeline.set=true;
				RVS.F.updateEasyInputs({container:jQuery('#form_slidergeneral_effects_scroll'), trigger:"init", visualUpdate:true});
				RVS.F.showInfo({content:RVS_LANG.timelinescrollsettoenabled, type:"goodtoknow", showdelay:0, hidedelay:2, hideon:"", event:"" });
			}
			if (RVS.SLIDER.settings.scrolltimeline.set===true && (RVS.L[p.layerid].timeline.scrollBased=='true' ||  (RVS.L[p.layerid].timeline.scrollBased=='default' && RVS.SLIDER.settings.scrolltimeline.layers===true))) {
				// Disable Loop Animation on Layer
				RVS.L[p.layerid].timeline.loop.use = false;
				RVS.F.updateEasyInputs({container:jQuery('#layer_looping_wrap'), trigger:"init", visualUpdate:true});
				RVS.F.showInfo({content:RVS_LANG.layerloopdisabledduetimeline, type:"goodtoknow", showdelay:0, hidedelay:2, hideon:"", event:"" });
			}
		});


		RVS.DOC.on('click','.action_collection_wrap',function() {
			jQuery(this).toggleClass("showmore");
		});

		RVS.DOC.on('click','#rb_editor_logo',function() {
			clearTimeout(hideWPMENUTIMER);
			jQuery('.rs-builder-mode.hideallwp').addClass("showwpmenus");
			jQuery('.menu-wrapper').addClass('show_magento_menu');
			punchgs.TweenLite.to('.menu-wrapper', 0.5,{opacity:1});
		});

		RVS.DOC.on('mouseenter','.menu-wrapper',function() {
			clearTimeout(hideWPMENUTIMER);
			jQuery('.rs-builder-mode.hideallwp').addClass("showwpmenus");
			punchgs.TweenLite.to('.menu-wrapper', 0.5,{opacity:1});
		});

		RVS.DOC.on('mouseleave','.menu-wrapper',function() {
			hideWPMENUTIMER = setTimeout(function() {
				punchgs.TweenLite.to('.menu-wrapper', 0.1,{opacity:0,onComplete:function() {
					jQuery('.rs-builder-mode.hideallwp').removeClass("showwpmenus");
					jQuery('.menu-wrapper').removeClass('show_magento_menu');
				}});
			},200);

		});

		RVS.DOC.on('mouseleave','.action_collection_wrap',function() {
			jQuery(this).removeClass("showmore");
		});

		// MOUSE ENTER/LEAVE THE SELECTABLE AND MARKABLE ELEMENTS
		RVS.DOC.on('mouseover','.aable',function(e) {
			jQuery('.aable.hovered').removeClass("hovered");
			var jtoE = jQuery(e.toElement);
			if (jtoE.hasClass("aable"))
				jtoE.addClass("aable").addClass('hovered');
			else
				jtoE.closest('.aable').addClass('hovered');
		});
		RVS.DOC.on('mouseleave','.aable',function(e) {
			var jt = jQuery(this);
			if (jt.hasClass("aable"))
				jt.removeClass("hovered");
			else
				jt.closest('.aable').removeClass("hovered");
		});

		// SELECT ELEMENT WHICH IS MARKABLE ON CLICK
		RVS.DOC.on('click','.markable',function(){
			if (RVS.S.justresized) return;
			//selectElement({element:this});
			selectElement({id:this.id});
			RVS.F.openSettings({forms:jQuery(this).data('forms'), uncollapse:this.dataset.collapse});
			return false;
		});



		// SLIDE SELECTOR
		RVS.DOC.on('click','.slide_list_element, .slide_list_child_element',function() {
			var wasstatic = RVS.SLIDER[RVS.S.slideId].slide.static.isstatic;
			RVS.S.lastShownSlideId = RVS.S.slideId;
			var slidestyle = RVS.C.vW.hasClass("mode__slidestyle");
			RVS.F.mainMode({mode:"slidelayout",slide:this.dataset.ref});
			if (slidestyle)	{
				RVS.DOC.trigger('changeToSlideMode');
				if (!RVS.SLIDER[RVS.S.slideId].slide.static.isstatic && wasstatic) {
					jQuery('.slide_submodule_trigger.selected').removeClass("selected");
					RVS.F.showForms('#form_slidebg',true);
				}
			}
			else
				RVS.DOC.trigger('changeToLayerMode');
			return false;
		});

		//CALL EVENT BUTTON
		RVS.DOC.on('click','.callEventButton',function() {
			if (this.dataset.evt!==undefined)
				RVS.DOC.trigger(this.dataset.evt,this.dataset.evtparam);

		});
		jQuery('#back_to_overview').on('click',function() {
			RVS.F.setCookie("rs6_shortly_edited_slider",RVS.ENV.sliderID,0.0001700);
			redirectingtooverview = true;
			if (!RVS.S.need_to_save) RVS.F.showWaitAMinute({fadeIn:500,text:RVS_LANG.redirectingtooverview});
			RVS.F.backToOverview();
		});

		// REDO / UNDO
		RVS.DOC.on('click','#undo, #undo_redo_wrap',function() {
				RVS.F.undo({step:1});
		});
		RVS.DOC.on('click','#redo',function() {
				RVS.F.redo({step:1});
		});

		RVS.DOC.on('click','.undoredostep',function() {
			if (this.parentElement.id==="redolist")
				RVS.F.redo({step:(parseInt(jQuery(this).index(),0)+1)});
			else
				RVS.F.undo({step:(jQuery('#undolist li').length - jQuery(this).index()-1)});
		});

		RVS.DOC.on('click','#noactiondone_undo',function() {
			RVS.F.undo({step:(jQuery('#undolist li').length)});
		});


		// KEYBOARD LISTENERS FOR UNDO / REDO
		RVS.DOC.on('keydown',function(e) {
			if ((RVS.S.inFocus==="none" || RVS.S.inFocus===undefined)) {
				if ((RVS.S.OSName==="MacOS" && e.metaKey && !e.ctrlKey) || e.ctrlKey) {
					switch (e.keyCode) {
						case 83: // s
							e.preventDefault();
							RVS.DOC.trigger("saveslider");
							return false;

						case 90: // z
							RVS.F.undo({step:1});
							return false;

						case 89: // y
							RVS.F.redo({step:1});
							return false;

					}
				}
			}
		});




		// ACCEPT SETTINGS WITHIN A MODULAR WINDOW
		RVS.DOC.on('click','.close_and_accept',function() {
			var fc = jQuery(this.closest('.form_collector'));
			fc.hide();
			if (fc.data('underlay')!==undefined) {
				jQuery(fc.data('underlay')).hide();
				punchgs.TweenLite.set('#the_container',{filter:'none'});
			}
			if (this.dataset.evt!==undefined)
				RVS.DOC.trigger(this.dataset.evt,this.dataset.evtparam);
		});

		RVS.DOC.on('mouseover','.callhoverevt',function(e) {
			if (this.dataset.hoverevt!==undefined)
				RVS.DOC.trigger(this.dataset.hoverevt,this.dataset.hoverevtparam);
		});

		RVS.DOC.on('mouseleave','.callhoverevt',function(e) {
			if (this.dataset.leaveevt!==undefined) {
				RVS.DOC.trigger(this.dataset.leaveevt,this.dataset.leaveevtparam);
			}
		});

		RVS.DOC.on('mouseover','#ruler_top, #ruler_left',function(e) {
			RVS.S.builderHover="overruler";
			return false;
		});

		RVS.DOC.on('mouseover','#timeline_settings',function(e) {
			RVS.S.builderHover="overtimeline";
			return false;
		});

		RVS.DOC.on('mouseover','#rev_builder_inner',function(e) {
			RVS.S.builderHover="overbuilder";
			return false;
		});
		RVS.DOC.on('mouseleave','#rev_builder_wrapper',function(e){
			RVS.S.builderHover=false;
			RVS.F.setRulerMarkers();
			return false;
		});

		RVS.DOC.on('mouseleave','#the_right_toolbar_inner',function(e){
			RVS.S.builderHover=false;
			RVS.F.setRulerMarkers();
			return false;
		});

		RVS.DOC.on('click','#save_slider',function() {
			RVS.F.convertIDStoTxt();
			RVS.F.convertArrayToObjects();
			RVS.DOC.trigger("saveslider");
		});

		RVS.DOC.on('click','#preview_slider',function() {
			RVS.F.openPreivew({title:RVS.SLIDER.settings.title,alias:RVS.SLIDER.settings.alias, id:RVS.SLIDER.id, mode:this.dataset.mode});
		});

		RVS.DOC.on('saveslider',function() {
			RVS.F.saveSlides({index:0,slides:RVS.SLIDER.slideIDs, trigger:RVS.F.saveSliderSettings,works:RVS.SLIDER.inWork});
		});


	}

})();
