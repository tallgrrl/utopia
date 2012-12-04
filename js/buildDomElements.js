/** Ph2.BuildDomElements.js
 **
 ** Written By Tracy Lauren
 ** The Job Route Corporation
 ** July 4, 2010
 **
 Examples:
  $("#foo").html(createBasicElement("h1",{"align":"right"},"bar"));

   $("#bar").html(createNestedElements("div",{"align":"right"},
   [
      createNestedElements("form",{"id":"testFrm","action":"/foo"},
      [
         createBasicElement("h3",{},"Hey"),
         BuildFormElement.textField("fe1", null, "some value", {"style":"width:350px","class":"newt"}),
         BuildFormElement.hiddenField("fe2", null, "some value", {}),
         BuildFormElement.file("fe3", null, null, {}),
         BuildFormElement.passwordField("fe4", null, "some value", {}),
         BuildFormElement.radio("fe5", null, "some value", true, {}),
         BuildFormElement.checkbox("fe6", null, "some value", true, {}),
         BuildFormElement.textarea("fe7", null, "some value",  {}, 50, 15),
         BuildFormElement.button("fe8", null, "button", {}),
         BuildFormElement.submit("fe9", null, "submit", {}),
         BuildFormElement.reset("fe10", null, "reset", {})
      ]),
      "This is for fun"
    ]));


UPDATE LOG: Fab 27, 2012 BuildElement.populatedSelect  -- chenged optList[i].cloneNode(true) from optList[i];  now mulitple copies of this list can be used on page at any time;
 *******/

window.createBasicElement   = function(ele,att,child) { var div=document.createElement(ele); for(var obj in att) { if (obj == "class") div.className = att[obj]; div.setAttribute(obj,att[obj]); } if(child) { if(!child.substring) { div.appendChild(child); } else { div.appendChild(document.createTextNode(child)); } } return div; };
window.createNestedElements = function(ele,att,list)  { var div=document.createElement(ele); for(var obj in att) { if (obj == "class") div.className = att[obj]; div.setAttribute(obj,att[obj]); } for(var i=0;i<list.length;i++) { if(!list[i].substring){ div.appendChild(list[i]); }else { div.appendChild(document.createTextNode(list[i])); } } return div; };

