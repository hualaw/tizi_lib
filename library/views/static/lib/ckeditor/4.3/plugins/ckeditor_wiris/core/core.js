function wrs_addElementEvents(target,doubleClickHandler,mousedownHandler,mouseupHandler){doubleClickHandler&&wrs_addEvent(target,"dblclick",function(event){var realEvent=event?event:window.event,element=realEvent.srcElement?realEvent.srcElement:realEvent.target;doubleClickHandler(target,element,realEvent)}),mousedownHandler&&wrs_addEvent(target,"mousedown",function(event){var realEvent=event?event:window.event,element=realEvent.srcElement?realEvent.srcElement:realEvent.target;_wrs_temporalFocusElement=element,mousedownHandler(target,element,realEvent)}),mouseupHandler&&wrs_addEvent(target,"mouseup",function(event){var realEvent=event?event:window.event,element=realEvent.srcElement?realEvent.srcElement:realEvent.target;mouseupHandler(target,element,realEvent)})}function wrs_addEvent(element,event,func){element.addEventListener?element.addEventListener(event,func,!1):element.attachEvent&&element.attachEvent("on"+event,func)}function wrs_addIframeEvents(iframe,doubleClickHandler,mousedownHandler,mouseupHandler){wrs_addElementEvents(iframe.contentWindow.document,function(target,element,event){doubleClickHandler(iframe,element,event)},function(target,element,event){mousedownHandler(iframe,element,event)},function(target,element,event){mouseupHandler(iframe,element,event)})}function wrs_addTextareaEvents(textarea,clickHandler){clickHandler&&wrs_addEvent(textarea,"click",function(event){var realEvent=event?event:window.event;clickHandler(textarea,realEvent)})}function wrs_appletCodeToImgObject(creator,appletCode,image,imageWidth,imageHeight){var imageSrc=wrs_createImageCASSrc(image),imgObject=creator.createElement("img");return imgObject.src=imageSrc,imgObject.align="middle",imgObject.width=imageWidth,imgObject.height=imageHeight,imgObject.setAttribute(_wrs_conf_CASMathmlAttribute,wrs_mathmlEncode(appletCode)),imgObject.className=_wrs_conf_CASClassName,imgObject}function wrs_arrayContains(stack,element){for(var i=stack.length-1;i>=0;--i)if(stack[i]===element)return i;return-1}function wrs_containsClass(element,className){if(!("className"in element))return!1;for(var currentClasses=element.className.split(" "),i=currentClasses.length-1;i>=0;--i)if(currentClasses[i]==className)return!0;return!1}function wrs_convertOldXmlinitialtextAttribute(text){var val="value=",xitpos=text.indexOf("xmlinitialtext"),valpos=text.indexOf(val,xitpos),quote=text.charAt(valpos+val.length),startquote=valpos+val.length+1,endquote=text.indexOf(quote,startquote),value=text.substring(startquote,endquote),newvalue=value.split("«").join("§lt;");return newvalue=newvalue.split("»").join("§gt;"),newvalue=newvalue.split("&").join("§"),newvalue=newvalue.split("¨").join("§quot;"),text=text.split(value).join(newvalue)}function wrs_createElement(elementName,attributes,creator){void 0===attributes&&(attributes={}),void 0===creator&&(creator=document);var element;try{var html="<"+elementName;for(var attributeName in attributes)html+=" "+attributeName+'="'+wrs_htmlentities(attributes[attributeName])+'"';html+=">",element=creator.createElement(html)}catch(e){element=creator.createElement(elementName);for(var attributeName in attributes)element.setAttribute(attributeName,attributes[attributeName])}return element}function wrs_createHttpRequest(){if("file://"==_wrs_currentPath.substr(0,7))throw"Cross site scripting is only allowed for HTTP.";if("undefined"!=typeof XMLHttpRequest)return new XMLHttpRequest;try{return new ActiveXObject("Msxml2.XMLHTTP")}catch(e){try{return new ActiveXObject("Microsoft.XMLHTTP")}catch(oc){}}return!1}function wrs_createImageCASSrc(image,appletCode){var data={image:image,mml:appletCode};return wrs_getContent(_wrs_conf_createcasimagePath,data)}function wrs_createImageSrc(mathml,wirisProperties){var data=wirisProperties?wirisProperties:{};data.mml=mathml,window._wrs_conf_useDigestInsteadOfMathml&&_wrs_conf_useDigestInsteadOfMathml&&(data.returnDigest="true");var result=wrs_getContent(_wrs_conf_createimagePath,data);if(-1!=result.indexOf("@BASE@")){var baseParts=_wrs_conf_createimagePath.split("/");baseParts.pop(),result=result.split("@BASE@").join(baseParts.join("/"))}return result}function wrs_createObject(objectCode,creator){function recursiveParamsFix(object){if(object.getAttribute&&"WirisParam"==object.getAttribute("wirisObject")){for(var attributesParsed={},i=0;i<object.attributes.length;++i)null!==object.attributes[i].nodeValue&&(attributesParsed[object.attributes[i].nodeName]=object.attributes[i].nodeValue);var param=wrs_createElement("param",attributesParsed,creator);param.NAME&&(param.name=param.NAME,param.value=param.VALUE),param.removeAttribute("wirisObject"),object.parentNode.replaceChild(param,object)}else if(object.getAttribute&&"WirisApplet"==object.getAttribute("wirisObject")){for(var attributesParsed={},i=0;i<object.attributes.length;++i)null!==object.attributes[i].nodeValue&&(attributesParsed[object.attributes[i].nodeName]=object.attributes[i].nodeValue);var applet=wrs_createElement("applet",attributesParsed,creator);applet.removeAttribute("wirisObject");for(var i=0;i<object.childNodes.length;++i)recursiveParamsFix(object.childNodes[i]),"param"==object.childNodes[i].nodeName.toLowerCase()&&(applet.appendChild(object.childNodes[i]),--i);object.parentNode.replaceChild(applet,object)}else for(var i=0;i<object.childNodes.length;++i)recursiveParamsFix(object.childNodes[i])}void 0===creator&&(creator=document),objectCode=objectCode.split("<applet ").join('<span wirisObject="WirisApplet" ').split("<APPLET ").join('<span wirisObject="WirisApplet" '),objectCode=objectCode.split("</applet>").join("</span>").split("</APPLET>").join("</span>"),objectCode=objectCode.split("<param ").join('<br wirisObject="WirisParam" ').split("<PARAM ").join('<br wirisObject="WirisParam" '),objectCode=objectCode.split("</param>").join("</br>").split("</PARAM>").join("</br>");var container=wrs_createElement("div",{},creator);return container.innerHTML=objectCode,recursiveParamsFix(container),container.firstChild}function wrs_createObjectCode(object){if(1==object.nodeType){for(var output="<"+object.tagName,i=0;i<object.attributes.length;++i)object.attributes[i].specified&&(output+=" "+object.attributes[i].name+'="'+wrs_htmlentities(object.attributes[i].value)+'"');if(object.childNodes.length>0){output+=">";for(var i=0;i<object.childNodes.length;++i)output+=wrs_createObjectCode(object.childNodes[i]);output+="</"+object.tagName+">"}else output+="DIV"==object.nodeName||"SCRIPT"==object.nodeName?"></"+object.tagName+">":"/>";return output}return 3==object.nodeType?wrs_htmlentities(object.nodeValue):""}function wrs_endParse(code,wirisProperties,language){return code=wrs_endParseEditMode(code,wirisProperties,language),wrs_endParseSaveMode(code)}function wrs_regexpIndexOf(input,regexp,start){var index=input.substring(start||0).search(regexp);return index>=0?index+(start||0):index}function wrs_endParseEditMode(code,wirisProperties,language){if(void 0!==window._wrs_conf_parseModes&&-1!=wrs_arrayContains(_wrs_conf_parseModes,"latex")){for(var output="",endPosition=0,startPosition=code.indexOf("$$");-1!=startPosition;){if(output+=code.substring(endPosition,startPosition),endPosition=code.indexOf("$$",startPosition+2),-1!=endPosition){var latex=code.substring(startPosition+2,endPosition);latex=wrs_htmlentitiesDecode(latex);var mathml=wrs_getMathMLFromLatex(latex,!0),imgObject=wrs_mathmlToImgObject(document,mathml,wirisProperties,language);output+=wrs_createObjectCode(imgObject),endPosition+=2}else output+="$$",endPosition=startPosition+2;startPosition=code.indexOf("$$",endPosition)}output+=code.substring(endPosition,code.length),code=output}if(window._wrs_conf_defaultEditMode&&"iframes"==_wrs_conf_defaultEditMode){for(var output="",pattern=' class="'+_wrs_conf_imageClassName+'"',formulaPosition=code.indexOf(pattern),endPosition=0;-1!=formulaPosition;){startPosition=formulaPosition;for(var i=formulaPosition,startTagFound=!1;i>=0&&!startTagFound;){var character=code.charAt(i);if('"'==character||"'"==character){var characterNextPosition=code.lastIndexOf(character,i);i=-1==characterNextPosition?-1:characterNextPosition}else"<"==character?(startPosition=i,startTagFound=!0):">"==character&&(i=-1);--i}if(output+=code.substring(endPosition,startPosition),startTagFound){i=formulaPosition;for(var counter=1;i<code.length&&counter>0;){var character=code.charAt(i);if('"'==character||"'"==character){var characterNextPosition=code.indexOf(character,i);i=-1==characterNextPosition?code.length:characterNextPosition}else"<"==character?i+1<code.length&&"/"==code.charAt(i+1)?(--counter,0==counter&&(endPosition=code.indexOf(">",i)+1,-1==endPosition&&(counter=-1))):++counter:">"==character&&"/"==code.charAt(i-1)&&(--counter,0==counter&&(endPosition=i+1));++i}if(0==counter){var formulaTagCode=code.substring(startPosition,endPosition),formulaTagObject=wrs_createObject(formulaTagCode),mathml=formulaTagObject.getAttribute(_wrs_conf_imageMathmlAttribute);null==mathml&&(mathml=formulaTagObject.getAttribute("alt"));var imgObject=wrs_mathmlToImgObject(document,mathml,wirisProperties,language);output+=wrs_createObjectCode(imgObject)}else output+=code.charAt(formulaPosition),endPosition=formulaPosition+1}else output+=code.charAt(formulaPosition),endPosition=formulaPosition+1;formulaPosition=code.indexOf(pattern,endPosition)}output+=code.substring(endPosition,code.length),code=output}return code}function wrs_endParseSaveMode(code){var output="",convertToXml=!1,convertToSafeXml=!1;window._wrs_conf_saveMode&&("safeXml"==_wrs_conf_saveMode?(convertToXml=!0,convertToSafeXml=!0):"xml"==_wrs_conf_saveMode&&(convertToXml=!0));for(var endPosition=0,pattern=/<img/gi,patternLength=pattern.source.length;pattern.test(code);){var startPosition=pattern.lastIndex-patternLength;output+=code.substring(endPosition,startPosition);for(var i=startPosition+1;i<code.length&&startPosition>=endPosition;){var character=code.charAt(i);if('"'==character||"'"==character){var characterNextPosition=code.indexOf(character,i+1);i=-1==characterNextPosition?code.length:characterNextPosition}else">"==character&&(endPosition=i+1);++i}if(startPosition>endPosition)return output+=code.substring(startPosition,code.length);var imgCode=code.substring(startPosition,endPosition);output+=wrs_getWIRISImageOutput(imgCode,convertToXml,convertToSafeXml)}return output+=code.substring(endPosition,code.length)}function wrs_fireEvent(element,event){if(document.createEvent){var eventObject=document.createEvent("HTMLEvents");return eventObject.initEvent(event,!0,!0),!element.dispatchEvent(eventObject)}var eventObject=document.createEventObject();return element.fireEvent("on"+event,eventObject)}function wrs_getCode(variableName,imageHashCode){var data={};return data[variableName]=imageHashCode,wrs_getContent(_wrs_conf_getmathmlPath,data)}function wrs_getContent(url,postVariables){try{var httpRequest=wrs_createHttpRequest();if(postVariables.oldsrc=_wrs_editImgSrc,httpRequest)return"/"==url.substr(0,1)||"http://"==url.substr(0,7)||"https://"==url.substr(0,8)?httpRequest.open("POST",url,!1):httpRequest.open("POST",_wrs_currentPath+url,!1),void 0!==postVariables?(httpRequest.setRequestHeader("Content-type","application/x-www-form-urlencoded; charset=UTF-8"),httpRequest.send(wrs_httpBuildQuery(postVariables))):httpRequest.send(null),httpRequest.responseText;alert("Your browser is not compatible with AJAX technology. Please, use the latest version of Mozilla Firefox.")}catch(e){}return""}function wrs_getInnerHTML(element){for(var innerHTML="",i=0;i<element.childNodes.length;++i)innerHTML+=wrs_createObjectCode(element.childNodes[i]);return innerHTML}function wrs_getLatexFromMathML(mathml){var data={service:"mathml2latex",mml:mathml};return wrs_getContent(_wrs_conf_servicePath,data)}function wrs_getLatexFromTextNode(textNode,caretPosition){function getNextLatexPosition(currentNode,currentPosition){for(var position=currentNode.nodeValue.indexOf("$$",currentPosition);-1==position;){if(currentNode=currentNode.nextSibling,!currentNode||3!=currentNode.nodeType)return null;position=currentNode.nodeValue.indexOf("$$")}return{node:currentNode,position:position}}function isPrevious(node,position,endNode,endPosition){if(node==endNode)return endPosition>=position;for(;node&&node!=endNode;)node=node.nextSibling;return node==endNode}for(var startNode=textNode;startNode.previousSibling&&3==startNode.previousSibling.nodeType;)startNode=startNode.previousSibling;var start,end={node:startNode,position:0};do{var start=getNextLatexPosition(end.node,end.position);if(null==start||isPrevious(textNode,caretPosition,start.node,start.position))return null;var end=getNextLatexPosition(start.node,start.position+2);if(null==end)return null;end.position+=2}while(isPrevious(end.node,end.position,textNode,caretPosition));var latex;if(start.node==end.node)latex=start.node.nodeValue.substring(start.position+2,end.position-2);else{latex=start.node.nodeValue.substring(start.position+2,start.node.nodeValue.length);var currentNode=start.node;do currentNode=currentNode.nextSibling,latex+=currentNode==end.node?end.node.nodeValue.substring(0,end.position-2):currentNode.nodeValue;while(currentNode!=end.node)}return{latex:latex,startNode:start.node,startPosition:start.position,endNode:end.node,endPosition:end.position}}function wrs_getMathMLFromLatex(latex,includeLatexOnSemantics){var data={service:"latex2mathml",latex:latex};includeLatexOnSemantics&&(data.saveLatex="");var mathML=wrs_getContent(_wrs_conf_servicePath,data);return mathML.split("\r").join("").split("\n").join(" ")}function wrs_getNodeLength(node){if(3==node.nodeType)return node.nodeValue.length;if(1==node.nodeType){var length=_wrs_staticNodeLengths[node.nodeName.toUpperCase()];void 0===length&&(length=0);for(var i=0;i<node.childNodes.length;++i)length+=wrs_getNodeLength(node.childNodes[i]);return length}return 0}function wrs_getQueryParams(windowObject){var data={},start=windowObject.location.search.indexOf("?");start=-1==start?0:start+1;for(var queryStringParts=windowObject.location.search.substr(start).split("&"),i=0;i<queryStringParts.length;++i){var paramParts=queryStringParts[i].split("=",2);data[paramParts[0]]=wrs_urldecode(paramParts[1])}return data}function wrs_getSelectedItem(target,isIframe){var windowTarget;if(isIframe?(windowTarget=target.contentWindow,windowTarget.focus()):(windowTarget=window,target.focus()),document.selection){var range=windowTarget.document.selection.createRange();if(range.parentElement){if(range.text.length>0)return null;windowTarget.document.execCommand("InsertImage",!1,"#");var temporalObject=range.parentElement();"IMG"!=temporalObject.nodeName.toUpperCase()&&(range.pasteHTML('<span id="wrs_openEditorWindow_temporalObject"></span>'),temporalObject=windowTarget.document.getElementById("wrs_openEditorWindow_temporalObject"));var node,caretPosition;return temporalObject.nextSibling&&3==temporalObject.nextSibling.nodeType?(node=temporalObject.nextSibling,caretPosition=0):temporalObject.previousSibling&&3==temporalObject.previousSibling.nodeType?(node=temporalObject.previousSibling,caretPosition=node.nodeValue.length):(node=windowTarget.document.createTextNode(""),temporalObject.parentNode.insertBefore(node,temporalObject),caretPosition=0),temporalObject.parentNode.removeChild(temporalObject),{node:node,caretPosition:caretPosition}}return range.length>1?null:{node:range.item(0)}}var selection=windowTarget.getSelection();try{var range=selection.getRangeAt(0)}catch(e){var range=windowTarget.document.createRange()}var node=range.startContainer;if(3==node.nodeType)return range.startOffset!=range.endOffset?null:{node:node,caretPosition:range.startOffset};if(1==node.nodeType){var position=range.startOffset;if(node.childNodes[position])return{node:node.childNodes[position]}}return null}function wrs_getWIRISImageOutput(imgCode,convertToXml,convertToSafeXml){var imgObject=wrs_createObject(imgCode);if(imgObject){if(imgObject.className==_wrs_conf_imageClassName){if(!convertToXml)return imgCode;var xmlCode=imgObject.getAttribute(_wrs_conf_imageMathmlAttribute);return null==xmlCode&&(xmlCode=imgObject.getAttribute("alt")),convertToSafeXml||(xmlCode=wrs_mathmlDecode(xmlCode)),xmlCode}if(imgObject.className==_wrs_conf_CASClassName){var appletCode=imgObject.getAttribute(_wrs_conf_CASMathmlAttribute);appletCode=wrs_mathmlDecode(appletCode);var appletObject=wrs_createObject(appletCode);appletObject.setAttribute("src",imgObject.src);var appletCodeToBeInserted=wrs_createObjectCode(appletObject);return convertToSafeXml&&(appletCodeToBeInserted=wrs_mathmlEncode(appletCodeToBeInserted)),appletCodeToBeInserted}}return imgCode}function wrs_htmlentities(input){return input.split("&").join("&amp;").split("<").join("&lt;").split(">").join("&gt;").split('"').join("&quot;")}function wrs_htmlentitiesDecode(input){return input.split("&quot;").join('"').split("&gt;").join(">").split("&lt;").join("<").split("&amp;").join("&")}function wrs_httpBuildQuery(properties){var result="";for(i in properties)null!=properties[i]&&(result+=wrs_urlencode(i)+"="+wrs_urlencode(properties[i])+"&");return result}function wrs_initParse(code,language){return code=wrs_initParseSaveMode(code,language),wrs_initParseEditMode(code)}function wrs_initParseImgToIframes(windowTarget){if(window._wrs_conf_defaultEditMode&&"iframes"==_wrs_conf_defaultEditMode)for(var imgList=windowTarget.document.getElementsByTagName("img"),i=0;i<imgList.length;)if(imgList[i].className==_wrs_conf_imageClassName){var mathml=imgList[i].getAttribute(_wrs_conf_imageMathmlAttribute);null==mathml&&(mathml=imgList[i].getAttribute("alt"));var iframe=wrs_mathmlToIframeObject(windowTarget,wrs_mathmlDecode(mathml));imgList[i].parentNode.replaceChild(iframe,imgList[i])}else++i}function wrs_initParseEditMode(code){if(void 0!==window._wrs_conf_parseModes&&-1!=wrs_arrayContains(_wrs_conf_parseModes,"latex"))for(var imgList=wrs_getElementsByNameFromString(code,"img",!0),token='encoding="LaTeX">',carry=0,i=0;i<imgList.length;++i){var imgCode=code.substring(imgList[i].start+carry,imgList[i].end+carry);if(-1!=imgCode.indexOf(' class="'+_wrs_conf_imageClassName+'"')){var mathmlStartToken=" "+_wrs_conf_imageMathmlAttribute+'="',mathmlStart=imgCode.indexOf(mathmlStartToken);if(-1==mathmlStart&&(mathmlStartToken=' alt="',mathmlStart=imgCode.indexOf(mathmlStartToken)),-1!=mathmlStart){mathmlStart+=mathmlStartToken.length;var mathmlEnd=imgCode.indexOf('"',mathmlStart),mathml=wrs_mathmlDecode(imgCode.substring(mathmlStart,mathmlEnd)),latexStartPosition=mathml.indexOf(token);if(-1!=latexStartPosition){latexStartPosition+=token.length;var latexEndPosition=mathml.indexOf("</annotation>",latexStartPosition),latex=mathml.substring(latexStartPosition,latexEndPosition),replaceText="$$"+wrs_htmlentitiesDecode(latex)+"$$";code=code.substring(0,imgList[i].start+carry)+replaceText+code.substring(imgList[i].end+carry),carry+=replaceText.length-(imgList[i].end-imgList[i].start)}}}}return code}function wrs_initParseSaveMode(code,language){window._wrs_conf_saveMode&&("safeXml"==_wrs_conf_saveMode?(code=wrs_mathmlDecodeSafeXmlEntities(code),code=wrs_parseSafeAppletsToObjects(code),code=wrs_parseMathmlToLatex(code,_wrs_safeXmlCharacters),code=wrs_parseMathmlToImg(code,_wrs_safeXmlCharacters,language)):"xml"==_wrs_conf_saveMode&&(code=wrs_parseMathmlToLatex(code,_wrs_xmlCharacters),code=wrs_parseMathmlToImg(code,_wrs_xmlCharacters,language)));for(var appletList=wrs_getElementsByNameFromString(code,"applet",!1),carry=0,i=0;i<appletList.length;++i){var appletCode=code.substring(appletList[i].start+carry,appletList[i].end+carry);if(-1!=appletCode.indexOf(' class="'+_wrs_conf_CASClassName+'"')||-1!=appletCode.toUpperCase().indexOf("WIRIS")){if(-1!=appletCode.indexOf(' src="'))var srcStart=appletCode.indexOf(' src="')+' src="'.length,srcEnd=appletCode.indexOf('"',srcStart),src=appletCode.substring(srcStart,srcEnd);else{if("undefined"!=typeof _wrs_conf_pluginBasePath)var src=_wrs_conf_pluginBasePath+"/integration/showcasimage.php?formula=noimage";else var src="";if(-1==appletCode.indexOf(' class="'+_wrs_conf_CASClassName+'"')){var closeSymbol=appletCode.indexOf(">"),appletTag=appletCode.substring(0,closeSymbol),newAppletTag=appletTag.split(" width=").join(' class="Wiriscas" width=');appletCode=appletCode.split(appletTag).join(newAppletTag),appletCode=appletCode.split("'").join('"')}}var imgCode='<img align="middle" class="'+_wrs_conf_CASClassName+'" '+_wrs_conf_CASMathmlAttribute+'="'+wrs_mathmlEncode(appletCode)+'" src="'+src+'" />';code=code.substring(0,appletList[i].start+carry)+imgCode+code.substring(appletList[i].end+carry),carry+=imgCode.length-(appletList[i].end-appletList[i].start)}}return code}function wrs_getElementsByNameFromString(code,name,autoClosed){var elements=[],code=code.toLowerCase();name=name.toLowerCase();for(var start=code.indexOf("<"+name+" ");-1!=start;){var endString;endString=autoClosed?">":"</"+name+">";var end=code.indexOf(endString,start);-1!=end?(end+=endString.length,elements.push({start:start,end:end})):end=start+1,start=code.indexOf("<"+name+" ",end)}return elements}function wrs_insertElementOnSelection(element,focusElement,windowTarget){try{if(focusElement.focus(),_wrs_isNewElement)if(document.selection){var range=windowTarget.document.selection.createRange();if(windowTarget.document.execCommand("InsertImage",!1,element.src),"parentElement"in range||(windowTarget.document.execCommand("delete",!1),range=windowTarget.document.selection.createRange(),windowTarget.document.execCommand("InsertImage",!1,element.src)),"parentElement"in range){var temporalObject=range.parentElement();"IMG"==temporalObject.nodeName.toUpperCase()?temporalObject.parentNode.replaceChild(element,temporalObject):range.pasteHTML(wrs_createObjectCode(element))}}else{var isAndroid=!1;if(_wrs_androidRange)var isAndroid=!0,range=_wrs_androidRange;else{var selection=windowTarget.getSelection();try{var range=selection.getRangeAt(0)}catch(e){var range=windowTarget.document.createRange()}selection.removeAllRanges()}range.deleteContents();var node=range.startContainer,position=range.startOffset;3==node.nodeType?(node=node.splitText(position),node.parentNode.insertBefore(element,node),node=node.parentNode):1==node.nodeType&&node.insertBefore(element,node.childNodes[position]),isAndroid||(range.selectNode(element),position=range.endOffset,selection.collapse(node,position))}else if(_wrs_temporalRange)if(document.selection)_wrs_isNewElement=!0,_wrs_temporalRange.select(),wrs_insertElementOnSelection(element,focusElement,windowTarget);else{{_wrs_temporalRange.startContainer}_wrs_temporalRange.deleteContents(),_wrs_temporalRange.insertNode(element)}else _wrs_temporalImage.parentNode.replaceChild(element,_wrs_temporalImage)}catch(e){}}function wrs_isMathmlInAttribute(content,i){var math_att="['\"][\\s]*=[\\s]*[\\w-]+",att_content="\"[^\"]*\"|'[^']*'",att="[\\s]*("+att_content+")[\\s]*=[\\s]*[\\w-]+[\\s]*",atts="("+att+")*",regex="^"+math_att+atts+"[\\s]+gmi<",expression=new RegExp(regex),actual_content=content.substring(0,i),reversed=actual_content.split("").reverse().join(""),exists=expression.test(reversed);return exists}function wrs_mathmlDecodeSafeXmlEntities(input){return input=input.split(_wrs_safeXmlCharactersEntities.tagOpener).join(_wrs_safeXmlCharacters.tagOpener),input=input.split(_wrs_safeXmlCharactersEntities.tagCloser).join(_wrs_safeXmlCharacters.tagCloser),input=input.split(_wrs_safeXmlCharactersEntities.doubleQuote).join(_wrs_safeXmlCharacters.doubleQuote),input=input.split(_wrs_safeXmlCharactersEntities.realDoubleQuote).join(_wrs_safeXmlCharacters.realDoubleQuote)}function wrs_mathmlDecode(input){return input=wrs_mathmlDecodeSafeXmlEntities(input),"_wrs_blackboard"in window&&window._wrs_blackboard&&(input=input.split(_wrs_safeBadBlackboardCharacters.ltElement).join(_wrs_safeGoodBlackboardCharacters.ltElement),input=input.split(_wrs_safeBadBlackboardCharacters.gtElement).join(_wrs_safeGoodBlackboardCharacters.gtElement),input=input.split(_wrs_safeBadBlackboardCharacters.ampElement).join(_wrs_safeGoodBlackboardCharacters.ampElement)),input=input.split(_wrs_safeXmlCharacters.tagOpener).join(_wrs_xmlCharacters.tagOpener),input=input.split(_wrs_safeXmlCharacters.tagCloser).join(_wrs_xmlCharacters.tagCloser),input=input.split(_wrs_safeXmlCharacters.doubleQuote).join(_wrs_xmlCharacters.doubleQuote),input=input.split(_wrs_safeXmlCharacters.ampersand).join(_wrs_xmlCharacters.ampersand),input=input.split(_wrs_safeXmlCharacters.quote).join(_wrs_xmlCharacters.quote),input=input.split("$").join("&")}function wrs_mathmlEncode(input){return input=input.split(_wrs_xmlCharacters.tagOpener).join(_wrs_safeXmlCharacters.tagOpener),input=input.split(_wrs_xmlCharacters.tagCloser).join(_wrs_safeXmlCharacters.tagCloser),input=input.split(_wrs_xmlCharacters.doubleQuote).join(_wrs_safeXmlCharacters.doubleQuote),input=input.split(_wrs_xmlCharacters.ampersand).join(_wrs_safeXmlCharacters.ampersand),input=input.split(_wrs_xmlCharacters.quote).join(_wrs_safeXmlCharacters.quote)}function wrs_mathmlEntities(mathml){for(var toReturn="",i=0;i<mathml.length;++i)toReturn+=mathml.charCodeAt(i)>128?"&#"+mathml.charCodeAt(i)+";":mathml.charAt(i);return toReturn}function wrs_mathmlToAccessible(mathml,language){var data={service:"mathml2accessible",mml:mathml};return language&&(data.lang=language),wrs_getContent(_wrs_conf_servicePath,data)}function wrs_mathmlToIframeObject(windowTarget,mathml){function waitForViewer(){function prepareDiv(){windowTarget._wrs_viewer.isReady()?(container.style.height=formulaContainer.style.height,container.style.width=formulaContainer.style.width,container.style.verticalAlign=formulaContainer.style.verticalAlign):setTimeout(prepareDiv,100)}windowTarget.com&&windowTarget.com.wiris?("_wrs_viewer"in windowTarget||(windowTarget._wrs_viewer=new windowTarget.com.wiris.jsEditor.JsViewerMain(_wrs_conf_pluginBasePath+"/integration/editor"),windowTarget._wrs_viewer.insertCSS(null,windowTarget.document)),windowTarget._wrs_viewer.paintFormulaOnContainer(mathml,formulaContainer,null),prepareDiv()):setTimeout(waitForViewer,100)}if(-1!=window.navigator.userAgent.toLowerCase().indexOf("webkit")){var container=windowTarget.document.createElement("span");container.className=_wrs_conf_imageClassName,container.setAttribute(_wrs_conf_imageMathmlAttribute,mathml),container.setAttribute("height","1"),container.setAttribute("width","1"),container.style.display="inline-block",container.style.cursor="pointer",container.style.webkitUserModify="read-only",container.style.webkitUserSelect="all";var formulaContainer=windowTarget.document.createElement("span");if(formulaContainer.style.display="inline",container.appendChild(formulaContainer),!("_wrs_viewerAppended"in windowTarget)){var viewerScript=windowTarget.document.createElement("script");viewerScript.src=_wrs_conf_pluginBasePath+"/integration/editor/viewer.js",windowTarget.document.getElementsByTagName("head")[0].appendChild(viewerScript),windowTarget._wrs_viewerAppended=!0}return waitForViewer(),container}windowTarget.document.wrs_assignIframeEvents=function(myIframe){wrs_addEvent(myIframe.contentWindow.document,"click",function(){wrs_fireEvent(myIframe,"dblclick")})};var iframe=windowTarget.document.createElement("iframe");return iframe.className=_wrs_conf_imageClassName,iframe.setAttribute(_wrs_conf_imageMathmlAttribute,mathml),iframe.style.display="inline",iframe.style.border="none",iframe.setAttribute("height","1"),iframe.setAttribute("width","1"),iframe.setAttribute("scrolling","no"),iframe.setAttribute("frameBorder","0"),iframe.src=_wrs_conf_pluginBasePath+"/core/iframe.html#"+_wrs_conf_imageMathmlAttribute,iframe}function wrs_mathmlToImgObject(creator,mathml,wirisProperties,language){var imgObject=creator.createElement("img");imgObject.align="middle",window._wrs_conf_enableAccessibility&&_wrs_conf_enableAccessibility&&(imgObject.alt=wrs_mathmlToAccessible(mathml,language)),imgObject.className=_wrs_conf_imageClassName;var result=wrs_createImageSrc(mathml,wirisProperties);if(window._wrs_conf_useDigestInsteadOfMathml&&_wrs_conf_useDigestInsteadOfMathml){var parts=result.split(":",2);imgObject.setAttribute(_wrs_conf_imageMathmlAttribute,parts[0]),imgObject.src=parts[1]}else imgObject.setAttribute(_wrs_conf_imageMathmlAttribute,wrs_mathmlEncode(mathml)),imgObject.src=result;return imgObject}function wrs_openCASWindow(target,isIframe,language){if(void 0===isIframe&&(isIframe=!0),_wrs_temporalRange=null,target){var selectedItem=wrs_getSelectedItem(target,isIframe);null!=selectedItem&&void 0===selectedItem.caretPosition&&"IMG"==selectedItem.node.nodeName.toUpperCase()&&selectedItem.node.className==_wrs_conf_CASClassName&&(_wrs_temporalImage=selectedItem.node,_wrs_isNewElement=!1)}var path=_wrs_conf_CASPath;return language&&(path+="?lang="+language),window.open(path,"WIRISCAS",_wrs_conf_CASAttributes)}function wrs_openEditorWindow(language,target,isIframe){var ua=navigator.userAgent.toLowerCase(),isAndroid=ua.indexOf("android")>-1;if(isAndroid){var selection=target.contentWindow.getSelection();_wrs_androidRange=selection.getRangeAt(0)}void 0===isIframe&&(isIframe=!0);var path=_wrs_conf_editorPath;language&&(path+="?lang="+language);var availableDirs=new Array("rtl","ltr");if("undefined"!=typeof _wrs_int_directionality&&-1!=wrs_arrayContains(availableDirs,_wrs_int_directionality)&&(path+="&dir="+_wrs_int_directionality),_wrs_editMode=window._wrs_conf_defaultEditMode?_wrs_conf_defaultEditMode:"images",_wrs_temporalRange=null,target){var selectedItem=wrs_getSelectedItem(target,isIframe);if(null!=selectedItem)if(void 0===selectedItem.caretPosition)selectedItem.node.className==_wrs_conf_imageClassName&&("IMG"==selectedItem.node.nodeName.toUpperCase()?_wrs_editMode="images":"IFRAME"==selectedItem.node.nodeName.toUpperCase()&&(_wrs_editMode="iframes"),_wrs_editImgSrc=selectedItem.node.getAttribute("src"),_wrs_temporalImage=selectedItem.node,_wrs_isNewElement=!1);else{var latexResult=wrs_getLatexFromTextNode(selectedItem.node,selectedItem.caretPosition);if(null!=latexResult){_wrs_editMode="latex";var mathml=wrs_getMathMLFromLatex(latexResult.latex);_wrs_isNewElement=!1,_wrs_temporalImage=document.createElement("img"),_wrs_temporalImage.setAttribute(_wrs_conf_imageMathmlAttribute,wrs_mathmlEncode(mathml));var windowTarget=isIframe?target.contentWindow:window;if(document.selection){for(var leftOffset=0,previousNode=latexResult.startNode.previousSibling;previousNode;)leftOffset+=wrs_getNodeLength(previousNode),previousNode=previousNode.previousSibling;_wrs_temporalRange=windowTarget.document.selection.createRange(),_wrs_temporalRange.moveToElementText(latexResult.startNode.parentNode),_wrs_temporalRange.move("character",leftOffset+latexResult.startPosition),_wrs_temporalRange.moveEnd("character",latexResult.latex.length+4)}else _wrs_temporalRange=windowTarget.document.createRange(),_wrs_temporalRange.setStart(latexResult.startNode,latexResult.startPosition),_wrs_temporalRange.setEnd(latexResult.endNode,latexResult.endPosition)}}}return window.open(path,"WIRISeditor",_wrs_conf_editorAttributes)}function wrs_parseMathmlToLatex(content,characters){for(var mathml,startAnnotation,closeAnnotation,output="",mathTagBegin=characters.tagOpener+"math",mathTagEnd=characters.tagOpener+"/math"+characters.tagCloser,openTarget=characters.tagOpener+"annotation encoding="+characters.doubleQuote+"LaTeX"+characters.doubleQuote+characters.tagCloser,closeTarget=characters.tagOpener+"/annotation"+characters.tagCloser,start=content.indexOf(mathTagBegin),end=0;-1!=start;)output+=content.substring(end,start),end=content.indexOf(mathTagEnd,start),-1==end?end=content.length-1:end+=mathTagEnd.length,mathml=content.substring(start,end),startAnnotation=mathml.indexOf(openTarget),-1!=startAnnotation?(startAnnotation+=openTarget.length,closeAnnotation=mathml.indexOf(closeTarget),output+="$$"+mathml.substring(startAnnotation,closeAnnotation)+"$$"):output+=mathml,start=content.indexOf(mathTagBegin,end);return output+=content.substring(end,content.length)
}function wrs_parseMathmlToImg(content,characters,language){for(var output="",mathTagBegin=characters.tagOpener+"math",mathTagEnd=characters.tagOpener+"/math"+characters.tagCloser,start=content.indexOf(mathTagBegin),end=0;-1!=start;){if(output+=content.substring(end,start),end=content.indexOf(mathTagEnd,start),-1==end?end=content.length-1:end+=mathTagEnd.length,wrs_isMathmlInAttribute(content,start))output+=content.substring(start,end);else{var mathml=content.substring(start,end);mathml=characters==_wrs_safeXmlCharacters?wrs_mathmlDecode(mathml):wrs_mathmlEntities(mathml),output+=wrs_createObjectCode(wrs_mathmlToImgObject(document,mathml,null,language))}start=content.indexOf(mathTagBegin,end)}return output+=content.substring(end,content.length)}function wrs_parseSafeAppletsToObjects(content){for(var applet,output="",appletTagBegin=_wrs_safeXmlCharacters.tagOpener+"APPLET",appletTagEnd=_wrs_safeXmlCharacters.tagOpener+"/APPLET"+_wrs_safeXmlCharacters.tagCloser,upperCaseContent=content.toUpperCase(),start=upperCaseContent.indexOf(appletTagBegin),end=0;-1!=start;)output+=content.substring(end,start),end=upperCaseContent.indexOf(appletTagEnd,start),-1==end?end=content.length-1:end+=appletTagEnd.length,applet=wrs_convertOldXmlinitialtextAttribute(content.substring(start,end)),output+=wrs_mathmlDecode(applet),start=upperCaseContent.indexOf(appletTagBegin,end);return output+=content.substring(end,content.length)}function wrs_removeEvent(element,event,func){element.removeEventListener?element.removeEventListener(event,func,!1):element.detachEvent&&element.detachEvent("on"+event,func)}function wrs_splitBody(code){var prefix="",sufix="",bodyPosition=code.indexOf("<body");if(-1!=bodyPosition&&(bodyPosition=code.indexOf(">",bodyPosition),-1!=bodyPosition)){++bodyPosition;var endBodyPosition=code.indexOf("</body>",bodyPosition);-1==endBodyPosition&&(endBodyPosition=code.length),prefix=code.substring(0,bodyPosition),sufix=code.substring(endBodyPosition,code.length),code=code.substring(bodyPosition,endBodyPosition)}return{prefix:prefix,code:code,sufix:sufix}}function wrs_updateCAS(focusElement,windowTarget,appletCode,image,imageWidth,imageHeight){var imgObject=wrs_appletCodeToImgObject(windowTarget.document,appletCode,image,imageWidth,imageHeight);wrs_insertElementOnSelection(imgObject,focusElement,windowTarget)}function wrs_updateFormula(focusElement,windowTarget,mathml,wirisProperties,editMode,language){if("latex"==editMode){var latex=wrs_getLatexFromMathML(mathml),textNode=windowTarget.document.createTextNode("$$"+latex+"$$");wrs_insertElementOnSelection(textNode,focusElement,windowTarget)}else if("iframes"==editMode){var iframe=wrs_mathmlToIframeObject(windowTarget,mathml);wrs_insertElementOnSelection(iframe,focusElement,windowTarget)}else{var imgObject=wrs_mathmlToImgObject(windowTarget.document,mathml,wirisProperties,language);wrs_insertElementOnSelection(imgObject,focusElement,windowTarget)}}function wrs_updateTextarea(textarea,text){if(textarea&&text)if(textarea.focus(),null!=textarea.selectionStart)textarea.value=textarea.value.substring(0,textarea.selectionStart)+text+textarea.value.substring(textarea.selectionEnd,textarea.value.length);else{var selection=document.selection.createRange();selection.text=text}}function wrs_urldecode(input){return decodeURIComponent(input)}function wrs_urlencode(clearString){var output="";return output=encodeURIComponent(clearString).replace(/!/g,"%21").replace(/'/g,"%27").replace(/\(/g,"%28").replace(/\)/g,"%29").replace(/\*/g,"%2A").replace(/~/g,"%7E")}var _wrs_currentPath=window.location.toString().substr(0,window.location.toString().lastIndexOf("/")+1),_wrs_editMode,_wrs_isNewElement=!0,_wrs_temporalImage,_wrs_temporalFocusElement,_wrs_androidRange,_wrs_xmlCharacters={tagOpener:"<",tagCloser:">",doubleQuote:'"',ampersand:"&",quote:"'"},_wrs_safeXmlCharacters={tagOpener:"«",tagCloser:"»",doubleQuote:"¨",ampersand:"§",quote:"`",realDoubleQuote:"¨"},_wrs_safeXmlCharactersEntities={tagOpener:"&laquo;",tagCloser:"&raquo;",doubleQuote:"&uml;",realDoubleQuote:"&quot;"},_wrs_safeBadBlackboardCharacters={ltElement:"«mo»<«/mo»",gtElement:"«mo»>«/mo»",ampElement:"«mo»&«/mo»"},_wrs_safeGoodBlackboardCharacters={ltElement:"«mo»§lt;«/mo»",gtElement:"«mo»§gt;«/mo»",ampElement:"«mo»§amp;«/mo»"},_wrs_staticNodeLengths={IMG:1,BR:1};window._wrs_conf_imageClassName||(_wrs_conf_imageClassName="Wirisformula"),window._wrs_conf_CASClassName||(_wrs_conf_CASClassName="Wiriscas");