/*!
 * MiRenova widgets — header menu, hero slideshow (lazy), price calculator, FAQ, CF7 "sent" label.
 * No dependencies, ES5-compatible syntax, deferred.
 */
(function () {
	'use strict';

	var doc = document;
	var win = window;
	var slice = Array.prototype.slice;
	var passive = { passive: true };

	function all(root, sel) {
		var list = slice.call(root.querySelectorAll(sel));
		if (root !== doc && root.matches && root.matches(sel)) {
			list.unshift(root);
		}
		return list;
	}

	function each(root, sel, fn) {
		all(root, sel).forEach(function (el) {
			if (el.getAttribute('data-mrn-ready')) {
				return;
			}
			el.setAttribute('data-mrn-ready', '1');
			fn(el);
		});
	}

	function json(el, attr) {
		try {
			return JSON.parse(el.getAttribute(attr)) || {};
		} catch (e) {
			return {};
		}
	}

	/* ------------------------------------------------------------ Header */
	function header(h) {
		var btn = h.querySelector('.mrn-header__burger');
		var panel = btn ? doc.getElementById(btn.getAttribute('aria-controls')) : null;
		if (!btn || !panel) {
			return;
		}
		function set(open) {
			h.classList.toggle('is-open', open);
			btn.setAttribute('aria-expanded', open ? 'true' : 'false');
			if (open) {
				panel.removeAttribute('hidden');
			} else {
				panel.setAttribute('hidden', '');
			}
		}
		btn.addEventListener('click', function () {
			set(btn.getAttribute('aria-expanded') !== 'true');
		});
		panel.addEventListener('click', function (e) {
			if (e.target.closest && e.target.closest('a')) {
				set(false);
			}
		});
		doc.addEventListener('keydown', function (e) {
			if ((e.key === 'Escape' || e.key === 'Esc') && h.classList.contains('is-open')) {
				set(false);
				btn.focus();
			}
		});
		doc.addEventListener('click', function (e) {
			if (h.classList.contains('is-open') && !h.contains(e.target)) {
				set(false);
			}
		});
		if (win.matchMedia) {
			var mq = win.matchMedia('(min-width: 1200px)');
			var onChange = function () {
				if (mq.matches) {
					set(false);
				}
			};
			if (mq.addEventListener) {
				mq.addEventListener('change', onChange);
			} else if (mq.addListener) {
				mq.addListener(onChange);
			}
		}
	}

	/* ------------------------------------------------------------ Hero slideshow */
	function hero(sec) {
		var cfg = json(sec, 'data-mrn-hero');
		var slides = slice.call(sec.querySelectorAll('.mrn-hero__slide'));
		var reduce = win.matchMedia && win.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var kb = sec.classList.contains('mrn-hero--kb') && !reduce;
		var cur = 0;
		var timer = null;
		var loaded = false;
		var events = ['scroll', 'wheel', 'touchstart', 'pointerdown', 'pointermove', 'mousemove', 'keydown'];

		function zoom(el, on) {
			if (kb && el) {
				el.classList.toggle('is-zoom', on);
			}
		}

		// Start the slow zoom on slide 1 only after it has painted (keeps LCP clean).
		var raf = win.requestAnimationFrame || function (f) { return setTimeout(f, 16); };
		raf(function () {
			raf(function () {
				zoom(slides[0], true);
			});
		});

		if (slides.length < 2) {
			return;
		}

		function ready(img) {
			return img.complete && img.naturalWidth > 1;
		}

		function next() {
			var n = (cur + 1) % slides.length;
			var img = slides[n];
			if (!ready(img)) {
				return; // try again on the next tick
			}
			var prev = slides[cur];
			img.classList.add('is-on');
			zoom(img, true);
			prev.classList.remove('is-on');
			zoom(prev, false);
			cur = n;
		}

		function start() {
			if (!timer) {
				timer = setInterval(next, Math.max(2000, cfg.i || 6000));
			}
		}

		function stop() {
			clearInterval(timer);
			timer = null;
		}

		function load() {
			if (loaded) {
				return;
			}
			loaded = true;
			events.forEach(function (ev) {
				win.removeEventListener(ev, load, passive);
			});
			slides.forEach(function (img) {
				var sizes = img.getAttribute('data-sizes');
				var srcset = img.getAttribute('data-srcset');
				var src = img.getAttribute('data-src');
				if (sizes) {
					img.setAttribute('sizes', sizes);
				}
				if (srcset) {
					img.setAttribute('srcset', srcset);
				}
				if (src) {
					img.setAttribute('src', src);
				}
				img.removeAttribute('data-sizes');
				img.removeAttribute('data-srcset');
				img.removeAttribute('data-src');
			});
			start();
		}

		doc.addEventListener('visibilitychange', function () {
			if (doc.hidden) {
				stop();
			} else if (loaded) {
				start();
			}
		});

		if (doc.body && doc.body.classList.contains('elementor-editor-active')) {
			load();
			return;
		}
		// Slides 2+ wait for the first interaction (or an idle delay) so they never compete with LCP.
		events.forEach(function (ev) {
			win.addEventListener(ev, load, passive);
		});
		if (cfg.lazy !== 'interaction') {
			var later = function () {
				setTimeout(load, cfg.idle || 5000);
			};
			if (doc.readyState === 'complete') {
				later();
			} else {
				win.addEventListener('load', later);
			}
		}
	}

	/* ------------------------------------------------------------ Price calculator */
	function calc(box) {
		var c = json(box, 'data-mrn-calc');
		var input = box.querySelector('.mrn-range input');
		var range = box.querySelector('.mrn-range');
		var val = box.querySelector('[data-v]');
		var out = box.querySelector('[data-out]');
		var rate = box.querySelector('[data-rate]');
		var tiers = slice.call(box.querySelectorAll('.mrn-calc__tier'));
		var t = +c.t || 0;
		var min = +c.min || 0;
		var max = +c.max || 100;
		var step = +c.step || 1;
		if (!input || !c.tiers) {
			return;
		}

		function fmt(n) {
			return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, c.sep || '.');
		}

		function render() {
			var m = +input.value;
			var tr = c.tiers[t];
			box.style.setProperty('--p', (m - min) / (max - min));
			if (val) {
				val.textContent = m;
			}
			if (tr && out) {
				out.textContent = fmt(m * tr[0]) + ' – ' + fmt(m * tr[1]) + ' ' + (c.cur || '');
			}
			if (tr && rate) {
				rate.textContent = tr[2];
			}
		}

		function set(m) {
			m = Math.round((m - min) / step) * step + min;
			input.value = Math.min(max, Math.max(min, m));
			render();
		}

		input.addEventListener('input', render);
		input.addEventListener('change', render);
		input.addEventListener('focus', function () {
			range.classList.add('is-focus');
		});
		input.addEventListener('blur', function () {
			range.classList.remove('is-focus');
		});
		slice.call(box.querySelectorAll('.mrn-calc__step')).forEach(function (b) {
			b.addEventListener('click', function () {
				set(+input.value + (+b.getAttribute('data-d')) * step);
			});
		});
		tiers.forEach(function (b) {
			b.addEventListener('click', function () {
				t = +b.getAttribute('data-t');
				tiers.forEach(function (x) {
					var on = x === b;
					x.classList.toggle('is-on', on);
					x.setAttribute('aria-pressed', on ? 'true' : 'false');
				});
				render();
			});
		});

		// Touch: tap or drag anywhere on the track (iOS only drags from the native thumb).
		if (win.PointerEvent) {
			var drag = false;
			var fromX = function (x) {
				var r = range.getBoundingClientRect();
				var p = (x - r.left - 11) / Math.max(1, r.width - 22);
				set(min + Math.min(1, Math.max(0, p)) * (max - min));
			};
			range.addEventListener('pointerdown', function (e) {
				if (e.pointerType === 'mouse') {
					return;
				}
				drag = true;
				fromX(e.clientX);
				if (range.setPointerCapture) {
					try {
						range.setPointerCapture(e.pointerId);
					} catch (err) {} // eslint-disable-line no-empty
				}
			});
			range.addEventListener('pointermove', function (e) {
				if (drag) {
					fromX(e.clientX);
				}
			});
			var end = function () {
				drag = false;
			};
			range.addEventListener('pointerup', end);
			range.addEventListener('pointercancel', end);
		}
		render();
	}

	/* ------------------------------------------------------------ FAQ accordion */
	function toggle(item, open) {
		var b = item.querySelector('.mrn-faq__btn');
		var a = item.querySelector('.mrn-faq__a');
		item.classList.toggle('is-open', open);
		if (b) {
			b.setAttribute('aria-expanded', open ? 'true' : 'false');
		}
		if (a) {
			if (open) {
				a.removeAttribute('hidden');
			} else {
				a.setAttribute('hidden', '');
			}
		}
	}

	function faq(list) {
		var one = list.getAttribute('data-mrn-faq') === 'one';
		list.addEventListener('click', function (e) {
			var btn = e.target.closest ? e.target.closest('.mrn-faq__btn') : null;
			if (!btn || !list.contains(btn)) {
				return;
			}
			var item = btn.closest('.mrn-faq__item');
			var open = btn.getAttribute('aria-expanded') !== 'true';
			if (one && open) {
				slice.call(list.querySelectorAll('.mrn-faq__item.is-open')).forEach(function (it) {
					toggle(it, false);
				});
			}
			toggle(item, open);
		});
	}

	/* ------------------------------------------------------------ Contact Form 7: button label after sending */
	doc.addEventListener('wpcf7mailsent', function (e) {
		var form = e.target;
		var wrap = form && form.closest ? form.closest('[data-mrn-sent]') : null;
		var label = form && form.querySelector ? form.querySelector('.wpcf7-submit .mrn-btn__label') : null;
		if (wrap && label) {
			label.textContent = wrap.getAttribute('data-mrn-sent');
		}
	});

	/* ------------------------------------------------------------ Init */
	function init(root) {
		root = root || doc;
		each(root, '[data-mrn-header]', header);
		each(root, '[data-mrn-hero]', hero);
		each(root, '[data-mrn-calc]', calc);
		each(root, '[data-mrn-faq]', faq);
	}

	init(doc);

	// Elementor editor: widgets are re-rendered on every change.
	function hooks() {
		if (win.elementorFrontend && win.elementorFrontend.hooks) {
			win.elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($el) {
				if ($el && $el[0]) {
					init($el[0]);
				}
			});
		}
	}
	if (win.elementorFrontend && win.elementorFrontend.hooks) {
		hooks();
	} else if (win.jQuery) {
		win.jQuery(win).on('elementor/frontend/init', hooks);
	}
})();