if(!window.BuildFormElement)
{
   window.BuildFormElement=new Object();
   var BuildFormElement=window.BuildFormElement;

   BuildFormElement.textField     = function(fieldName,id,value,attr,eh) { if(!fieldName) { alert("invalid FieldName"); return false; } var newAttr={}; newAttr['name']=fieldName; if(!id) { newAttr['id']=fieldName; } else {  newAttr['id']=id; } if(value) { newAttr['value']=value; } for(var obj in attr) { newAttr[obj]=attr[obj]; } var x=createBasicElement("input",newAttr); if(eh) { for(obj in eh){ x[obj] = eh[obj]; } }  return x; };
   BuildFormElement.hiddenField   = function(fieldName,id,value,attr){var atrib = {};if(attr){atrib = attr;}atrib['type']='hidden';return this.textField(fieldName,id,value,atrib,{});};
   BuildFormElement.file          = function(fieldName,id,value,attr){attr['type']='file';return this.textField(fieldName,id,value,attr,{});};
   BuildFormElement.passwordField = function(fieldName,id,value,attr,eh){attr['type']='password';return this.textField(fieldName,id,value,attr,eh);};
   BuildFormElement.radio         = function(fieldName,id,value,checked,attr,eh){attr['type']='radio';if(checked){attr['checked']='true';}return this.textField(fieldName,id,value,attr,eh);};
   BuildFormElement.checkbox      = function(fieldName,id,value,checked,attr,eh){attr['type']='checkbox';if(checked){attr['checked']='true';}return this.textField(fieldName,id,value,attr,eh);};
   BuildFormElement.submit        = function(fieldName,id,value,attr,eh){attr['type']='submit';return this.textField(fieldName,id,value,attr,eh);};
   BuildFormElement.reset         = function(fieldName,id,value,attr,eh){attr['type']='reset';return this.textField(fieldName,id,value,attr,eh);};
   BuildFormElement.button        = function(fieldName,id,value,attr,eh){var newAttr={};if(fieldName){newAttr['name']=fieldName;}if(id){newAttr['id']=id;}for(var obj in attr){newAttr[obj]=attr[obj];}var butt=createBasicElement("button",newAttr,value);if(eh){for(obj in eh){butt[obj] = eh[obj];}}return butt;};
   BuildFormElement.image         = function(fieldName,id,src,value,attr,eh){var newAttr={"type":"image","src":src};if(fieldName){newAttr['name']=fieldName;}if(id){newAttr['id']=id;}for(var obj in attr){newAttr[obj]=attr[obj];}return BuildFormElement.textField(fieldName,id,"",newAttr,eh);};
   BuildFormElement.textarea      = function(fieldName,id,value,attr,eh,cols,rows){if(!fieldName){alert("invalid FieldName");return false;}var newAttr={};newAttr['name']=fieldName;if(!id){newAttr['id']=fieldName;}else{newAttr['id']=id;}if(cols){newAttr['cols']=cols;}if(rows){newAttr['rows']=rows;}else{newAttr['id']=id;}for(var obj in attr){newAttr[obj]=attr[obj];}var x=createBasicElement("textarea",newAttr,value);if(eh){for(obj in eh){x[obj] = eh[obj];}}return x;};
   BuildFormElement.select        = function(fieldName,id,attr,eh){if(!fieldName){alert("invalid FieldName");return false;}var newAttr={};newAttr['name']=fieldName;if(!id){newAttr['id']=fieldName;}else{newAttr['id']=id;}for(var obj in attr){newAttr[obj]=attr[obj];}var x=createBasicElement("select",newAttr);if(eh){for(obj in eh){x[obj] = eh[obj];}}return x;};
   BuildFormElement.option        = function(txt,val,selectedVal){if(selectedVal==val){return createBasicElement("option",{"selected":"true","value":val},txt);}return createBasicElement("option",{"value":val},txt);};
}
if(!window.BuildElement)
{
   window.BuildElement=new Object();
   var BuildElement=window.BuildElement;
   BuildElement.form            = function(formName,id,action,method,enctype){var newAttr={};if(!formName){newAttr['name']=fieldName;}if(id){newAttr['id']=id;}if(action){newAttr['action']=action;}if(method){newAttr['method']=method;}if(enctype){newAttr['enctype']="multipart/form-data";}return createBasicElement("form",newAttr);};
   BuildElement.a               = function(name,id,txt,attr,eh){var newAttr={};if(name){newAttr['name']=name;}if(id){newAttr['id']=id;}for(var obj in attr){newAttr[obj]=attr[obj];}var x=createBasicElement("a",newAttr,txt);if(eh){for(obj in eh){x[obj] = eh[obj];}}return x;};
   BuildElement.encloseda       = function(left,a,right){return createNestedElements("span",{},[left,a,right]);};
   BuildElement.populatedSelect = function(selectObj, optList){for(var i=0;i<optList.length;i++){selectObj.appendChild(optList[i].cloneNode(true));}return selectObj;};
   BuildElement.populatedList   = function(ListObj, LiList){for(var i=0;i<LiList.length;i++){ListObj.appendChild(LiList[i]);}return ListObj;};
   BuildElement.image           = function(src, height, width, attr, eh){var atrib = {};if(attr){atrib = attr;}atrib['src']=src;atrib['height']=height;atrib['width']=width;var img=createBasicElement("img",atrib);if(eh){for(obj in eh){img[obj] = eh[obj];}} return img;};
   BuildElement.br              = function(){return createBasicElement("br",{}); };
}
// pull in other Javascripts, and append to head
window.AssembleJScripts = function(arr){for(var i=0;i<arr.length;i++){var scr=createBasicElement("script",{"language":"javascript","type":"text/javascript","src":arr[i]});$("head").append(scr);}};
window.AssembleCss      = function(arr){ for(var i=0;i<arr.length;i++) { var scr=createBasicElement("link",{"rel":"stylesheet","type":"text/css","media":"screen","href":arr[i]}); $("head").append(scr); }};

