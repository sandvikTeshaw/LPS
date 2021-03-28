function getCookie(n){
var d=document,srch=n+'=',p,e
if(d.cookie.length>0){
p=d.cookie.indexOf(srch)
if(-1!=p){
p+=srch.length
e=d.cookie.indexOf(';',p)
if(-1==e)e=d.cookie.length
return unescape(d.cookie.substring(p,e))
}
}
d=null
return "";
}
function setCookie(str,val,persist)
{
val=escape(val)
if(persist) document.cookie=str+"="+val+";expires="+"Friday, 16-Oct-10 12:00:00 GMT"+";domain="+".intuit.com"+"; path=/;secure"
else document.cookie=str+"="+val+"; domain="+".intuit.com"+"; path=/;secure"
}
function expireCookie(Name) {
document.cookie=Name+"=0; expires="+"Friday, 12-Dec-00 12:00:00 GMT"+"; domain="+".intuit.com"+"; path=/"
}
function clearAuthCookies(){
document.cookie="A1AUTH=0; expires="+"Friday, 12-Dec-00 12:00:00 GMT"+"; domain="+".intuit.com"+"; path=/"
document.cookie="A1SSLAUTH=0; expires="+"Friday, 12-Dec-00 12:00:00 GMT"+"; domain="+".intuit.com"+"; path=/"
}
function sessionCookieExists() {
return (getCookie("A1SSLAUTH") != "")
}
function clearTechSuppCookies(){
document.cookie="TECHSUPP=0; expires="+"Friday, 12-Dec-00 12:00:00 GMT"+"; domain="+".intuit.com"+"; path=/"
}
function zBuf(){}
function _zBufAppend(s){if(null!=s)this.buf+=s}
function _zBufDelete(){this.buf="";this.buf=null}
p=zBuf.prototype
p.Append=_zBufAppend
p.Delete=_zBufDelete
p.buf=""
p=null
function zPrmFormatSubmit(t,b){
if(t._fDirty){
t._fDirty=false
if(t.fPageID){
if(gApp.fPgCnt!=t.fPageID){
return
}
}
FormatSubmit(t.fID,t.fVal,b)
}
b=null
}
function FormatSubmit(n,v,b){if(n&&null!=v)b.buf+=n+'='+escape(v)+'&'}
function zPrm(){}
function _zPrmInit(n,v,w,h){
var t=this
t.updt(n)
if(v||v==0)t.fVal=v
if(w)t._fWidth=w
if(h)t._fHeight=h
}
function _zprmDelete(){this.RemoveAllKids()}
function _zprmIsOKToSubmit(){
if(null==this._fKids)return true
var t=this,i,n=t._fKids.length
for(i=0;i<n;i++)if(t._fKids[i]&&!t._fKids[i].isOKToSubmit())return false
return true
}
function _zprmGetPixelWidth(){
var t=this
if(null==t._fWidth)return 0
var typ=t._fWidth.charAt(0)
if('c'==typ)return 0
var w=parseInt(t._fWidth.substr(1))
if ('x'==typ)return w
var pW=t.GetParentContainerWidth()
if(0==pW)return 0
return Math.floor(pW*(w/100))
}
function _zprmGetNumChild(){
if(null==this._fKids)return 0
return this._fKids.length
}
function _zprmGetNthChild(n){
if(null==this._fKids||n>=this._fKids.length)return null
return this._fKids[n]
}
function _zprmGetParentContainerWidth(){
var pW=0,prnt=this.fParent
while(null!=prnt){
if(prnt.GetContainerWidth)pW=prnt.GetContainerWidth()
if(pW>0)break
prnt=prnt.fParent
}
prnt=null
return pW
}
function _zprmUpdt(n){
this.fID=n
if(this.fID)this.fPageID=gApp.fPgCnt
}
function _zprmAddChild(p){
if(null==p)return
var t=this
if(null==t._fKids)t._fKids=new Array()
t._fKids.push(p)
p.fParent=t
}
function _zprmRemoveAllKids(){
var t=this
if(null==t._fKids)return
var i,n=t._fKids.length
for(i=0;i<n;i++){
if(t._fKids[i]){
t._fKids[i].Delete()
t._fKids[i].fParent=null
t._fKids[i]=null
}
}
t._fKids=null
}
function _zprmRenderLineBreak(d,p){p.appendChild(d.createElement('BR'))}
function _zprmSetVal(v){
if(null==v){
this.fVal=null
this._fDirty=false
}else if(v!=this.fVal){
this.fVal=v
this._fDirty=true
}
}
function _zprmGetSubmit(prnt,b){
var t=this
if(t._fKids){
var i,n=t._fKids.length,p
for(i=0;i<n;i++){
p=t._fKids[i]
if(p)p.getSubmit(t,b)
p=null
}
}
zPrmFormatSubmit(t,b)
}
function _zprmOnResize(w,h){
var t=this
if(t._fKids){
var i,n=t._fKids.length,p
for(i=0;i<n;i++){
p=t._fKids[i]
if(p)p.onResize(w,h)
p=null
}
}
}
var p=zPrm.prototype
p.fID=null
p.fVal=null
p.fParent=null
p.Delete=_zprmDelete
p.getSubmit=_zprmGetSubmit
p.isOKToSubmit=_zprmIsOKToSubmit
p.GetNumChild=_zprmGetNumChild
p.GetNthChild=_zprmGetNthChild
p.GetPixelWidth=_zprmGetPixelWidth
p.GetParentContainerWidth=_zprmGetParentContainerWidth
p.updt=_zprmUpdt
p.SetVal=_zprmSetVal
p.AddChild=_zprmAddChild
p.RemoveAllKids=_zprmRemoveAllKids
p.onResize=_zprmOnResize
p.RenderLineBreak=_zprmRenderLineBreak
p._initPrm=_zPrmInit
p._fWidth=null
p._fHeight=null
p._fDirty=false
p._fKids=null
p=null
var eOmnitureBeacon= 0
var eOffermaticaBeacon= 1
var eDoubleClickBeacon= 2
var eClickTracksBeacon= 3
var head = document.getElementsByTagName('head').item(0)
var scriptTagSID = document.getElementById("SIDScript")
if(!scriptTagSID)
{
var script = document.createElement('script')
script.src = "sessionId.js"
script.type = 'text/javascript'
script.id = "SIDScript"
head.appendChild(script)
}
var s_channel=""
var s_pageName=""
var s_events=""
var s_products=""
var s_purchaseID=""
var s_eVar1=""
var s_eVar19=""
var s_prop1=""
var s_prop5=""
var s_prop6=""
var s_prop7=""
var s_prop8=""
var head = document.getElementsByTagName('head').item(0)
var scriptTag = document.getElementById("OMScript")
if(!scriptTag)
{
var script = document.createElement('script')
script.src = "s_code_remote.js"
script.type = 'text/javascript'
script.id = "OMScript"
head.appendChild(script)
}
zBeacon.prototype=new zPrm()
var p=zBeacon.prototype
p.constructor=zBeacon
p.renderOmniture=_zBeaconRenderOmniture
p.renderOffermatica=_zBeaconRenderOffermatica
p.renderDoubleClick=_zBeaconRenderDoubleClick
p.renderClickTracks=_zBeaconRenderClickTracks
p.Render=_zBeaconRender
p._setBeaconArea=_zBeaconSetArea
p=null
function zBeacon(n,typ, k, v){
var t=this
t._initPrm(n)
t._fType=typ
t._fKeys=k
t._fValues=v
t._fShowData=false
t._fParent=t._setBeaconArea()
}
function _zBeaconRender(d,p){
var t=this
switch(t._fType){
case eOmnitureBeacon:
t.renderOmniture(d)
break
case eOffermaticaBeacon:
t.renderOffermatica(d)
break
case eDoubleClickBeacon:
t.renderDoubleClick(d)
break
case eClickTracksBeacon:
t.renderClickTracks(d)
break
default:
break
}
}
function _zBeaconRenderOmniture(d)
{
var t=this
try{
var val=""
var s=""
var pt=""
var cs=""
var convert=""
var cartadd=""
var citem=""
var custrpt=""
var product=""
var updx=0
var cdx=0
var idx=-1
var total=0
var prodCode=""
var id=""
var title=""
var ttsid=getSessionId()
var kttsid="ttsid"
s_channel=""
s_pageName=""
s_events=""
s_products=""
s_purchaseID=""
s_eVar1=""
s_eVar19=""
s_prop1=""
s_prop2=""
s_prop5="TTO"
s_prop6=""
s_prop7=""
s_prop8=""
p = t._fParent;
if (!p) return
for (var i=0; i<t._fKeys.length; i++){
idx=-1
val=t._fValues[i]
if (typeof val != 'number')
idx=val.indexOf(kttsid)
if(-1!=idx){
var nv = val.replace(kttsid, ttsid)
t._fValues[i]=nv
}
if ("s_pageName"==t._fKeys[i])
s_pageName=t._fValues[i]
else
if ("s_channel"==t._fKeys[i])
s_channel=t._fValues[i]
else
if ("s_purchaseID"==t._fKeys[i])
s_purchaseID=t._fValues[i]
else
if ("s_events"==t._fKeys[i])
s_events=t._fValues[i]
else
if ("s_prop1"==t._fKeys[i])
s_prop1=t._fValues[i]
else
if ("s_prop2"==t._fKeys[i])
s_prop2=t._fValues[i]
else
if ("s_prop6"==t._fKeys[i])
s_prop6=t._fValues[i]
else
if ("s_prop7"==t._fKeys[i])
s_prop7=t._fValues[i]
else
if ("s_prop8"==t._fKeys[i])
s_prop8=t._fValues[i]
else
if ("s_products"==t._fKeys[i])
s_products=t._fValues[i]
else
if ("s_eVar1"==t._fKeys[i])
s_eVar1=t._fValues[i]
else
if ("s_eVar19"==t._fKeys[i]){
s_eVar19=t._fValues[i]
}
}
var re = /[+]/g
s_pageName=s_pageName.replace(re, " ")
s_channel=s_channel.replace(re, " ")
s_code=s_dc(s_account)
s = rs
}
catch(e)
{
}
appendImg(d,p,s,1,1)
}
function _zBeaconRenderOffermatica(d)
{
var t=this
var ofid=""
var pl=""
var tmp=""
for (var i=0; i<t._fKeys.length; i++){
if ("ofid"==t._fKeys[i]){
if (ofid.length>0){
pl+="&ofid="
pl+=ofid
pl+=tmp
tmp=""
}
ofid=t._fValues[i]
}
else {
tmp+="&"
tmp+=t._fKeys[i]
tmp+="="
tmp+=t._fValues[i]
}
}
pl+="&ofid="
pl+=ofid
pl+=tmp
var f = t._fParent
if (f){
var s="beacon.htm"
s+="?"
s+=pl
f.location.replace(s)
}
}
function _zBeaconRenderDoubleClick(d)
{
var t=this
var p = t._fParent
if (!p) return
var fn="https://ad.doubleclick.net/activity;src=1121318;"
var pl=""
var val=""
var key=""
var idx=-1
var pd=false
var axel = Math.random()+"";
var ax = axel * 10000000000000;
for (var i=0; i<t._fKeys.length; i++){
if (i>0) pl+=';'
pl+=t._fKeys[i]
pl+='='
val=t._fKeys[i]
if (typeof val != 'number'){
idx=val.indexOf("products")
if(-1!=idx){
pd=true
}
}
val=t._fValues[i]
if (typeof val != 'number'){
idx=val.indexOf("random")
if(-1!=idx){
var nv = val.replace("random", ax)
t._fValues[i]=nv
}
}
pl+=t._fValues[i]
}
fn+=pl
fn+="?"
if (pd)
fn+="https://servedby.advertising.com/action/type=918754229/bins=1/rich=0/mnum=1516/logs=0"
appendImg(d,p,fn,1,1)
}
function _zBeaconRenderClickTracks(d)
{
var t=this
var beaconDev="0"
var dev=""
var f = t._fParent
var fd=f.document
var src=""
var id=""
if("1"==beaconDev){
dev='%26dev%3d1'
}
for (var i=0; i<t._fKeys.length; i++){
if ("id"==t._fKeys[i]){
id=t._fValues[i]
break
}
}
src = "https://ct.intuit.com/cgi-bin/ctasp-server.cgi" +
'?i=b8v6Xp90i5NNt4Tk' +
'&X=ClickTracksPageTitle%3d' + escape(document.title) + '%26page%3d' + id + dev +
'&d=qtwuxintuitcom'
fd.write('<s' + 'cript language="javascript" type="text/javascript" src="' + src + '"></s' + 'cript>')
}
function _zBeaconSetArea(){
var t=this
var d=document
var bf
switch(t._fType){
case eOmnitureBeacon:
case eDoubleClickBeacon:
bf = document.getElementById("BeaconArea")
if (!bf){
bf = appendLayer(d,document.body,"BeaconArea")
bf.id="BeaconArea"
}
break;
case eOffermaticaBeacon:
var f = document.getElementById("beacon")
if (!f){
appendIFrame(d,document.body,"beacon","Beacon","blank.htm")
}
bf = frames["beacon"]
break;
case eClickTracksBeacon:
var f = document.getElementById("clicktracks")
if (!f){
appendIFrame(d,document.body,"clicktracks","Beacon","blank.htm")
}
bf = frames["clicktracks"]
break;
}
return bf
}
function isNN4(){return (null!=document.layers)}
function isNN6(){return (navigator.userAgent.toLowerCase().indexOf("mozilla/5")!=-1)}
function isIE5(){return (navigator.appVersion.toLowerCase().indexOf("msie 5")!=-1||navigator.appVersion.toLowerCase().indexOf("msie 6")!=-1)}
function isIE6(){return (navigator.appVersion.toLowerCase().indexOf("msie 6")!=-1)}
function isWin(){return ("Win32"==navigator.platform||"Windows"==navigator.platform)}
function isMac(){return (navigator.userAgent.toLowerCase().indexOf("mac")!=-1)}
function isSafari(){return (navigator.userAgent.toLowerCase().indexOf("safari")!=-1)}
function isFireFox(){return (navigator.userAgent.toLowerCase().indexOf("firefox")!=-1)}
function isIE5_5(){
var a=new Array()
if(a.push)return true
return false
}
var gbNN4=isNN4()
var gbIE5=isIE5()
var gbIE5_5=(gbIE5)?isIE5_5():false
var gbIE6=isIE6()
var gbNN6=isNN6()
var gbDom=(gbNN6||gbIE5)
var gbIE4=(!(gbDom||gbNN4))
var gbWin=isWin()
var gbMac=isMac()
var gbUnix=(!(gbWin||gbMac))
var gbSafari=(gbMac&&isSafari())
var gbFireFox=isFireFox()
function getPlat(){
var s=''
if(gbNN4)s="nn4"
else if(gbNN6)s="nn6"
else if(gbIE5)s="ie5"
else s="ie4"
return s
}
function isJavaInstalled(){
var bJava=true
if(gbNN4)bJava=navigator.javaEnabled()
return bJava
}
function isUndefined(v){
return(v==undefined||null==v)
}
function isDefined(v){
return(v!=undefined&&v!=null)
}
zColor = function(hex)
{
if(!hex) return;
if(hex.match(this.reRGB))
{
var a = hex.match(this.reRGBVal);
if(!a || a.length != 4) throw "invalid";
this.red = parseInt(a[1]);
this.green = parseInt(a[2]);
this.blue = parseInt(a[3]);
}
else if(hex.match(this.reHex))
{
var a = hex.match(this.reHexVal);
if(!a || a.length != 4) throw "invalid";
this.red = parseInt(a[1], 16);
this.green = parseInt(a[2], 16);
this.blue = parseInt(a[3], 16);
}
else
{
hex = hex.toLowerCase();
switch(hex)
{
case "black": break;
case "red": this.red = 255; break;
case "white": this.red=255; this.green=255; this.blue=255; break;
default: throw hex + " unknown";
}
}
}
zColor.prototype = {
red : 0,
green : 0,
blue : 0,
reHex : /^#/,
reHexVal : /^#([A-Fa-f0-9]{2})([A-Fa-f0-9]{2})([A-Fa-f0-9]{2})/,
reRGB : /^rgb/,
reRGBVal : /^rgb.*\((\d+),\s*(\d+),\s*(\d+)/,
copyTo : function(c)
{
if(!c) return;
c.red = this.red;
c.green = this.green;
c.blue = this.blue;
},
clone : function()
{
var c = new zColor();
this.copyTo(c);
return c;
},
add : function(r, g, b)
{
this.red += r;
if(this.red < 0 ) this.red = 0;
if(this.red > 255) this.red = 255;
this.green += g;
if(this.green < 0 ) this.green = 0;
if(this.green > 255) this.green = 255;
this.blue += b;
if(this.blue < 0 ) this.blue = 0;
if(this.blue > 255) this.blue = 255;
},
isEqual : function(cmp) {return (cmp && this.red == cmp.red && this.green == cmp.green && this.blue == cmp.blue);},
toString : function()
{
var clr = '#';
var n, s;
for(var i=0; i<3; ++i)
{
switch(i)
{
case 0: n = this.red; break;
case 1: n = this.green; break;
case 2: n = this.blue; break;
}
s = Math.round(n).toString(16);
if(s.length < 2) clr += '0';
clr += s;
}
return clr;
}
}
Animator=function(){}
Animator=new function(){
var kIEOpacityFilter="DXImageTransform.Microsoft.Alpha";
var Animator=this;
Animator.fadeColor=function(e,fromClrStr,toClrStr,iterations, intervalTime,bBkg){
var css=e.style;
if(bBkg&&css.backgroundColor==toClrStr)return;
if(!bBkg&&css.color==toClrStr)return;
var fromClr=new zColor(fromClrStr);
var endClr=new zColor(toClrStr);
var cur=fromClr.clone();
if(iterations<=0)iterations=1;
var rInc=Math.floor((endClr.red-fromClr.red)/iterations);
var gInc=Math.floor((endClr.green-fromClr.green)/iterations);
var bInc=Math.floor((endClr.blue-fromClr.blue)/iterations);
fromClr.copyTo(endClr);
endClr.add((rInc*iterations),(gInc*iterations),(bInc*iterations));
var key=this._locateId(e)+fromClrStr+toClrStr;
e.fadeColorKey=key;
function nextColor(){
if(isUndefined(e.fadeColorKey)||e.fadeColorKey!=key)clearInterval(tmrId);
if(cur.isEqual(endClr)){
if(bBkg)css.backgroundColor=toClrStr;
else css.color=toClrStr;
clearInterval(tmrId);
fromClr.copyTo(cur);
}
else{
cur.add(rInc,gInc,bInc);
if(bBkg)css.background=cur.toString();
else css.color=cur.toString();
}
}
var tmrId=setInterval(nextColor, intervalTime);
}
Animator._locateId=function(e){
var id=e.id;
if(isUndefined(id))id=e.firstChild;
if(isUndefined(id))id=e.tagName;
return id;
}
}
kCurrentYear = "2005"
kLastYear = "2004"
kImagePath = "/img/"
kTechSupportURL = "http://support.turbotax.com/go/web"
kLicenseStatement = "Service and License agreement"
kBullet = " \u2022 "
kSpace = "\u00A0"
kAsterisk = "\u002A"
d = document
gTabIndex=0
gAllowClick=true
kOmnitureTitle = "s_pageName"
kOmnitureSection = "s_channel"
function cancelEvent(evt) {
if (gbIE5)
{
window.event.cancelBubble=true
window.event.returnValue=false
}
else
{
evt.preventDefault()
evt.stopPropagation()
}
}
function getWindowWidth(){
var w=(gbNN6)?window.innerWidth:(gbIE6)?document.documentElement.clientWidth:document.body.clientWidth
return w
}
function getWindowHeight(){
var h=(gbNN6)?window.innerHeight:(gbIE6)?document.documentElement.clientHeight:document.body.clientHeight
return h
}
function setFocus(e){
e.style.borderColor='#333333'
if (typeof Animator!="undefined" && null!=Animator)
Animator.fadeColor (e,'#ffffff','#cfe3fe',8, 30,true)
else
e.style.backgroundColor='#cfe3fe'
}
function setBlur(e){
e.style.borderColor='#666666'
if (typeof Animator!="undefined" && null!=Animator)
Animator.fadeColor (e,'#cfe3fe','#ffffff',8, 30,true)
else
e.style.backgroundColor='#ffffee'
}