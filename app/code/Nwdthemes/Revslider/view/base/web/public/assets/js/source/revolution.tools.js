/********************************************
	-	THEMEPUNCH TOOLS Ver. 6.0     -
	 Last Update of Tools 12.02.2019
*********************************************/

define('revolutionTools', [], function() {

	var punchgs;

	var RS_CacheGS = window.GreenSockGlobals, RS_CacheGS_queue = window._gsQueue,RS_Cache_define = window._gsDefine; window._gsDefine = null;delete window._gsDefine; punchgs = window.GreenSockGlobals = {};

	/* CREATE CUSTOM EASE */
	punchgs.SFXBounceLite = punchgs.CustomBounce.create("SFXBounceLite", { strength:0.3 ,squash:1, squashID:"SFXBounceLite-squash"});
	punchgs.SFXBounceSolid = punchgs.CustomBounce.create("SFXBounceSolid", { strength:0.5,squash:2,squashID:"SFXBounceSolid-squash"});
	punchgs.SFXBounceStrong = punchgs.CustomBounce.create("SFXBounceStrong", { strength:0.7,squash:3,squashID:"SFXBounceStrong-squash"});
	punchgs.SFXBounceExtrem = punchgs.CustomBounce.create("SFXBounceExtrem", { strength:0.9,squash:4,squashID:"SFXBounceExtrem-squash"});

	punchgs.BounceLite = punchgs.CustomBounce.create("BounceLite", { strength:0.3 });
	punchgs.BounceSolid = punchgs.CustomBounce.create("BounceSolid", { strength:0.5});
	punchgs.BounceStrong = punchgs.CustomBounce.create("BounceStrong", { strength:0.7});
	punchgs.BounceExtrem = punchgs.CustomBounce.create("BounceExtrem", { strength:0.9});

	try{
		window.GreenSockGlobals = null;
		window._gsQueue = null;
		window._gsDefine = null;
		delete(window.GreenSockGlobals);
		delete(window._gsQueue);
		delete(window._gsDefine);
	} catch(e) {}

	try{
		window.GreenSockGlobals = RS_CacheGS;
		window._gsQueue = RS_CacheGS_queue;
		window._gsDefine = RS_Cache_define;
	} catch(e) {}

	return punchgs;
});