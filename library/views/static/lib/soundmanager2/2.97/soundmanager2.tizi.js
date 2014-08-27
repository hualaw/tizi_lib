(function(window, _undefined) {
    "use strict";
    var soundManager = null;
    function SoundManager(smURL, smID) {
        this.setupOptions = {
            'url': (smURL || null),
            'flashVersion': 9,
            'debugMode': false,
            'debugFlash': false,
            'useConsole': true,
            'consoleOnly': true,
            'waitForWindowLoad': false,
            'bgColor': '#ffffff',
            'useHighPerformance': false,
            'flashPollingInterval': null,
            'html5PollingInterval': null,
            'flashLoadTimeout': 1000,
            'wmode': null,
            'allowScriptAccess': 'always',
            'useFlashBlock': false,
            'useHTML5Audio': true,
            'html5Test': /^(probably|maybe)$/i,
            'preferFlash': false,
            'noSWFCache': false,
            'idPrefix': 'sound'
        };
        this.defaultOptions = {
            'autoLoad': false,
            'autoPlay': false,
            'from': null,
            'loops': 1,
            'onid3': null,
            'onload': null,
            'whileloading': null,
            'onplay': null,
            'onpause': null,
            'onresume': null,
            'whileplaying': null,
            'onposition': null,
            'onstop': null,
            'onfailure': null,
            'onfinish': null,
            'multiShot': true,
            'multiShotEvents': false,
            'position': null,
            'pan': 0,
            'stream': true,
            'to': null,
            'type': null,
            'usePolicyFile': false,
            'volume': 100
        };
        this.flash9Options = {
            'isMovieStar': null,
            'usePeakData': false,
            'useWaveformData': false,
            'useEQData': false,
            'onbufferchange': null,
            'ondataerror': null
        };
        this.movieStarOptions = {
            'bufferTime': 3,
            'serverURL': null,
            'onconnect': null,
            'duration': null
        };
        this.audioFormats = {
            'mp3': {
                'type': ['audio/mpeg; codecs="mp3"', 'audio/mpeg', 'audio/mp3', 'audio/MPA', 'audio/mpa-robust'],
                'required': true
            },
            'mp4': {
                'related': ['aac', 'm4a', 'm4b'],
                'type': ['audio/mp4; codecs="mp4a.40.2"', 'audio/aac', 'audio/x-m4a', 'audio/MP4A-LATM', 'audio/mpeg4-generic'],
                'required': false
            },
            'ogg': {
                'type': ['audio/ogg; codecs=vorbis'],
                'required': false
            },
            'opus': {
                'type': ['audio/ogg; codecs=opus', 'audio/opus'],
                'required': false
            },
            'wav': {
                'type': ['audio/wav; codecs="1"', 'audio/wav', 'audio/wave', 'audio/x-wav'],
                'required': false
            }
        };
        this.movieID = 'sm2-container';
        this.id = (smID || 'sm2movie');
        this.debugID = 'soundmanager-debug';
        this.debugURLParam = /([#?&])debug=1/i;
        this.versionNumber = 'V2.97a.20131201';
        this.version = null;
        this.movieURL = null;
        this.altURL = null;
        this.swfLoaded = false;
        this.enabled = false;
        this.oMC = null;
        this.sounds = {};
        this.soundIDs = [];
        this.muted = false;
        this.didFlashBlock = false;
        this.filePattern = null;
        this.filePatterns = {
            'flash8': /\.mp3(\?.*)?$/i,
            'flash9': /\.mp3(\?.*)?$/i
        };
        this.features = {
            'buffering': false,
            'peakData': false,
            'waveformData': false,
            'eqData': false,
            'movieStar': false
        };
        this.sandbox = {
            'type': null,
            'types': {
                'remote': 'remote (domain-based) rules',
                'localWithFile': 'local with file access (no internet access)',
                'localWithNetwork': 'local with network (internet access only, no local access)',
                'localTrusted': 'local, trusted (local+internet access)'
            },
            'description': null,
            'noRemote': null,
            'noLocal': null
        };
        this.html5 = {
            'usingFlash': null
        };
        this.flash = {};
        this.html5Only = false;
        this.ignoreFlash = false;
        var SMSound,
        sm2 = this,
        globalHTML5Audio = null,
        flash = null,
        sm = 'soundManager',
        smc = sm + ': ',
        h5 = 'HTML5::',
        id,
        ua = navigator.userAgent,
        wl = window.location.href.toString(),
        doc = document,
        doNothing,
        setProperties,
        init,
        fV,
        on_queue = [],
        debugOpen = true,
        debugTS,
        didAppend = false,
        appendSuccess = false,
        didInit = false,
        disabled = false,
        windowLoaded = false,
        _wDS,
        wdCount = 0,
        initComplete,
        mixin,
        assign,
        extraOptions,
        addOnEvent,
        processOnEvents,
        initUserOnload,
        delayWaitForEI,
        waitForEI,
        rebootIntoHTML5,
        setVersionInfo,
        handleFocus,
        strings,
        initMovie,
        preInit,
        domContentLoaded,
        winOnLoad,
        didDCLoaded,
        getDocument,
        createMovie,
        catchError,
        setPolling,
        initDebug,
        debugLevels = ['log', 'info', 'warn', 'error'],
        defaultFlashVersion = 8,
        disableObject,
        failSafely,
        normalizeMovieURL,
        oRemoved = null,
        oRemovedHTML = null,
        str,
        flashBlockHandler,
        getSWFCSS,
        swfCSS,
        toggleDebug,
        loopFix,
        policyFix,
        complain,
        idCheck,
        waitingForEI = false,
        initPending = false,
        startTimer,
        stopTimer,
        timerExecute,
        h5TimerCount = 0,
        h5IntervalTimer = null,
        parseURL,
        messages = [],
        canIgnoreFlash,
        needsFlash = null,
        featureCheck,
        html5OK,
        html5CanPlay,
        html5Ext,
        html5Unload,
        domContentLoadedIE,
        testHTML5,
        event,
        slice = Array.prototype.slice,
        useGlobalHTML5Audio = false,
        lastGlobalHTML5URL,
        hasFlash,
        detectFlash,
        badSafariFix,
        html5_events,
        showSupport,
        flushMessages,
        wrapCallback,
        idCounter = 0,
        is_iDevice = ua.match(/(ipad|iphone|ipod)/i),
        isAndroid = ua.match(/android/i),
        isIE = ua.match(/msie/i),
        isWebkit = ua.match(/webkit/i),
        isSafari = (ua.match(/safari/i) && !ua.match(/chrome/i)),
        isOpera = (ua.match(/opera/i)),
        mobileHTML5 = (ua.match(/(mobile|pre\/|xoom)/i) || is_iDevice || isAndroid),
        isBadSafari = (!wl.match(/usehtml5audio/i) && !wl.match(/sm2\-ignorebadua/i) && isSafari && !ua.match(/silk/i) && ua.match(/OS X 10_6_([3-7])/i)),
        hasConsole = (window.console !== _undefined && console.log !== _undefined),
        isFocused = (doc.hasFocus !== _undefined ? doc.hasFocus() : null),
        tryInitOnFocus = (isSafari && (doc.hasFocus === _undefined || !doc.hasFocus())),
        okToDisable = !tryInitOnFocus,
        flashMIME = /(mp3|mp4|mpa|m4a|m4b)/i,
        msecScale = 1000,
        emptyURL = 'about:blank',
        emptyWAV = 'data:audio/wave;base64,/UklGRiYAAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YQIAAAD//w==',
        overHTTP = (doc.location ? doc.location.protocol.match(/http/i) : null),
        http = (!overHTTP ? 'http:/' + '/': ''),
        netStreamMimeTypes = /^\s*audio\/(?:x-)?(?:mpeg4|aac|flv|mov|mp4||m4v|m4a|m4b|mp4v|3gp|3g2)\s*(?:$|;)/i,
        netStreamTypes = ['mpeg4', 'aac', 'flv', 'mov', 'mp4', 'm4v', 'f4v', 'm4a', 'm4b', 'mp4v', '3gp', '3g2'],
        netStreamPattern = new RegExp('\\.(' + netStreamTypes.join('|') + ')(\\?.*)?$', 'i');
        this.mimePattern = /^\s*audio\/(?:x-)?(?:mp(?:eg|3))\s*(?:$|;)/i;
        this.useAltURL = !overHTTP;
        swfCSS = {
            'swfBox': 'sm2-object-box',
            'swfDefault': 'movieContainer',
            'swfError': 'swf_error',
            'swfTimedout': 'swf_timedout',
            'swfLoaded': 'swf_loaded',
            'swfUnblocked': 'swf_unblocked',
            'sm2Debug': 'sm2_debug',
            'highPerf': 'high_performance',
            'flashDebug': 'flash_debug'
        };
        this.hasHTML5 = (function() {
            try {
                return (Audio !== _undefined && (isOpera && opera !== _undefined && opera.version() < 10 ? new Audio(null) : new Audio()).canPlayType !== _undefined);
            } catch(e) {
                return false;
            }
        } ());
        this.setup = function(options) {
            var noURL = (!sm2.url);
            if (options !== _undefined && didInit && needsFlash && sm2.ok() && (options.flashVersion !== _undefined || options.url !== _undefined || options.html5Test !== _undefined)) {
                complain(str('setupLate'));
            }
            assign(options);
            if (options) {
                if (noURL && didDCLoaded && options.url !== _undefined) {
                    sm2.beginDelayedInit();
                }
                if (!didDCLoaded && options.url !== _undefined && doc.readyState === 'complete') {
                    setTimeout(domContentLoaded, 1);
                }
            }
            return sm2;
        };
        this.ok = function() {
            return (needsFlash ? (didInit && !disabled) : (sm2.useHTML5Audio && sm2.hasHTML5));
        };
        this.supported = this.ok;
        this.getMovie = function(smID) {
            return id(smID) || doc[smID] || window[smID];
        };
        this.createSound = function(oOptions, _url) {
            var cs,
            cs_string,
            options,
            oSound = null;
            cs = sm + '.createSound(): ';
            cs_string = cs + str(!didInit ? 'notReady': 'notOK');
            if (!didInit || !sm2.ok()) {
                complain(cs_string);
                return false;
            }
            if (_url !== _undefined) {
                oOptions = {
                    'id': oOptions,
                    'url': _url
                };
            }
            options = mixin(oOptions);
            options.url = parseURL(options.url);
            if (options.id === undefined) {
                options.id = sm2.setupOptions.idPrefix + (idCounter++);
            }
            if (options.id.toString().charAt(0).match(/^[0-9]$/)) {
                sm2._wD(cs + str('badID', options.id), 2);
            }
            sm2._wD(cs + options.id + (options.url ? ' (' + options.url + ')': ''), 1);
            if (idCheck(options.id, true)) {
                sm2._wD(cs + options.id + ' exists', 1);
                return sm2.sounds[options.id];
            }
            function make() {
                options = loopFix(options);
                sm2.sounds[options.id] = new SMSound(options);
                sm2.soundIDs.push(options.id);
                return sm2.sounds[options.id];
            }
            if (html5OK(options)) {
                oSound = make();
                sm2._wD(options.id + ': Using HTML5');
                oSound._setup_html5(options);
            } else {
                if (sm2.html5Only) {
                    sm2._wD(options.id + ': No HTML5 support for this sound, and no Flash. Exiting.');
                    return make();
                }
                if (sm2.html5.usingFlash && options.url && options.url.match(/data\:/i)) {
                    sm2._wD(options.id + ': data: URIs not supported via Flash. Exiting.');
                    return make();
                }
                if (fV > 8) {
                    if (options.isMovieStar === null) {
                        options.isMovieStar = !!(options.serverURL || (options.type ? options.type.match(netStreamMimeTypes) : false) || (options.url && options.url.match(netStreamPattern)));
                    }
                    if (options.isMovieStar) {
                        sm2._wD(cs + 'using MovieStar handling');
                        if (options.loops > 1) {
                            _wDS('noNSLoop');
                        }
                    }
                }
                options = policyFix(options, cs);
                oSound = make();
                if (fV === 8) {
                    flash._createSound(options.id, options.loops || 1, options.usePolicyFile);
                } else {
                    flash._createSound(options.id, options.url, options.usePeakData, options.useWaveformData, options.useEQData, options.isMovieStar, (options.isMovieStar ? options.bufferTime: false), options.loops || 1, options.serverURL, options.duration || null, options.autoPlay, true, options.autoLoad, options.usePolicyFile);
                    if (!options.serverURL) {
                        oSound.connected = true;
                        if (options.onconnect) {
                            options.onconnect.apply(oSound);
                        }
                    }
                }
                if (!options.serverURL && (options.autoLoad || options.autoPlay)) {
                    oSound.load(options);
                }
            }
            if (!options.serverURL && options.autoPlay) {
                oSound.play();
            }
            return oSound;
        };
        this.destroySound = function(sID, _bFromSound) {
            if (!idCheck(sID)) {
                return false;
            }
            var oS = sm2.sounds[sID],
            i;
            oS._iO = {};
            oS.stop();
            oS.unload();
            for (i = 0; i < sm2.soundIDs.length; i++) {
                if (sm2.soundIDs[i] === sID) {
                    sm2.soundIDs.splice(i, 1);
                    break;
                }
            }
            if (!_bFromSound) {
                oS.destruct(true);
            }
            oS = null;
            delete sm2.sounds[sID];
            return true;
        };
        this.load = function(sID, oOptions) {
            if (!idCheck(sID)) {
                return false;
            }
            return sm2.sounds[sID].load(oOptions);
        };
        this.unload = function(sID) {
            if (!idCheck(sID)) {
                return false;
            }
            return sm2.sounds[sID].unload();
        };
        this.onPosition = function(sID, nPosition, oMethod, oScope) {
            if (!idCheck(sID)) {
                return false;
            }
            return sm2.sounds[sID].onposition(nPosition, oMethod, oScope);
        };
        this.onposition = this.onPosition;
        this.clearOnPosition = function(sID, nPosition, oMethod) {
            if (!idCheck(sID)) {
                return false;
            }
            return sm2.sounds[sID].clearOnPosition(nPosition, oMethod);
        };
        this.play = function(sID, oOptions) {
            var result = null,
            overloaded = (oOptions && !(oOptions instanceof Object));
            if (!didInit || !sm2.ok()) {
                complain(sm + '.play(): ' + str(!didInit ? 'notReady': 'notOK'));
                return false;
            }
            if (!idCheck(sID, overloaded)) {
                if (!overloaded) {
                    return false;
                }
                if (overloaded) {
                    oOptions = {
                        url: oOptions
                    };
                }
                if (oOptions && oOptions.url) {
                    sm2._wD(sm + '.play(): Attempting to create "' + sID + '"', 1);
                    oOptions.id = sID;
                    result = sm2.createSound(oOptions).play();
                }
            } else if (overloaded) {
                oOptions = {
                    url: oOptions
                };
            }
            if (result === null) {
                result = sm2.sounds[sID].play(oOptions);
            }
            return result;
        };
        this.start = this.play;
        this.setPosition = function(sID, nMsecOffset) {
            if (!idCheck(sID)) {
                return false;
            }
            return sm2.sounds[sID].setPosition(nMsecOffset);
        };
        this.stop = function(sID) {
            if (!idCheck(sID)) {
                return false;
            }
            sm2._wD(sm + '.stop(' + sID + ')', 1);
            return sm2.sounds[sID].stop();
        };
        this.stopAll = function() {
            var oSound;
            sm2._wD(sm + '.stopAll()', 1);
            for (oSound in sm2.sounds) {
                if (sm2.sounds.hasOwnProperty(oSound)) {
                    sm2.sounds[oSound].stop();
                }
            }
        };
        this.pause = function(sID) {
            if (!idCheck(sID)) {
                return false;
            }
            return sm2.sounds[sID].pause();
        };
        this.pauseAll = function() {
            var i;
            for (i = sm2.soundIDs.length - 1; i >= 0; i--) {
                sm2.sounds[sm2.soundIDs[i]].pause();
            }
        };
        this.resume = function(sID) {
            if (!idCheck(sID)) {
                return false;
            }
            return sm2.sounds[sID].resume();
        };
        this.resumeAll = function() {
            var i;
            for (i = sm2.soundIDs.length - 1; i >= 0; i--) {
                sm2.sounds[sm2.soundIDs[i]].resume();
            }
        };
        this.togglePause = function(sID) {
            if (!idCheck(sID)) {
                return false;
            }
            return sm2.sounds[sID].togglePause();
        };
        this.setPan = function(sID, nPan) {
            if (!idCheck(sID)) {
                return false;
            }
            return sm2.sounds[sID].setPan(nPan);
        };
        this.setVolume = function(sID, nVol) {
            if (!idCheck(sID)) {
                return false;
            }
            return sm2.sounds[sID].setVolume(nVol);
        };
        this.mute = function(sID) {
            var i = 0;
            if (sID instanceof String) {
                sID = null;
            }
            if (!sID) {
                sm2._wD(sm + '.mute(): Muting all sounds');
                for (i = sm2.soundIDs.length - 1; i >= 0; i--) {
                    sm2.sounds[sm2.soundIDs[i]].mute();
                }
                sm2.muted = true;
            } else {
                if (!idCheck(sID)) {
                    return false;
                }
                sm2._wD(sm + '.mute(): Muting "' + sID + '"');
                return sm2.sounds[sID].mute();
            }
            return true;
        };
        this.muteAll = function() {
            sm2.mute();
        };
        this.unmute = function(sID) {
            var i;
            if (sID instanceof String) {
                sID = null;
            }
            if (!sID) {
                sm2._wD(sm + '.unmute(): Unmuting all sounds');
                for (i = sm2.soundIDs.length - 1; i >= 0; i--) {
                    sm2.sounds[sm2.soundIDs[i]].unmute();
                }
                sm2.muted = false;
            } else {
                if (!idCheck(sID)) {
                    return false;
                }
                sm2._wD(sm + '.unmute(): Unmuting "' + sID + '"');
                return sm2.sounds[sID].unmute();
            }
            return true;
        };
        this.unmuteAll = function() {
            sm2.unmute();
        };
        this.toggleMute = function(sID) {
            if (!idCheck(sID)) {
                return false;
            }
            return sm2.sounds[sID].toggleMute();
        };
        this.getMemoryUse = function() {
            var ram = 0;
            if (flash && fV !== 8) {
                ram = parseInt(flash._getMemoryUse(), 10);
            }
            return ram;
        };
        this.disable = function(bNoDisable) {
            var i;
            if (bNoDisable === _undefined) {
                bNoDisable = false;
            }
            if (disabled) {
                return false;
            }
            disabled = true;
            _wDS('shutdown', 1);
            for (i = sm2.soundIDs.length - 1; i >= 0; i--) {
                disableObject(sm2.sounds[sm2.soundIDs[i]]);
            }
            initComplete(bNoDisable);
            event.remove(window, 'load', initUserOnload);
            return true;
        };
        this.canPlayMIME = function(sMIME) {
            var result;
            if (sm2.hasHTML5) {
                result = html5CanPlay({
                    type: sMIME
                });
            }
            if (!result && needsFlash) {
                result = (sMIME && sm2.ok() ? !!((fV > 8 ? sMIME.match(netStreamMimeTypes) : null) || sMIME.match(sm2.mimePattern)) : null);
            }
            return result;
        };
        this.canPlayURL = function(sURL) {
            var result;
            if (sm2.hasHTML5) {
                result = html5CanPlay({
                    url: sURL
                });
            }
            if (!result && needsFlash) {
                result = (sURL && sm2.ok() ? !!(sURL.match(sm2.filePattern)) : null);
            }
            return result;
        };
        this.canPlayLink = function(oLink) {
            if (oLink.type !== _undefined && oLink.type) {
                if (sm2.canPlayMIME(oLink.type)) {
                    return true;
                }
            }
            return sm2.canPlayURL(oLink.href);
        };
        this.getSoundById = function(sID, _suppressDebug) {
            if (!sID) {
                return null;
            }
            var result = sm2.sounds[sID];
            if (!result && !_suppressDebug) {
                sm2._wD(sm + '.getSoundById(): Sound "' + sID + '" not found.', 2);
            }
            return result;
        };
        this.onready = function(oMethod, oScope) {
            var sType = 'onready',
            result = false;
            if (typeof oMethod === 'function') {
                if (didInit) {
                    sm2._wD(str('queue', sType));
                }
                if (!oScope) {
                    oScope = window;
                }
                addOnEvent(sType, oMethod, oScope);
                processOnEvents();
                result = true;
            } else {
                throw str('needFunction', sType);
            }
            return result;
        };
        this.ontimeout = function(oMethod, oScope) {
            var sType = 'ontimeout',
            result = false;
            if (typeof oMethod === 'function') {
                if (didInit) {
                    sm2._wD(str('queue', sType));
                }
                if (!oScope) {
                    oScope = window;
                }
                addOnEvent(sType, oMethod, oScope);
                processOnEvents({
                    type: sType
                });
                result = true;
            } else {
                throw str('needFunction', sType);
            }
            return result;
        };
        this._writeDebug = function(sText, sTypeOrObject) {
            var sDID = 'soundmanager-debug',
            o,
            oItem;
            if (!sm2.debugMode) {
                return false;
            }
            if (hasConsole && sm2.useConsole) {
                if (sTypeOrObject && typeof sTypeOrObject === 'object') {
                    console.log(sText, sTypeOrObject);
                } else if (debugLevels[sTypeOrObject] !== _undefined) {
                    console[debugLevels[sTypeOrObject]](sText);
                } else {
                    console.log(sText);
                }
                if (sm2.consoleOnly) {
                    return true;
                }
            }
            o = id(sDID);
            if (!o) {
                return false;
            }
            oItem = doc.createElement('div');
            if (++wdCount % 2 === 0) {
                oItem.className = 'sm2-alt';
            }
            if (sTypeOrObject === _undefined) {
                sTypeOrObject = 0;
            } else {
                sTypeOrObject = parseInt(sTypeOrObject, 10);
            }
            oItem.appendChild(doc.createTextNode(sText));
            if (sTypeOrObject) {
                if (sTypeOrObject >= 2) {
                    oItem.style.fontWeight = 'bold';
                }
                if (sTypeOrObject === 3) {
                    oItem.style.color = '#ff3333';
                }
            }
            o.insertBefore(oItem, o.firstChild);
            o = null;
            return true;
        };
        if (wl.indexOf('sm2-debug=alert') !== -1) {
            this._writeDebug = function(sText) {
                window.alert(sText);
            };
        }
        this._wD = this._writeDebug;
        this._debug = function() {
            var i,
            j;
            _wDS('currentObj', 1);
            for (i = 0, j = sm2.soundIDs.length; i < j; i++) {
                sm2.sounds[sm2.soundIDs[i]]._debug();
            }
        };
        this.reboot = function(resetEvents, excludeInit) {
            if (sm2.soundIDs.length) {
                sm2._wD('Destroying ' + sm2.soundIDs.length + ' SMSound object' + (sm2.soundIDs.length !== 1 ? 's': '') + '...');
            }
            var i,
            j,
            k;
            for (i = sm2.soundIDs.length - 1; i >= 0; i--) {
                sm2.sounds[sm2.soundIDs[i]].destruct();
            }
            if (flash) {
                try {
                    if (isIE) {
                        oRemovedHTML = flash.innerHTML;
                    }
                    oRemoved = flash.parentNode.removeChild(flash);
                } catch(e) {
                    _wDS('badRemove', 2);
                }
            }
            oRemovedHTML = oRemoved = needsFlash = flash = null;
            sm2.enabled = didDCLoaded = didInit = waitingForEI = initPending = didAppend = appendSuccess = disabled = useGlobalHTML5Audio = sm2.swfLoaded = false;
            sm2.soundIDs = [];
            sm2.sounds = {};
            idCounter = 0;
            if (!resetEvents) {
                for (i in on_queue) {
                    if (on_queue.hasOwnProperty(i)) {
                        for (j = 0, k = on_queue[i].length; j < k; j++) {
                            on_queue[i][j].fired = false;
                        }
                    }
                }
            } else {
                on_queue = [];
            }
            if (!excludeInit) {
                sm2._wD(sm + ': Rebooting...');
            }
            sm2.html5 = {
                'usingFlash': null
            };
            sm2.flash = {};
            sm2.html5Only = false;
            sm2.ignoreFlash = false;
            window.setTimeout(function() {
                preInit();
                if (!excludeInit) {
                    sm2.beginDelayedInit();
                }
            },
            20);
            return sm2;
        };
        this.reset = function() {
            _wDS('reset');
            return sm2.reboot(true, true);
        };
        this.getMoviePercent = function() {
            return (flash && 'PercentLoaded' in flash ? flash.PercentLoaded() : null);
        };
        this.beginDelayedInit = function() {
            windowLoaded = true;
            domContentLoaded();
            setTimeout(function() {
                if (initPending) {
                    return false;
                }
                createMovie();
                initMovie();
                initPending = true;
                return true;
            },
            20);
            delayWaitForEI();
        };
        this.destruct = function() {
            sm2._wD(sm + '.destruct()');
            sm2.disable(true);
        };
        SMSound = function(oOptions) {
            var s = this,
            resetProperties,
            add_html5_events,
            remove_html5_events,
            stop_html5_timer,
            start_html5_timer,
            attachOnPosition,
            onplay_called = false,
            onPositionItems = [],
            onPositionFired = 0,
            detachOnPosition,
            applyFromTo,
            lastURL = null,
            lastHTML5State,
            urlOmitted;
            lastHTML5State = {
                duration: null,
                time: null
            };
            this.id = oOptions.id;
            this.sID = this.id;
            this.url = oOptions.url;
            this.options = mixin(oOptions);
            this.instanceOptions = this.options;
            this._iO = this.instanceOptions;
            this.pan = this.options.pan;
            this.volume = this.options.volume;
            this.isHTML5 = false;
            this._a = null;
            urlOmitted = (this.url ? false: true);
            this.id3 = {};
            this._debug = function() {
                sm2._wD(s.id + ': Merged options:', s.options);
            };
            this.load = function(oOptions) {
                var oSound = null,
                instanceOptions;
                if (oOptions !== _undefined) {
                    s._iO = mixin(oOptions, s.options);
                } else {
                    oOptions = s.options;
                    s._iO = oOptions;
                    if (lastURL && lastURL !== s.url) {
                        _wDS('manURL');
                        s._iO.url = s.url;
                        s.url = null;
                    }
                }
                if (!s._iO.url) {
                    s._iO.url = s.url;
                }
                s._iO.url = parseURL(s._iO.url);
                s.instanceOptions = s._iO;
                instanceOptions = s._iO;
                sm2._wD(s.id + ': load (' + instanceOptions.url + ')');
                if (!instanceOptions.url && !s.url) {
                    sm2._wD(s.id + ': load(): url is unassigned. Exiting.', 2);
                    return s;
                }
                if (!s.isHTML5 && fV === 8 && !s.url && !instanceOptions.autoPlay) {
                    sm2._wD(s.id + ': Flash 8 load() limitation: Wait for onload() before calling play().', 1);
                }
                if (instanceOptions.url === s.url && s.readyState !== 0 && s.readyState !== 2) {
                    _wDS('onURL', 1);
                    if (s.readyState === 3 && instanceOptions.onload) {
                        wrapCallback(s,
                        function() {
                            instanceOptions.onload.apply(s, [( !! s.duration)]);
                        });
                    }
                    return s;
                }
                s.loaded = false;
                s.readyState = 1;
                s.playState = 0;
                s.id3 = {};
                if (html5OK(instanceOptions)) {
                    oSound = s._setup_html5(instanceOptions);
                    if (!oSound._called_load) {
                        s._html5_canplay = false;
                        if (s.url !== instanceOptions.url) {
                            sm2._wD(_wDS('manURL') + ': ' + instanceOptions.url);
                            s._a.src = instanceOptions.url;
                            s.setPosition(0);
                        }
                        s._a.autobuffer = 'auto';
                        s._a.preload = 'auto';
                        s._a._called_load = true;
                    } else {
                        sm2._wD(s.id + ': Ignoring request to load again');
                    }
                } else {
                    if (sm2.html5Only) {
                        sm2._wD(s.id + ': No flash support. Exiting.');
                        return s;
                    }
                    if (s._iO.url && s._iO.url.match(/data\:/i)) {
                        sm2._wD(s.id + ': data: URIs not supported via Flash. Exiting.');
                        return s;
                    }
                    try {
                        s.isHTML5 = false;
                        s._iO = policyFix(loopFix(instanceOptions));
                        instanceOptions = s._iO;
                        if (fV === 8) {
                            flash._load(s.id, instanceOptions.url, instanceOptions.stream, instanceOptions.autoPlay, instanceOptions.usePolicyFile);
                        } else {
                            flash._load(s.id, instanceOptions.url, !!(instanceOptions.stream), !!(instanceOptions.autoPlay), instanceOptions.loops || 1, !!(instanceOptions.autoLoad), instanceOptions.usePolicyFile);
                        }
                    } catch(e) {
                        _wDS('smError', 2);
                        debugTS('onload', false);
                        catchError({
                            type: 'SMSOUND_LOAD_JS_EXCEPTION',
                            fatal: true
                        });
                    }
                }
                s.url = instanceOptions.url;
                return s;
            };
            this.unload = function() {
                if (s.readyState !== 0) {
                    sm2._wD(s.id + ': unload()');
                    if (!s.isHTML5) {
                        if (fV === 8) {
                            flash._unload(s.id, emptyURL);
                        } else {
                            flash._unload(s.id);
                        }
                    } else {
                        stop_html5_timer();
                        if (s._a) {
                            s._a.pause();
                            lastURL = html5Unload(s._a);
                        }
                    }
                    resetProperties();
                }
                return s;
            };
            this.destruct = function(_bFromSM) {
                sm2._wD(s.id + ': Destruct');
                if (!s.isHTML5) {
                    s._iO.onfailure = null;
                    flash._destroySound(s.id);
                } else {
                    stop_html5_timer();
                    if (s._a) {
                        s._a.pause();
                        html5Unload(s._a);
                        if (!useGlobalHTML5Audio) {
                            remove_html5_events();
                        }
                        s._a._s = null;
                        s._a = null;
                    }
                }
                if (!_bFromSM) {
                    sm2.destroySound(s.id, true);
                }
            };
            this.play = function(oOptions, _updatePlayState) {
                var fN,
                allowMulti,
                a,
                onready,
                audioClone,
                onended,
                oncanplay,
                startOK = true,
                exit = null;
                fN = s.id + ': play(): ';
                _updatePlayState = (_updatePlayState === _undefined ? true: _updatePlayState);
                if (!oOptions) {
                    oOptions = {};
                }
                if (s.url) {
                    s._iO.url = s.url;
                }
                s._iO = mixin(s._iO, s.options);
                s._iO = mixin(oOptions, s._iO);
                s._iO.url = parseURL(s._iO.url);
                s.instanceOptions = s._iO;
                if (!s.isHTML5 && s._iO.serverURL && !s.connected) {
                    if (!s.getAutoPlay()) {
                        sm2._wD(fN + ' Netstream not connected yet - setting autoPlay');
                        s.setAutoPlay(true);
                    }
                    return s;
                }
                if (html5OK(s._iO)) {
                    s._setup_html5(s._iO);
                    start_html5_timer();
                }
                if (s.playState === 1 && !s.paused) {
                    allowMulti = s._iO.multiShot;
                    if (!allowMulti) {
                        sm2._wD(fN + 'Already playing (one-shot)', 1);
                        if (s.isHTML5) {
                            s.setPosition(s._iO.position);
                        }
                        exit = s;
                    } else {
                        sm2._wD(fN + 'Already playing (multi-shot)', 1);
                    }
                }
                if (exit !== null) {
                    return exit;
                }
                if (oOptions.url && oOptions.url !== s.url) {
                    if (!s.readyState && !s.isHTML5 && fV === 8 && urlOmitted) {
                        urlOmitted = false;
                    } else {
                        s.load(s._iO);
                    }
                }
                if (!s.loaded) {
                    if (s.readyState === 0) {
                        sm2._wD(fN + 'Attempting to load');
                        if (!s.isHTML5 && !sm2.html5Only) {
                            s._iO.autoPlay = true;
                            s.load(s._iO);
                        } else if (s.isHTML5) {
                            s.load(s._iO);
                        } else {
                            sm2._wD(fN + 'Unsupported type. Exiting.');
                            exit = s;
                        }
                        s.instanceOptions = s._iO;
                    } else if (s.readyState === 2) {
                        sm2._wD(fN + 'Could not load - exiting', 2);
                        exit = s;
                    } else {
                        sm2._wD(fN + 'Loading - attempting to play...');
                    }
                } else {
                    sm2._wD(fN.substr(0, fN.lastIndexOf(':')));
                }
                if (exit !== null) {
                    return exit;
                }
                if (!s.isHTML5 && fV === 9 && s.position > 0 && s.position === s.duration) {
                    sm2._wD(fN + 'Sound at end, resetting to position:0');
                    oOptions.position = 0;
                }
                if (s.paused && s.position >= 0 && (!s._iO.serverURL || s.position > 0)) {
                    sm2._wD(fN + 'Resuming from paused state', 1);
                    s.resume();
                } else {
                    s._iO = mixin(oOptions, s._iO);
                    if (s._iO.from !== null && s._iO.to !== null && s.instanceCount === 0 && s.playState === 0 && !s._iO.serverURL) {
                        onready = function() {
                            s._iO = mixin(oOptions, s._iO);
                            s.play(s._iO);
                        };
                        if (s.isHTML5 && !s._html5_canplay) {
                            sm2._wD(fN + 'Beginning load for from/to case');
                            s.load({
                                _oncanplay: onready
                            });
                            exit = false;
                        } else if (!s.isHTML5 && !s.loaded && (!s.readyState || s.readyState !== 2)) {
                            sm2._wD(fN + 'Preloading for from/to case');
                            s.load({
                                onload: onready
                            });
                            exit = false;
                        }
                        if (exit !== null) {
                            return exit;
                        }
                        s._iO = applyFromTo();
                    }
                    if (!s.instanceCount || s._iO.multiShotEvents || (s.isHTML5 && s._iO.multiShot && !useGlobalHTML5Audio) || (!s.isHTML5 && fV > 8 && !s.getAutoPlay())) {
                        s.instanceCount++;
                    }
                    if (s._iO.onposition && s.playState === 0) {
                        attachOnPosition(s);
                    }
                    s.playState = 1;
                    s.paused = false;
                    s.position = (s._iO.position !== _undefined && !isNaN(s._iO.position) ? s._iO.position: 0);
                    if (!s.isHTML5) {
                        s._iO = policyFix(loopFix(s._iO));
                    }
                    if (s._iO.onplay && _updatePlayState) {
                        s._iO.onplay.apply(s);
                        onplay_called = true;
                    }
                    s.setVolume(s._iO.volume, true);
                    s.setPan(s._iO.pan, true);
                    if (!s.isHTML5) {
                        startOK = flash._start(s.id, s._iO.loops || 1, (fV === 9 ? s.position: s.position / msecScale), s._iO.multiShot || false);
                        if (fV === 9 && !startOK) {
                            sm2._wD(fN + 'No sound hardware, or 32-sound ceiling hit', 2);
                            if (s._iO.onplayerror) {
                                s._iO.onplayerror.apply(s);
                            }
                        }
                    } else {
                        if (s.instanceCount < 2) {
                            start_html5_timer();
                            a = s._setup_html5();
                            s.setPosition(s._iO.position);
                            a.play();
                        } else {
                            sm2._wD(s.id + ': Cloning Audio() for instance #' + s.instanceCount + '...');
                            audioClone = new Audio(s._iO.url);
                            onended = function() {
                                event.remove(audioClone, 'ended', onended);
                                s._onfinish(s);
                                html5Unload(audioClone);
                                audioClone = null;
                            };
                            oncanplay = function() {
                                event.remove(audioClone, 'canplay', oncanplay);
                                try {
                                    audioClone.currentTime = s._iO.position / msecScale;
                                } catch(err) {
                                    complain(s.id + ': multiShot play() failed to apply position of ' + (s._iO.position / msecScale));
                                }
                                audioClone.play();
                            };
                            event.add(audioClone, 'ended', onended);
                            if (s._iO.volume !== undefined) {
                                audioClone.volume = Math.max(0, Math.min(1, s._iO.volume / 100));
                            }
                            if (s.muted) {
                                audioClone.muted = true;
                            }
                            if (s._iO.position) {
                                event.add(audioClone, 'canplay', oncanplay);
                            } else {
                                audioClone.play();
                            }
                        }
                    }
                }
                return s;
            };
            this.start = this.play;
            this.stop = function(bAll) {
                var instanceOptions = s._iO,
                originalPosition;
                if (s.playState === 1) {
                    sm2._wD(s.id + ': stop()');
                    s._onbufferchange(0);
                    s._resetOnPosition(0);
                    s.paused = false;
                    if (!s.isHTML5) {
                        s.playState = 0;
                    }
                    detachOnPosition();
                    if (instanceOptions.to) {
                        s.clearOnPosition(instanceOptions.to);
                    }
                    if (!s.isHTML5) {
                        flash._stop(s.id, bAll);
                        if (instanceOptions.serverURL) {
                            s.unload();
                        }
                    } else {
                        if (s._a) {
                            originalPosition = s.position;
                            s.setPosition(0);
                            s.position = originalPosition;
                            s._a.pause();
                            s.playState = 0;
                            s._onTimer();
                            stop_html5_timer();
                        }
                    }
                    s.instanceCount = 0;
                    s._iO = {};
                    if (instanceOptions.onstop) {
                        instanceOptions.onstop.apply(s);
                    }
                }
                return s;
            };
            this.setAutoPlay = function(autoPlay) {
                sm2._wD(s.id + ': Autoplay turned ' + (autoPlay ? 'on': 'off'));
                s._iO.autoPlay = autoPlay;
                if (!s.isHTML5) {
                    flash._setAutoPlay(s.id, autoPlay);
                    if (autoPlay) {
                        if (!s.instanceCount && s.readyState === 1) {
                            s.instanceCount++;
                            sm2._wD(s.id + ': Incremented instance count to ' + s.instanceCount);
                        }
                    }
                }
            };
            this.getAutoPlay = function() {
                return s._iO.autoPlay;
            };
            this.setPosition = function(nMsecOffset) {
                if (nMsecOffset === _undefined) {
                    nMsecOffset = 0;
                }
                var position,
                position1K,
                offset = (s.isHTML5 ? Math.max(nMsecOffset, 0) : Math.min(s.duration || s._iO.duration, Math.max(nMsecOffset, 0)));
                s.position = offset;
                position1K = s.position / msecScale;
                s._resetOnPosition(s.position);
                s._iO.position = offset;
                if (!s.isHTML5) {
                    position = (fV === 9 ? s.position: position1K);
                    if (s.readyState && s.readyState !== 2) {
                        flash._setPosition(s.id, position, (s.paused || !s.playState), s._iO.multiShot);
                    }
                } else if (s._a) {
                    if (s._html5_canplay) {
                        if (s._a.currentTime !== position1K) {
                            sm2._wD(s.id + ': setPosition(' + position1K + ')');
                            try {
                                s._a.currentTime = position1K;
                                if (s.playState === 0 || s.paused) {
                                    s._a.pause();
                                }
                            } catch(e) {
                                sm2._wD(s.id + ': setPosition(' + position1K + ') failed: ' + e.message, 2);
                            }
                        }
                    } else if (position1K) {
                        sm2._wD(s.id + ': setPosition(' + position1K + '): Cannot seek yet, sound not ready', 2);
                        return s;
                    }
                    if (s.paused) {
                        s._onTimer(true);
                    }
                }
                return s;
            };
            this.pause = function(_bCallFlash) {
                if (s.paused || (s.playState === 0 && s.readyState !== 1)) {
                    return s;
                }
                sm2._wD(s.id + ': pause()');
                s.paused = true;
                if (!s.isHTML5) {
                    if (_bCallFlash || _bCallFlash === _undefined) {
                        flash._pause(s.id, s._iO.multiShot);
                    }
                } else {
                    s._setup_html5().pause();
                    stop_html5_timer();
                }
                if (s._iO.onpause) {
                    s._iO.onpause.apply(s);
                }
                return s;
            };
            this.resume = function() {
                var instanceOptions = s._iO;
                if (!s.paused) {
                    return s;
                }
                sm2._wD(s.id + ': resume()');
                s.paused = false;
                s.playState = 1;
                if (!s.isHTML5) {
                    if (instanceOptions.isMovieStar && !instanceOptions.serverURL) {
                        s.setPosition(s.position);
                    }
                    flash._pause(s.id, instanceOptions.multiShot);
                } else {
                    s._setup_html5().play();
                    start_html5_timer();
                }
                if (!onplay_called && instanceOptions.onplay) {
                    instanceOptions.onplay.apply(s);
                    onplay_called = true;
                } else if (instanceOptions.onresume) {
                    instanceOptions.onresume.apply(s);
                }
                return s;
            };
            this.togglePause = function() {
                sm2._wD(s.id + ': togglePause()');
                if (s.playState === 0) {
                    s.play({
                        position: (fV === 9 && !s.isHTML5 ? s.position: s.position / msecScale)
                    });
                    return s;
                }
                if (s.paused) {
                    s.resume();
                } else {
                    s.pause();
                }
                return s;
            };
            this.setPan = function(nPan, bInstanceOnly) {
                if (nPan === _undefined) {
                    nPan = 0;
                }
                if (bInstanceOnly === _undefined) {
                    bInstanceOnly = false;
                }
                if (!s.isHTML5) {
                    flash._setPan(s.id, nPan);
                }
                s._iO.pan = nPan;
                if (!bInstanceOnly) {
                    s.pan = nPan;
                    s.options.pan = nPan;
                }
                return s;
            };
            this.setVolume = function(nVol, _bInstanceOnly) {
                if (nVol === _undefined) {
                    nVol = 100;
                }
                if (_bInstanceOnly === _undefined) {
                    _bInstanceOnly = false;
                }
                if (!s.isHTML5) {
                    flash._setVolume(s.id, (sm2.muted && !s.muted) || s.muted ? 0: nVol);
                } else if (s._a) {
                    if (sm2.muted && !s.muted) {
                        s.muted = true;
                        s._a.muted = true;
                    }
                    s._a.volume = Math.max(0, Math.min(1, nVol / 100));
                }
                s._iO.volume = nVol;
                if (!_bInstanceOnly) {
                    s.volume = nVol;
                    s.options.volume = nVol;
                }
                return s;
            };
            this.mute = function() {
                s.muted = true;
                if (!s.isHTML5) {
                    flash._setVolume(s.id, 0);
                } else if (s._a) {
                    s._a.muted = true;
                }
                return s;
            };
            this.unmute = function() {
                s.muted = false;
                var hasIO = (s._iO.volume !== _undefined);
                if (!s.isHTML5) {
                    flash._setVolume(s.id, hasIO ? s._iO.volume: s.options.volume);
                } else if (s._a) {
                    s._a.muted = false;
                }
                return s;
            };
            this.toggleMute = function() {
                return (s.muted ? s.unmute() : s.mute());
            };
            this.onPosition = function(nPosition, oMethod, oScope) {
                onPositionItems.push({
                    position: parseInt(nPosition, 10),
                    method: oMethod,
                    scope: (oScope !== _undefined ? oScope: s),
                    fired: false
                });
                return s;
            };
            this.onposition = this.onPosition;
            this.clearOnPosition = function(nPosition, oMethod) {
                var i;
                nPosition = parseInt(nPosition, 10);
                if (isNaN(nPosition)) {
                    return false;
                }
                for (i = 0; i < onPositionItems.length; i++) {
                    if (nPosition === onPositionItems[i].position) {
                        if (!oMethod || (oMethod === onPositionItems[i].method)) {
                            if (onPositionItems[i].fired) {
                                onPositionFired--;
                            }
                            onPositionItems.splice(i, 1);
                        }
                    }
                }
            };
            this._processOnPosition = function() {
                var i,
                item,
                j = onPositionItems.length;
                if (!j || !s.playState || onPositionFired >= j) {
                    return false;
                }
                for (i = j - 1; i >= 0; i--) {
                    item = onPositionItems[i];
                    if (!item.fired && s.position >= item.position) {
                        item.fired = true;
                        onPositionFired++;
                        item.method.apply(item.scope, [item.position]);
                        j = onPositionItems.length;
                    }
                }
                return true;
            };
            this._resetOnPosition = function(nPosition) {
                var i,
                item,
                j = onPositionItems.length;
                if (!j) {
                    return false;
                }
                for (i = j - 1; i >= 0; i--) {
                    item = onPositionItems[i];
                    if (item.fired && nPosition <= item.position) {
                        item.fired = false;
                        onPositionFired--;
                    }
                }
                return true;
            };
            applyFromTo = function() {
                var instanceOptions = s._iO,
                f = instanceOptions.from,
                t = instanceOptions.to,
                start,
                end;
                end = function() {
                    sm2._wD(s.id + ': "To" time of ' + t + ' reached.');
                    s.clearOnPosition(t, end);
                    s.stop();
                };
                start = function() {
                    sm2._wD(s.id + ': Playing "from" ' + f);
                    if (t !== null && !isNaN(t)) {
                        s.onPosition(t, end);
                    }
                };
                if (f !== null && !isNaN(f)) {
                    instanceOptions.position = f;
                    instanceOptions.multiShot = false;
                    start();
                }
                return instanceOptions;
            };
            attachOnPosition = function() {
                var item,
                op = s._iO.onposition;
                if (op) {
                    for (item in op) {
                        if (op.hasOwnProperty(item)) {
                            s.onPosition(parseInt(item, 10), op[item]);
                        }
                    }
                }
            };
            detachOnPosition = function() {
                var item,
                op = s._iO.onposition;
                if (op) {
                    for (item in op) {
                        if (op.hasOwnProperty(item)) {
                            s.clearOnPosition(parseInt(item, 10));
                        }
                    }
                }
            };
            start_html5_timer = function() {
                if (s.isHTML5) {
                    startTimer(s);
                }
            };
            stop_html5_timer = function() {
                if (s.isHTML5) {
                    stopTimer(s);
                }
            };
            resetProperties = function(retainPosition) {
                if (!retainPosition) {
                    onPositionItems = [];
                    onPositionFired = 0;
                }
                onplay_called = false;
                s._hasTimer = null;
                s._a = null;
                s._html5_canplay = false;
                s.bytesLoaded = null;
                s.bytesTotal = null;
                s.duration = (s._iO && s._iO.duration ? s._iO.duration: null);
                s.durationEstimate = null;
                s.buffered = [];
                s.eqData = [];
                s.eqData.left = [];
                s.eqData.right = [];
                s.failures = 0;
                s.isBuffering = false;
                s.instanceOptions = {};
                s.instanceCount = 0;
                s.loaded = false;
                s.metadata = {};
                s.readyState = 0;
                s.muted = false;
                s.paused = false;
                s.peakData = {
                    left: 0,
                    right: 0
                };
                s.waveformData = {
                    left: [],
                    right: []
                };
                s.playState = 0;
                s.position = null;
                s.id3 = {};
            };
            resetProperties();
            this._onTimer = function(bForce) {
                var duration,
                isNew = false,
                time,
                x = {};
                if (s._hasTimer || bForce) {
                    if (s._a && (bForce || ((s.playState > 0 || s.readyState === 1) && !s.paused))) {
                        duration = s._get_html5_duration();
                        if (duration !== lastHTML5State.duration) {
                            lastHTML5State.duration = duration;
                            s.duration = duration;
                            isNew = true;
                        }
                        s.durationEstimate = s.duration;
                        time = (s._a.currentTime * msecScale || 0);
                        if (time !== lastHTML5State.time) {
                            lastHTML5State.time = time;
                            isNew = true;
                        }
                        if (isNew || bForce) {
                            s._whileplaying(time, x, x, x, x);
                        }
                    }
                    return isNew;
                }
            };
            this._get_html5_duration = function() {
                var instanceOptions = s._iO,
                d = (s._a && s._a.duration ? s._a.duration * msecScale: (instanceOptions && instanceOptions.duration ? instanceOptions.duration: null)),
                result = (d && !isNaN(d) && d !== Infinity ? d: null);
                return result;
            };
            this._apply_loop = function(a, nLoops) {
                if (!a.loop && nLoops > 1) {
                    sm2._wD('Note: Native HTML5 looping is infinite.', 1);
                }
                a.loop = (nLoops > 1 ? 'loop': '');
            };
            this._setup_html5 = function(oOptions) {
                var instanceOptions = mixin(s._iO, oOptions),
                a = useGlobalHTML5Audio ? globalHTML5Audio: s._a,
                dURL = decodeURI(instanceOptions.url),
                sameURL;
                if (useGlobalHTML5Audio) {
                    if (dURL === decodeURI(lastGlobalHTML5URL)) {
                        sameURL = true;
                    }
                } else if (dURL === decodeURI(lastURL)) {
                    sameURL = true;
                }
                if (a) {
                    if (a._s) {
                        if (useGlobalHTML5Audio) {
                            if (a._s && a._s.playState && !sameURL) {
                                a._s.stop();
                            }
                        } else if (!useGlobalHTML5Audio && dURL === decodeURI(lastURL)) {
                            s._apply_loop(a, instanceOptions.loops);
                            return a;
                        }
                    }
                    if (!sameURL) {
                        if (lastURL) {
                            resetProperties(false);
                        }
                        a.src = instanceOptions.url;
                        s.url = instanceOptions.url;
                        lastURL = instanceOptions.url;
                        lastGlobalHTML5URL = instanceOptions.url;
                        a._called_load = false;
                    }
                } else {
                    if (instanceOptions.autoLoad || instanceOptions.autoPlay) {
                        s._a = new Audio(instanceOptions.url);
                        s._a.load();
                    } else {
                        s._a = (isOpera && opera.version() < 10 ? new Audio(null) : new Audio());
                    }
                    a = s._a;
                    a._called_load = false;
                    if (useGlobalHTML5Audio) {
                        globalHTML5Audio = a;
                    }
                }
                s.isHTML5 = true;
                s._a = a;
                a._s = s;
                add_html5_events();
                s._apply_loop(a, instanceOptions.loops);
                if (instanceOptions.autoLoad || instanceOptions.autoPlay) {
                    s.load();
                } else {
                    a.autobuffer = false;
                    a.preload = 'auto';
                }
                return a;
            };
            add_html5_events = function() {
                if (s._a._added_events) {
                    return false;
                }
                var f;
                function add(oEvt, oFn, bCapture) {
                    return s._a ? s._a.addEventListener(oEvt, oFn, bCapture || false) : null;
                }
                s._a._added_events = true;
                for (f in html5_events) {
                    if (html5_events.hasOwnProperty(f)) {
                        add(f, html5_events[f]);
                    }
                }
                return true;
            };
            remove_html5_events = function() {
                var f;
                function remove(oEvt, oFn, bCapture) {
                    return (s._a ? s._a.removeEventListener(oEvt, oFn, bCapture || false) : null);
                }
                sm2._wD(s.id + ': Removing event listeners');
                s._a._added_events = false;
                for (f in html5_events) {
                    if (html5_events.hasOwnProperty(f)) {
                        remove(f, html5_events[f]);
                    }
                }
            };
            this._onload = function(nSuccess) {
                var fN,
                loadOK = !!nSuccess || (!s.isHTML5 && fV === 8 && s.duration);
                fN = s.id + ': ';
                sm2._wD(fN + (loadOK ? 'onload()': 'Failed to load / invalid sound?' + (!s.duration ? ' Zero-length duration reported.': ' -') + ' (' + s.url + ')'), (loadOK ? 1: 2));
                if (!loadOK && !s.isHTML5) {
                    if (sm2.sandbox.noRemote === true) {
                        sm2._wD(fN + str('noNet'), 1);
                    }
                    if (sm2.sandbox.noLocal === true) {
                        sm2._wD(fN + str('noLocal'), 1);
                    }
                }
                s.loaded = loadOK;
                s.readyState = loadOK ? 3: 2;
                s._onbufferchange(0);
                if (s._iO.onload) {
                    wrapCallback(s,
                    function() {
                        s._iO.onload.apply(s, [loadOK]);
                    });
                }
                return true;
            };
            this._onbufferchange = function(nIsBuffering) {
                if (s.playState === 0) {
                    return false;
                }
                if ((nIsBuffering && s.isBuffering) || (!nIsBuffering && !s.isBuffering)) {
                    return false;
                }
                s.isBuffering = (nIsBuffering === 1);
                if (s._iO.onbufferchange) {
                    sm2._wD(s.id + ': Buffer state change: ' + nIsBuffering);
                    s._iO.onbufferchange.apply(s);
                }
                return true;
            };
            this._onsuspend = function() {
                if (s._iO.onsuspend) {
                    sm2._wD(s.id + ': Playback suspended');
                    s._iO.onsuspend.apply(s);
                }
                return true;
            };
            this._onfailure = function(msg, level, code) {
                s.failures++;
                sm2._wD(s.id + ': Failures = ' + s.failures);
                if (s._iO.onfailure && s.failures === 1) {
                    s._iO.onfailure(s, msg, level, code);
                } else {
                    sm2._wD(s.id + ': Ignoring failure');
                }
            };
            this._onfinish = function() {
                var io_onfinish = s._iO.onfinish;
                s._onbufferchange(0);
                s._resetOnPosition(0);
                if (s.instanceCount) {
                    s.instanceCount--;
                    if (!s.instanceCount) {
                        detachOnPosition();
                        s.playState = 0;
                        s.paused = false;
                        s.instanceCount = 0;
                        s.instanceOptions = {};
                        s._iO = {};
                        stop_html5_timer();
                        if (s.isHTML5) {
                            s.position = 0;
                        }
                    }
                    if (!s.instanceCount || s._iO.multiShotEvents) {
                        if (io_onfinish) {
                            sm2._wD(s.id + ': onfinish()');
                            wrapCallback(s,
                            function() {
                                io_onfinish.apply(s);
                            });
                        }
                    }
                }
            };
            this._whileloading = function(nBytesLoaded, nBytesTotal, nDuration, nBufferLength) {
                var instanceOptions = s._iO;
                s.bytesLoaded = nBytesLoaded;
                s.bytesTotal = nBytesTotal;
                s.duration = Math.floor(nDuration);
                s.bufferLength = nBufferLength;
                if (!s.isHTML5 && !instanceOptions.isMovieStar) {
                    if (instanceOptions.duration) {
                        s.durationEstimate = (s.duration > instanceOptions.duration) ? s.duration: instanceOptions.duration;
                    } else {
                        s.durationEstimate = parseInt((s.bytesTotal / s.bytesLoaded) * s.duration, 10);
                    }
                } else {
                    s.durationEstimate = s.duration;
                }
                if (!s.isHTML5) {
                    s.buffered = [{
                        'start': 0,
                        'end': s.duration
                    }];
                }
                if ((s.readyState !== 3 || s.isHTML5) && instanceOptions.whileloading) {
                    instanceOptions.whileloading.apply(s);
                }
            };
            this._whileplaying = function(nPosition, oPeakData, oWaveformDataLeft, oWaveformDataRight, oEQData) {
                var instanceOptions = s._iO,
                eqLeft;
                if (isNaN(nPosition) || nPosition === null) {
                    return false;
                }
                s.position = Math.max(0, nPosition);
                s._processOnPosition();
                if (!s.isHTML5 && fV > 8) {
                    if (instanceOptions.usePeakData && oPeakData !== _undefined && oPeakData) {
                        s.peakData = {
                            left: oPeakData.leftPeak,
                            right: oPeakData.rightPeak
                        };
                    }
                    if (instanceOptions.useWaveformData && oWaveformDataLeft !== _undefined && oWaveformDataLeft) {
                        s.waveformData = {
                            left: oWaveformDataLeft.split(','),
                            right: oWaveformDataRight.split(',')
                        };
                    }
                    if (instanceOptions.useEQData) {
                        if (oEQData !== _undefined && oEQData && oEQData.leftEQ) {
                            eqLeft = oEQData.leftEQ.split(',');
                            s.eqData = eqLeft;
                            s.eqData.left = eqLeft;
                            if (oEQData.rightEQ !== _undefined && oEQData.rightEQ) {
                                s.eqData.right = oEQData.rightEQ.split(',');
                            }
                        }
                    }
                }
                if (s.playState === 1) {
                    if (!s.isHTML5 && fV === 8 && !s.position && s.isBuffering) {
                        s._onbufferchange(0);
                    }
                    if (instanceOptions.whileplaying) {
                        instanceOptions.whileplaying.apply(s);
                    }
                }
                return true;
            };
            this._oncaptiondata = function(oData) {
                sm2._wD(s.id + ': Caption data received.');
                s.captiondata = oData;
                if (s._iO.oncaptiondata) {
                    s._iO.oncaptiondata.apply(s, [oData]);
                }
            };
            this._onmetadata = function(oMDProps, oMDData) {
                sm2._wD(s.id + ': Metadata received.');
                var oData = {},
                i,
                j;
                for (i = 0, j = oMDProps.length; i < j; i++) {
                    oData[oMDProps[i]] = oMDData[i];
                }
                s.metadata = oData;
                if (s._iO.onmetadata) {
                    s._iO.onmetadata.apply(s);
                }
            };
            this._onid3 = function(oID3Props, oID3Data) {
                sm2._wD(s.id + ': ID3 data received.');
                var oData = [],
                i,
                j;
                for (i = 0, j = oID3Props.length; i < j; i++) {
                    oData[oID3Props[i]] = oID3Data[i];
                }
                s.id3 = mixin(s.id3, oData);
                if (s._iO.onid3) {
                    s._iO.onid3.apply(s);
                }
            };
            this._onconnect = function(bSuccess) {
                bSuccess = (bSuccess === 1);
                sm2._wD(s.id + ': ' + (bSuccess ? 'Connected.': 'Failed to connect? - ' + s.url), (bSuccess ? 1: 2));
                s.connected = bSuccess;
                if (bSuccess) {
                    s.failures = 0;
                    if (idCheck(s.id)) {
                        if (s.getAutoPlay()) {
                            s.play(_undefined, s.getAutoPlay());
                        } else if (s._iO.autoLoad) {
                            s.load();
                        }
                    }
                    if (s._iO.onconnect) {
                        s._iO.onconnect.apply(s, [bSuccess]);
                    }
                }
            };
            this._ondataerror = function(sError) {
                if (s.playState > 0) {
                    sm2._wD(s.id + ': Data error: ' + sError);
                    if (s._iO.ondataerror) {
                        s._iO.ondataerror.apply(s);
                    }
                }
            };
            this._debug();
        };
        getDocument = function() {
            return (doc.body || doc.getElementsByTagName('div')[0]);
        };
        id = function(sID) {
            return doc.getElementById(sID);
        };
        mixin = function(oMain, oAdd) {
            var o1 = (oMain || {}),
            o2,
            o;
            o2 = (oAdd === _undefined ? sm2.defaultOptions: oAdd);
            for (o in o2) {
                if (o2.hasOwnProperty(o) && o1[o] === _undefined) {
                    if (typeof o2[o] !== 'object' || o2[o] === null) {
                        o1[o] = o2[o];
                    } else {
                        o1[o] = mixin(o1[o], o2[o]);
                    }
                }
            }
            return o1;
        };
        wrapCallback = function(oSound, callback) {
            if (!oSound.isHTML5 && fV === 8) {
                window.setTimeout(callback, 0);
            } else {
                callback();
            }
        };
        extraOptions = {
            'onready': 1,
            'ontimeout': 1,
            'defaultOptions': 1,
            'flash9Options': 1,
            'movieStarOptions': 1
        };
        assign = function(o, oParent) {
            var i,
            result = true,
            hasParent = (oParent !== _undefined),
            setupOptions = sm2.setupOptions,
            bonusOptions = extraOptions;
            if (o === _undefined) {
                result = [];
                for (i in setupOptions) {
                    if (setupOptions.hasOwnProperty(i)) {
                        result.push(i);
                    }
                }
                for (i in bonusOptions) {
                    if (bonusOptions.hasOwnProperty(i)) {
                        if (typeof sm2[i] === 'object') {
                            result.push(i + ': {...}');
                        } else if (sm2[i] instanceof Function) {
                            result.push(i + ': function() {...}');
                        } else {
                            result.push(i);
                        }
                    }
                }
                sm2._wD(str('setup', result.join(', ')));
                return false;
            }
            for (i in o) {
                if (o.hasOwnProperty(i)) {
                    if (typeof o[i] !== 'object' || o[i] === null || o[i] instanceof Array || o[i] instanceof RegExp) {
                        if (hasParent && bonusOptions[oParent] !== _undefined) {
                            sm2[oParent][i] = o[i];
                        } else if (setupOptions[i] !== _undefined) {
                            sm2.setupOptions[i] = o[i];
                            sm2[i] = o[i];
                        } else if (bonusOptions[i] === _undefined) {
                            complain(str((sm2[i] === _undefined ? 'setupUndef': 'setupError'), i), 2);
                            result = false;
                        } else {
                            if (sm2[i] instanceof Function) {
                                sm2[i].apply(sm2, (o[i] instanceof Array ? o[i] : [o[i]]));
                            } else {
                                sm2[i] = o[i];
                            }
                        }
                    } else {
                        if (bonusOptions[i] === _undefined) {
                            complain(str((sm2[i] === _undefined ? 'setupUndef': 'setupError'), i), 2);
                            result = false;
                        } else {
                            return assign(o[i], i);
                        }
                    }
                }
            }
            return result;
        };
        function preferFlashCheck(kind) {
            return (sm2.preferFlash && hasFlash && !sm2.ignoreFlash && (sm2.flash[kind] !== _undefined && sm2.flash[kind]));
        }
        event = (function() {
            var old = (window.attachEvent),
            evt = {
                add: (old ? 'attachEvent': 'addEventListener'),
                remove: (old ? 'detachEvent': 'removeEventListener')
            };
            function getArgs(oArgs) {
                var args = slice.call(oArgs),
                len = args.length;
                if (old) {
                    args[1] = 'on' + args[1];
                    if (len > 3) {
                        args.pop();
                    }
                } else if (len === 3) {
                    args.push(false);
                }
                return args;
            }
            function apply(args, sType) {
                var element = args.shift(),
                method = [evt[sType]];
                if (old) {
                    element[method](args[0], args[1]);
                } else {
                    element[method].apply(element, args);
                }
            }
            function add() {
                apply(getArgs(arguments), 'add');
            }
            function remove() {
                apply(getArgs(arguments), 'remove');
            }
            return {
                'add': add,
                'remove': remove
            };
        } ());
        function html5_event(oFn) {
            return function(e) {
                var s = this._s,
                result;
                if (!s || !s._a) {
                    if (s && s.id) {
                        sm2._wD(s.id + ': Ignoring ' + e.type);
                    } else {
                        sm2._wD(h5 + 'Ignoring ' + e.type);
                    }
                    result = null;
                } else {
                    result = oFn.call(this, e);
                }
                return result;
            };
        }
        html5_events = {
            abort: html5_event(function() {
                sm2._wD(this._s.id + ': abort');
            }),
            canplay: html5_event(function() {
                var s = this._s,
                position1K;
                if (s._html5_canplay) {
                    return true;
                }
                s._html5_canplay = true;
                sm2._wD(s.id + ': canplay');
                s._onbufferchange(0);
                position1K = (s._iO.position !== _undefined && !isNaN(s._iO.position) ? s._iO.position / msecScale: null);
                if (s.position && this.currentTime !== position1K) {
                    sm2._wD(s.id + ': canplay: Setting position to ' + position1K);
                    try {
                        this.currentTime = position1K;
                    } catch(ee) {
                        sm2._wD(s.id + ': canplay: Setting position of ' + position1K + ' failed: ' + ee.message, 2);
                    }
                }
                if (s._iO._oncanplay) {
                    s._iO._oncanplay();
                }
            }),
            canplaythrough: html5_event(function() {
                var s = this._s;
                if (!s.loaded) {
                    s._onbufferchange(0);
                    s._whileloading(s.bytesLoaded, s.bytesTotal, s._get_html5_duration());
                    s._onload(true);
                }
            }),
            ended: html5_event(function() {
                var s = this._s;
                sm2._wD(s.id + ': ended');
                s._onfinish();
            }),
            error: html5_event(function() {
                sm2._wD(this._s.id + ': HTML5 error, code ' + this.error.code);
                this._s._onload(false);
            }),
            loadeddata: html5_event(function() {
                var s = this._s;
                sm2._wD(s.id + ': loadeddata');
                if (!s._loaded && !isSafari) {
                    s.duration = s._get_html5_duration();
                }
            }),
            loadedmetadata: html5_event(function() {
                sm2._wD(this._s.id + ': loadedmetadata');
            }),
            loadstart: html5_event(function() {
                sm2._wD(this._s.id + ': loadstart');
                this._s._onbufferchange(1);
            }),
            play: html5_event(function() {
                this._s._onbufferchange(0);
            }),
            playing: html5_event(function() {
                sm2._wD(this._s.id + ': playing');
                this._s._onbufferchange(0);
            }),
            progress: html5_event(function(e) {
                var s = this._s,
                i,
                j,
                progStr,
                buffered = 0,
                isProgress = (e.type === 'progress'),
                ranges = e.target.buffered,
                loaded = (e.loaded || 0),
                total = (e.total || 1);
                s.buffered = [];
                if (ranges && ranges.length) {
                    for (i = 0, j = ranges.length; i < j; i++) {
                        s.buffered.push({
                            'start': ranges.start(i) * msecScale,
                            'end': ranges.end(i) * msecScale
                        });
                    }
                    buffered = (ranges.end(0) - ranges.start(0)) * msecScale;
                    loaded = Math.min(1, buffered / (e.target.duration * msecScale));
                    if (isProgress && ranges.length > 1) {
                        progStr = [];
                        j = ranges.length;
                        for (i = 0; i < j; i++) {
                            progStr.push(e.target.buffered.start(i) * msecScale + '-' + e.target.buffered.end(i) * msecScale);
                        }
                        sm2._wD(this._s.id + ': progress, timeRanges: ' + progStr.join(', '));
                    }
                    if (isProgress && !isNaN(loaded)) {
                        sm2._wD(this._s.id + ': progress, ' + Math.floor(loaded * 100) + '% loaded');
                    }
                }
                if (!isNaN(loaded)) {
                    s._onbufferchange(0);
                    s._whileloading(loaded, total, s._get_html5_duration());
                    if (loaded && total && loaded === total) {
                        html5_events.canplaythrough.call(this, e);
                    }
                }
            }),
            ratechange: html5_event(function() {
                sm2._wD(this._s.id + ': ratechange');
            }),
            suspend: html5_event(function(e) {
                var s = this._s;
                sm2._wD(this._s.id + ': suspend');
                html5_events.progress.call(this, e);
                s._onsuspend();
            }),
            stalled: html5_event(function() {
                sm2._wD(this._s.id + ': stalled');
            }),
            timeupdate: html5_event(function() {
                this._s._onTimer();
            }),
            waiting: html5_event(function() {
                var s = this._s;
                sm2._wD(this._s.id + ': waiting');
                s._onbufferchange(1);
            })
        };
        html5OK = function(iO) {
            var result;
            if (!iO || (!iO.type && !iO.url && !iO.serverURL)) {
                result = false;
            } else if (iO.serverURL || (iO.type && preferFlashCheck(iO.type))) {
                result = false;
            } else {
                result = ((iO.type ? html5CanPlay({
                    type: iO.type
                }) : html5CanPlay({
                    url: iO.url
                }) || sm2.html5Only || iO.url.match(/data\:/i)));
            }
            return result;
        };
        html5Unload = function(oAudio) {
            var url;
            if (oAudio) {
                url = (isSafari ? emptyURL: (sm2.html5.canPlayType('audio/wav') ? emptyWAV: emptyURL));
                oAudio.src = url;
                if (oAudio._called_unload !== undefined) {
                    oAudio._called_load = false;
                }
            }
            if (useGlobalHTML5Audio) {
                lastGlobalHTML5URL = null;
            }
            return url;
        };
        html5CanPlay = function(o) {
            if (!sm2.useHTML5Audio || !sm2.hasHTML5) {
                return false;
            }
            var url = (o.url || null),
            mime = (o.type || null),
            aF = sm2.audioFormats,
            result,
            offset,
            fileExt,
            item;
            if (mime && sm2.html5[mime] !== _undefined) {
                return (sm2.html5[mime] && !preferFlashCheck(mime));
            }
            if (!html5Ext) {
                html5Ext = [];
                for (item in aF) {
                    if (aF.hasOwnProperty(item)) {
                        html5Ext.push(item);
                        if (aF[item].related) {
                            html5Ext = html5Ext.concat(aF[item].related);
                        }
                    }
                }
                html5Ext = new RegExp('\\.(' + html5Ext.join('|') + ')(\\?.*)?$', 'i');
            }
            fileExt = (url ? url.toLowerCase().match(html5Ext) : null);
            if (!fileExt || !fileExt.length) {
                if (!mime) {
                    result = false;
                } else {
                    offset = mime.indexOf(';');
                    fileExt = (offset !== -1 ? mime.substr(0, offset) : mime).substr(6);
                }
            } else {
                fileExt = fileExt[1];
            }
            if (fileExt && sm2.html5[fileExt] !== _undefined) {
                result = (sm2.html5[fileExt] && !preferFlashCheck(fileExt));
            } else {
                mime = 'audio/' + fileExt;
                result = sm2.html5.canPlayType({
                    type: mime
                });
                sm2.html5[fileExt] = result;
                result = (result && sm2.html5[mime] && !preferFlashCheck(mime));
            }
            return result;
        };
        testHTML5 = function() {
            if (!sm2.useHTML5Audio || !sm2.hasHTML5) {
                sm2.html5.usingFlash = true;
                needsFlash = true;
                return false;
            }
            var a = (Audio !== _undefined ? (isOpera && opera.version() < 10 ? new Audio(null) : new Audio()) : null),
            item,
            lookup,
            support = {},
            aF,
            i;
            function cp(m) {
                var canPlay,
                j,
                result = false,
                isOK = false;
                if (!a || typeof a.canPlayType !== 'function') {
                    return result;
                }
                if (m instanceof Array) {
                    for (i = 0, j = m.length; i < j; i++) {
                        if (sm2.html5[m[i]] || a.canPlayType(m[i]).match(sm2.html5Test)) {
                            isOK = true;
                            sm2.html5[m[i]] = true;
                            sm2.flash[m[i]] = !!(m[i].match(flashMIME));
                        }
                    }
                    result = isOK;
                } else {
                    canPlay = (a && typeof a.canPlayType === 'function' ? a.canPlayType(m) : false);
                    result = !!(canPlay && (canPlay.match(sm2.html5Test)));
                }
                return result;
            }
            aF = sm2.audioFormats;
            for (item in aF) {
                if (aF.hasOwnProperty(item)) {
                    lookup = 'audio/' + item;
                    support[item] = cp(aF[item].type);
                    support[lookup] = support[item];
                    if (item.match(flashMIME)) {
                        sm2.flash[item] = true;
                        sm2.flash[lookup] = true;
                    } else {
                        sm2.flash[item] = false;
                        sm2.flash[lookup] = false;
                    }
                    if (aF[item] && aF[item].related) {
                        for (i = aF[item].related.length - 1; i >= 0; i--) {
                            support['audio/' + aF[item].related[i]] = support[item];
                            sm2.html5[aF[item].related[i]] = support[item];
                            sm2.flash[aF[item].related[i]] = support[item];
                        }
                    }
                }
            }
            support.canPlayType = (a ? cp: null);
            sm2.html5 = mixin(sm2.html5, support);
            sm2.html5.usingFlash = featureCheck();
            needsFlash = sm2.html5.usingFlash;
            return true;
        };
        strings = {
            notReady: 'Unavailable - wait until onready() has fired.',
            notOK: 'Audio support is not available.',
            domError: sm + 'exception caught while appending SWF to DOM.',
            spcWmode: 'Removing wmode, preventing known SWF loading issue(s)',
            swf404: smc + 'Verify that %s is a valid path.',
            tryDebug: 'Try ' + sm + '.debugFlash = true for more security details (output goes to SWF.)',
            checkSWF: 'See SWF output for more debug info.',
            localFail: smc + 'Non-HTTP page (' + doc.location.protocol + ' URL?) Review Flash player security settings for this special case:\nhttp://www.macromedia.com/support/documentation/en/flashplayer/help/settings_manager04.html\nMay need to add/allow path, eg. c:/sm2/ or /users/me/sm2/',
            waitFocus: smc + 'Special case: Waiting for SWF to load with window focus...',
            waitForever: smc + 'Waiting indefinitely for Flash (will recover if unblocked)...',
            waitSWF: smc + 'Waiting for 100% SWF load...',
            needFunction: smc + 'Function object expected for %s',
            badID: 'Sound ID "%s" should be a string, starting with a non-numeric character',
            currentObj: smc + '_debug(): Current sound objects',
            waitOnload: smc + 'Waiting for window.onload()',
            docLoaded: smc + 'Document already loaded',
            onload: smc + 'initComplete(): calling soundManager.onload()',
            onloadOK: sm + '.onload() complete',
            didInit: smc + 'init(): Already called?',
            secNote: 'Flash security note: Network/internet URLs will not load due to security restrictions. Access can be configured via Flash Player Global Security Settings Page: http://www.macromedia.com/support/documentation/en/flashplayer/help/settings_manager04.html',
            badRemove: smc + 'Failed to remove Flash node.',
            shutdown: sm + '.disable(): Shutting down',
            queue: smc + 'Queueing %s handler',
            smError: 'SMSound.load(): Exception: JS-Flash communication failed, or JS error.',
            fbTimeout: 'No flash response, applying .' + swfCSS.swfTimedout + ' CSS...',
            fbLoaded: 'Flash loaded',
            fbHandler: smc + 'flashBlockHandler()',
            manURL: 'SMSound.load(): Using manually-assigned URL',
            onURL: sm + '.load(): current URL already assigned.',
            badFV: sm + '.flashVersion must be 8 or 9. "%s" is invalid. Reverting to %s.',
            as2loop: 'Note: Setting stream:false so looping can work (flash 8 limitation)',
            noNSLoop: 'Note: Looping not implemented for MovieStar formats',
            needfl9: 'Note: Switching to flash 9, required for MP4 formats.',
            mfTimeout: 'Setting flashLoadTimeout = 0 (infinite) for off-screen, mobile flash case',
            needFlash: smc + 'Fatal error: Flash is needed to play some required formats, but is not available.',
            gotFocus: smc + 'Got window focus.',
            policy: 'Enabling usePolicyFile for data access',
            setup: sm + '.setup(): allowed parameters: %s',
            setupError: sm + '.setup(): "%s" cannot be assigned with this method.',
            setupUndef: sm + '.setup(): Could not find option "%s"',
            setupLate: sm + '.setup(): url, flashVersion and html5Test property changes will not take effect until reboot().',
            noURL: smc + 'Flash URL required. Call soundManager.setup({url:...}) to get started.',
            sm2Loaded: 'SoundManager 2: Ready.',
            reset: sm + '.reset(): Removing event callbacks',
            mobileUA: 'Mobile UA detected, preferring HTML5 by default.',
            globalHTML5: 'Using singleton HTML5 Audio() pattern for this device.'
        };
        str = function() {
            var args,
            i,
            j,
            o,
            sstr;
            args = slice.call(arguments);
            o = args.shift();
            sstr = (strings && strings[o] ? strings[o] : '');
            if (sstr && args && args.length) {
                for (i = 0, j = args.length; i < j; i++) {
                    sstr = sstr.replace('%s', args[i]);
                }
            }
            return sstr;
        };
        loopFix = function(sOpt) {
            if (fV === 8 && sOpt.loops > 1 && sOpt.stream) {
                _wDS('as2loop');
                sOpt.stream = false;
            }
            return sOpt;
        };
        policyFix = function(sOpt, sPre) {
            if (sOpt && !sOpt.usePolicyFile && (sOpt.onid3 || sOpt.usePeakData || sOpt.useWaveformData || sOpt.useEQData)) {
                sm2._wD((sPre || '') + str('policy'));
                sOpt.usePolicyFile = true;
            }
            return sOpt;
        };
        complain = function(sMsg) {
            if (hasConsole && console.warn !== _undefined) {
                console.warn(sMsg);
            } else {
                sm2._wD(sMsg);
            }
        };
        doNothing = function() {
            return false;
        };
        disableObject = function(o) {
            var oProp;
            for (oProp in o) {
                if (o.hasOwnProperty(oProp) && typeof o[oProp] === 'function') {
                    o[oProp] = doNothing;
                }
            }
            oProp = null;
        };
        failSafely = function(bNoDisable) {
            if (bNoDisable === _undefined) {
                bNoDisable = false;
            }
            if (disabled || bNoDisable) {
                sm2.disable(bNoDisable);
            }
        };
        normalizeMovieURL = function(smURL) {
            var urlParams = null,
            url;
            if (smURL) {
                if (smURL.match(/\.swf(\?.*)?$/i)) {
                    urlParams = smURL.substr(smURL.toLowerCase().lastIndexOf('.swf?') + 4);
                    if (urlParams) {
                        return smURL;
                    }
                } else if (smURL.lastIndexOf('/') !== smURL.length - 1) {
                    smURL += '/';
                }
            }
            url = (smURL && smURL.lastIndexOf('/') !== -1 ? smURL.substr(0, smURL.lastIndexOf('/') + 1) : './') + sm2.movieURL;
            if (sm2.noSWFCache) {
                url += ('?ts=' + new Date().getTime());
            }
            return url;
        };
        setVersionInfo = function() {
            fV = parseInt(sm2.flashVersion, 10);
            if (fV !== 8 && fV !== 9) {
                sm2._wD(str('badFV', fV, defaultFlashVersion));
                sm2.flashVersion = fV = defaultFlashVersion;
            }
            var isDebug = (sm2.debugMode || sm2.debugFlash ? '_debug.swf': '.swf');
            if (sm2.useHTML5Audio && !sm2.html5Only && sm2.audioFormats.mp4.required && fV < 9) {
                sm2._wD(str('needfl9'));
                sm2.flashVersion = fV = 9;
            }
            sm2.version = sm2.versionNumber + (sm2.html5Only ? ' (HTML5-only mode)': (fV === 9 ? ' (AS3/Flash 9)': ' (AS2/Flash 8)'));
            if (fV > 8) {
                sm2.defaultOptions = mixin(sm2.defaultOptions, sm2.flash9Options);
                sm2.features.buffering = true;
                sm2.defaultOptions = mixin(sm2.defaultOptions, sm2.movieStarOptions);
                sm2.filePatterns.flash9 = new RegExp('\\.(mp3|' + netStreamTypes.join('|') + ')(\\?.*)?$', 'i');
                sm2.features.movieStar = true;
            } else {
                sm2.features.movieStar = false;
            }
            sm2.filePattern = sm2.filePatterns[(fV !== 8 ? 'flash9': 'flash8')];
            sm2.movieURL = (fV === 8 ? 'soundmanager2.swf': 'soundmanager2_flash9.swf').replace('.swf', isDebug);
            sm2.features.peakData = sm2.features.waveformData = sm2.features.eqData = (fV > 8);
        };
        setPolling = function(bPolling, bHighPerformance) {
            if (!flash) {
                return false;
            }
            flash._setPolling(bPolling, bHighPerformance);
        };
        initDebug = function() {
            if (sm2.debugURLParam.test(wl)) {
                sm2.debugMode = true;
            }
            if (id(sm2.debugID)) {
                return false;
            }
            var oD,
            oDebug,
            oTarget,
            oToggle,
            tmp;
            if (sm2.debugMode && !id(sm2.debugID) && (!hasConsole || !sm2.useConsole || !sm2.consoleOnly)) {
                oD = doc.createElement('div');
                oD.id = sm2.debugID + '-toggle';
                oToggle = {
                    'position': 'fixed',
                    'bottom': '0px',
                    'right': '0px',
                    'width': '1.2em',
                    'height': '1.2em',
                    'lineHeight': '1.2em',
                    'margin': '2px',
                    'textAlign': 'center',
                    'border': '1px solid #999',
                    'cursor': 'pointer',
                    'background': '#fff',
                    'color': '#333',
                    'zIndex': 10001
                };
                oD.appendChild(doc.createTextNode('-'));
                oD.onclick = toggleDebug;
                oD.title = 'Toggle SM2 debug console';
                if (ua.match(/msie 6/i)) {
                    oD.style.position = 'absolute';
                    oD.style.cursor = 'hand';
                }
                for (tmp in oToggle) {
                    if (oToggle.hasOwnProperty(tmp)) {
                        oD.style[tmp] = oToggle[tmp];
                    }
                }
                oDebug = doc.createElement('div');
                oDebug.id = sm2.debugID;
                oDebug.style.display = (sm2.debugMode ? 'block': 'none');
                if (sm2.debugMode && !id(oD.id)) {
                    try {
                        oTarget = getDocument();
                        oTarget.appendChild(oD);
                    } catch(e2) {
                        throw new Error(str('domError') + ' \n' + e2.toString());
                    }
                    oTarget.appendChild(oDebug);
                }
            }
            oTarget = null;
        };
        idCheck = this.getSoundById;
        _wDS = function(o, errorLevel) {
            return (!o ? '': sm2._wD(str(o), errorLevel));
        };
        toggleDebug = function() {
            var o = id(sm2.debugID),
            oT = id(sm2.debugID + '-toggle');
            if (!o) {
                return false;
            }
            if (debugOpen) {
                oT.innerHTML = '+';
                o.style.display = 'none';
            } else {
                oT.innerHTML = '-';
                o.style.display = 'block';
            }
            debugOpen = !debugOpen;
        };
        debugTS = function(sEventType, bSuccess, sMessage) {
            if (window.sm2Debugger !== _undefined) {
                try {
                    sm2Debugger.handleEvent(sEventType, bSuccess, sMessage);
                } catch(e) {
                    return false;
                }
            }
            return true;
        };
        getSWFCSS = function() {
            var css = [];
            if (sm2.debugMode) {
                css.push(swfCSS.sm2Debug);
            }
            if (sm2.debugFlash) {
                css.push(swfCSS.flashDebug);
            }
            if (sm2.useHighPerformance) {
                css.push(swfCSS.highPerf);
            }
            return css.join(' ');
        };
        flashBlockHandler = function() {
            var name = str('fbHandler'),
            p = sm2.getMoviePercent(),
            css = swfCSS,
            error = {
                type: 'FLASHBLOCK'
            };
            if (sm2.html5Only) {
                return false;
            }
            if (!sm2.ok()) {
                if (needsFlash) {
                    sm2.oMC.className = getSWFCSS() + ' ' + css.swfDefault + ' ' + (p === null ? css.swfTimedout: css.swfError);
                    sm2._wD(name + ': ' + str('fbTimeout') + (p ? ' (' + str('fbLoaded') + ')': ''));
                }
                sm2.didFlashBlock = true;
                processOnEvents({
                    type: 'ontimeout',
                    ignoreInit: true,
                    error: error
                });
                catchError(error);
            } else {
                if (sm2.didFlashBlock) {
                    sm2._wD(name + ': Unblocked');
                }
                if (sm2.oMC) {
                    sm2.oMC.className = [getSWFCSS(), css.swfDefault, css.swfLoaded + (sm2.didFlashBlock ? ' ' + css.swfUnblocked: '')].join(' ');
                }
            }
        };
        addOnEvent = function(sType, oMethod, oScope) {
            if (on_queue[sType] === _undefined) {
                on_queue[sType] = [];
            }
            on_queue[sType].push({
                'method': oMethod,
                'scope': (oScope || null),
                'fired': false
            });
        };
        processOnEvents = function(oOptions) {
            if (!oOptions) {
                oOptions = {
                    type: (sm2.ok() ? 'onready': 'ontimeout')
                };
            }
            if (!didInit && oOptions && !oOptions.ignoreInit) {
                return false;
            }
            if (oOptions.type === 'ontimeout' && (sm2.ok() || (disabled && !oOptions.ignoreInit))) {
                return false;
            }
            var status = {
                success: (oOptions && oOptions.ignoreInit ? sm2.ok() : !disabled)
            },
            srcQueue = (oOptions && oOptions.type ? on_queue[oOptions.type] || [] : []),
            queue = [],
            i,
            j,
            args = [status],
            canRetry = (needsFlash && !sm2.ok());
            if (oOptions.error) {
                args[0].error = oOptions.error;
            }
            for (i = 0, j = srcQueue.length; i < j; i++) {
                if (srcQueue[i].fired !== true) {
                    queue.push(srcQueue[i]);
                }
            }
            if (queue.length) {
                for (i = 0, j = queue.length; i < j; i++) {
                    if (queue[i].scope) {
                        queue[i].method.apply(queue[i].scope, args);
                    } else {
                        queue[i].method.apply(this, args);
                    }
                    if (!canRetry) {
                        queue[i].fired = true;
                    }
                }
            }
            return true;
        };
        initUserOnload = function() {
            window.setTimeout(function() {
                if (sm2.useFlashBlock) {
                    flashBlockHandler();
                }
                processOnEvents();
                if (typeof sm2.onload === 'function') {
                    _wDS('onload', 1);
                    sm2.onload.apply(window);
                    _wDS('onloadOK', 1);
                }
                if (sm2.waitForWindowLoad) {
                    event.add(window, 'load', initUserOnload);
                }
            },
            1);
        };
        detectFlash = function() {
            if (hasFlash !== _undefined) {
                return hasFlash;
            }
            var hasPlugin = false,
            n = navigator,
            nP = n.plugins,
            obj,
            type,
            types,
            AX = window.ActiveXObject;
            if (nP && nP.length) {
                type = 'application/x-shockwave-flash';
                types = n.mimeTypes;
                if (types && types[type] && types[type].enabledPlugin && types[type].enabledPlugin.description) {
                    hasPlugin = true;
                }
            } else if (AX !== _undefined && !ua.match(/MSAppHost/i)) {
                try {
                    obj = new AX('ShockwaveFlash.ShockwaveFlash');
                } catch(e) {
                    obj = null;
                }
                hasPlugin = ( !! obj);
                obj = null;
            }
            hasFlash = hasPlugin;
            return hasPlugin;
        };
        featureCheck = function() {
            var flashNeeded,
            item,
            formats = sm2.audioFormats,
            isSpecial = (is_iDevice && !!(ua.match(/os (1|2|3_0|3_1)/i)));
            if (isSpecial) {
                sm2.hasHTML5 = false;
                sm2.html5Only = true;
                if (sm2.oMC) {
                    sm2.oMC.style.display = 'none';
                }
            } else {
                if (sm2.useHTML5Audio) {
                    if (!sm2.html5 || !sm2.html5.canPlayType) {
                        sm2._wD('SoundManager: No HTML5 Audio() support detected.');
                        sm2.hasHTML5 = false;
                    }
                    if (isBadSafari) {
                        sm2._wD(smc + 'Note: Buggy HTML5 Audio in Safari on this OS X release, see https://bugs.webkit.org/show_bug.cgi?id=32159 - ' + (!hasFlash ? ' would use flash fallback for MP3/MP4, but none detected.': 'will use flash fallback for MP3/MP4, if available'), 1);
                    }
                }
            }
            if (sm2.useHTML5Audio && sm2.hasHTML5) {
                canIgnoreFlash = true;
                for (item in formats) {
                    if (formats.hasOwnProperty(item)) {
                        if (formats[item].required) {
                            if (!sm2.html5.canPlayType(formats[item].type)) {
                                canIgnoreFlash = false;
                                flashNeeded = true;
                            } else if (sm2.preferFlash && (sm2.flash[item] || sm2.flash[formats[item].type])) {
                                flashNeeded = true;
                            }
                        }
                    }
                }
            }
            if (sm2.ignoreFlash) {
                flashNeeded = false;
                canIgnoreFlash = true;
            }
            sm2.html5Only = (sm2.hasHTML5 && sm2.useHTML5Audio && !flashNeeded);
            return (!sm2.html5Only);
        };
        parseURL = function(url) {
            var i,
            j,
            urlResult = 0,
            result;
            if (url instanceof Array) {
                for (i = 0, j = url.length; i < j; i++) {
                    if (url[i] instanceof Object) {
                        if (sm2.canPlayMIME(url[i].type)) {
                            urlResult = i;
                            break;
                        }
                    } else if (sm2.canPlayURL(url[i])) {
                        urlResult = i;
                        break;
                    }
                }
                if (url[urlResult].url) {
                    url[urlResult] = url[urlResult].url;
                }
                result = url[urlResult];
            } else {
                result = url;
            }
            return result;
        };
        startTimer = function(oSound) {
            if (!oSound._hasTimer) {
                oSound._hasTimer = true;
                if (!mobileHTML5 && sm2.html5PollingInterval) {
                    if (h5IntervalTimer === null && h5TimerCount === 0) {
                        h5IntervalTimer = setInterval(timerExecute, sm2.html5PollingInterval);
                    }
                    h5TimerCount++;
                }
            }
        };
        stopTimer = function(oSound) {
            if (oSound._hasTimer) {
                oSound._hasTimer = false;
                if (!mobileHTML5 && sm2.html5PollingInterval) {
                    h5TimerCount--;
                }
            }
        };
        timerExecute = function() {
            var i;
            if (h5IntervalTimer !== null && !h5TimerCount) {
                clearInterval(h5IntervalTimer);
                h5IntervalTimer = null;
                return false;
            }
            for (i = sm2.soundIDs.length - 1; i >= 0; i--) {
                if (sm2.sounds[sm2.soundIDs[i]].isHTML5 && sm2.sounds[sm2.soundIDs[i]]._hasTimer) {
                    sm2.sounds[sm2.soundIDs[i]]._onTimer();
                }
            }
        };
        catchError = function(options) {
            options = (options !== _undefined ? options: {});
            if (typeof sm2.onerror === 'function') {
                sm2.onerror.apply(window, [{
                    type: (options.type !== _undefined ? options.type: null)
                }]);
            }
            if (options.fatal !== _undefined && options.fatal) {
                sm2.disable();
            }
        };
        badSafariFix = function() {
            if (!isBadSafari || !detectFlash()) {
                return false;
            }
            var aF = sm2.audioFormats,
            i,
            item;
            for (item in aF) {
                if (aF.hasOwnProperty(item)) {
                    if (item === 'mp3' || item === 'mp4') {
                        sm2._wD(sm + ': Using flash fallback for ' + item + ' format');
                        sm2.html5[item] = false;
                        if (aF[item] && aF[item].related) {
                            for (i = aF[item].related.length - 1; i >= 0; i--) {
                                sm2.html5[aF[item].related[i]] = false;
                            }
                        }
                    }
                }
            }
        };
        this._setSandboxType = function(sandboxType) {
            var sb = sm2.sandbox;
            sb.type = sandboxType;
            sb.description = sb.types[(sb.types[sandboxType] !== _undefined ? sandboxType: 'unknown')];
            if (sb.type === 'localWithFile') {
                sb.noRemote = true;
                sb.noLocal = false;
                _wDS('secNote', 2);
            } else if (sb.type === 'localWithNetwork') {
                sb.noRemote = false;
                sb.noLocal = true;
            } else if (sb.type === 'localTrusted') {
                sb.noRemote = false;
                sb.noLocal = false;
            }
        };
        this._externalInterfaceOK = function(swfVersion) {
            if (sm2.swfLoaded) {
                return false;
            }
            var e;
            debugTS('swf', true);
            debugTS('flashtojs', true);
            sm2.swfLoaded = true;
            tryInitOnFocus = false;
            if (isBadSafari) {
                badSafariFix();
            }
            if (!swfVersion || swfVersion.replace(/\+dev/i, '') !== sm2.versionNumber.replace(/\+dev/i, '')) {
                e = sm + ': Fatal: JavaScript file build "' + sm2.versionNumber + '" does not match Flash SWF build "' + swfVersion + '" at ' + sm2.url + '. Ensure both are up-to-date.';
                setTimeout(function versionMismatch() {
                    throw new Error(e);
                },
                0);
                return false;
            }
            setTimeout(init, isIE ? 100: 1);
        };
        createMovie = function(smID, smURL) {
            if (didAppend && appendSuccess) {
                return false;
            }
            function initMsg() {
                var options = [],
                title,
                msg = [],
                delimiter = ' + ';
                title = 'SoundManager ' + sm2.version + (!sm2.html5Only && sm2.useHTML5Audio ? (sm2.hasHTML5 ? ' + HTML5 audio': ', no HTML5 audio support') : '');
                if (!sm2.html5Only) {
                    if (sm2.preferFlash) {
                        options.push('preferFlash');
                    }
                    if (sm2.useHighPerformance) {
                        options.push('useHighPerformance');
                    }
                    if (sm2.flashPollingInterval) {
                        options.push('flashPollingInterval (' + sm2.flashPollingInterval + 'ms)');
                    }
                    if (sm2.html5PollingInterval) {
                        options.push('html5PollingInterval (' + sm2.html5PollingInterval + 'ms)');
                    }
                    if (sm2.wmode) {
                        options.push('wmode (' + sm2.wmode + ')');
                    }
                    if (sm2.debugFlash) {
                        options.push('debugFlash');
                    }
                    if (sm2.useFlashBlock) {
                        options.push('flashBlock');
                    }
                } else {
                    if (sm2.html5PollingInterval) {
                        options.push('html5PollingInterval (' + sm2.html5PollingInterval + 'ms)');
                    }
                }
                if (options.length) {
                    msg = msg.concat([options.join(delimiter)]);
                }
                sm2._wD(title + (msg.length ? delimiter + msg.join(', ') : ''), 1);
                showSupport();
            }
            if (sm2.html5Only) {
                setVersionInfo();
                initMsg();
                sm2.oMC = id(sm2.movieID);
                init();
                didAppend = true;
                appendSuccess = true;
                return false;
            }
            var remoteURL = (smURL || sm2.url),
            localURL = (sm2.altURL || remoteURL),
            swfTitle = 'JS/Flash audio component (SoundManager 2)',
            oTarget = getDocument(),
            extraClass = getSWFCSS(),
            isRTL = null,
            html = doc.getElementsByTagName('html')[0],
            oEmbed,
            oMovie,
            tmp,
            movieHTML,
            oEl,
            s,
            x,
            sClass;
            isRTL = (html && html.dir && html.dir.match(/rtl/i));
            smID = (smID === _undefined ? sm2.id: smID);
            function param(name, value) {
                return '<param name="' + name + '" value="' + value + '" />';
            }
            setVersionInfo();
            sm2.url = normalizeMovieURL(overHTTP ? remoteURL: localURL);
            smURL = sm2.url;
            sm2.wmode = (!sm2.wmode && sm2.useHighPerformance ? 'transparent': sm2.wmode);
            if (sm2.wmode !== null && (ua.match(/msie 8/i) || (!isIE && !sm2.useHighPerformance)) && navigator.platform.match(/win32|win64/i)) {
                messages.push(strings.spcWmode);
                sm2.wmode = null;
            }
            oEmbed = {
                'name': smID,
                'id': smID,
                'src': smURL,
                'quality': 'high',
                'allowScriptAccess': sm2.allowScriptAccess,
                'bgcolor': sm2.bgColor,
                'pluginspage': http + 'www.macromedia.com/go/getflashplayer',
                'title': swfTitle,
                'type': 'application/x-shockwave-flash',
                'wmode': sm2.wmode,
                'hasPriority': 'true'
            };
            if (sm2.debugFlash) {
                oEmbed.FlashVars = 'debug=1';
            }
            if (!sm2.wmode) {
                delete oEmbed.wmode;
            }
            if (isIE) {
                oMovie = doc.createElement('div');
                movieHTML = ['<object id="' + smID + '" data="' + smURL + '" type="' + oEmbed.type + '" title="' + oEmbed.title + '" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="' + http + 'download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0">', param('movie', smURL), param('AllowScriptAccess', sm2.allowScriptAccess), param('quality', oEmbed.quality), (sm2.wmode ? param('wmode', sm2.wmode) : ''), param('bgcolor', sm2.bgColor), param('hasPriority', 'true'), (sm2.debugFlash ? param('FlashVars', oEmbed.FlashVars) : ''), '</object>'].join('');
            } else {
                oMovie = doc.createElement('embed');
                for (tmp in oEmbed) {
                    if (oEmbed.hasOwnProperty(tmp)) {
                        oMovie.setAttribute(tmp, oEmbed[tmp]);
                    }
                }
            }
            initDebug();
            extraClass = getSWFCSS();
            oTarget = getDocument();
            if (oTarget) {
                sm2.oMC = (id(sm2.movieID) || doc.createElement('div'));
                if (!sm2.oMC.id) {
                    sm2.oMC.id = sm2.movieID;
                    sm2.oMC.className = swfCSS.swfDefault + ' ' + extraClass;
                    s = null;
                    oEl = null;
                    if (!sm2.useFlashBlock) {
                        if (sm2.useHighPerformance) {
                            s = {
                                'position': 'fixed',
                                'width': '8px',
                                'height': '8px',
                                'bottom': '0px',
                                'left': '0px',
                                'overflow': 'hidden'
                            };
                        } else {
                            s = {
                                'position': 'absolute',
                                'width': '6px',
                                'height': '6px',
                                'top': '-9999px',
                                'left': '-9999px'
                            };
                            if (isRTL) {
                                s.left = Math.abs(parseInt(s.left, 10)) + 'px';
                            }
                        }
                    }
                    if (isWebkit) {
                        sm2.oMC.style.zIndex = 10000;
                    }
                    if (!sm2.debugFlash) {
                        for (x in s) {
                            if (s.hasOwnProperty(x)) {
                                sm2.oMC.style[x] = s[x];
                            }
                        }
                    }
                    try {
                        if (!isIE) {
                            sm2.oMC.appendChild(oMovie);
                        }
                        oTarget.appendChild(sm2.oMC);
                        if (isIE) {
                            oEl = sm2.oMC.appendChild(doc.createElement('div'));
                            oEl.className = swfCSS.swfBox;
                            oEl.innerHTML = movieHTML;
                        }
                        appendSuccess = true;
                    } catch(e) {
                        throw new Error(str('domError') + ' \n' + e.toString());
                    }
                } else {
                    sClass = sm2.oMC.className;
                    sm2.oMC.className = (sClass ? sClass + ' ': swfCSS.swfDefault) + (extraClass ? ' ' + extraClass: '');
                    sm2.oMC.appendChild(oMovie);
                    if (isIE) {
                        oEl = sm2.oMC.appendChild(doc.createElement('div'));
                        oEl.className = swfCSS.swfBox;
                        oEl.innerHTML = movieHTML;
                    }
                    appendSuccess = true;
                }
            }
            didAppend = true;
            initMsg();
            return true;
        };
        initMovie = function() {
            if (sm2.html5Only) {
                createMovie();
                return false;
            }
            if (flash) {
                return false;
            }
            if (!sm2.url) {
                _wDS('noURL');
                return false;
            }
            flash = sm2.getMovie(sm2.id);
            if (!flash) {
                if (!oRemoved) {
                    createMovie(sm2.id, sm2.url);
                } else {
                    if (!isIE) {
                        sm2.oMC.appendChild(oRemoved);
                    } else {
                        sm2.oMC.innerHTML = oRemovedHTML;
                    }
                    oRemoved = null;
                    didAppend = true;
                }
                flash = sm2.getMovie(sm2.id);
            }
            if (typeof sm2.oninitmovie === 'function') {
                setTimeout(sm2.oninitmovie, 1);
            }
            flushMessages();
            return true;
        };
        delayWaitForEI = function() {
            setTimeout(waitForEI, 1000);
        };
        rebootIntoHTML5 = function() {
            window.setTimeout(function() {
                complain(smc + 'useFlashBlock is false, 100% HTML5 mode is possible. Rebooting with preferFlash: false...');
                sm2.setup({
                    preferFlash: false
                }).reboot();
                sm2.didFlashBlock = true;
                sm2.beginDelayedInit();
            },
            1);
        };
        waitForEI = function() {
            var p,
            loadIncomplete = false;
            if (!sm2.url) {
                return false;
            }
            if (waitingForEI) {
                return false;
            }
            waitingForEI = true;
            event.remove(window, 'load', delayWaitForEI);
            if (hasFlash && tryInitOnFocus && !isFocused) {
                _wDS('waitFocus');
                return false;
            }
            if (!didInit) {
                p = sm2.getMoviePercent();
                if (p > 0 && p < 100) {
                    loadIncomplete = true;
                }
            }
            setTimeout(function() {
                p = sm2.getMoviePercent();
                if (loadIncomplete) {
                    waitingForEI = false;
                    sm2._wD(str('waitSWF'));
                    window.setTimeout(delayWaitForEI, 1);
                    return false;
                }
                if (!didInit) {
                    sm2._wD(sm + ': No Flash response within expected time. Likely causes: ' + (p === 0 ? 'SWF load failed, ': '') + 'Flash blocked or JS-Flash security error.' + (sm2.debugFlash ? ' ' + str('checkSWF') : ''), 2);
                    if (!overHTTP && p) {
                        _wDS('localFail', 2);
                        if (!sm2.debugFlash) {
                            _wDS('tryDebug', 2);
                        }
                    }
                    if (p === 0) {
                        sm2._wD(str('swf404', sm2.url), 1);
                    }
                    debugTS('flashtojs', false, ': Timed out' + overHTTP ? ' (Check flash security or flash blockers)': ' (No plugin/missing SWF?)');
                }
                if (!didInit && okToDisable) {
                    if (p === null) {
                        if (sm2.useFlashBlock || sm2.flashLoadTimeout === 0) {
                            if (sm2.useFlashBlock) {
                                flashBlockHandler();
                            }
                            _wDS('waitForever');
                        } else {
                            if (!sm2.useFlashBlock && canIgnoreFlash) {
                                rebootIntoHTML5();
                            } else {
                                _wDS('waitForever');
                                processOnEvents({
                                    type: 'ontimeout',
                                    ignoreInit: true,
                                    error: {
                                        type: 'INIT_FLASHBLOCK'
                                    }
                                });
                            }
                        }
                    } else {
                        if (sm2.flashLoadTimeout === 0) {
                            _wDS('waitForever');
                        } else {
                            if (!sm2.useFlashBlock && canIgnoreFlash) {
                                rebootIntoHTML5();
                            } else {
                                failSafely(true);
                            }
                        }
                    }
                }
            },
            sm2.flashLoadTimeout);
        };
        handleFocus = function() {
            function cleanup() {
                event.remove(window, 'focus', handleFocus);
            }
            if (isFocused || !tryInitOnFocus) {
                cleanup();
                return true;
            }
            okToDisable = true;
            isFocused = true;
            _wDS('gotFocus');
            waitingForEI = false;
            delayWaitForEI();
            cleanup();
            return true;
        };
        flushMessages = function() {
            if (messages.length) {
                sm2._wD('SoundManager 2: ' + messages.join(' '), 1);
                messages = [];
            }
        };
        showSupport = function() {
            flushMessages();
            var item,
            tests = [];
            if (sm2.useHTML5Audio && sm2.hasHTML5) {
                for (item in sm2.audioFormats) {
                    if (sm2.audioFormats.hasOwnProperty(item)) {
                        tests.push(item + ' = ' + sm2.html5[item] + (!sm2.html5[item] && needsFlash && sm2.flash[item] ? ' (using flash)': (sm2.preferFlash && sm2.flash[item] && needsFlash ? ' (preferring flash)': (!sm2.html5[item] ? ' (' + (sm2.audioFormats[item].required ? 'required, ': '') + 'and no flash support)': ''))));
                    }
                }
                sm2._wD('SoundManager 2 HTML5 support: ' + tests.join(', '), 1);
            }
        };
        initComplete = function(bNoDisable) {
            if (didInit) {
                return false;
            }
            if (sm2.html5Only) {
                _wDS('sm2Loaded');
                didInit = true;
                initUserOnload();
                debugTS('onload', true);
                return true;
            }
            var wasTimeout = (sm2.useFlashBlock && sm2.flashLoadTimeout && !sm2.getMoviePercent()),
            result = true,
            error;
            if (!wasTimeout) {
                didInit = true;
            }
            error = {
                type: (!hasFlash && needsFlash ? 'NO_FLASH': 'INIT_TIMEOUT')
            };
            sm2._wD('SoundManager 2 ' + (disabled ? 'failed to load': 'loaded') + ' (' + (disabled ? 'Flash security/load error': 'OK') + ')', disabled ? 2: 1);
            if (disabled || bNoDisable) {
                if (sm2.useFlashBlock && sm2.oMC) {
                    sm2.oMC.className = getSWFCSS() + ' ' + (sm2.getMoviePercent() === null ? swfCSS.swfTimedout: swfCSS.swfError);
                }
                processOnEvents({
                    type: 'ontimeout',
                    error: error,
                    ignoreInit: true
                });
                debugTS('onload', false);
                catchError(error);
                result = false;
            } else {
                debugTS('onload', true);
            }
            if (!disabled) {
                if (sm2.waitForWindowLoad && !windowLoaded) {
                    _wDS('waitOnload');
                    event.add(window, 'load', initUserOnload);
                } else {
                    if (sm2.waitForWindowLoad && windowLoaded) {
                        _wDS('docLoaded');
                    }
                    initUserOnload();
                }
            }
            return result;
        };
        setProperties = function() {
            var i,
            o = sm2.setupOptions;
            for (i in o) {
                if (o.hasOwnProperty(i)) {
                    if (sm2[i] === _undefined) {
                        sm2[i] = o[i];
                    } else if (sm2[i] !== o[i]) {
                        sm2.setupOptions[i] = sm2[i];
                    }
                }
            }
        };
        init = function() {
            if (didInit) {
                _wDS('didInit');
                return false;
            }
            function cleanup() {
                event.remove(window, 'load', sm2.beginDelayedInit);
            }
            if (sm2.html5Only) {
                if (!didInit) {
                    cleanup();
                    sm2.enabled = true;
                    initComplete();
                }
                return true;
            }
            initMovie();
            try {
                flash._externalInterfaceTest(false);
                setPolling(true, (sm2.flashPollingInterval || (sm2.useHighPerformance ? 10: 50)));
                if (!sm2.debugMode) {
                    flash._disableDebug();
                }
                sm2.enabled = true;
                debugTS('jstoflash', true);
                if (!sm2.html5Only) {
                    event.add(window, 'unload', doNothing);
                }
            } catch(e) {
                sm2._wD('js/flash exception: ' + e.toString());
                debugTS('jstoflash', false);
                catchError({
                    type: 'JS_TO_FLASH_EXCEPTION',
                    fatal: true
                });
                failSafely(true);
                initComplete();
                return false;
            }
            initComplete();
            cleanup();
            return true;
        };
        domContentLoaded = function() {
            if (didDCLoaded) {
                return false;
            }
            didDCLoaded = true;
            setProperties();
            initDebug(); (function() {
                var a = 'sm2-usehtml5audio=',
                a2 = 'sm2-preferflash=',
                b = null,
                b2 = null,
                l = wl.toLowerCase();
                if (l.indexOf(a) !== -1) {
                    b = (l.charAt(l.indexOf(a) + a.length) === '1');
                    if (hasConsole) {
                        console.log((b ? 'Enabling ': 'Disabling ') + 'useHTML5Audio via URL parameter');
                    }
                    sm2.setup({
                        'useHTML5Audio': b
                    });
                }
                if (l.indexOf(a2) !== -1) {
                    b2 = (l.charAt(l.indexOf(a2) + a2.length) === '1');
                    if (hasConsole) {
                        console.log((b2 ? 'Enabling ': 'Disabling ') + 'preferFlash via URL parameter');
                    }
                    sm2.setup({
                        'preferFlash': b2
                    });
                }
            } ());
            if (!hasFlash && sm2.hasHTML5) {
                sm2._wD('SoundManager 2: No Flash detected' + (!sm2.useHTML5Audio ? ', enabling HTML5.': '. Trying HTML5-only mode.'), 1);
                sm2.setup({
                    'useHTML5Audio': true,
                    'preferFlash': false
                });
            }
            testHTML5();
            if (!hasFlash && needsFlash) {
                messages.push(strings.needFlash);
                sm2.setup({
                    'flashLoadTimeout': 1
                });
            }
            if (doc.removeEventListener) {
                doc.removeEventListener('DOMContentLoaded', domContentLoaded, false);
            }
            initMovie();
            return true;
        };
        domContentLoadedIE = function() {
            if (doc.readyState === 'complete') {
                domContentLoaded();
                doc.detachEvent('onreadystatechange', domContentLoadedIE);
            }
            return true;
        };
        winOnLoad = function() {
            windowLoaded = true;
            event.remove(window, 'load', winOnLoad);
        };
        preInit = function() {
            if (mobileHTML5) {
                if (!sm2.setupOptions.useHTML5Audio || sm2.setupOptions.preferFlash) {
                    messages.push(strings.mobileUA);
                }
                sm2.setupOptions.useHTML5Audio = true;
                sm2.setupOptions.preferFlash = false;
                if (is_iDevice || (isAndroid && !ua.match(/android\s2\.3/i))) {
                    messages.push(strings.globalHTML5);
                    if (is_iDevice) {
                        sm2.ignoreFlash = true;
                    }
                    useGlobalHTML5Audio = true;
                }
            }
        };
        preInit();
        detectFlash();
        event.add(window, 'focus', handleFocus);
        event.add(window, 'load', delayWaitForEI);
        event.add(window, 'load', winOnLoad);
        if (doc.addEventListener) {
            doc.addEventListener('DOMContentLoaded', domContentLoaded, false);
        } else if (doc.attachEvent) {
            doc.attachEvent('onreadystatechange', domContentLoadedIE);
        } else {
            debugTS('onload', false);
            catchError({
                type: 'NO_DOM2_EVENTS',
                fatal: true
            });
        }
    }
    if (window.SM2_DEFER === undefined || !SM2_DEFER) {
        soundManager = new SoundManager();
    }
    window.SoundManager = SoundManager;
    window.soundManager = soundManager;
} (window));

// 初始化配置开始
soundManager.setup({
    url: staticPath + 'lib/soundmanager2/2.97/swf'
});
// 点击播放音频
$('.sm2_button').each(function(){
    $(this).click(function(){
        var _this = $(this);
        soundManager.createSound({
            id: _this.attr('soundID'),
            url: _this.attr('url')
        }).play({
            whileplaying:function(){
                var _thisObj = this;
                _this.addClass('sm2_playing').removeClass('sm2_paused');
                _this.siblings('.sm2_stop').addClass('sm2_covers').removeClass('sm2_undisI');
            },
            onfinish:function(){
                _this.removeClass('sm2_playing');
                _this.siblings('.sm2_stop').removeClass('sm2_covers').addClass('sm2_undisI');
            }
        });
    })
});
// 点击暂停播放
$('.sm2_stop').each(function(){
    $(this).click(function(){
        $(this).siblings('.sm2_button').addClass('sm2_paused');
        $(this).removeClass('sm2_covers').addClass('sm2_undisI');
        soundManager.pause($(this).siblings('.sm2_button').attr('soundID'));
        
    });
});
// 配置结束