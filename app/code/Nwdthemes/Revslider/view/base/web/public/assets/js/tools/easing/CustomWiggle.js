/* CUSTOM WIGGLE */
/*!
 * VERSION: 0.2.1
 * DATE: 2018-02-15
 * UPDATES AND DOCS AT: http://greensock.com
 *
 * @license Copyright (c) 2008-2018, GreenSock. All rights reserved.
 * This work is subject to the terms at http://greensock.com/standard-license or for
 * Club GreenSock members, the software agreement that was issued with your membership.
 *
 * @author: Jack Doyle, jack@greensock.com
 **/
var _gsScope="undefined"!=typeof module&&module.exports&&"undefined"!=typeof global?global:this||window;(_gsScope._gsQueue||(_gsScope._gsQueue=[])).push(function(){"use strict";_gsScope._gsDefine("easing.CustomWiggle",["easing.CustomEase","easing.Ease"],function(a,b){var c,d={easeOut:new a("","M0,1,C0.7,1,0.6,0,1,0"),easeInOut:new a("","M0,0,C0.104,0,0.242,1,0.444,1,0.644,1,0.608,0,1,0"),anticipate:new a("","M0,0,C0,0.222,0.024,0.386,0.06,0.402,0.181,0.455,0.647,0.646,0.7,0.67,0.9,0.76,1,0.846,1,1"),uniform:new a("","M0,0,C0,0.95,0.01,1,0.01,1,0.01,1,1,1,1,1,1,1,1,0.01,1,0")},e=new a,f=function(c,d){return c=c.getRatio?c:b.map[c]||new a("",c),c.rawBezier||!d?c:{getRatio:function(a){return 1-c.getRatio(a)}}},g=function(b,c){this.vars=c||{},a.call(this,b),this.update(this.vars)};return g.prototype=c=new a,c.constructor=g,c.update=function(a){a=a||this.vars;var b,c,g,h,i,j,k,l,m,n=0|(a.wiggles||10),o=1/n,p=o/2,q="anticipate"===a.type,r=d[a.type]||d.easeOut,s=e,t=1e3;if(q&&(s=r,r=d.easeOut),a.timingEase&&(s=f(a.timingEase)),a.amplitudeEase&&(r=f(a.amplitudeEase,!0)),j=s.getRatio(p),k=q?-r.getRatio(p):r.getRatio(p),l=[0,0,j/4,0,j/2,k,j,k],"random"===a.type){for(l.length=4,b=s.getRatio(o),c=2*Math.random()-1,m=2;n>m;m++)p=b,k=c,b=s.getRatio(o*m),c=2*Math.random()-1,g=Math.atan2(c-l[l.length-3],b-l[l.length-4]),h=Math.cos(g)*o,i=Math.sin(g)*o,l.push(p-h,k-i,p,k,p+h,k+i);l.push(b,0,1,0)}else{for(m=1;n>m;m++)l.push(s.getRatio(p+o/2),k),p+=o,k=(k>0?-1:1)*r.getRatio(m*o),j=s.getRatio(p),l.push(s.getRatio(p-o/2),k,j,k);l.push(s.getRatio(p+o/4),k,s.getRatio(p+o/4),0,1,0)}for(m=l.length;--m>-1;)l[m]=(l[m]*t|0)/t;l[2]="C"+l[2],this.setData("M"+l.join(","))},g.create=function(a,b){return new g(a,b)},g.version="0.2.1",g.eases=d,g},!0)}),_gsScope._gsDefine&&_gsScope._gsQueue.pop()(),function(a){"use strict";var b=function(){return(_gsScope.GreenSockGlobals||_gsScope)[a]};"undefined"!=typeof module&&module.exports?(require("./CustomEase.min.js"),require("../TweenLite.min.js"),module.exports=b()):"function"==typeof define&&define.amd&&define(["TweenLite","CustomEase"],b)}("CustomWiggle");
