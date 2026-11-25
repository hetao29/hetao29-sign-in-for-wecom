(function () {
	'use strict';

	function showLoginPanel() {
		var panel = document.getElementById('login-b');
		if (!window.ww || typeof window.ww.createWWLoginPanel !== 'function' || !window.hetao29WecomLoginConfig || !panel) {
			return false;
		}
		if (panel.getAttribute('data-wecom-ready') === 'true') {
			return true;
		}
		try {
			window.ww.createWWLoginPanel({
				el: '#login-b',
				params: {
					login_type: 'CorpApp',
					appid: window.hetao29WecomLoginConfig.appid,
					agentid: window.hetao29WecomLoginConfig.agentid,
					redirect_uri: window.hetao29WecomLoginConfig.redirect_uri,
					state: window.hetao29WecomLoginConfig.state,
					redirect_type: 'top',
					panel_size: 'small'
				}
			});
		} catch (error) {
			return false;
		}
		panel.setAttribute('data-wecom-ready', 'true');
		return true;
	}

	function init() {
		var link = document.getElementById('login-a');
		var form = document.getElementById('loginform');
		if (link && form) {
			link.addEventListener('click', function (event) {
				event.preventDefault();
				var panel = document.getElementById('login-b');
				if (!panel) {
					panel = document.createElement('div');
					panel.id = 'login-b';
					panel.style.cssText = 'display:flex;justify-content:center;width:100%';
					form.parentNode.insertBefore(panel, form);
				}
				// Keep password login available if the SDK is blocked or fails.
				if (showLoginPanel()) {
					form.hidden = true;
				} else {
					panel.parentNode.removeChild(panel);
				}
			});
		}
		showLoginPanel();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
