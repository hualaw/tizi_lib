var wrs_int_opener,closeFunction;if(window.opener)wrs_int_opener=window.opener,closeFunction=window.close;else for(wrs_int_opener=window.parent;wrs_int_opener.InnerDialogLoaded;)wrs_int_opener=wrs_int_opener.parent;window.parent.InnerDialogLoaded?(window.parent.InnerDialogLoaded(),closeFunction=window.parent.Cancel):window.opener.parent.FCKeditorAPI&&(wrs_int_opener=window.opener.parent),wrs_int_opener.wrs_addEvent(window,"load",function(){var editor,queryParams=wrs_int_opener.wrs_getQueryParams(window);"wrs_attributes"in window?wrs_attributes.language=queryParams.lang:wrs_attributes={language:queryParams.lang},editor=com.wiris.jsEditor.defaultBasePath?com.wiris.jsEditor.JsEditor.newInstance(wrs_attributes):new com.wiris.jsEditor.JsEditor("editor",null);var editorElement=editor.getElement(),editorContainer=document.getElementById("editorContainer");if(editor.insertInto(editorContainer),!wrs_int_opener._wrs_isNewElement){var mathml,attributeValue=wrs_int_opener._wrs_temporalImage.getAttribute(wrs_int_opener._wrs_conf_imageMathmlAttribute);null==attributeValue&&(attributeValue=wrs_int_opener._wrs_temporalImage.getAttribute("alt")),mathml=wrs_int_opener._wrs_conf_useDigestInsteadOfMathml?wrs_int_opener.wrs_getCode(wrs_int_opener._wrs_conf_digestPostVariable,attributeValue):wrs_int_opener.wrs_mathmlDecode(attributeValue),editor.setMathML(mathml)}var controls=document.getElementById("controls"),submitButton=document.createElement("input");submitButton.type="button",submitButton.value=null!=strings.accept?strings.accept:"Accept",wrs_int_opener.wrs_addEvent(submitButton,"click",function(){var mathml="";editor.isFormulaEmpty()||(mathml+=editor.getMathML(),mathml=wrs_int_opener.wrs_mathmlEntities(mathml)),window.parent.InnerDialogLoaded&&window.parent.FCKBrowserInfo.IsIE?(closeFunction(),wrs_int_opener.wrs_int_updateFormula(mathml,wrs_int_opener._wrs_editMode,queryParams.lang)):(wrs_int_opener.wrs_int_updateFormula&&wrs_int_opener.wrs_int_updateFormula(mathml,wrs_int_opener._wrs_editMode,queryParams.lang),closeFunction())}),controls.appendChild(submitButton);var cancelButton=document.createElement("input");cancelButton.type="button",cancelButton.value=null!=strings.cancel?strings.cancel:"Cancel",wrs_int_opener.wrs_addEvent(cancelButton,"click",function(){closeFunction()}),controls.appendChild(cancelButton);var manualLink=document.getElementById("a_manual");"undefined"!=typeof manualLink&&null!=strings.manual&&(manualLink.innerHTML=strings.manual);var latexLink=document.getElementById("a_latex");"undefined"!=typeof latexLink&&null!=strings.latex&&(latexLink.innerHTML=strings.latex);var queryLang="";if("lang"in queryParams&&(queryLang=queryParams.lang.substr(0,2)),"rtl"==queryParams.dir||("he"==queryLang||"ar"==queryLang)&&"ltr"!=queryParams.dir){var body=document.getElementsByTagName("BODY");body[0].setAttribute("dir","rtl");var links=document.getElementById("links");links.id="links_rtl";var controls=document.getElementById("controls");controls.id="controls_rtl"}setInterval(function(){editorElement.style.height=document.getElementById("container").offsetHeight-controls.offsetHeight-10+"px"},100),setTimeout(function(){editor.focus()},100)}),wrs_int_opener.wrs_addEvent(window,"unload",function(){wrs_int_opener.wrs_int_notifyWindowClosed()});