/**
 * File navigation.js.
 *
 * Handles toggling the navigation menu for small screens and enables TAB key
 * navigation support for dropdown menus.
 */
(function () {
	var container, button, menu, links, i, len;

	container = document.getElementById('site-navigation');
	if (!container) {
		return;
	}

	button = container.getElementsByTagName('button')[0];
	if ('undefined' === typeof button) {
		return;
	}

	menu = container.getElementsByTagName('ul')[0];

	// Hide menu toggle button if menu is empty and return early.
	if ('undefined' === typeof menu) {
		button.style.display = 'none';
		return;
	}

	if (-1 === menu.className.indexOf('nav-menu')) {
		menu.className += ' nav-menu';
	}

	button.onclick = function () {
		if (-1 !== container.className.indexOf('toggled')) {
			container.className = container.className.replace(' toggled', '');
			button.setAttribute('aria-expanded', 'false');
		} else {
			container.className += ' toggled';
			button.setAttribute('aria-expanded', 'true');
		}
	};

	// Close small menu when user clicks outside
	document.addEventListener('click', function (event) {
		var isClickInside = container.contains(event.target);

		if (!isClickInside) {
			container.className = container.className.replace(' toggled', '');
			button.setAttribute('aria-expanded', 'false');
		}
	});

	// Get all the link elements within the menu.
	links = menu.getElementsByTagName('a');

	// Each time a menu link is focused or blurred, toggle focus.
	for (i = 0, len = links.length; i < len; i++) {
		links[i].addEventListener('focus', toggleFocus, true);
		links[i].addEventListener('blur', toggleFocus, true);
	}

	/**
	 * Sets or removes .focus class on an element.
	 */
	function toggleFocus() {
		var self = this;

		// Move up through the ancestors of the current link until we hit .nav-menu.
		while (-1 === self.className.indexOf('nav-menu')) {
			// On li elements toggle the class .focus.
			if ('li' === self.tagName.toLowerCase()) {
				if (-1 !== self.className.indexOf('focus')) {
					self.className = self.className.replace(' focus', '');
				} else {
					self.className += ' focus';
				}
			}

			self = self.parentElement;
		}
	}

	/**
	 * Toggles `focus` class to allow submenu access on tablets.
	 */
	(function () {
		var touchStartFn,
			parentLink = container.querySelectorAll('.menu-item-has-children > a, .page_item_has_children > a');

		if ('ontouchstart' in window) {
			touchStartFn = function (e) {
				var menuItem = this.parentNode;

				if (!menuItem.classList.contains('focus')) {
					e.preventDefault();
					for (i = 0; i < menuItem.parentNode.children.length; ++i) {
						if (menuItem === menuItem.parentNode.children[i]) {
							continue;
						}
						menuItem.parentNode.children[i].classList.remove('focus');
					}
					menuItem.classList.add('focus');
				} else {
					menuItem.classList.remove('focus');
				}
			};

			for (i = 0; i < parentLink.length; ++i) {
				parentLink[i].addEventListener('touchstart', touchStartFn, false);
			}
		}
	}(container));

	/* Toggle hamburger menu */
	document.getElementById('nav-icon1').addEventListener('click', function () {
		this.classList.toggle('open');
	});

}());

// When the user scrolls down 100px from the top of the document, show the woocommerce fixed menu icons

window.onscroll = function () {
	esteraScrollFunction();
};

function esteraScrollFunction() {
	if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
		if (document.getElementById("scroll-btn")) {
			document.getElementById("scroll-btn").style.display = "block";
		}
		if (document.getElementById("scroll-cart")) {
			document.getElementById("scroll-cart").style.display = "block";
		}
		document.getElementsByClassName('back-to-top')[0].style.display = "block";

	} else {
		if (document.getElementById("scroll-btn")) {
			document.getElementById("scroll-btn").style.display = "none";
		}
		if (document.getElementById("scroll-cart")) {
			document.getElementById("scroll-cart").style.display = "none";
		}
		document.getElementsByClassName('back-to-top')[0].style.display = "none";
	}
}

// Smooth scroll to top when back-to-top button is pressed
document.getElementsByClassName('back-to-top')[0].addEventListener('click', function () {
	window.scrollTo({
		top: 0,
		behavior: 'smooth'
	});
})