//bad Browser detection
badBrowser = function(){ if($.browser.msie && parseInt($.browser.version, 10) <= 6){ return true;} return false; };
killVisual = function(){
   
   $("body").animate({ opacity:0 }, 1000, function(){
       $("body").html(
         createNestedElements("div",{"style":"width:500px;height:400px;margin-left:auto;margin-right:auto;margin-top:100px;border:1px solid #000"},
         [
            createBasicElement("h2",{"style":"width:555px; height:auto; margin:0px; padding:0px 10px 10px 10px; position:relative; float:left; overflow:hidden; color:#7fa20d; font-size:17px; font-weight:bold; text-shadow:0px 1px 0px #f9f9f9;"},"This site does not support " + navigator.appName + " version " + navigator.appVersion),
            createBasicElement("p",{"style":"width:555px; height:auto; margin:0px; padding:0px 0px 15px 0px; position:relative; float:left; overflow:hidden; line-height:21px;"},"If you are seeing this message, it is because thejobroute.com does not support " + navigator.appName + " version " + navigator.appVersion + ". We are not telling you to stop using " + navigator.appName + ", simply upgrade to a newer more mature version. Here are a few suggestions:"),
            BuildElement.image("/images/browser_firefox.gif", "150px", "150px", {"style":"border:1px solid red","alt":"Firefox 3+","title":"Our Recomendation - Firefox 3+"}, {"onclick":function(){window.location.href='http://www.mozilla.com/firefox/';}}),
            BuildElement.image("/images/browser_ie.gif", "150px", "150px", {"style":"border:1px solid red","alt":"Internet Explorer","title":"Internet Explorer 7+"}, {"onclick":function(){window.location.href='http://www.microsoft.com/windows/Internet-explorer/default.aspx';}}),
            BuildElement.image("/images/browser_chrome.gif", "150px", "150px", {"style":"border:1px solid red","alt":"Chrome 2.0+","title":"Chrome 2.0+"}, {"onclick":function(){window.location.href='http://www.google.com/chrome';}}),
            BuildElement.image("/images/browser_safari.gif", "150px", "150px", {"style":"border:1px solid red","alt":"Safari 3+","title":"Safari 3+"}, {"onclick":function(){window.location.href='http://www.apple.com/safari/download/';}}),
            BuildElement.image("/images/browser_opera.gif", "150px", "150px", {"style":"border:1px solid red","alt":"Opera 9.5+","title":"Opera 9.5+"}, {"onclick":function(){window.location.href='http://www.opera.com/download/';}}) ]) );
       $("body").animate({ opacity:100 }, 3000, function(){ 
         $("head").html( createBasicElement("title",{},"This site does not support " + navigator.appName + " version " + navigator.appVersion));   
       });
    });  

};
animateDelete = function(divId)
{
   var animateOpt = {
         "height": "0px"
      };

   if (jQuery.support.opacity)
      animateOpt['opacity'] = "0";

   $("#"+divId).animate(animateOpt, 500, function(){
      $("#"+divId).remove();  
   });  
}
animateTool = function(divId, opts, ele, func)
{
  
   var animateOpt = {
         'one': { height:'0px' },
         'two': opts
      };

   if ($.support.opacity)
   {
      animateOpt.one['opacity'] = "0";
      animateOpt.two['opacity'] = "100";
   }
  
    
   $("#"+divId).animate(animateOpt.one, 500, function(){
       if(ele){$("#"+divId).html(ele);}
       if(func){func();}
       $("#"+divId).animate(animateOpt.two, 1000, function(){ return true; });
    });  
};

