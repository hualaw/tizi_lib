window.attachEvent&&window.attachEvent("onload",function(){function isInAContentEditableElement(element){return null==element?!1:element.contentEditable&&"inherit"!==element.contentEditable?!0:isInAContentEditableElement(element.parentNode)}for(var images=document.getElementsByTagName("img"),i=images.length-1;i>=0;--i)"Wirisformula"!=images[i].className||isInAContentEditableElement(images[i])||(images[i].align="",images[i].style.verticalAlign=-images[i].height/2+"px")});