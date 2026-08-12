/**
 * AIKairali Portal Frontend JS
 */
(function($) {
	'use strict';

	$(document).ready(function() {
		// Prevent duplicate drawer rendering if header has it
		if ($('#aikMobileDrawer').length > 1) {
			$('#aikMobileDrawer').last().remove();
			$('#aikDrawerOverlay').last().remove();
		}

		var $mobileToggle  = $('#aikMobileToggle');
		var $mobileDrawer  = $('#aikMobileDrawer');
		var $drawerOverlay = $('#aikDrawerOverlay');
		var $drawerClose   = $('#aikDrawerClose');

		function openDrawer() {
			$mobileDrawer.addClass('active');
			$drawerOverlay.addClass('active');
			$('body').addClass('aik-drawer-open');
		}

		function closeDrawer() {
			$mobileDrawer.removeClass('active');
			$drawerOverlay.removeClass('active');
			$('body').removeClass('aik-drawer-open');
		}

		if ($mobileToggle.length) {
			$mobileToggle.on('click', function(e) {
				e.preventDefault();
				if ($mobileDrawer.hasClass('active')) {
					closeDrawer();
				} else {
					openDrawer();
				}
			});
		}

		if ($drawerClose.length) {
			$drawerClose.on('click', function(e) {
				e.preventDefault();
				closeDrawer();
			});
		}

		if ($drawerOverlay.length) {
			$drawerOverlay.on('click', function(e) {
				e.preventDefault();
				closeDrawer();
			});
		}

		// Highlight current active menu item in drawer
		var currentPath = window.location.pathname.replace(/\/$/, '') || '/';
		$('.aik-drawer-menu a').each(function() {
			var href = $(this).attr('href');
			if (!href) return;
			var linkPath = href.replace(/\/$/, '') || '/';
			if (linkPath === currentPath || (linkPath !== '' && linkPath !== '/' && currentPath.indexOf(linkPath) === 0)) {
				$(this).parent('li').addClass('current-menu-item');
			}
		});
	});
})(jQuery);
