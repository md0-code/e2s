if (Hls.isSupported()) {
	var video = document.getElementById('video');
	var hls = new Hls();
	hls.on(Hls.Events.ERROR,function(event, data) {
		if (data.details == 'manifestLoadError') {
			sleep(1000).then(() => {
		//		hls.detachMedia();
				hls.loadSource('stream/e2s.m3u8');
			//	hls.attachMedia(video);
			});
		}
	});
}

$(".serviceLink").click(function(e) {
	e.preventDefault();
	sessionStorage.sRef = $(this).attr('sRef');
	if (localStorage.autoplay == 1) startTranscode();
	$(".serviceLink").removeClass('active');
	$(this).addClass('active');
});

$('#resolutions').on('change', function() {
	saveSettings();
	if (localStorage.autoplay == 1) startTranscode();
});

$('#videoBitrates').on('change', function() {
	saveSettings();
	if (localStorage.autoplay == 1) startTranscode();
});

$('#audioBitrates').on('change', function() {
	saveSettings();
	if (localStorage.autoplay == 1) startTranscode();
});

$('#restart').on('click', function() {
	startTranscode();
});

$('#stop').on('click', function() {
	stopTranscode();
});

$('#power').on('click', function() {
	if (sessionStorage.inStandby == 0) {
		$('#power').removeClass('btn-success');
		$('#power').addClass('btn-danger');
		$.get('?power=0', function(data, status) {
			if ($.parseJSON(data).result == true) sessionStorage.inStandby = 1;
		});
	}
	else if (sessionStorage.getItem('inStandby') == 1) {
		$('#power').removeClass('btn-danger');
		$('#power').addClass('btn-success');
		$.get('?power=1', function(data, status) {
			if ($.parseJSON(data).result == true) sessionStorage.inStandby = 0;
		});
	}

});

$('#logout').on('click', function() {
	$.get('?logout', function(data, status) {
		window.location = '?';
	});
});

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

function loadSettings() {
	if (localStorage.e2sResolution == null || localStorage.e2sResolution == 'undefined') localStorage.e2sResolution = $('#resolutions').val();
	else $('#resolutions').val(localStorage.e2sResolution);
	if (localStorage.e2sVideoBitrate == null || localStorage.e2sVideoBitrate == 'undefined') localStorage.e2sVideoBitrate = $('#videoBitrates').val();
	else $('#videoBitrates').val(localStorage.e2sVideoBitrate);
	if (localStorage.e2sAudioBitrate == null || localStorage.e2sAudioBitrate == 'undefined') localStorage.e2sAudioBitrate = $('#audioBitrates').val();
	else $('#audioBitrates').val(localStorage.e2sAudioBitrate);
}

function saveSettings() {
	localStorage.e2sResolution = $('#resolutions').val();
	localStorage.e2sVideoBitrate = $('#videoBitrates').val();
	localStorage.e2sAudioBitrate = $('#audioBitrates').val();
}

function stopTranscode() {
//	video.pause();
	hls.detachMedia();
	$.get('?stop');
	$('#videoText').hide();
}

function startTranscode() {
//	video.pause();
	hls.detachMedia();
	$.get('?stop');
	$('#videoText').show();
    $.get(sessionStorage.sRef+'&res='+localStorage.e2sResolution+'&vb='+localStorage.e2sVideoBitrate+'&ab='+localStorage.e2sAudioBitrate, function(data, status) {
		$('#activeService').html($.parseJSON(data).service);
		$('#activeTuner').html($.parseJSON(data).tuner);
		hls.loadSource('stream/e2s.m3u8');
		hls.attachMedia(video);
		hls.on(Hls.Events.MANIFEST_PARSED,function() {
			video.play();
			$('#videoText').hide();
		});
	});
}

window.addEventListener('beforeinstallprompt', (e) => {
//e.preventDefault();
  e.prompt();
//deferredPrompt = e;
}); 

$(document).ready(function() {
	if ('serviceWorker' in navigator) {
		navigator.serviceWorker
			 .register('sw.js')
			 .then(function() { console.log('Service Worker Registered'); });
	} 
	loadSettings();
	if (sessionStorage.inStandby == 0) {
		$('#power').addClass('btn-success');
	}
	else if (sessionStorage.inStandby == 1) {
		$('#power').addClass('btn-danger');
	}
	if (localStorage.autoplay == 1) startTranscode();
});