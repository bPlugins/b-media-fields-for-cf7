/**
 * Media Fields for Contact Form 7 – settings page behaviour.
 *
 * - Tab navigation (hash + localStorage, survives save/reload).
 * - Enable switches mirrored between overview cards and section headers.
 * - Colour pickers, unsaved-changes hint.
 *
 * @package BMediaFieldsCF7
 */
( function ( window, document, $ ) {
	'use strict';

	var STORAGE_KEY = 'bmfcf7SettingsTab';
	var form = document.getElementById( 'bmfcf7-settings-form' );

	if ( ! form ) {
		return;
	}

	var navItems = form.querySelectorAll( '[data-bmfcf7-tab]' );
	var panels = form.querySelectorAll( '[data-bmfcf7-panel]' );

	function panelExists( key ) {
		return !! form.querySelector( '[data-bmfcf7-panel="' + key + '"]' );
	}

	function activate( key, push ) {
		if ( ! panelExists( key ) ) {
			key = 'overview';
		}

		panels.forEach( function ( panel ) {
			if ( panel.getAttribute( 'data-bmfcf7-panel' ) === key ) {
				panel.removeAttribute( 'hidden' );
			} else {
				panel.setAttribute( 'hidden', 'hidden' );
			}
		} );

		navItems.forEach( function ( item ) {
			item.classList.toggle( 'is-active', item.getAttribute( 'data-bmfcf7-tab' ) === key );
		} );

		try {
			window.localStorage.setItem( STORAGE_KEY, key );
		} catch ( e ) { /* private mode */ }

		if ( push && window.history && window.history.replaceState ) {
			window.history.replaceState( null, '', '#' + key );
		}

		// Keep the active tab in the referer so options.php returns here.
		var referer = form.querySelector( 'input[name="_wp_http_referer"]' );
		if ( referer ) {
			referer.value = referer.value.replace( /#.*$/, '' ) + '#' + key;
		}
	}

	function initialTab() {
		var hash = ( window.location.hash || '' ).replace( '#', '' );
		if ( hash && panelExists( hash ) ) {
			return hash;
		}
		try {
			var stored = window.localStorage.getItem( STORAGE_KEY );
			if ( stored && panelExists( stored ) ) {
				return stored;
			}
		} catch ( e ) { /* ignore */ }
		return 'overview';
	}

	form.addEventListener( 'click', function ( event ) {
		var link = event.target.closest ? event.target.closest( '[data-bmfcf7-tab], [data-bmfcf7-goto]' ) : null;
		if ( ! link ) {
			return;
		}
		event.preventDefault();
		activate( link.getAttribute( 'data-bmfcf7-tab' ) || link.getAttribute( 'data-bmfcf7-goto' ), true );
		window.scrollTo( { top: 0, behavior: 'smooth' } );
	} );

	window.addEventListener( 'hashchange', function () {
		activate( ( window.location.hash || '' ).replace( '#', '' ), false );
	} );

	activate( initialTab(), false );

	/* Enable switches: keep overview + header in sync, update nav dot. */
	function syncEnable( key, checked, source ) {
		form.querySelectorAll( '[data-bmfcf7-enable="' + key + '"]' ).forEach( function ( input ) {
			if ( input !== source ) {
				input.checked = checked;
			}
			var text = input.closest( '.bmfcf7-switch' ) && input.closest( '.bmfcf7-switch' ).querySelector( '[data-bmfcf7-enable-text]' );
			if ( text ) {
				text.textContent = checked ? text.getAttribute( 'data-on' ) : text.getAttribute( 'data-off' );
			}
		} );
		var dot = form.querySelector( '[data-bmfcf7-dot="' + key + '"]' );
		if ( dot ) {
			dot.classList.toggle( 'is-on', checked );
		}
	}

	form.querySelectorAll( '[data-bmfcf7-enable]' ).forEach( function ( input ) {
		input.addEventListener( 'change', function () {
			syncEnable( input.getAttribute( 'data-bmfcf7-enable' ), input.checked, input );
		} );
	} );

	/* Colour pickers */
	if ( $ && $.fn.wpColorPicker ) {
		$( '.bmfcf7-color' ).wpColorPicker( {
			change: function () {
				markDirty();
			},
			clear: function () {
				markDirty();
			}
		} );
	}

	/* Videos: only contact YouTube once the visitor asks for it. */
	form.querySelectorAll( '[data-bmfcf7-video]' ).forEach( function ( facade ) {
		facade.addEventListener( 'click', function () {
			var id = facade.getAttribute( 'data-bmfcf7-video' );
			var frame = document.createElement( 'iframe' );
			var label = facade.querySelector( '.screen-reader-text' );

			frame.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent( id ) +
				'?autoplay=1&rel=0&modestbranding=1&origin=' +
				encodeURIComponent( window.location.origin );
			frame.title = label ? label.textContent : 'Video';
			frame.allow = 'accelerometer; autoplay; encrypted-media; picture-in-picture; web-share';
			frame.allowFullscreen = true;

			// YouTube needs to know which site is embedding. Sites that send
			// "Referrer-Policy: same-origin" strip that, and the player fails
			// with "Error 153". Setting the policy on the iframe overrides the
			// page default for this request, which is what YouTube's own embed
			// code does.
			frame.referrerPolicy = 'strict-origin-when-cross-origin';

			facade.parentNode.replaceChild( frame, facade );
		} );
	} );

	/* Videos shutter. The open/closed state is stored per user on the server,
	   so it survives a reload and is rendered before paint (no flash). */
	( function () {
		var panel = form.querySelector( '[data-bmfcf7-videos]' );
		if ( ! panel ) {
			return;
		}

		var toggle = panel.querySelector( '[data-bmfcf7-videos-toggle]' );
		var label = panel.querySelector( '[data-bmfcf7-videos-label]' );
		var grid = panel.querySelector( '.bmfcf7-videos__grid' );
		var note = panel.querySelector( '.bmfcf7-videos__note' );
		var cfg = window.bmfcf7Settings || {};
		var strings = cfg.i18n || {};

		if ( ! toggle || ! grid ) {
			return;
		}

		toggle.addEventListener( 'click', function () {
			var hidden = ! panel.classList.contains( 'is-collapsed' );

			panel.classList.toggle( 'is-collapsed', hidden );
			grid.hidden = hidden;
			if ( note ) {
				note.hidden = hidden;
			}
			toggle.setAttribute( 'aria-expanded', hidden ? 'false' : 'true' );
			if ( label ) {
				label.textContent = hidden
					? ( strings.show || 'Show videos' )
					: ( strings.hide || 'Hide videos' );
			}

			// Stop any playing embed when the panel is closed.
			if ( hidden ) {
				grid.querySelectorAll( 'iframe' ).forEach( function ( frame ) {
					frame.src = frame.src;
				} );
			}

			if ( ! cfg.ajaxUrl || ! cfg.nonce ) {
				return;
			}

			var body = new window.FormData();
			body.append( 'action', 'bmfcf7_toggle_videos' );
			body.append( '_wpnonce', cfg.nonce );
			body.append( 'hidden', hidden ? '1' : '0' );

			window.fetch( cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body } )
				.catch( function () {} );
		} );
	}() );

	/* Unsaved changes hint */
	var hint = form.querySelector( '[data-bmfcf7-dirty-hint]' );
	var dirty = false;
	var ready = false;

	window.setTimeout( function () {
		ready = true;
	}, 600 );

	function markDirty() {
		if ( dirty || ! ready ) {
			return;
		}
		dirty = true;
		if ( hint ) {
			hint.removeAttribute( 'hidden' );
		}
	}

	form.addEventListener( 'change', function ( event ) {
		if ( event.target.matches( 'input, select, textarea' ) ) {
			markDirty();
		}
	} );

	form.addEventListener( 'submit', function () {
		dirty = false;
	} );

	window.addEventListener( 'beforeunload', function ( event ) {
		if ( dirty ) {
			event.preventDefault();
			event.returnValue = '';
		}
	} );
}( window, document, window.jQuery ) );
