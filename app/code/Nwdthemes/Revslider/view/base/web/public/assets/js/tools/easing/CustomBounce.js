/* CUSTOM BOUNCE */
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
var _gsScope="undefined"!=typeof module&&module.exports&&"undefined"!=typeof global?global:this||window;(_gsScope._gsQueue||(_gsScope._gsQueue=[])).push(function(){"use strict";_gsScope._gsDefine("easing.CustomBounce",["easing.CustomEase"],function(a){var b,c=function(a){var b,c=a.length,d=1/a[c-2],e=1e3;for(b=2;c>b;b+=2)a[b]=(a[b]*d*e|0)/e;a[c-2]=1},d=function(b,c){this.vars=c=c||{},c.squash&&(this.squash=new a(c.squashID||b+"-squash")),a.call(this,b),this.bounce=this,this.update(c)};return d.prototype=b=new a,b.constructor=d,b.update=function(b){b=b||this.vars;var d,e,f,g,h,i,j,k=.999,l=Math.min(k,b.strength||.7),m=l,n=(b.squash||0)/100,o=n,p=1/.03,q=.2,r=1,s=.1,t=[0,0,.07,0,.1,1,.1,1],u=[0,0,0,0,.1,0,.1,0];for(h=0;200>h&&(q*=m*((m+1)/2),r*=l*l,i=s+q,f=s+.49*q,g=1-r,d=s+r/p,e=f+.8*(f-d),n&&(s+=n,d+=n,f+=n,e+=n,i+=n,j=n/o,u.push(s-n,0,s-n,j,s-n/2,j,s,j,s,0,s,0,s,j*-.6,s+(i-s)/6,0,i,0),t.push(s-n,1,s,1,s,1),n*=l*l),t.push(s,1,d,g,f,g,e,g,i,1,i,1),l*=.95,p=r/(i-e),s=i,!(g>k));h++);if(b.endAtStart){if(f=-.1,t.unshift(f,1,f,1,-.07,0),o)for(n=2.5*o,f-=n,t.unshift(f,1,f,1,f,1),u.splice(0,6),u.unshift(f,0,f,0,f,1,f+n/2,1,f+n,1,f+n,0,f+n,0,f+n,-.6,f+n+.033,0),h=0;h<u.length;h+=2)u[h]-=f;for(h=0;h<t.length;h+=2)t[h]-=f,t[h+1]=1-t[h+1]}return n&&(c(u),u[2]="C"+u[2],this.squash||(this.squash=new a(b.squashID||this.id+"-squash")),this.squash.setData("M"+u.join(","))),c(t),t[2]="C"+t[2],this.setData("M"+t.join(","))},d.create=function(a,b){return new d(a,b)},d.version="0.2.1",d},!0)}),_gsScope._gsDefine&&_gsScope._gsQueue.pop()(),function(a){"use strict";var b=function(){return(_gsScope.GreenSockGlobals||_gsScope)[a]};"undefined"!=typeof module&&module.exports?(require("./CustomEase.min.js"),require("../TweenLite.min.js"),module.exports=b()):"function"==typeof define&&define.amd&&define(["TweenLite","CustomEase"],b)}("CustomBounce");

