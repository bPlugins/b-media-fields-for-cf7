/**
 * B Media Fields for Contact Form 7 – [gallery] front-end behaviour.
 *
 * Dependency-free: carousel scrolling and a lightbox with keyboard support.
 *
 * @package BMediaFieldsCF7
 */
( function ( window, document ) {
	'use strict';

	var i18n = ( window.bmfcf7Gallery && window.bmfcf7Gallery.i18n ) || {};
	var lightbox = null;
	var group = [];
	var current = 0;
	var lastFocus = null;

	/* ------------------------------------------------------------------ */
	/* Lightbox                                                            */
	/* ------------------------------------------------------------------ */

	function buildLightbox() {
		if ( lightbox ) {
			return lightbox;
		}

		lightbox = document.createElement( 'div' );
		lightbox.className = 'bmfcf7-lightbox';
		lightbox.setAttribute( 'role', 'dialog' );
		lightbox.setAttribute( 'aria-modal', 'true' );
		lightbox.hidden = true;
		lightbox.innerHTML =
			'<span class="bmfcf7-lightbox__counter" data-role="counter"></span>' +
			'<button type="button" class="bmfcf7-lightbox__btn is-close" data-role="close">&times;</button>' +
			'<button type="button" class="bmfcf7-lightbox__btn is-prev" data-role="prev">&#8249;</button>' +
			'<button type="button" class="bmfcf7-lightbox__btn is-next" data-role="next">&#8250;</button>' +
			'<figure class="bmfcf7-lightbox__figure">' +
			'<img class="bmfcf7-lightbox__img" alt="" data-role="img" />' +
			'<figcaption class="bmfcf7-lightbox__caption" data-role="caption"></figcaption>' +
			'</figure>';

		lightbox.querySelector( '[data-role="close"]' ).setAttribute( 'aria-label', i18n.close || 'Close' );
		lightbox.querySelector( '[data-role="prev"]' ).setAttribute( 'aria-label', i18n.prev || 'Previous image' );
		lightbox.querySelector( '[data-role="next"]' ).setAttribute( 'aria-label', i18n.next || 'Next image' );

		lightbox.addEventListener( 'click', function ( event ) {
			var role = event.target.getAttribute && event.target.getAttribute( 'data-role' );
			if ( role === 'close' || event.target === lightbox ) {
				closeLightbox();
			} else if ( role === 'prev' ) {
				show( current - 1 );
			} else if ( role === 'next' ) {
				show( current + 1 );
			}
		} );

		document.body.appendChild( lightbox );
		return lightbox;
	}

	function show( index ) {
		if ( ! group.length ) {
			return;
		}

		current = ( index + group.length ) % group.length;

		var link = group[ current ];
		var img = lightbox.querySelector( '[data-role="img"]' );
		var caption = lightbox.querySelector( '[data-role="caption"]' );
		var counter = lightbox.querySelector( '[data-role="counter"]' );
		var text = link.getAttribute( 'data-caption' ) || '';
		var inner = link.querySelector( 'img' );

		img.src = link.getAttribute( 'href' );
		img.alt = text || ( inner ? inner.alt : '' );
		caption.textContent = text;
		caption.hidden = ! text;

		if ( counter ) {
			var showCounter = lightbox.getAttribute( 'data-counter' ) === 'true';
			counter.textContent = showCounter ? current + 1 + ' / ' + group.length : '';
		}

		var single = group.length < 2;
		lightbox.querySelector( '[data-role="prev"]' ).hidden = single;
		lightbox.querySelector( '[data-role="next"]' ).hidden = single;
	}

	function openLightbox( link ) {
		var wrap = link.closest( '.bmfcf7-gallery' );
		if ( ! wrap ) {
			return;
		}

		buildLightbox();
		group = Array.prototype.slice.call( wrap.querySelectorAll( '[data-bmfcf7-lightbox]' ) );
		lightbox.setAttribute( 'data-counter', wrap.getAttribute( 'data-counter' ) === 'true' ? 'true' : 'false' );

		lastFocus = document.activeElement;
		lightbox.hidden = false;
		document.documentElement.style.overflow = 'hidden';
		show( group.indexOf( link ) );
		lightbox.querySelector( '[data-role="close"]' ).focus();
	}

	function closeLightbox() {
		if ( ! lightbox || lightbox.hidden ) {
			return;
		}
		lightbox.hidden = true;
		lightbox.querySelector( '[data-role="img"]' ).src = '';
		document.documentElement.style.overflow = '';
		if ( lastFocus && lastFocus.focus ) {
			lastFocus.focus();
		}
	}

	document.addEventListener( 'click', function ( event ) {
		var link = event.target.closest ? event.target.closest( '[data-bmfcf7-lightbox]' ) : null;
		if ( link ) {
			event.preventDefault();
			openLightbox( link );
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( ! lightbox || lightbox.hidden ) {
			return;
		}
		if ( event.key === 'Escape' ) {
			closeLightbox();
		} else if ( event.key === 'ArrowLeft' ) {
			show( current - 1 );
		} else if ( event.key === 'ArrowRight' ) {
			show( current + 1 );
		} else if ( event.key === 'Tab' ) {
			// Keep focus inside the dialog.
			var focusable = lightbox.querySelectorAll( 'button:not([hidden])' );
			var first = focusable[ 0 ];
			var last = focusable[ focusable.length - 1 ];
			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && document.activeElement === last ) {
				event.preventDefault();
				first.focus();
			}
		}
	} );

	/* ------------------------------------------------------------------ */
	/* Carousel                                                            */
	/* ------------------------------------------------------------------ */

	function initCarousel( wrap ) {
		var track = wrap.querySelector( '.bmfcf7-gallery__track' );
		var dots = wrap.querySelectorAll( '[data-bmfcf7-dot]' );
		var items = wrap.querySelectorAll( '.bmfcf7-gallery__item' );

		if ( ! track || ! items.length ) {
			return;
		}

		function step() {
			return items[ 0 ].getBoundingClientRect().width + parseFloat( getComputedStyle( track ).columnGap || 0 );
		}

		function activeIndex() {
			return Math.round( track.scrollLeft / step() );
		}

		function goTo( index ) {
			var max = items.length - 1;
			var target = index < 0 ? max : ( index > max ? 0 : index );
			track.scrollTo( { left: target * step(), behavior: 'smooth' } );
		}

		wrap.addEventListener( 'click', function ( event ) {
			var nav = event.target.closest ? event.target.closest( '[data-bmfcf7-slide]' ) : null;
			if ( nav ) {
				goTo( activeIndex() + ( nav.getAttribute( 'data-bmfcf7-slide' ) === 'next' ? 1 : -1 ) );
				return;
			}
			var dot = event.target.closest ? event.target.closest( '[data-bmfcf7-dot]' ) : null;
			if ( dot ) {
				goTo( parseInt( dot.getAttribute( 'data-bmfcf7-dot' ), 10 ) );
			}
		} );

		function syncDots() {
			var index = activeIndex();
			for ( var i = 0; i < dots.length; i++ ) {
				dots[ i ].classList.toggle( 'is-active', i === index );
			}
		}

		track.addEventListener( 'scroll', function () {
			window.clearTimeout( track._bmfcf7Timer );
			track._bmfcf7Timer = window.setTimeout( syncDots, 80 );
		} );
		syncDots();

		if ( wrap.getAttribute( 'data-autoplay' ) === 'true' ) {
			var seconds = parseInt( wrap.getAttribute( 'data-interval' ), 10 ) || 5;
			var timer = window.setInterval( function () {
				goTo( activeIndex() + 1 );
			}, seconds * 1000 );

			// Pause while the visitor is interacting.
			[ 'mouseenter', 'focusin', 'touchstart' ].forEach( function ( evt ) {
				wrap.addEventListener( evt, function () {
					window.clearInterval( timer );
				}, { passive: true } );
			} );
		}
	}

	/* ------------------------------------------------------------------ */
	/* Boot                                                                */
	/* ------------------------------------------------------------------ */

	function initAll( root ) {
		var scope = root && root.querySelectorAll ? root : document;
		var carousels = scope.querySelectorAll( '.bmfcf7-gallery--carousel' );

		for ( var i = 0; i < carousels.length; i++ ) {
			if ( carousels[ i ].getAttribute( 'data-bmfcf7-ready' ) !== '1' ) {
				carousels[ i ].setAttribute( 'data-bmfcf7-ready', '1' );
				initCarousel( carousels[ i ] );
			}
		}
	}

	function boot() {
		initAll( document );

		if ( typeof window.MutationObserver === 'function' && document.body ) {
			new window.MutationObserver( function ( mutations ) {
				for ( var i = 0; i < mutations.length; i++ ) {
					for ( var j = 0; j < mutations[ i ].addedNodes.length; j++ ) {
						var node = mutations[ i ].addedNodes[ j ];
						if ( node.nodeType === 1 ) {
							initAll( node );
						}
					}
				}
			} ).observe( document.body, { childList: true, subtree: true } );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}

	window.bmfcf7GalleryInit = initAll;
}( window, document ) );
