KindEditor.plugin("insertfile",
function(K) {
	var self = this,
	name = "insertfile",
	allowFileUpload = K.undef(self.allowFileUpload, !0),
	allowFileManager = K.undef(self.allowFileManager, !1),
	formatUploadUrl = K.undef(self.formatUploadUrl, !0),
	uploadJson = K.undef(self.uploadJson, self.basePath + "php/upload_json.php"),
	extraParams = K.undef(self.extraFileUploadParams, {}),
	filePostName = K.undef(self.filePostName, "imgFile"),
	lang = self.lang(name + ".");
	self.plugin.fileDialog = function(options) {
		var fileUrl = K.undef(options.fileUrl, "http://"),
		fileTitle = K.undef(options.fileTitle, ""),
		clickFn = options.clickFn,
		html = ['<div style="padding:20px;">', '<div class="ke-dialog-row">', '<input type="button" class="ke-upload-button" value="' + lang.upload + '文件" /> &nbsp;<br /><br />', '<label for="keUrl" style="width:60px;">输入' + lang.url + "</label>", '<input type="text" id="keUrl" name="url" class="ke-input-text" style="width:160px;" /> &nbsp;', '<span class="ke-button-common ke-button-outer">', '<input type="button" class="ke-button-common ke-button" style="display:none" name="viewServer" value="' + lang.viewServer + '" />', "</span>", "</div>", '<div class="ke-dialog-row"></div>', "</div>", "</form>", "</div>"].join(""),
		dialog = self.createDialog({
			name: name,
			width: 450,
			title: self.lang(name),
			body: html,
			yesBtn: {
				name: self.lang("yes"),
				click: function() {
					var url = K.trim(urlBox.val()),
					title = titleBox.val();
					return "http://" == url || K.invalidUrl(url) ? (alert(self.lang("invalidUrl")), urlBox[0].focus(), void 0) : ("" === K.trim(title) && (title = url), clickFn.call(self, url, title), void 0)
				}
			}
		}),
		div = dialog.div,
		urlBox = K('[name="url"]', div),
		viewServerBtn = K('[name="viewServer"]', div),
		titleBox = K('[name="title"]', div);
		if (allowFileUpload) {
			var uploadbutton = K.uploadbutton({
				button: K(".ke-upload-button", div)[0],
				fieldName: filePostName,
				url: K.addParam(uploadJson, "dir=file"),
				extraParams: extraParams,
				afterUpload: function(data) {
					if (dialog.hideLoading(), 0 === data.error) {
						var url = data.url;
						formatUploadUrl && (url = K.formatUrl(url, "absolute")),
						urlBox.val(url),
						self.afterUpload && self.afterUpload.call(self, url, data, name);
						// alert(self.lang("uploadSuccess"));
						// 判断是否有自定义属性openDialog
						if(typeof self.closeDialog == "function"){
							// 关闭dialog
							dialog.hide();
							$('.ke-dialog-mask').hide();
							// 弹出框
							$.dialog({
								content:self.lang("uploadSuccess"),
								icon:"succeed",
								zIndex:811214,
								ok:false,
								cancelVal:'关闭',
								cancel:true,
								time:3
							});
							seajs.use('module/myspace/edit',function(_edit){
								_edit.uploadFile();
							});
						}else{
							alert(self.lang("uploadSuccess"));
						}
					} else alert(data.message)
				},
				afterError: function(html) {
					dialog.hideLoading(),
					self.errorDialog(html)
				}
			});
			uploadbutton.fileBox.change(function() {
				dialog.showLoading(self.lang("uploadLoading")),
				uploadbutton.submit()
			})
		} else K(".ke-upload-button", div).hide();
		allowFileManager ? viewServerBtn.click(function() {
			self.loadPlugin("filemanager",
			function() {
				self.plugin.filemanagerDialog({
					viewType: "LIST",
					dirName: "file",
					clickFn: function(url) {
						self.dialogs.length > 1 && (K('[name="url"]', div).val(url), self.afterSelectFile && self.afterSelectFile.call(self, url), self.hideDialog())
					}
				})
			})
		}) : viewServerBtn.hide(),
		urlBox.val(fileUrl),
		titleBox.val(fileTitle),
		urlBox[0].focus(),
		urlBox[0].select()
	},
	self.clickToolbar(name,
	function() {
		self.plugin.fileDialog({
			clickFn: function(url, title) {
				var html = '<a class="ke-insertfile" href="' + url + '" data-ke-src="' + url + '" target="_blank">' + title + "</a>";
				self.insertHtml(html).hideDialog().focus()
			}
		})
	})
});