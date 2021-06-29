/*!
 * REVOLUTION 6.0.0 OVERVIEW JS
 * @version: 1.0 (01.07.2019)
 * @author ThemePunch
*/

define(
    'overview',
    ['admin', 'revolutionTools', 'rs6'],
    function(RVS, punchgs) {

;function showPluginInfos() {
			//jQuery('.plugin_inforow').each(function(i){

			//});
		}
/**********************************
	-	OVERVIEW FUNCTIONS	-
********************************/

(function() {

	// INIT OVERVIEW
	RVS.F.initOverView = function() {
		RVS.F.initAdmin();
		RVS.C.rsOVM = jQuery('#rs_overview_menu');
		//jQuery('.update-nag').hide();
		RVS.S.ovMode = true;
		RVS.F.initialiseInputBoxes();
		//RVS.F.initialiseGlobalBoxes();
		initLocalListeners();
		initHistory();
		sliderLibrary.output = jQuery('#existing_sliders');
		sliderLibrary.sfw = jQuery('#slider_folders_wrap');
		sliderLibrary.sfwu = jQuery('#slider_folders_wrap_underlay');
		sliderLibrary.backOneLevel = jQuery('<div id="back_one_folder" class="new_slider_block"><i class="material-icons">more_horiz</i><span class="nsb_title">Back</span></div>');
		sliderLibrary.selectedFolder = -1;
		sliderLibrary.selectedPage = 1;
		updateParentAttributes();
		sliderLibrary.filters = buildModuleFilters();
		initOverViewMenu();
		RVS.F.updateDraw();
		RVS.F.isActivated();
		updateOVFilteredList();
		updateSysChecks();
		initBasics();

		// VERSION ACTIVATED, SHOW WELCOME MESSAGE
		if (RVS.ENV.updated) RVS.F.welcome();

		//Load and Regeenrate Missing Images imported by Slider Revolution into the Media Library
		RVS.F.generateAttachmentMetaData();
		checkAddOnVersions();

		// CHECK IF EDITOR WAS OPEN IN THE LAST 15sec AND OPEN FOLDER IF NEEDED
		var ses = RVS.F.getCookie("rs6_shortly_edited_slider")+"";
		if (ses!==undefined && ses.length>0) {

			RVS.F.setCookie("rs6_shortly_edited_slider","",0);
			var folder = false;
			for (var i in sliderLibrary.sliders) if (sliderLibrary.sliders.hasOwnProperty(i)) {
				if (folder!==false) continue;
				folder = sliderLibrary.sliders[i].id==ses ? sliderLibrary.sliders[i].parent : folder;
			}
			if (folder!==false && folder!==-1 && folder!=='-1') RVS.F.changeOVToFolder(folder);
		}

		//RVS.F.openAddonModal();
		RVS.F.notifications();
	};

	RVS.F.getBackupList = function() {
		RVS.F.ajaxRequest('get_v5_slider_list', {}, function(response){
			if (response.success) {
				console.log(response.slider);
			} else {
				console.log("Response Error")
			}
		},false,false,undefined,true);
		return "Getting Slide List from Backup Database...";
	}

	RVS.F.reImportBackup = function(id) {

		RVS.F.ajaxRequest('reimport_v5_slider', {id:id}, function(response){
			console.log(response);
		},false,false,undefined,true);
		return "Importing Slider "+id+" from the Backup Database...";
	}


	// NOTIFCATION MESSAGES IN CASE THERE ARE ANY !
	RVS.F.notifications = function() {
		var list = {0:"", 1:"", 2:""},
			highest = 2,
			notifier = jQuery('#rso_menu_notices'),
			nw = document.getElementById('rs_notices_wrapper'),
			bell = document.getElementById('rs_notice_bell'),
			bellcounter = document.getElementById('rs_notice_counter'),
			thebell = document.getElementById('rs_notice_the_bell'),
			dismiscodes = new Array();


		nw.innerHTML = "";

		RVS.ENV.notices = RVS.ENV.notices===undefined ? new Array() : RVS.ENV.notices;
		RVS.ENV.noticeCache = RVS.ENV.noticeCache===undefined ? RVS.ENV.notices.slice() : RVS.ENV.noticeCache;

		RVS.ENV.notices = RVS.ENV.noticeCache.slice();

		// NOT REGSITERED WARNINGS
		if (RVS.ENV.activated!=="true" && RVS.ENV.activated!==true) {
			RVS.ENV.notices.push({function:"registerPlugin", additional:[], code:"INTERN", disable:true, icon:"vpn_key", is_global:false, text:RVS_LANG.notRegistered, type:1});
			RVS.ENV.notices.push({function:"registerPlugin", additional:[], code:"INTERN", disable:true, icon:"style", is_global:false, text:RVS_LANG.notRegNoAll, type:1});
		}

		//ADDONS MUST BE UPDATED
		if (RVS.ENV.addOns_to_updateArray!==undefined && RVS.ENV.addOns_to_updateArray.length>0) RVS.ENV.notices.push({function:"checkAddOnVersions", additional:[], code:"INTERN", disable:true, icon:"extension", is_global:false, text:RVS_LANG.addonsmustbeupdated, type:0});

		//UPDATE PLUGIN, NEW VERSION AVAILABLE
		if (RVS.F.compareVersion(RVS.ENV.latest_version, RVS.ENV.revision) > 0) RVS.ENV.notices.push({function:"updatePluginNow", additional:[], code:"INTERN", disable:true, icon:"new_releases", is_global:false, text:RVS_LANG.newVersionAvailable, type:1});

		//ANY ADDON TO UPDATE ?
		var found = false;

		for (var i in RVS.LIB.ADDONS) if (RVS.LIB.ADDONS.hasOwnProperty(i)) {
			if (found) continue;
			if (RVS.LIB.ADDONS[i].available>RVS.LIB.ADDONS[i].installed) {
				found=true;
				RVS.ENV.notices.push({additional:[], code:"INTERN", disable:true, icon:"extension", is_global:false, text:RVS_LANG.someAddonnewVersionAvailable, type:1});
			}
		}
		var count = 0;
		//BUILD THE NOTICES
		for (var i in RVS.ENV.notices) if (RVS.ENV.notices.hasOwnProperty(i)) {
			if (RVS.ENV.notices[i].type!=="3") count++;
			if (RVS.ENV.notices[i].type==="2") dismiscodes.push(RVS.ENV.notices[i].code)
			if (RVS.ENV.notices[i].type==="3") {
				RVS.S.advert = RVS.F.safeExtend({},true,RVS.ENV.notices[i]);
				continue;
			}
			var func = RVS.ENV.notices[i].function!==undefined && RVS.ENV.notices[i].function.length>0? 'notification_function_'+RVS.ENV.notices[i].function : 'no_notification_function';
			list[RVS.ENV.notices[i].type] += '<li data-code="'+RVS.ENV.notices[i].code+'" class="'+func+' notice_level_'+RVS.ENV.notices[i].type+'"><i class="material-icons">'+RVS.ENV.notices[i].icon+'</i>'+RVS.ENV.notices[i].text+'</li>'
			highest = highest>parseInt(RVS.ENV.notices[i].type) ? parseInt(RVS.ENV.notices[i].type) : highest;

		}
		if (count>0) {
			notifier.show();
			if (list[0].length>0) nw.innerHTML += list[0];
			if (list[1].length>0) nw.innerHTML += list[1];
			if (list[2].length>0) nw.innerHTML += list[2];
			bell.classList.remove("notice_level_1");
			bell.classList.remove("notice_level_2");
			bell.classList.remove("notice_level_3");

			bellcounter.classList.remove("notice_level_1");
			bellcounter.classList.remove("notice_level_2");
			bellcounter.classList.remove("notice_level_3");

			bell.className += " notice_level_"+highest;
			bellcounter.className += " notice_level_"+highest;

			bellcounter.innerHTML = count;

			// ADD DISMISS BUTTON IF NEEDED
			if (nw.innerHTML.length>0 && dismiscodes.length>0) nw.innerHTML += '<li id="remove_notifications" class="notice_level_2"><i class="material-icons">close</i>'+RVS_LANG.dismissmessages+'</li>';

			// CHECK ADDON VERSIONS
			jQuery('.notification_function_checkAddOnVersions').click(checkAddOnVersions);

			// REGISTER PLUGIN
			jQuery('.notification_function_registerPlugin').click(function() {RVS.F.showRegisterSliderInfo();});

			// UPDATE PLUGIN OUT OF NOTIFICATIONS
			jQuery('.notification_function_updatePluginNow').click(function() {
				if (RVS.ENV.activated===true) {
					RVS.F.RSDialog.create({
						bgopacity:0.85,
						modalid:'rbm_decisionModal',
						icon:'update',
						title:RVS_LANG.updateplugin,
						maintext:RVS_LANG.areyousureupdateplugin,
						subtext:RVS_LANG.updatingtakes,
						do:{
							icon:"check_circle",
							text:RVS_LANG.updatenow,
							event: "updateThePlugin"
						},
						cancel:{
							icon:"cancel",
							text:RVS_LANG.cancel
						},
						swapbuttons:true
					});
				} else {
					RVS.F.showRegisterSliderInfo();
				}
			});

			// DISMISS MESSAGES
			jQuery('#remove_notifications').click(function() {RVS.F.ajaxRequest('dismiss_dynamic_notice', {id:dismiscodes}, function(response){},false,false,undefined,true);});

			if (RVS.S.noticesListener===undefined) {
				RVS.S.noticesListener  =true;
				var bellTL = new punchgs.TimelineMax({repeat:-1});
				punchgs.CustomWiggle.create("myWiggle", {
				  wiggles: 8,
				  type:"uniform",
				});

				bellTL.add(punchgs.TweenLite.to('#rs_notice_the_bell', 0.5, {
				  transformOrigin:"50% 0%",
				  x: 5,
				  rotationZ:10,
				  ease: "myWiggle",
				  onComplete:function() {
				  	thebell.innerHTML = "notifications";
				  },
				  onStart:function() {
				  	thebell.innerHTML = "notifications_active";
				  }
				}),2);
			}
		} else {
			notifier.hide();
		}

		// CHECK TYPE 3 (ADVERT) NOTICES
		if (RVS.S.advert!==undefined) {
			RVS.S.advert.id = "rs_advert_"+Math.round(Math.random()*10000000);
			jQuery('#rs_welcome_h3').after('<div style="display:block;position:relative;" id="'+RVS.S.advert.id+'"></div>');
			RVS.S.advert.container = document.getElementById(RVS.S.advert.id);
			RVS.S.advert.container.innerHTML = RVS.S.advert.text;
			RVS.S.advert.mwrap = RVS.S.advert.container.getElementsByTagName('RS-MODULE-WRAP');
			jQuery(RVS.S.advert.mwrap).append('<div id="rs_close_advert"><i class="material-icons">close</i>'+RVS_LANG.closeNews+'</div>');
			RVS.S.advert.moduleId = RVS.S.advert.container.getElementsByTagName('RS-MODULE')[0].id;
			RVS.S.advert.rsoptions = JSON.parse(RVS.S.advert.script);
			jQuery('#'+RVS.S.advert.moduleId).show().revolution(RVS.S.advert.rsoptions);

			punchgs.TweenLite.fromTo(jQuery('#rs_close_advert'),1,{opacity:0},{opacity:1,delay:2});
			punchgs.TweenLite.set(RVS.S.advert.mwrap,{boxShadow: "0px 0px 0px 0px rgba(0,0,0,0.2)"});
			punchgs.TweenLite.to(RVS.S.advert.mwrap,1,{boxShadow: "0px 0px 20px 10px rgba(0,0,0,0.2)",delay:2});

			jQuery('#rs_close_advert').click(function() {
				punchgs.TweenLite.to(RVS.S.advert.mwrap,1,{marginTop:0,marginBottom:0,overflow:"hidden",height:0,ease:punchgs.Power3.easeInOut,onComplete:function() {
					RVS.S.advert.container.innerHTML = "";
				}});
				punchgs.TweenLite.to(RVS.S.advert.container,1,{autoAlpha:0});
				var dismiscodes = new Array();
				dismiscodes.push(RVS.S.advert.code)
				RVS.F.ajaxRequest('dismiss_dynamic_notice', {id:dismiscodes}, function(response){},false,false,undefined,true);
			});
		}
	};

	RVS.F.welcome = function() {
		RVS.F.RSDialog.create({modalid:'rbm_welcomeModal', bgopacity:0.85});
		jQuery('#rbm_welcomeModal .rbm_close').click(RVS.F.RSDialog.close);
		if (RVS.ENV.activated)
			jQuery('#open_welcome_register_form').click(RVS.F.RSDialog.close);
		else
			jQuery('#open_welcome_register_form').click(RVS.F.showRegisterSliderInfo);
	}

	RVS.F.changeOVToFolder = function(folder) {
		sliderLibrary.selectedFolder = folder;
		resetAllOVFilters();
		updateOVFilteredList();
	}

	/*
	GET SLIDE INDEX
	*/
	RVS.F.getOVSliderIndex = function(id) {
		var ret = -1;
		//id = parseInt(id,0);
		for (var i in sliderLibrary.sliders) {
			if(!sliderLibrary.sliders.hasOwnProperty(i)) continue;
			if (sliderLibrary.sliders[i].id == id) ret = i;
		}
		return ret;
	};


	// CHECK IF UPDATED NEEDED
	RVS.F.updateDraw = function() {
		if (RVS.F.compareVersion(RVS.ENV.latest_version, RVS.ENV.revision) > 0){
			jQuery('#available_version_icon').addClass("warning");
			jQuery('#available_version_content').addClass("warning");
			//jQuery('#rso_menu_updatewarning').show();
		} else {
			jQuery('#available_version_icon').removeClass("warning");
			jQuery('#available_version_content').removeClass("warning");
			//jQuery('#rso_menu_updatewarning').hide();
		}
	};

	//REDRAW ACTIVATED ELEMENTS
	RVS.F.isActivated = function() {
		if (RVS.ENV.activated=="true" || RVS.ENV.activated==true) {
			jQuery('#rs_register_to_unlock').text(RVS_LANG.premium_features_unlocked);
			jQuery('#purchasekey').val(RVS.ENV.code);
			jQuery('#updateplugin').removeClass("halfdisabled").text(RVS_LANG.updateNow);
			jQuery('#activated_ornot_box').removeClass("not_activated").html('<i class="material-icons">done</i>'+RVS_LANG.registered);
			jQuery('#activateplugin').text(RVS_LANG.deregisterCode);
			jQuery('#purchasekey_wrap').addClass("activated");
			jQuery('.activate_to_unlock').hide();
		} else {
			jQuery('#rs_register_to_unlock').text(RVS_LANG.register_to_unlock);
			jQuery('#purchasekey').val();
			jQuery('#updateplugin').addClass("halfdisabled").text(RVS_LANG.activateToUpdate);
			jQuery('#activated_ornot_box').addClass("not_activated").html('<i class="material-icons">do_not_disturb</i>'+RVS_LANG.notRegisteredNow);
			jQuery('#activateplugin').text(RVS_LANG.registerCode);
			jQuery('#purchasekey_wrap').removeClass("activated");
			jQuery('.activate_to_unlock').show();
		}
		if(RVS.F.compareVersion(RVS.ENV.latest_version, RVS.ENV.revision) <= 0)
			jQuery('#updateplugin').hide()
		else
			jQuery('#updateplugin').show();
	};

	RVS.F.createNewFolder = function(_) {
		hideElementSubMenu({keepOverlay:false});
		var csfobj = _!==undefined && _.foldername!==undefined ? {title:_.foldername} : {};
		 RVS.F.ajaxRequest('create_slider_folder', csfobj, function(response){

		 	response.folder.parent = sliderLibrary.selectedFolder;
		 	if (sliderLibrary.selectedFolder!==-1) sliderLibrary.sliders[RVS.F.getOVSliderIndex(sliderLibrary.selectedFolder)].children.push(response.folder.id);

		 	if (response.success) sliderLibrary.sliders.push(response.folder);

		 	resetAllOVFilters();

		 	if (_!==undefined && _.enter) {
		 		sliderLibrary.selectedFolder = response.folder.id;
		 		sliderLibrary.filters = buildModuleFilters();
		 	} else {
		 		sliderLibrary.filters = buildModuleFilters();
		    	jQuery('#slider_id_'+response.folder.id).addClass("selected");
		    }

		    if (response.success && _!==undefined && _.callBack!==undefined) RVS.DOC.trigger(_.callBack,_.callBackParam);
        });
	};

	/*OPEND GLOABAL SETTINGS*/
	var  openGlobalSettings = function() {

		if (!window.initGlobalSettings) {
			RVS.F.initOnOff(jQuery('#rbm_globalsettings'));
			window.revbuilder = window.revbuilder===undefined ? {} : window.revbuilder;
			RVS.SLIDER = RVS.SLIDER===undefined ? {} : RVS.SLIDER;
			//LOAD GLOBAL AJAX OPTIONS
			RVS.F.ajaxRequest('get_global_settings', {}, function(response){
				if (response.success) {
					RVS.SLIDER.globals = getNewGlobalObject(response.global_settings);
					window.initGlobalSettings = true;
					RVS.F.updateEasyInputs({container:jQuery('#rbm_globalsettings'), path:"", trigger:"init"});
				}
			});

			//SAVE GLOBAL AJAX
			jQuery('#rbm_globalsettings_savebtn').off('click').on('click', function() {
				RVS.F.ajaxRequest('update_global_settings', {global_settings:RVS.SLIDER.globals}, function(response){
					RVS.F.RSDialog.close();
				});
			});


			//CALL RS DB CREATION
		}
		RVS.F.RSDialog.create({modalid:'rbm_globalsettings', bgopacity:0.85});
	},

	// RESET AND INIT 2NDARY FUNCTIONS
	initBasics = function() {
		jQuery('#newsletter_mail').val("");
		punchgs.TweenLite.set('.plugin_inforow',{autoAlpha:0});
		initFeatureSliders();
	},

	// INIT FEATURE SLIDERS
	initFeatureSliders = function() {
		jQuery(".feature_slider").each(function() {
			jQuery(this).show().revolution({
				jsFileLocation:RVS.ENV.plugin_url+"public/assets/js/",
				visibilityLevels:1240,
				gridwidth:400,
				gridheight:200,
				lazyType:"all",
				responsiveLevels:1240,
				disableProgressBar:"on",
				navigation: {
					onHoverStop:false
				},
				viewPort: {
					enable:true,
					visible_area:100,
					presize:false
				},
				fallbacks: {
					disableFocusListener:true,
					allowHTML5AutoPlayOnAndroid:true
				}
			});
		});
	},

	// DRAW AN OVERVIEW LIST WITH PRESELECTED FILTERS AND SIZES
	drawOVOverview = function(_) {
		_ = _ === undefined ? {noanimation:false} : _;

		var container = sliderLibrary.output.find('.overview_elements');

		container.find('.rsl_breadcrumb_wrap').remove();
		if (sliderLibrary.selectedFolder!==-1) {
			var bread = '<div class="rsl_breadcrumb_wrap">';
			bread += '<div class="rsl_breadcrumb" data-folderid="-1"><i class="material-icons">apps</i>'+RVS_LANG.simproot+'</div>';
			bread += '<i class="rsl_breadcrumb_div material-icons">keyboard_arrow_right</i>';
			var folderlist = '';
			var pd = sliderLibrary.selectedFolder;
			while (pd !== -1) {
				var sindex = RVS.F.getOVSliderIndex(pd);
				folderlist = '<div class="rsl_breadcrumb" data-folderid="'+pd+'"><i class="material-icons">folder_open</i>'+sliderLibrary.sliders[sindex].title+'</div>' + '<i class="rsl_breadcrumb_div material-icons">keyboard_arrow_right</i>' + folderlist;
				pd = sliderLibrary.sliders[sindex].parent || -1;
			}
			bread += folderlist;
			bread += '<div id="rsl_bread_selected" class="rsl_breadcrumb"></div>';
			bread += '</div>';
			container.append(bread);
		}

		if (sliderLibrary.selectedFolder!=-1)
			sliderLibrary.backOneLevel.appendTo(container);
		else
			sliderLibrary.backOneLevel.detach();

		var d = 0;
		for (var i in sliderLibrary.sliders) {
			if(!sliderLibrary.sliders.hasOwnProperty(i)) continue;
			var slideobj = sliderLibrary.sliders[i];
			if (sliderLibrary.pages===undefined || jQuery.inArray(slideobj.id,sliderLibrary.pages[sliderLibrary.selectedPage-1])>=0) {
				d++;
				if ( slideobj.ref!==undefined && slideobj.folder) slideobj.ref.remove();
				slideobj.ref = slideobj.ref===undefined || slideobj.folder ? buildOVElement(slideobj) : slideobj.ref;
				if (!_.noanimation)
				punchgs.TweenLite.fromTo(slideobj.ref,0.4,{autoAlpha:0,scale:0.8,transformOrigin:"50% 50%", force3D:true},{scale:1,autoAlpha:1,ease:punchgs.Power3.easeInOut,delay:d*0.02});
				slideobj.ref.appendTo(container);
				doOVDraggable(slideobj.ref);
			} else
			if (slideobj.ref!==undefined) slideobj.ref.detach();
		}
		overviewMenuScroll();
	},


	// BUILD ONE SINGLE ELEMENT IN THE OVERVIEW
	buildOVElement = function(_,withouttoolbar) {
		var folderclass = _.folder ? "folder_library_element" : "",
			imgobjunder = jQuery('<div class="image_container_underlay"></div>'),
			obj = !withouttoolbar ? jQuery('<div data-sliderid="'+_.id+'" id="slider_id_'+_.id+'" data-slideid="slide_id_'+_.slide_id+'" class="rs_library_element '+folderclass+'"><div class="rsle_footer"><input data-id="'+_.id+'" id="slider_title_'+_.slide_id+'" class="title_container" value="'+_.title+'""><i class="show_rsle material-icons">arrow_drop_down</i></div></div>')
					: jQuery('<div data-sliderid="'+_.id+'" class="folder_in_list rs_library_element '+folderclass+'"><div class="rsle_footer"><input class="title_container" value="'+_.title+'""><i class="show_rsle material-icons">keyboard_arrow_down</i></div></div>');

		// ADD IMAGE UNDERLAY
		obj.append(imgobjunder);

		// ADD TOOLBAR
		if (!withouttoolbar) {
			var toolbar = '<div class="rsle_tbar">',
				linkobj = _.folder ? jQuery('<div class="link_to_slideadmin enter_into_folder" data-folderid="'+_.id+'"></div>') : jQuery('<a class="link_to_slideadmin" data-title="'+_.title+'" href="'+RVS.ENV.admin_url+'?id='+_.slide_id+'"><i class="material-icons">edit</i></a>');
			toolbar += '<div class="rsle_tool embedslider" data-id="'+_.id+'"><i class="material-icons">add_to_queue</i><span class="rsle_ttitle">'+RVS_LANG.embed+'</span></div>';
			toolbar += '<div class="rsle_tool exportslider" data-id="'+_.id+'" ><i class="material-icons">file_download</i><span class="rsle_ttitle">'+RVS_LANG.export+'</span></div>';
			toolbar += '<div class="rsle_tool exporthtmlslider" data-id="'+_.id+'" ><i class="material-icons">code</i><span class="rsle_ttitle">'+RVS_LANG.exporthtml+'</span></div>';

			toolbar += '<div class="rsle_tool duplicateslider" data-id="'+_.id+'" ><i class="material-icons">content_copy</i><span class="rsle_ttitle">'+RVS_LANG.duplicate+'</span></div>';
			toolbar += '<div class="rsle_tool previewslider" data-title="'+_.title+'" data-id="'+_.id+'" ><i class="material-icons">search</i><span class="rsle_ttitle">'+RVS_LANG.preview+'</span></div>';
			toolbar += '<div class="rsle_tool tagsslider" data-id="'+_.id+'" ><i class="material-icons">local_offer</i><span class="rsle_ttitle">'+RVS_LANG.tags+'</span></div>';
			toolbar += '<div class="rsle_tool renameslider" data-id="'+_.id+'" ><i class="material-icons">title</i><span class="rsle_ttitle">'+RVS_LANG.rename+'</span></div>';
			toolbar += '<div class="rsle_tool deleteslider" data-id="'+_.id+'" ><i class="material-icons">delete</i><span class="rsle_ttitle">'+RVS_LANG.delete+'</span></div>';
			toolbar += '<div class="rsle_tool_tagwrap"><select data-id="'+_.id+'" id="tags_'+_.slide_id+'" class="elementtags searchbox" multiple="multiple" data-theme="blue">';


			// BUILD THE TAG LISTS IN THE ELEMENT
			for (var i in sliderLibrary.filters.tags) {
				if(!sliderLibrary.filters.tags.hasOwnProperty(i)) continue;
				var m = jQuery.inArray(sliderLibrary.filters.tags[i].toLowerCase(),_.tags)>=0 ? ' selected="selected" ' : "";
				toolbar += '<option '+m+'value="'+RVS.F.sanitize_input(sliderLibrary.filters.tags[i].toLowerCase())+'">'+RVS.F.sanitize_input(sliderLibrary.filters.tags[i])+'</option>';
			}

			toolbar += '</select></div></div>';
			toolbar = jQuery(toolbar);
			obj.append(linkobj);
			obj.append(toolbar);
			toolbar.find('.elementtags').select2RS({tags:true, tokenSeparators: [',', ' ']});
			toolbar.find('.elementfolders').select2RS({});
			if (!_.folder) obj.append('<div class="rsle_move_and_edit"></div>');
		}

		if (_.children && _.children.length>0) {
			var cleanchildren = [],
				exist = false;
			for (var i in _.children) {
				if(!_.children.hasOwnProperty(i)) continue;
				exist = false;
				for (var j in sliderLibrary.sliders) {
					if(!sliderLibrary.sliders.hasOwnProperty(j)) continue;
					if ( sliderLibrary.sliders[j].id==_.children[i]) {exist = true;break;}
				}
				if (exist) cleanchildren.push(_.children[i]);
			}
			_.children = cleanchildren;
		}
		// FOLDER OR SLIDER
		if (_.folder) {	 // DRAW FOLDER
			if (_.id==-1 || _.quicktype=="root") {	 // ROOT ?
				obj.addClass("rootlevel_wrap");
				imgobjunder.append('<div class="rootfolder"><i class="material-icons">apps</i><span class="nsb_title">'+RVS_LANG.root+'</span></div>');
			}
			if (_.quicktype==="parent") {
				obj.addClass("rootlevel_wrap");
				imgobjunder.append('<div class="rootfolder"><i class="material-icons">reply</i><span class="nsb_title">'+RVS_LANG.parent+'</span></div>');
				obj.append(jQuery('<div class="rsle_folder"><i class="material-icons">folder_open</i></div>'));
			} else	{
				obj.append(jQuery('<div class="rsle_folder"><i class="material-icons">folder_open</i></div>'));
				for (var i=1;i<=4;i++) {
					var sio = jQuery('<div class="folder_img_placeholder folder_img_'+i+'"></div>');
					if (_.children!==undefined && _.children.length>=i) {
						// IT HAS CHILDREN
						var _childindex = RVS.F.getOVSliderIndex(_.children[_.children.length - i]);
						if (_childindex!==-1) setObjBg(sliderLibrary.sliders[_childindex],sio);
					}
					imgobjunder.append(sio);
				}
			}
		} else { // DRAW SLIDER
			var imgobj = jQuery('<div class="image_container"></div>');
			obj.append(imgobj);
			setObjBg(_,imgobj);
		}
		return obj;
	},


	setObjBg = function(_,imgobj) {
		var	imgsrc = _.bg.src!==undefined && _.bg.src.length>3 ? _.bg.src : RVS.ENV.plugin_url+'admin/assets/images/sources/'+_.source+".png",
			styl = _.bg.style!==undefined ? _.bg.style : {};
		switch (_.bg.type) {
			case "image":
				styl.backgroundImage = "url("+imgsrc+")";
				punchgs.TweenLite.set(imgobj,styl);
			break;
			case "color":
			case "colored":
			case "solid":
				var colval = window.RSColor.get(styl["background-color"]);
				if (colval.indexOf("gradient")>=0)
					punchgs.TweenLite.set(imgobj,{backgroundImage:colval});
				else
					punchgs.TweenLite.set(imgobj,{backgroundColor:colval});
			break;
			case "transparent":
				punchgs.TweenLite.set(imgobj,{backgroundImage:"url("+RVS.ENV.plugin_url+'admin/assets/images/sources/'+_.source+".png)", backgroundRepeat:"no-repeat", backgroundSize:"cover"});
			break;
		}
	},

	// BUILD THE PAGINATION BASED ON THE CURRENT FILTERS
	buildOVPagination = function(_) {

		var maxamount = Math.max(1,Math.floor((sliderLibrary.output.width()+30) / 290)),
			dbl = maxamount,
			cpage = RVS.F.getCookie("rs6_overview_pagination");

		// REBUILD PAGINATION DROPDOWN
		if (sliderLibrary.maxAmountPerPage!==maxamount) {
			jQuery('#pagination_select_2').select2RS('destroy');
			sliderLibrary.maxAmountPerPage=maxamount;

			for (var i=0;i<=4;i++) {
				var opt = document.getElementById('page_per_page_'+i);
				opt.value = dbl;
				opt.selected = (opt.value===cpage);
				opt.innerHTML = RVS_LANG.show+" "+dbl+" "+RVS_LANG.perpage;
				dbl = dbl * 2;
			}
			jQuery('#pagination_select_2').select2RS({
				minimumResultsForSearch:"Infinity"
			});
		}
		//if (!sliderLibrary.inited) punchgs.TweenLite.to('#modulesoverviewheader, #modulesoverviewfooter',0.5,{autoAlpha:1,opacity:1,ease:punchgs.Power3.easeInOut});
		sliderLibrary.inited = true;

		if (sliderLibrary.sliders.length<=maxamount) {
			sliderLibrary.output.find('.overview_header_footer').hide();
			sliderLibrary.output.find('.overview_pagination').val("all");
		} else {
			sliderLibrary.output.find('.overview_header_footer').show();
		}

		sliderLibrary.selectedPage = !_.keeppage ? 1 : jQuery('.page_button.global_library_pagination.selected').length>0 ? jQuery('.page_button.global_library_pagination.selected').data('page') : 1;
		var wrap = sliderLibrary.output.find('.ov-pagination'),
			a = sliderLibrary.output.find('.overview_pagination').select2RS('data')[0] === undefined ? 4 : sliderLibrary.output.find('.overview_pagination').select2RS('data')[0].id,
			counter = 0;
		var filtleng = sliderLibrary.selectedFolder!=-1 ? sliderLibrary.filteredList.length +1 : sliderLibrary.filteredList.length;
		sliderLibrary.pageAmount = a==="all" ? 1 : Math.ceil(filtleng / parseInt(a));
		sliderLibrary.itemPerPage = a === "all" ? 99999 : parseInt(a);
		sliderLibrary.itemPerPage = sliderLibrary.selectedFolder!=-1 ? sliderLibrary.itemPerPage-1 : sliderLibrary.itemPerPage;
		wrap[0].innerHTML = "";
		var sel;
		sliderLibrary.selectedPage = sliderLibrary.selectedPage>sliderLibrary.pageAmount ? sliderLibrary.pageAmount : sliderLibrary.selectedPage;


		// BUILD THE PAGINATION BUTTONS
		if (sliderLibrary.pageAmount>1){
			for (var i=1;i<=sliderLibrary.pageAmount;i++) {

				sel = i!==sliderLibrary.selectedPage ? "" : "selected";
				wrap[0].innerHTML += '<div data-page="'+i+'" class="'+sel+' page_button global_library_pagination">'+i+'</div>';
				if (i===1)
					wrap[0].innerHTML += '<div data-page="-9999" class="page_button global_library_pagination">...</div>';
				else
				if (i===sliderLibrary.pageAmount-1)
					wrap[0].innerHTML += '<div data-page="9999" class="page_button global_library_pagination">...</div>';
			}
		}


		smartPagination();

		// BUILD THE PAGES LIST
		sliderLibrary.pages = [];
		sliderLibrary.pages.push([]);
		for (var f in sliderLibrary.filteredList) {
			if(!sliderLibrary.filteredList.hasOwnProperty(f)) continue;
			sliderLibrary.pages[sliderLibrary.pages.length-1].push(sliderLibrary.filteredList[f]);
			counter++;
			if (counter===sliderLibrary.itemPerPage) {
				counter = 0;
				sliderLibrary.pages.push([]);
			}
		}

	},

	resetAllOVFilters = function() {
		sliderLibrary.selectedPage = 1;
		jQuery('#sel_overview_sorting').val("datedesc").trigger('change.select2RS');
		jQuery('#sel_overview_filtering').val("all").trigger('change.select2RS');
		RVS.DOC.trigger('updateSlidersOverview',{val:"datedesc", eventparam:"#reset_sorting",ignoreRebuild:true,ignoreCookie:true});
		RVS.DOC.trigger('updateSlidersOverview',{val:"all", eventparam:"#reset_filtering",ignoreCookie:true});
	},

	// SMART PAGINATION
	smartPagination = function() {
		sliderLibrary.pageAmount = parseInt(sliderLibrary.pageAmount,0);
		sliderLibrary.selectedPage = parseInt(sliderLibrary.selectedPage,0);
		jQuery('.page_button.global_library_pagination').each(function() {
			var i = parseInt(this.dataset.page,0),
				s = false;
			if ((i===1) || (i===sliderLibrary.pageAmount)) s = true;
			if (sliderLibrary.selectedPage<4 && i>0 && i<5) s = true;
			if (sliderLibrary.selectedPage>sliderLibrary.pageAmount-3 && i>sliderLibrary.pageAmount-4 && i<9999) s = true;
			if (i<9999 && i>=sliderLibrary.selectedPage-1 && i<=sliderLibrary.selectedPage+1 && i>0) s = true;
			if ((sliderLibrary.selectedPage>=4 && i===-9999) || (sliderLibrary.selectedPage<= sliderLibrary.pageAmount-3 && i===9999)) s = true;
			if (sliderLibrary.pageAmount<8) if (i==9999 || i==-9999) s=false; else s=true;
			this.style.display = s ? "inline-block" : "none";
		});
	},

	// SELECTED FILTER MATCH
	filterMatch = function(_) {
		return ((_.filter === _.o.source || _.filter === _.o.type || _.filter === _.o.size || jQuery.inArray(_.filter,_.o.tags)>=0));
	},

	// DELIVER PARRENT FOLDERS OF ELEMENT
	getParentPath = function(pd) {
		var f = [];
		f.push(pd);
		while (pd !== -1) {
			var sindex = RVS.F.getOVSliderIndex(pd);
			pd = sindex!==-1 && sliderLibrary.sliders[sindex]!==undefined ? sliderLibrary.sliders[sindex].parent || -1 : -1;
			f.push(pd);
		}
		return f;
	},

	// UPDATE THE CURRENT VISIBILITY LIST
	updateOVFilteredList = function(_) {
		_ = _===undefined ? {force:false,keeppage:false,noanimation:false, focusItem:false} : _;
		var sFilter = sliderLibrary.output.find('.overview_filterby').select2RS('data')[0];

		//Sort the Sliders First
		switch(sliderLibrary.output.find('.overview_sortby').select2RS('data')[0].id) {
			case "datedesc":
				sliderLibrary.sliders.sort(function(a,b) { return b.id - a.id;});
			break;
			case "title":
				sliderLibrary.sliders.sort(function(a,b) { return a.title.toUpperCase().localeCompare(b.title.toUpperCase()); });
			break;
			case "titledesc":
				sliderLibrary.sliders.sort(function(a,b) { return b.title.toUpperCase().localeCompare(a.title.toUpperCase()); });
			break;
			default:
				sliderLibrary.sliders.sort(function(a,b) { return a.id - b.id;});
			break;
		}
		sliderLibrary.oldlist = sliderLibrary.filteredList;
		sliderLibrary.filteredList = [];
		var s = jQuery('#searchmodules').val().toLowerCase();

		// ADD SLIDERS
		for (var i in sliderLibrary.sliders) {
			if(!sliderLibrary.sliders.hasOwnProperty(i)) continue;
			var slide = sliderLibrary.sliders[i];
			/* addToFilter = false; */
			slide.parent = slide.parent===undefined ? -1 : slide.parent;
			var folderPath = getParentPath(slide.parent);

			// SEARCHED && SLIDE IS CHILDREN FROM SELECTED FOLDER && SEARCHED TEXT IN TITLE OR TAGLIST
			if ((s.length>2 && jQuery.inArray(sliderLibrary.selectedFolder,folderPath)>=0 && (slide.title.toLowerCase().indexOf(s)>=0 || slide.tags.toString().toLowerCase().indexOf(s)>=0) && (sFilter.id=="all" || filterMatch({o:slide, filter:sFilter.id}))) ||
				(s.length<3 && sFilter.id=="all" && slide.parent == sliderLibrary.selectedFolder) ||
				(s.length<3 && filterMatch({o:slide, filter:sFilter.id}) && jQuery.inArray(sliderLibrary.selectedFolder,folderPath)>=0)) sliderLibrary.filteredList.push(slide.id);
		}

		if (sliderLibrary.filteredList.length<1 && sliderLibrary.selectedFolder===-1 && s.length===0)
			punchgs.TweenLite.to('#modulesoverviewheader, #modulesoverviewfooter',0.5,{autoAlpha:0,opacity:0,ease:punchgs.Power3.easeInOut});
		else
			punchgs.TweenLite.to('#modulesoverviewheader, #modulesoverviewfooter',0.5,{autoAlpha:1,opacity:1,ease:punchgs.Power3.easeInOut});


		// ONLY REDRAW WHEN FORCED OR FILTERED RESULT CHANGED
		if(_.force || JSON.stringify(sliderLibrary.oldlist) !== JSON.stringify(sliderLibrary.filteredList)){
			buildOVPagination({keeppage:_.keeppage, focusItem:_.focusitem});
			drawOVOverview({noanimation:_.noanimation, focusItem:_.focusItem});
		}
	},

	/*
	UPDATE THE PARENT ATTRIBUTES ON THE SINGLE SLIDERS AND FOLDERS
	*/
	updateParentAttributes = function() {
		for (var i in sliderLibrary.sliders) {
			if(!sliderLibrary.sliders.hasOwnProperty(i)) continue;
			if (sliderLibrary.sliders[i].folder) {
				for (var c in sliderLibrary.sliders[i].children) {
					if(!sliderLibrary.sliders[i].children.hasOwnProperty(c)) continue;
					var sindex = RVS.F.getOVSliderIndex(sliderLibrary.sliders[i].children[c]);
					if (sindex!==-1)
						sliderLibrary.sliders[sindex].parent = sliderLibrary.sliders[i].id;
				}
			}
		}
	},

	/*
	BUILD THE DROP DOWN LIST FOR MODULES
	*/
	buildModuleFilters = function() {
		var ret = {folders:[], tags:[], types:[], sources:[], sizes:[]};
		ret.folders.push({id:-1, title:"Root"});
		for (var i in sliderLibrary.sliders) {
			if(!sliderLibrary.sliders.hasOwnProperty(i)) continue;
			var slide = sliderLibrary.sliders[i];
			ret.tags = extendArray(ret.tags, slide.tags);
			ret.types = extendArray(ret.types, slide.type);
			ret.sources = extendArray(ret.sources, slide.source);
			ret.sizes = extendArray(ret.sizes, slide.size);
			if (slide.folder) ret.folders.push({id:slide.id});
		}

		var select = sliderLibrary.output.find('.overview_filterby'),
			w = select.val();
		select.find('.dynamicadded').remove();
		extendSelect({select:select, array:ret.tags, group:"Tags", old:w, sanitize:true});
		extendSelect({select:select, array:ret.types, group:"Types", old:w});
		extendSelect({select:select, array:ret.sources, group:"Sources", old:w});
		extendSelect({select:select, array:ret.size, group:"Sizes", old:w});

		// replace post option with products
		jQuery.each(select[0].options, function(key, option) {
			if (jQuery(option).text() == 'Posts') {
				jQuery(option).text('Products')
			}
		});

		select.select2RS({
			minimumResultsForSearch:"Infinity",
			placeholder:"Select From List"
		});
		return ret;
	},

	/*
	BUILD THE FOLDER OVERVIEW SIDEBAR AND HANDLE FOLDER INCLUDES
	*/
	drawFolderListSideBar = function(sliderid) {
		sliderLibrary.filters = buildModuleFilters();
		window.showFolderOverview = new punchgs.TimelineLite();
		sliderLibrary.sfw[0].innerHTML = "";
		window.showFolderOverview.add(punchgs.TweenLite.fromTo(sliderLibrary.sfw,0.6,{display:"none",x:-400},{display:"block",x:0,ease:punchgs.Power3.easeOut}),0.1);
		window.showFolderOverview.add(punchgs.TweenLite.fromTo(sliderLibrary.sfwu,0.3,{display:"none",autoAlpha:0},{display:"block",autoAlpha:0.5,ease:punchgs.Power3.easeOut}),0);

		var target = sliderid===undefined ? undefined : sliderLibrary.sliders[RVS.F.getOVSliderIndex(sliderid)],
			firstfwlt = "first_fwlt";

		//CREATE ROOT FOLDER

		if (sliderLibrary.selectedFolder!==-1) {
			sliderLibrary.sfw.append('<div class="folder_wrap_level_title '+firstfwlt+'">'+RVS_LANG.toplevels+'</div>')
			buildDroppableList(buildOVElement({id:-1,title:"Root",quicktype:"root", folder:true,children:[]},true),0);
			firstfwlt="";
		}

		//CREATE PARENT FOLDER IF NEEDED
		if (target!==undefined && target.parent!==-1) {
			var pt = sliderLibrary.sliders[RVS.F.getOVSliderIndex(target.parent)];
			if (pt.parent!==-1) buildDroppableList(buildOVElement({id:pt.parent,title:"Parent",quicktype:"parent", folder:true,children:[]},true),0); //sliderLibrary.sliders[RVS.F.getOVSliderIndex(pt.parent)].children
		}
		var written=false;
		//CREATE SIBLINGS
		for (var f in sliderLibrary.filters.folders) {
			if(!sliderLibrary.filters.folders.hasOwnProperty(f)) continue;
			var findex = RVS.F.getOVSliderIndex(sliderLibrary.filters.folders[f].id);
			if (target!==undefined && sliderLibrary.sliders[findex]!==undefined && target.parent!==sliderLibrary.sliders[findex].parent) continue;
			if (findex===-1) continue;
			if (written===false) {
				sliderLibrary.sfw.append('<div class="folder_wrap_level_title '+firstfwlt+'">'+RVS_LANG.siblings+'</div>')
				written = true;
				firstfwlt="";
			}
			buildDroppableList(buildOVElement({id:sliderLibrary.filters.folders[f].id,title:sliderLibrary.sliders[findex].title,folder:true,children:sliderLibrary.sliders[findex].children},true),f);
		}
		written = false;
		//ANY OTHER FOLDERS
		for (var f in sliderLibrary.filters.folders) {
			if(!sliderLibrary.filters.folders.hasOwnProperty(f)) continue;
			var findex = RVS.F.getOVSliderIndex(sliderLibrary.filters.folders[f].id);
			if (target!==undefined && sliderLibrary.sliders[findex]!==undefined && target.parent===sliderLibrary.sliders[findex].parent) continue;
			if (target!==undefined && target.parent===sliderLibrary.filters.folders[f].id) continue;
			if (findex===-1) continue;
			if (written===false) {
				sliderLibrary.sfw.append('<div class="folder_wrap_level_title '+firstfwlt+'">'+RVS_LANG.otherfolders+'</div>')
				written = true;
				firstfwlt="";
			}
			buildDroppableList(buildOVElement({id:sliderLibrary.filters.folders[f].id,title:sliderLibrary.sliders[findex].title,folder:true,children:sliderLibrary.sliders[findex].children},true),f);

		}
		// SCROLLBAR AND MOUSE SENSITIVY
		sliderLibrary.sfw.RSScroll({wheelPropagation:false});
	},

	buildDroppableList = function(folder,f) {
		window.showFolderOverview.add(punchgs.TweenLite.from(folder,0.2,{x:"-150%",ease:punchgs.Power3.easeOut}),(0.2+(f*0.04)));
		doOVDroppable(folder);
		sliderLibrary.sfw.append(folder);
	},

	/*
	MAKE FOLDER DROPPABLE
	*/
	doOVDroppable = function(folder) {
		folder.droppable({
			drop:function(e,ui) {
				var folderId = this.dataset.sliderid,
					sliderId = ui.draggable[0].dataset.sliderid,
					findex = RVS.F.getOVSliderIndex(folderId),
					sindex = RVS.F.getOVSliderIndex(sliderId);
				if (folderId !==sliderId) {
					// REMOVE FROM OLD FOLDER
					if (sliderLibrary.sliders[sindex].parent!=-1) {
						var oindex = RVS.F.getOVSliderIndex(sliderLibrary.sliders[sindex].parent);
						sliderLibrary.sliders[oindex].children.splice(jQuery.inArray(sliderId,sliderLibrary.sliders[oindex].children),1);
						RVS.F.ajaxRequest('save_slider_folder', {id:sliderLibrary.sliders[oindex].id, children:sliderLibrary.sliders[oindex].children}, function(response){});
					}

					// ADD INTO NEW FOLDER
					if (folder!=-1 && findex!==-1) {
						sliderLibrary.sliders[findex].children =  sliderLibrary.sliders[findex].children===undefined || sliderLibrary.sliders[findex].children.length===0 ? [] : sliderLibrary.sliders[findex].children;
						sliderLibrary.sliders[findex].children.push(sliderId);
						RVS.F.ajaxRequest('save_slider_folder', {id:folderId, children:sliderLibrary.sliders[findex].children}, function(response){});
					}
					sliderLibrary.filters = buildModuleFilters();
					sliderLibrary.sliders[sindex].parent = folderId;
					hideElementSubMenu({keepOverlay:false});
					updateOVFilteredList({force:true,keeppage:true,noanimation:false});
				}

				window.showFolderOverview.reverse();
				window.droppedIntoFolder=true;
				return false;
			}
		});
	},

	/*
	MAKE ELEMENT DRAGGABLE AND DROPPABLE
	*/
	doOVDraggable = function(_) {
		if (_.data('draggable')) _.draggable("destroy");
		_.draggable({
			distance: 20,
			helper:'clone',
			appendTo:'body',
			revert:'invalid',
			start:function(e,ui) {
				window.droppedIntoFolder = false;
				drawFolderListSideBar(ui.helper[0].dataset.sliderid);
			},
			stop:function(e,ui) {
				if (window.droppedIntoFolder===false) {
					window.showFolderOverview.reverse();
					hideElementSubMenu({keepOverlay:false});
					updateOVFilteredList({force:true,keeppage:true,noanimation:false});
				}
			}
		});
	},

	//DRAW SYSTEM CHECK
	updateSysChecks = function() {
		for (var i in window.rs_system) {
			if(!window.rs_system.hasOwnProperty(i)) continue;
			var _ = window.rs_system[i],
				w = (typeof(_) =="object" && _.good==true) || _===true || _==='1';

			if (!w)
				jQuery('#syscheck_'+i).addClass("warning");
			else
				jQuery('#syscheck_'+i).removeClass("warning");
		}
	},
	checkAddOnVersions = function() {
		if (RVS.ENV.activated!=="true" && RVS.ENV.activated!==true) return;
		var list = "";
		RVS.ENV.addOns_to_update = RVS.ENV.addOns_to_update===undefined ? {} : RVS.ENV.addOns_to_update;
		RVS.ENV.addOns_to_updateArray = [];
		window.addOnUpdateCounter = 0;
		for (var i in RVS.ENV.addOns_to_update) if (RVS.ENV.addOns_to_update.hasOwnProperty(i)) {
			RVS.ENV.addOns_to_updateArray.push(i);
			list +=  '<div id="need_update_'+i+'" class="addonlist_to_update">'+RVS.ENV.addOns_to_update[i].title+' '+RVS_LANG.to+' '+RVS.ENV.addOns_to_update[i].new+'<div class="addonlist_to_update_single_status circle-loader"><div class="checkmark draw"></div></div></div>';
		}
		if (list!=="")
		RVS.F.RSDialog.create({
			bgopacity:0.85,
			modalid:'rbm_decisionModal',
			icon:'extension',
			title:RVS_LANG.addonsupdatetitle,
			maintext:RVS_LANG.addonsupdatemain,
			subtext:list,
			do:{
				icon:"check_circle",
				text:RVS_LANG.updateallnow,
				event: "updateAddonsNow",
				keepDialog:true
			},
			cancel:{
				icon:"cancel",
				text:RVS_LANG.updatelater
			},
			swapbuttons:true
		});
	},

	updateNextRequiredAddon = function() {
		if (window.addOnUpdateCounter<RVS.ENV.addOns_to_updateArray.length) {
			var slug = RVS.ENV.addOns_to_updateArray[window.addOnUpdateCounter],
				_ = RVS.ENV.addOns_to_update[slug],
				le = jQuery('#need_update_'+slug);
			le.find('.addonlist_to_update_single_status').addClass("inload");
			RVS.F.ajaxRequest('activate_addon', {addon:slug, update:true}, function(response){
					if(response.success) {
						le.find('.addonlist_to_update_single_status').removeClass("inload").addClass('load-complete');
						_.updated = true;
					} else {
						le.find('.addonlist_to_update_single_status').removeClass("inload").addClass('load-complete').addClass("failure");
					}
					window.addOnUpdateCounter++;
					updateNextRequiredAddon();
			},false);
		} else {
			jQuery('#decmod_do_btn').html('<span id="decmod_do_txt">'+RVS_LANG.updatedoneexist,+'</span>').show().off("click").on("click",function() {
				RVS.F.RSDialog.close();
				RVS.F.RSDialog.close();
			});
		}
	},

	/*
	LOCAL LISTENERS
	*/
	initLocalListeners = function() {

		// RESIZE SCREEN
		RVS.WIN.on('resize',function() {
			clearTimeout(window.resizedOverviewTimeOut);
			window.resizedOverviewTimeOut = setTimeout(function() {
				var maxamount = Math.floor((sliderLibrary.output.width()+30) / 290);
				maxamount=maxamount<1 ? 1 : maxamount;
				if (sliderLibrary.maxAmountPerPage!==maxamount) {
					updateOVFilteredList({force:true,keeppage:true,noanimation:true});
				}
			},10);
		});

		RVS.DOC.on('updateAddonsNow',function() {
			updateNextRequiredAddon();
			jQuery('#decmod_dont_btn').hide();
			jQuery('#decmod_do_btn').hide();

		});

		RVS.DOC.on('updateThePlugin',function() {
			wp.updates.maybeRequestFilesystemCredentials( );
			RVS.F.showWaitAMinute({fadeIn:500,text:RVS_LANG.updatingplugin});
		    var args = {
		        plugin: RVS.ENV.slug_path,
		        slug:   RVS.ENV.slug,
				checkforupdates: true,
		        success: function(success) {
		        	RVS.F.showWaitAMinute({fadeOut:0});
		        	RVS.F.RSDialog.create({
					bgopacity:0.85,
					modalid:'rbm_decisionModal',
					icon:'update',
					title:RVS_LANG.updateplugin,
					maintext:"", //RVS_LANG.updatepluginsuccess,
					subtext:RVS_LANG.updatepluginsuccesssubtext+" <strong>"+success.newVersion+"</strong>",
					do:{
						icon:"check_circle",
						text:RVS_LANG.reloadpage,
						event: "reloadpagenow"
					}});
		        },
		        error: function(error) {
		        	RVS.F.showWaitAMinute({fadeOut:0});
		        	var debug="<br>";
		        	for (var i in error.debug) if (error.debug.hasOwnProperty(i)) debug += "<span style='white-space: nowrap;overflow: hidden;width: 400px;margin-bottom: 5px;font-size: 12px;display: block;'>- "+error.debug[i]+"</span>";
		        	 RVS.F.RSDialog.create({
					bgopacity:0.85,
					modalid:'rbm_decisionModal',
					icon:'update',
					title:RVS_LANG.updatepluginfailed,
					maintext:RVS_LANG.updatepluginfailure,
					subtext:(error!==undefined && error.errorMessage!==undefined && error.errorMessage.indexOf("PCLZIP_ERR_BAD_FORMAT")>=0 ? RVS_LANG.licenseissue : error.errorMessage)+"<br>"+debug,
					do:{
						icon:"error",
						text:RVS_LANG.leave,
						event: ""
					}});
		        }
		    }
		    wp.updates.ajax('update-plugin', args);
		});

		RVS.DOC.on('click','#updateplugin',function() {
			if (RVS.F.compareVersion(RVS.ENV.latest_version, RVS.ENV.revision) <= 0) return;
			if (this.className.indexOf("halfdisabled")>=0) {
				overviewMenuScroll();
				var o = { val:window.scroll_top};
				punchgs.TweenLite.to(o,0.6,{val:window.ov_scroll_targets[2].top-200, onUpdate:function() {
					RVS.WIN.scrollTop(o.val);
				}, ease:punchgs.Power3.easeOut});
				overviewMenuScroll();
				//scroll to position
			} else {
				RVS.F.RSDialog.create({
					bgopacity:0.85,
					modalid:'rbm_decisionModal',
					icon:'update',
					title:RVS_LANG.updateplugin,
					maintext:RVS_LANG.areyousureupdateplugin,
					subtext:RVS_LANG.updatingtakes,
					do:{
						icon:"check_circle",
						text:RVS_LANG.updatenow,
						event: "updateThePlugin"
					},
					cancel:{
						icon:"cancel",
						text:RVS_LANG.cancel
					},
					swapbuttons:true
				});
			}
		});



		RVS.DOC.on('reloadpagenow',function() {
			punchgs.TweenLite.to(jQuery('#wpwrap'),0.5,{opacity:0});
			jQuery('#waitaminute').appendTo('body');
			RVS.F.showWaitAMinute({fadeIn:500,text:RVS_LANG.reLoading});

			//setTimeout(function() {
				window.location.reload();
			//},500);
		});

		// LEAVING OVERVIEW TO EDIT
		RVS.DOC.on('click','.link_to_slideadmin',function() {
			if (this.tagName=="A" && this.href!==undefined) {
				punchgs.TweenLite.to(jQuery('#wpwrap'),0.5,{opacity:0});
				jQuery('#waitaminute').appendTo('body');
				RVS.F.showWaitAMinute({fadeIn:500,text:RVS_LANG.editorisLoading+"<span style='display:block;font-size:20px;line-height:25px'>"+RVS_LANG.opening+" "+this.dataset.title+"</span>"});
			}
			return;
		});
		//BACK ONE LEVEL
		RVS.DOC.on('click','#back_one_folder',function() {
			var findex = RVS.F.getOVSliderIndex(sliderLibrary.selectedFolder);
			sliderLibrary.selectedFolder = sliderLibrary.sliders[findex].parent || -1;
			resetAllOVFilters();
			updateOVFilteredList({force:true,keeppage:false,noanimation:false});
		});

		// FOLLOW BREADCRUMB
		RVS.DOC.on('click','.rsl_breadcrumb',function() {
			sliderLibrary.selectedFolder = parseInt(this.dataset.folderid,0);
			updateOVFilteredList({force:true,keeppage:false,noanimation:false});
		});

		// HIDE FOLDER LISTS
		RVS.DOC.on('click','#slider_folders_wrap_underlay',function() {
			window.showFolderOverview.reverse();
		});

		// CREATE NEW FOLDER
		RVS.DOC.on('click','#add_folder',function(e,par) {
			RVS.F.createNewFolder(par);
		});

		// FIX DATABASE ISSUES
		RVS.DOC.on('click','#rs_db_force_create',function(e,par) {
			RVS.F.ajaxRequest('fix_database_issues', {}, function(response){},false);
		});

		// REISSUE GOOGLE FONT DOWNLOAD
		RVS.DOC.on('click','#rs_trigger_font_deletion',function(e,par) {
			RVS.F.ajaxRequest('trigger_font_deletion', {}, function(response){},false);
		});


		RVS.DOC.on('click','#reset_sorting',function() {
			jQuery('#sel_overview_sorting').val("datedesc").trigger('change.select2RS');
			RVS.DOC.trigger('updateSlidersOverview',{val:"datedesc", eventparam:"#reset_sorting",ignoreCookie:true});
		});

		RVS.DOC.on('click','#reset_filtering',function() {
			jQuery('#sel_overview_filtering').val("all").trigger('change.select2RS');
			RVS.DOC.trigger('updateSlidersOverview',{val:"all", eventparam:"#reset_filtering",ignoreCookie:true});
		});

		//UPDATE SLIDER OVERVIEW
		RVS.DOC.on('updateSlidersOverview',function(e,p) {

			if (p!==undefined && p.eventparam!==undefined) {
				var a = p.eventparam === "#reset_sorting" ? p.val==="datedesc" ? 0 : 1 : p.val==="all" ? 0 : 1,
					d = a ===1 ? "inline-block" : "none";
				punchgs.TweenLite.set(p.eventparam,{autoAlpha:a, display:d});
			}

			if (p!==undefined && !p.ignoreRebuild) {
				if (p.val!==undefined && p.ignoreCookie!==true) RVS.F.setCookie("rs6_overview_pagination",p.val,360);
				hideElementSubMenu({keepOverlay:false});
				updateOVFilteredList({force:true,keeppage:false,noanimation:false});
			}
		});

		//PAGINATION TRIGGER
		RVS.DOC.on('click','.global_library_pagination',function() {
			hideElementSubMenu({keepOverlay:false});
			jQuery('.global_library_pagination.selected').removeClass('selected');
			jQuery(this).addClass("selected");
			sliderLibrary.selectedPage = parseInt(this.dataset.page,0)===-9999 ? sliderLibrary.selectedPage = parseInt(sliderLibrary.selectedPage,0)-3 : parseInt(this.dataset.page,0)===9999 ? sliderLibrary.selectedPage = parseInt(sliderLibrary.selectedPage,0)+3 : this.dataset.page;
			smartPagination();
			drawOVOverview();
		});



		// SEARCH MODULE TRIGGERING
		RVS.DOC.on('keyup','#searchmodules',function() {
			hideElementSubMenu({keepOverlay:false});
			clearTimeout(window.searchKeyUp);
			window.searchKeyUp = setTimeout(function() {
				 updateOVFilteredList();
			},200);
		});

		// NEW TAG ADDED / REMOVED / SELECTED
		RVS.DOC.on('select2RS:select select2RS:unselect','.elementtags',function(e) {

			//Update Slider Tags
			var sIndex = RVS.F.getOVSliderIndex(e.target.dataset.id);
			sliderLibrary.sliders[sIndex].tags = [];
			for (var i in e.target.options) {
				if(!e.target.options.hasOwnProperty(i)) continue;
				if (e.target.options[i] !== undefined && e.target.options[i].selected)
					sliderLibrary.sliders[sIndex].tags.push(RVS.F.sanitize_input(e.target.options[i].value.toLowerCase()));
			}

			// SAVE TAGS
			RVS.F.ajaxRequest('update_slider_tags', {id:sliderLibrary.sliders[sIndex].id, tags:sliderLibrary.sliders[sIndex].tags}, function(response){

			},false);

			//Update General List
			sliderLibrary.filters = buildModuleFilters();
			jQuery('.elementtags').each(function() {
				var s = jQuery(this),
					id = this.dataset.id;
				s.find('option').remove();
				for (var i in sliderLibrary.filters.tags) {
					if(!sliderLibrary.filters.tags.hasOwnProperty(i)) continue;
					var tag = RVS.F.sanitize_input(sliderLibrary.filters.tags[i].toLowerCase()),
						cIndex = RVS.F.getOVSliderIndex(this.dataset.id),
						m =  jQuery.inArray(tag,sliderLibrary.sliders[cIndex].tags)>=0 ? ' selected="selected" ' : '';
					s.append('<option value="'+tag+'" '+m+'>'+tag+'</option>');
				}
				//s.select2RS({tags:true});
			});
		});

		RVS.DOC.on('keyup','.title_container', function(e) {
			if (e.keyCode===13) {
				jQuery(document.activeElement).blur();
				hideElementSubMenu({keepOverlay:false});
			}
		});

		/* SHOW MENU OF SLIDER ELEMENT */
		RVS.DOC.on('click','.show_rsle, .rsle_folder',function() {
			var cl = jQuery(this).closest('.rs_library_element'),
				bar = cl.find('.rsle_tbar'),
				a = cl.hasClass("selected"),
				id = cl.attr('id'),
				sliderId = cl[0].dataset.sliderid;

			if (!a) {
				hideElementSubMenu({keepOverlay:true, id:id});
				clearTimeout(window.unsetFocusOverviewOverlay);
				cl.addClass("selected").addClass("menuopen");
				punchgs.TweenLite.fromTo(bar,0.3,{y:"-100%"},{opacity:1,y:"0%",ease:punchgs.Power3.easeOut});
				jQuery('.overview_elements').addClass("infocus");
				window.lastBreacCrumbText = sliderLibrary.sliders[RVS.F.getOVSliderIndex(sliderId)].title;
				jQuery('#rsl_bread_selected').html(window.lastBreacCrumbText);
			} else {
				hideElementSubMenu({keepOverlay:false});
				window.lastBreacCrumbText="";
				jQuery('#rsl_bread_selected').html(window.lastBreacCrumbText);
			}
		});



		/* HOVER / LEAVE ELEMENTS */
		RVS.DOC.on('mouseenter','.rs_library_element',function() {
			if (this.dataset.sliderid!=-1)
				jQuery('#rsl_bread_selected').html(sliderLibrary.sliders[RVS.F.getOVSliderIndex(this.dataset.sliderid)].title);
		});

		RVS.DOC.on('mouseleave','.rs_library_element',function() {
			window.lastBreacCrumbText = window.lastBreacCrumbText===undefined ? "" :window.lastBreacCrumbText;
			jQuery('#rsl_bread_selected').html(window.lastBreacCrumbText);
		});

		/* CLICK OUTSIDE A SLIDER ELEMENT */
		RVS.DOC.on('click','.overview_elements_overlay',function() {
			hideElementSubMenu({keepOverlay:false});
		});

		/* SHOW TAGS OF SLIDER ELEMENT */
		RVS.DOC.on('click','.tagsslider',function() {
			var cl = jQuery(this).closest('.rs_library_element');
			cl.toggleClass("in_tag_view");
			cl.removeClass("in_folder_view");
		});

		/* RENAME SLIDER */
		RVS.DOC.on('click','.renameslider',function() {
			var cl = jQuery(this).closest('.rs_library_element');
			/* n = cl.find('.title_container').focus(); */
			cl.find('.title_container').focus();
		});





		/* CHANGE TITLE */
		RVS.DOC.on('change','.title_container',function() {
			var cInp = this,
				sindex = RVS.F.getOVSliderIndex(this.dataset.id);
			RVS.F.ajaxRequest('update_slider_name', {id:this.dataset.id, title:this.value}, function(response){
				if (response.success) cInp.value = response.title;
				sliderLibrary.sliders[sindex].title = response.title;
			});
		});

		function collectAllInFolder(list,sindex) {
			list = list===undefined ? [] : list;
			var folder = sliderLibrary.sliders[sindex];

			for (var c in folder.children) {
				if(!folder.children.hasOwnProperty(c)) continue;
				var childindex = RVS.F.getOVSliderIndex(folder.children[c]);
				if (sliderLibrary.sliders[childindex] && sliderLibrary.sliders[childindex].folder) list = collectAllInFolder(list,childindex);
				if (sliderLibrary.sliders[childindex]) list.push(folder.children[c]);
			}
			return list;
		}

		/* DELETE SLIDER & FOLDER*/
		RVS.DOC.on('click','.deleteslider',function() {
			var sindex = RVS.F.getOVSliderIndex(this.dataset.id);
			hideElementSubMenu({keepOverlay:false});
			window.deleteSlidersIndex = 0;

			//IF FOLDER
			if (sliderLibrary.sliders[sindex].folder) {
				window.deleteSliders = collectAllInFolder([],sindex);
				window.deleteSliders.push(this.dataset.id);
				RVS.F.RSDialog.create({
					bgopacity:0.85,
					modalid:'rbm_decisionModal',
					icon:'delete',
					title:RVS_LANG.deleteslider,
					maintext:RVS_LANG.cannotbeundone,
					subtext:RVS_LANG.areyousuretodeleteeverything+" <strong>"+sliderLibrary.sliders[RVS.F.getOVSliderIndex(this.dataset.id)].title+"</strong> ?",
					do:{
						icon:"delete",
						text:RVS_LANG.yesdeleteall,
						event: "deletemarkedslider"
					},
					cancel:{
						icon:"cancel",
						text:RVS_LANG.cancel
					},
					swapbuttons:true
				});
			} else {
				window.deleteSliders = [this.dataset.id];
				RVS.F.RSDialog.create({
					bgopacity:0.85,
					modalid:'rbm_decisionModal',
					icon:'delete',
					title:RVS_LANG.deleteslider,
					maintext:RVS_LANG.cannotbeundone,
					subtext:RVS_LANG.areyousuretodelete+" <strong>"+sliderLibrary.sliders[RVS.F.getOVSliderIndex(this.dataset.id)].title+"</strong> ?",
					do:{
						icon:"delete",
						text:RVS_LANG.yesdelete,
						event: "deletemarkedslider"
					},
					cancel:{
						icon:"cancel",
						text:RVS_LANG.cancel
					},
					swapbuttons:true
				});
			}

		});

		RVS.DOC.on('deletemarkedslider',function() {
			window.deletedSliderSINDEX = RVS.F.getOVSliderIndex(window.deleteSliders[window.deleteSlidersIndex]);
			window.mayDeleteFolder = sliderLibrary.sliders[window.deletedSliderSINDEX];
			RVS.F.ajaxRequest('delete_slider', {id:window.deleteSliders[window.deleteSlidersIndex]}, function(response){
				if (response.success) {
					if (window.mayDeleteFolder!==undefined && window.mayDeleteFolder.parent!=-1) {
						var pindex = RVS.F.getOVSliderIndex(window.mayDeleteFolder.parent);
						if (sliderLibrary.sliders[pindex]) sliderLibrary.sliders[pindex].children.splice(jQuery.inArray(window.mayDeleteFolder.id,sliderLibrary.sliders[pindex].children),1); else console.log("Info:Folder with Index "+pindex+"  is not existing any more.");
					}
					if (sliderLibrary.sliders[window.deletedSliderSINDEX] && sliderLibrary.sliders[window.deletedSliderSINDEX].ref) sliderLibrary.sliders[window.deletedSliderSINDEX].ref.remove();
					jQuery('#slide_id_'+window.deleteSliders[window.deleteSlidersIndex]).remove();
					sliderLibrary.sliders.splice(window.deletedSliderSINDEX,1);
				}
				window.deleteSlidersIndex++;
				if (window.deleteSlidersIndex<window.deleteSliders.length)
					RVS.DOC.trigger('deletemarkedslider');
				else {
					sliderLibrary.filters = buildModuleFilters();
					updateOVFilteredList({force:true,keeppage:true,noanimation:false});
				}
			},undefined,undefined,RVS_LANG.deletingslider+"<span style='display:block;font-size:20px;line-height:25px'>"+(sliderLibrary.sliders[window.deletedSliderSINDEX] ? sliderLibrary.sliders[window.deletedSliderSINDEX].alias : window.deletedSliderSINDEX) +"</span>");
		});

			/* EXPORT SLIDER */
		RVS.DOC.on('click','.exportslider, .exporthtmlslider',function() {
			var param = this.className.indexOf("exportslider")>=0 ? "export_slider" : "export_slider_html";
			window.exportSliders = [this.dataset.id];
			window.exportSlidersIndex = 0;

			RVS.F.RSDialog.create({
				bgopacity:0.85,
				modalid:'rbm_decisionModal',
				icon:'cloud_download',
				title:RVS_LANG.exportslider+(param==="export_slider_html" ? " "+RVS_LANG.ashtmlexport : ""),
				maintext:RVS_LANG.exportslidertxt,
				subtext:RVS_LANG.areyousuretoexport+sliderLibrary.sliders[RVS.F.getOVSliderIndex(this.dataset.id)].alias,
				do:{
					icon:"cloud_download",
					text:RVS_LANG.yesexport,
					event: "exportmarkedslider",
					eventparam:param
				},
				cancel:{
					icon:"cancel",
					text:RVS_LANG.cancel
				},
				swapbuttons:true
			});

		});




		RVS.DOC.on('exportmarkedslider',function(e,calltype) {
			hideElementSubMenu({keepOverlay:false});
			window.lastBreacCrumbText="";
			jQuery('#rsl_bread_selected').html(window.lastBreacCrumbText);
			location.href = ajaxurl + ((ajaxurl.indexOf('?') === -1) ? '?' : '&') + 'action=' + RVS.ENV.plugin_dir + '_ajax_action&client_action='+calltype+'&nonce=' + RVS.ENV.nonce + '&id=' + window.exportSliders[window.exportSlidersIndex];
		});


		/* MENU HANDLINGS */
		RVS.DOC.on('click','#collapse-button',overviewMenuResize);
		RVS.DOC.on('click','#rbm_globalsettings .rbm_close',function() {
			RVS.F.RSDialog.close();
		});
		RVS.DOC.on('click','.rso_scrollmenuitem',function() {
			if (this.id==="globalsettings") {
				openGlobalSettings();
				return;
			} else
			if (this.id==="rso_menu_notices") {
				return;
			} else
			if (this.id==="rso_menu_updatewarning") {
				return;
			} else
			if (this.id==="contactsupport") {

				if (RVS.ENV.activated!=="true" && RVS.ENV.activated!==true) {
					RVS.F.showRegisterSliderInfo();
					return;
				} else window.open('https://support.nwdthemes.com/','_blank');


				return;
			} else
			if (this.id==="linktodocumentation") {
				window.open('https://www.themepunch.com/support-center','_blank');
				return;
			}
			overviewMenuScroll();
			var o = { val:window.scroll_top};
			punchgs.TweenLite.to(o,0.6,{val:window.ov_scroll_targets[this.dataset.ostref].top-200, onUpdate:function() {
				RVS.WIN.scrollTop(o.val);
			}, ease:punchgs.Power3.easeOut});
			overviewMenuScroll();
		});
		RVS.WIN.resize(overviewMenuResize).on('scroll',overviewMenuScroll);

		/* ENTER INTO FOLDER */
		RVS.DOC.on('click','.enter_into_folder',function() {
			sliderLibrary.selectedFolder = this.dataset.folderid;
			resetAllOVFilters();
			updateOVFilteredList();

		});

		/* ADD BLANK SLIDER */
		RVS.DOC.on('click','#new_blank_slider',function() {
			punchgs.TweenLite.to(jQuery('#wpwrap'),0.5,{opacity:0});
			jQuery('#waitaminute').appendTo('body');
			RVS.F.showWaitAMinute({fadeIn:500,text:RVS_LANG.editorisLoading+"<span style='display:block;font-size:20px;line-height:25px'>"+RVS_LANG.addingnewblankmodule+"</span>"});
			RVS.F.ajaxRequest('create_slider',{},function(response){
				if (response.success) {
					var parindex = RVS.F.getOVSliderIndex(sliderLibrary.selectedFolder);
					if (parindex!==-1) {
						sliderLibrary.sliders[parindex].children.push(response.slider_id);
						var slideid = response.slide_id;
						RVS.F.ajaxRequest('save_slider_folder', {id:sliderLibrary.selectedFolder, children:sliderLibrary.sliders[parindex].children}, function(response){
							window.location.href = RVS.ENV.admin_url+"?id="+slideid;
						});
					} else
					window.location.href = RVS.ENV.admin_url+"?id="+response.slide_id;
				}
			});
		});

		// ADD NEW SLIDER EVENT
		RVS.DOC.on('addNewSlider',function(e,param){
			if (param!==undefined && param.slider!==undefined) {
				param.slider.parent = sliderLibrary.selectedFolder;
				sliderLibrary.sliders.push(param.slider);
				// SAVE THE PARENT FOLDER STRUCTURE ALSO
				if (sliderLibrary.selectedFolder!==-1) {
					var parindex = RVS.F.getOVSliderIndex(sliderLibrary.selectedFolder);
					if (parindex!==-1) {
						sliderLibrary.sliders[parindex].children.push(param.slider.id);
						//If Folder Already Moved to the Right Container
						if (!param.ignoreAjaxFolderMove)
							RVS.F.ajaxRequest('save_slider_folder', {id:sliderLibrary.selectedFolder, children:sliderLibrary.sliders[parindex].children}, function(response){},param.silent);
					}
				}
				sliderLibrary.filters = buildModuleFilters();
			 	resetAllOVFilters();
			}
		});

		RVS.DOC.on('addDraftPage',function(e,param) {
			RVS.F.ajaxRequest('create_draft_page',{slider_ids:param.pages, modals:param.modals},function(response) {
				if (response.success) {
					window.visitURLCreatedPage = response.open;
					setTimeout(function() {
						RVS.F.RSDialog.create({
							bgopacity:0.85,
							modalid:'rbm_decisionModal',
							icon:'fiber_new',
							title:RVS_LANG.blank_page_added,
							maintext:RVS_LANG.blank_page_created,
							subtext:/*RVS_LANG.visit_page+': <a class="blankpagelink" href="'+response.open+'" target="blank">'+response.open+'</a><br>'+*/(response.edit!==undefined && response.edit.length>0 ? RVS_LANG.edit_page+': <a class="blankpagelink" href="'+response.edit+'" target="blank">'+response.edit.substr(0, 60)+'...</a>' : ''),
							do:{
								icon:"exit_to_app",
								text:RVS_LANG.visit_page,
								event: "visitcreatedpage"
							},
							cancel:{
								icon:"cancel",
								text:RVS_LANG.closeandstay
							},
							swapbuttons:true
						});
					},200);
				}
			});
		});

		RVS.DOC.on('visitcreatedpage',function() {
			window.open(window.visitURLCreatedPage,'_blank');
		});

		// TRIGGER THE SLIDER IMPORT FUNCTION
		RVS.DOC.on('click','#new_slider_import',function() {
			RVS.F.browserDroppable.init({success:"addNewSlider"});
		});

		// DUPLICATE SLIDER
		RVS.DOC.on('click','.duplicateslider',function(){
			var sindex = RVS.F.getOVSliderIndex(this.dataset.id),
				par = sindex==-1 ? -1 : sliderLibrary.sliders[sindex].parent,
				parindex = RVS.F.getOVSliderIndex(par);

			RVS.F.ajaxRequest('duplicate_slider', {id:this.dataset.id},function(response) {
				if (response.success) {
					response.slider.parent = par;
					sliderLibrary.sliders.push(response.slider);
					if (parindex!==-1) {
						sliderLibrary.sliders[parindex].children.push(response.slider.id);
						RVS.F.ajaxRequest('save_slider_folder', {id:par, children:sliderLibrary.sliders[parindex].children}, function(response){});
					}
					//Save Folder due its Children also
					sliderLibrary.filters = buildModuleFilters();
			 		resetAllOVFilters();
				}
			});
		});


		/*
		MOUSE INTERACTION OVER SCROLLBAR FOLDERLISTS
		*/
		RVS.DOC.on('mouseover','#slider_folders_wrap',function(e) {
			window.scrollInterval = setInterval(function() {
				var a = {top:sliderLibrary.sfw.scrollTop()};
				punchgs.TweenLite.to(a,0.1,{top:sliderLibrary.sfw.scrollTop() + window.scrollIntervalOffset, onUpdate:function() {
					sliderLibrary.sfw.scrollTop(a.top);
				}});
			},110);
		});

		RVS.DOC.on('mousemove','#slider_folders_wrap',function(e) {

			var y = (e.pageY - jQuery(this).offset().top) - window.innerHeight/2,
				zone = Math.round(window.innerHeight / 3),
				_y = y<0 ? y + zone/2 : y - zone/2;
				_y = y<0 ? Math.min(_y,0) : Math.max(_y,0);
			window.scrollIntervalOffset = Math.round(_y)/5;
		});
		RVS.DOC.on('mouseleave','#slider_folders_wrap',function(e) {
				clearInterval(window.scrollInterval);
		});

		RVS.DOC.on('dragstart dragend',function(e) {
			if (e.type==="dragstart") RVS.S.dragginginside = true;
			if (e.type==="dragend") RVS.S.dragginginside = false;
		});

		// DRAG OVER SLIDER OVERVIEW SHOULD IMPORT FILE
		 jQuery('#rs_overview').on(' dragover dragenter ', function(e) {
		 	if (!RVS.S.dragginginside && jQuery('#filedrop').length===0)
		 		RVS.F.browserDroppable.init({success:"addNewSlider"});
		 });


		 /*
		 ACTIVATE, DEACTIVATE PLUGIN
		 */
		 RVS.DOC.on('click','#activateplugin',function() {
		 	if (RVS.ENV.activated=="true" || RVS.ENV.activated==true) {
		 		RVS.F.ajaxRequest('deactivate_plugin', {},function(response) {
		 			if (response.success) {
		 				RVS.ENV.activated = false;
		 				RVS.ENV.code = "";
		 				RVS.F.updateDraw();
						RVS.F.isActivated();
						RVS.F.notifications();
		 			}
		 		});
		 	} else {
		 		var code = jQuery('#purchasekey').val();
		 		RVS.F.ajaxRequest('activate_plugin', {code:code},function(response) {

		 			if (response.success) {
		 				RVS.ENV.activated = true;
		 				RVS.ENV.code = code;
		 				RVS.F.updateDraw();
						RVS.F.isActivated();
						RVS.F.notifications();
		 			}
		 		});
		 	}
		 });

		 /*
		 CHECK FOR UPDATES
		 */
		 RVS.DOC.on('click','#check_for_updates',function() {
		 	RVS.F.ajaxRequest('check_for_updates',{},function(response) {
		 		if (response.success) {
		 			RVS.ENV.latest_version = response.version;
		 			jQuery('.available_latest_version').html(RVS.ENV.latest_version);
		 			RVS.F.updateDraw();
					RVS.F.isActivated();
		 		}
		 	});
		 });

		 /*
		 PREVIEW SLIDER
		 */
		 RVS.DOC.on('click','.previewslider',function() {
		 	//RVS.F.ajaxRequest('preview_slider',{id:this.dataset.id},function(response) {});
		 	var slide = sliderLibrary.sliders[RVS.F.getOVSliderIndex(this.dataset.id)];
		 	RVS.F.openPreivew({title:this.dataset.title,alias:slide.alias, id:this.dataset.id});
		 	hideElementSubMenu({keepOverlay:false});
			window.lastBreacCrumbText="";
			jQuery('#rsl_bread_selected').html(window.lastBreacCrumbText);
		 });

		 /*
		 SIGN UP
		 */
		 RVS.DOC.on('click','#signuptonewsletter',function() {
		 	var mail = jQuery('#newsletter_mail').val();
		 	if (mail.length>0 && mail.indexOf("@")>=0)
		 		RVS.F.ajaxRequest('subscribe_to_newsletter',{email:mail},function(response) {});

		 });

		 /*
		 CHECK FOR TP SERVER
		 */
		 RVS.DOC.on('click','#check_for_themepunchserver',function() {
		 	RVS.F.ajaxRequest('check_system',{},function(response) {
		 		if (response.success) {
		 			window.rs_system = RVS.F.safeExtend(true,{},response.system);
		 			updateSysChecks();
		 		}
		 	});
		 });

		 RVS.DOC.on('click','.embedslider',function() {

		 	var slide = sliderLibrary.sliders[RVS.F.getOVSliderIndex(this.dataset.id)],
		 		txt = '<i class="material-icons fullpage_main_icon">playlist_add</i>';
			txt += '<div class="fullpage_title">'+RVS_LANG.embedingLine1+'</div>';
			txt += '<div class="fullpage_content">'+RVS_LANG.embedingLine2+'</div>';
			txt += '<div class="inputrow">';
			txt += "<input class='inputtocopy' id='embed_shortcode_a' readonly value='{{block class=\"Nwdthemes\\Revslider\\Block\\Revslider\" alias=\""+slide.alias+"\"}}'/>";
			txt += '<div class="basic_action_button onlyicon copyshortcode" data-clipboard-action="copy" data-clipboard-target="#embed_shortcode_a"><i class="material-icons">content_copy</i></div>';
			txt += '</div>';
			txt += '<div class="div20"></div>';
			txt += '<div class="fullpage_content">'+RVS_LANG.embedingLine2a+'</div>';
			txt += '<div class="inputrow">';
			txt += "<input class='inputtocopy' id='embed_shortcode_modal' readonly value='{{block class=\"Nwdthemes\\Revslider\\Block\\Revslider\" usage=\"modal\"  alias=\""+slide.alias+"\"}}'/>";
			txt += '<div class="basic_action_button onlyicon copyshortcode" data-clipboard-action="copy" data-clipboard-target="#embed_shortcode_modal"><i class="material-icons">content_copy</i></div>';
			txt += '</div>';
			txt += '<div class="div20"></div>';
			txt += '<div class="fullpage_content">'+RVS_LANG.embedingLine3+'</div>';

			txt += '<div class="div40"></div>';
			txt += '<div class="fullpage_title">'+RVS_LANG.embedingLine4+'</div>';
			txt += '<div class="fullpage_content">'+RVS_LANG.embedingLine5+'</div>';
			txt += '<div class="inputrow">';
			txt += "<input class='inputtocopy' readonly id='embed_shortcode_b' value='<block class=\"Nwdthemes\\Revslider\\Block\\Revslider\"><arguments><argument name=\"alias\" xsi:type=\"string\">"+slide.alias+"</argument></arguments></block>'>";
			txt += '<div class="basic_action_button onlyicon copyshortcode" data-clipboard-action="copy" data-clipboard-target="#embed_shortcode_b"><i class="material-icons">content_copy</i></div>';
			txt += '</div>';
			txt += '<div class="div15"></div>';
			txt += '<div class="fullpage_content">'+RVS_LANG.embedingLine6+'</div>';
			txt += '<div class="inputrow">';
			txt += '<input class="inputtocopy" readonly id="embed_shortcode_c" value="echo $block->getLayout()->createBlock(\'Nwdthemes\\Revslider\\Block\\Revslider\')->setAlias(\''+slide.alias+'\')->toHtml();">';
			txt += '<div class="basic_action_button onlyicon copyshortcode" data-clipboard-action="copy" data-clipboard-target="#embed_shortcode_c"><i class="material-icons">content_copy</i></div>';
			txt += '</div>';
			RVS.F.fullPageInfo.init({content:txt});
			RVS.F.initCopyClipboard('.copyshortcode');
			hideElementSubMenu({keepOverlay:false});
			window.lastBreacCrumbText="";
			jQuery('#rsl_bread_selected').html(window.lastBreacCrumbText);
		 });

		 // OPEN TEMPLATE LIBRARY
		 RVS.DOC.on('click','#new_slider_from_template',function() {
		 	RVS.F.openObjectLibrary({types:["moduletemplates"],filter:"all", selected:["moduletemplates"], success:{slider:"addNewSlider", draftpage:"addDraftPage"}});
		 });
	};


/*******************************
 	INTERNAL FUNCTIONS
*******************************/

	/*
	DARKEN OF WP ELEMENTS
	*/
	function initOverViewMenu() {
		window.ov_scroll_targets = [];
		var id = 0;
		jQuery('.rso_scrollmenuitem').each(function() {
			if (this.dataset.ref!==undefined) {

				window.ov_scroll_targets.push({
					obj : jQuery(this.dataset.ref),
					top : jQuery(this.dataset.ref).offset().top,
					height : jQuery(this.dataset.ref).height(),
					menu : jQuery(this),
					menu_js : this
				});
				this.dataset.ostref = id;
				id++;
			}
		});

		// No need to do it in Magento
		/*jQuery('#adminmenuwrap').append('<div id="wpadmin_overlay"></div>');
		jQuery('#wpcontent').append('<div id="wpadmin_overlay_top"></div>');
		punchgs.TweenLite.to(['#wpadmin_overlay','#wpadmin_overlay_top'],0.6,{opacity:1,ease:punchgs.Power3.easeInOut});
		punchgs.TweenLite.to(['#adminmenuback','#adminmenuwrap','#wpadminbar'],0.6,{filter:'grayscale(100%)',ease:punchgs.Power3.easeInOut});

		jQuery('#adminmenuback, #adminmenuwrap, #wpadminbar').hover(function() {
			punchgs.TweenLite.to(['#wpadmin_overlay','#wpadmin_overlay_top'],0.3,{opacity:0,ease:punchgs.Power3.easeInOut});
			punchgs.TweenLite.to(['#adminmenuback','#adminmenuwrap','#wpadminbar'],0.6,{filter:'grayscale(0%)',ease:punchgs.Power3.easeInOut});
		}, function() {
			punchgs.TweenLite.to(['#wpadmin_overlay','#wpadmin_overlay_top'],0.3,{opacity:1,ease:punchgs.Power3.easeInOut});
			punchgs.TweenLite.to(['#adminmenuback','#adminmenuwrap','#wpadminbar'],0.6,{filter:'grayscale(100%)',ease:punchgs.Power3.easeInOut});
		});*/

		overviewMenuResize();


		overviewMenuScroll();
		punchgs.TweenLite.to('#rs_overview_menu',1,{opacity:1,ease:punchgs.Power3.easeOut});
	}

	function overviewMenuScroll() {
		window.scroll_top = RVS.WIN.scrollTop();
		var lastitem = -1,
			lasttop = 0;
		window.cacheOMT = jQuery('#rs_overview').offset().top;
		punchgs.TweenLite.set(RVS.C.rsOVM,{top:Math.max(0, (window.cacheOMT-window.scroll_top))});

		for (var i in window.ov_scroll_targets) {
			if(!window.ov_scroll_targets.hasOwnProperty(i)) continue;
			if (window.ov_scroll_targets[i].obj.length>0) {
				window.ov_scroll_targets[i].top = window.ov_scroll_targets[i].obj.offset().top;
				if (!window.ov_scroll_targets[i].shown && window.ov_scroll_targets[i].top<(window.scroll_top+window.outerHeight)-200){
					punchgs.TweenLite.to(window.ov_scroll_targets[i].obj[0],1,{autoAlpha:1,ease:punchgs.Power3.easeInOut});
					window.ov_scroll_targets[i].shown = true;
				}
				window.ov_scroll_targets[i].height = window.ov_scroll_targets[i].obj.height();
				if (window.scroll_top+200>=window.ov_scroll_targets[i].top && window.scroll_top<=window.ov_scroll_targets[i].top + window.ov_scroll_targets[i].height) lastitem = i;
			}
		}
		lastitem = lastitem===-1 ? window.ov_scroll_targets.length-1 : lastitem;
		jQuery('.rso_scrollmenuitem').removeClass("active");
		window.ov_scroll_targets[lastitem].menu.addClass("active");


	}

	function overviewMenuResize() {
		punchgs.TweenLite.set('#rs_overview_menu',{width: jQuery('#wpbody').width()});
		jQuery('#wpadmin_overlay').width(jQuery('#adminmenuback').width());
		jQuery('#wpadmin_overlay_top').height(jQuery('#wpadminbar').height());
		overviewMenuScroll();
	}

	/*
	ANIMATE MENU OUT
	*/
	function hideElementSubMenu(_) {
		if (!_.keepOverlay)
			jQuery('.overview_elements').removeClass("infocus");
		jQuery('.rs_library_element.selected').each(function() {
				var t = jQuery(this);
				if (_.id===undefined || t.id!==_.id) {
					punchgs.TweenLite.to(t.find('.rsle_tbar'),0.3,{y:"-100%",transformOrigin:"50% 0%",ease:punchgs.Power3.easeOut});
					t.removeClass("menuopen");
					setTimeout(function() {
						window.lastBreacCrumbText="";
						jQuery('#rsl_bread_selected').html(window.lastBreacCrumbText);
						t.removeClass("selected");
					},300);
				}
			});
	}



	function initHistory() {
		jQuery('#plugin_history').RSScroll({
				wheelPropagation:true,
				suppressScrollX:false,
				minScrollbarLength:30
			});
	}


	/*
	EXTEND ARRAY IF VALUE NOT YET ADDED
	*/
	function extendArray(a,b) {
		if (b===undefined || a===undefined) return a;
		if (jQuery.isArray(b))
			for (var i in b) {
				if(!b.hasOwnProperty(i)) continue;
				if (jQuery.inArray(b[i], a)==-1) a.push(b[i]);
			}
		else
			if (jQuery.inArray(b, a)==-1) a.push(b);
		return a;
	}
	/*
	BUILD THE SELECT DROP DOWN
	*/
	function extendSelect(_) {
		if (_.array !==undefined && _.array.length>0) {
			//var group = jQuery('<optgroup label="'+_.group+'"></optgroup');
			for (var i in _.array ) {
					if(!_.array.hasOwnProperty(i)) continue;
					var o = _.sanitize ? new Option(RVS.F.sanitize_input(RVS.F.capitalise(_.array[i])),_.array[i],false,_.old===_.array[i]) : new Option(RVS.F.capitalise(_.array[i]),_.array[i],false,_.old===_.array[i]);
					o.className="dynamicadded";
					_.select.append(o);
			}
			//_.select.append(group);
		}
	}


	function getNewGlobalObject(obj) {
		var newGlobal = {};
		obj = obj===undefined || obj===null ? {} : obj;

		/* VERSION CHECK */
		newGlobal.version = newGlobal.version<"6.0.0" ? "6.0.0" : newGlobal.version;

		/* SLIDER BASICS */
		newGlobal.permission = _d(obj.permission,"admin");
		newGlobal.allinclude = _truefalse(_d(obj.allinclude,true));
		newGlobal.includeids = _d(obj.includeids,"");
		newGlobal.script = _d(obj.script,{
								footer : false,
								defer : false,
								full : false
							});

		newGlobal.lazyloaddata = _d(obj.lazyloaddata,"");
		newGlobal.fontdownload = _d(obj.fontdownload,"off");
		newGlobal.script.footer = _truefalse(newGlobal.script.footer);
		newGlobal.script.defer = _truefalse(newGlobal.script.defer);
		newGlobal.script.full = _truefalse(newGlobal.script.full);
		newGlobal.fontawesomedisable = _truefalse(obj.fontawesomedisable);

		newGlobal.fonturl = _d(obj.fonturl,"");
		newGlobal.size = _d(obj.size,{
								desktop : 1240,
								notebook : 1024,
								tablet : 778,
								mobile : 480
							});

		return newGlobal;
	}

	/*
	SET VALUE TO A OR B DEPENDING IF VALUE A EXISTS AND NOT UNDEFINED OR NULL
	*/
	function _d(a,b) {
		if (a===undefined || a===null)
			return b;
		else
			return a;
	}

	function _truefalse(v) {
		if (v==="false" || v===false || v==="off" || v===undefined || v===0 || v===-1)
			v=false;
		else
		if (v==="true" || v===true || v==="on")
			v=true;
		return v;
	}

})();

return RVS;
}
);