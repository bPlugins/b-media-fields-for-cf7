/**
 * B Media Fields for Contact Form 7 – tag generator helpers.
 *
 * - Media Library pickers for sources, poster, captions, thumbnails, artwork.
 * - Shows YouTube / Vimeo groups only for the selected provider.
 * - Keeps typed values compatible with Contact Form 7's form-tag syntax
 *   (options may not contain spaces, commas or quotes).
 *
 * @package BMediaFieldsCF7
 */
( function ( window, document ) {
	'use strict';

	var i18n = ( window.bmfcf7Admin && window.bmfcf7Admin.i18n ) || {};

	// Characters Contact Form 7 accepts inside a form-tag option value.
	var OPTION_CHARS = /[^-+*=0-9a-zA-Z:.!?#$&@_\/|%]/g;
	var OPTION_MESSAGE = i18n.invalidChars || 'Only letters, numbers and - + * = : . ! ? # $ & @ _ / | % are allowed here (no spaces or commas).';

	function triggerChange( el ) {
		var event;
		try {
			event = new window.Event( 'change', { bubbles: true } );
		} catch ( e ) {
			event = document.createEvent( 'Event' );
			event.initEvent( 'change', true, true );
		}
		el.dispatchEvent( event );
	}

	/* ------------------------------------------------------------------ */
	/* Sanitisation                                                        */
	/* ------------------------------------------------------------------ */

	function sanitizeField( el ) {
		var mode = el.getAttribute( 'data-bmfcf7-sanitize' );
		var value = el.value;
		var invalid = false;

		switch ( mode ) {
			case 'url':
			case 'token':
				value = value.replace( /\s+/g, '' );
				invalid = OPTION_CHARS.test( value );
				break;

			case 'text':
				value = value.replace( /\s/g, '_' );
				invalid = OPTION_CHARS.test( value );
				break;

			case 'captions':
				// Items separated by spaces: lang|url|Label
				value = value.replace( /\s{2,}/g, ' ' );
				invalid = OPTION_CHARS.test( value.replace( / /g, '' ) );
				break;

			case 'color':
				value = value.replace( /[^#0-9a-fA-F]/g, '' );
				break;

			case 'url-lines':
				// Quoted values: anything except quotes and square brackets.
				value = value.replace( /["'[\]]/g, '' );
				break;

			case 'content':
				value = value.replace( /[[\]]/g, '' );
				break;
		}

		OPTION_CHARS.lastIndex = 0;

		if ( value !== el.value ) {
			var pos = el.selectionStart;
			el.value = value;
			if ( typeof pos === 'number' ) {
				try {
					el.setSelectionRange( pos, pos );
				} catch ( e ) { /* number inputs */ }
			}
		}

		if ( el.setCustomValidity ) {
			el.setCustomValidity( invalid ? OPTION_MESSAGE : '' );
		}
	}

	/* ------------------------------------------------------------------ */
	/* Provider / basetype visibility                                      */
	/* ------------------------------------------------------------------ */

	function refreshVisibility( panel ) {
		var providerSelect = panel.querySelector( '[data-bmfcf7-provider]' );
		var basetype = panel.querySelector( '[data-tag-part="basetype"]' );
		var isAudio = basetype && basetype.value === 'audio';

		if ( providerSelect ) {
			if ( isAudio ) {
				providerSelect.value = '';
			}
			providerSelect.disabled = !! isAudio;
		}

		var provider = ( providerSelect && providerSelect.value ) ? providerSelect.value : 'html5';
		var groups = panel.querySelectorAll( '[data-bmfcf7-show-for]' );

		for ( var i = 0; i < groups.length; i++ ) {
			var wanted = groups[ i ].getAttribute( 'data-bmfcf7-show-for' );
			if ( wanted === provider ) {
				groups[ i ].removeAttribute( 'hidden' );
			} else {
				groups[ i ].setAttribute( 'hidden', 'hidden' );
			}
		}

		// Placeholder hints only apply to the video/audio dialogs (which have a provider select).
		var sources = panel.querySelector( '[data-tag-part="value"]' );
		if ( sources && ( providerSelect || isAudio ) ) {
			if ( provider === 'youtube' ) {
				sources.placeholder = 'https://www.youtube.com/watch?v=bTqVqk7FSmY';
			} else if ( provider === 'vimeo' ) {
				sources.placeholder = 'https://vimeo.com/76979871';
			} else if ( isAudio ) {
				sources.placeholder = 'https://example.com/track.mp3';
			} else {
				sources.placeholder = 'https://example.com/video.mp4';
			}
		}
	}

	/* ------------------------------------------------------------------ */
	/* Media Library                                                       */
	/* ------------------------------------------------------------------ */

	function libraryType( kind, panel ) {
		if ( kind === 'video' || kind === 'audio' ) {
			var basetype = panel.querySelector( '[data-tag-part="basetype"]' );
			return ( basetype && basetype.value === 'audio' ) ? 'audio' : kind;
		}
		if ( kind === 'image' ) {
			return 'image';
		}
		if ( kind === 'text' ) {
			return 'text';
		}
		return '';
	}

	function openMedia( button ) {
		if ( ! window.wp || ! window.wp.media ) {
			return;
		}

		var panel = button.closest( 'form.tag-generator-panel' ) || document;
		var target = panel.querySelector( button.getAttribute( 'data-bmfcf7-target' ) );
		var mode = button.getAttribute( 'data-bmfcf7-mode' ) || 'replace';
		var type = libraryType( button.getAttribute( 'data-bmfcf7-media' ), panel );

		if ( ! target ) {
			return;
		}

		var options = {
			title: type === 'image' ? ( i18n.chooseImage || 'Select image' ) : ( i18n.chooseMedia || 'Select media' ),
			button: { text: i18n.useThis || 'Use this file' },
			multiple: mode === 'append'
		};

		if ( type && type !== 'text' ) {
			options.library = { type: type };
		}

		var frame = window.wp.media( options );

		// The CF7 generator is a native <dialog> in the top layer, which no
		// z-index can beat. Close it while the Media Library is open and
		// re-open it (values intact) when the library closes. An empty
		// returnValue keeps CF7 from inserting anything on close.
		var dialog = button.closest( 'dialog' );
		var reopen = false;
		if ( dialog && dialog.open ) {
			dialog.close( '' );
			reopen = true;
		}

		frame.on( 'close', function () {
			if ( reopen && dialog && ! dialog.open && typeof dialog.showModal === 'function' ) {
				dialog.showModal();
				window.setTimeout( function () {
					target.focus();
				}, 0 );
			}
		} );

		frame.on( 'select', function () {
			var selection = frame.state().get( 'selection' ).toJSON();
			var urls = [];

			for ( var i = 0; i < selection.length; i++ ) {
				if ( selection[ i ].url ) {
					urls.push( selection[ i ].url );
				}
			}

			if ( ! urls.length ) {
				return;
			}

			if ( mode === 'append' ) {
				var lines = target.value.split( '\n' ).filter( function ( line ) {
					return line.trim() !== '';
				} );
				target.value = lines.concat( urls ).join( '\n' );
			} else if ( mode === 'captions' ) {
				var existing = target.value.trim();
				target.value = ( existing ? existing + ' ' : '' ) + 'en|' + urls[ 0 ];
			} else {
				target.value = urls[ 0 ];
			}

			sanitizeField( target );
			triggerChange( target );
		} );

		frame.open();
	}

	/* ------------------------------------------------------------------ */
	/* Wiring                                                              */
	/* ------------------------------------------------------------------ */

	function onReady() {
		var panels = document.querySelectorAll( 'form.tag-generator-panel[data-id="video"], form.tag-generator-panel[data-id="audio"], form.tag-generator-panel[data-id="model3d"]' );

		for ( var i = 0; i < panels.length; i++ ) {
			( function ( panel ) {
				refreshVisibility( panel );

				panel.addEventListener( 'change', function ( event ) {
					var el = event.target;
					if ( el.matches( '[data-bmfcf7-provider], [data-tag-part="basetype"]' ) ) {
						refreshVisibility( panel );
					}
				} );

				panel.addEventListener( 'input', function ( event ) {
					var el = event.target;
					if ( el.hasAttribute( 'data-bmfcf7-sanitize' ) ) {
						sanitizeField( el );
					}
					// CF7 re-generates the tag on "change" (and keyup for text inputs);
					// make every edit – typing, pasting, pickers – update it immediately.
					triggerChange( el );
				} );

				panel.addEventListener( 'reset', function () {
					window.setTimeout( function () {
						refreshVisibility( panel );
						var fields = panel.querySelectorAll( '[data-bmfcf7-sanitize]' );
						for ( var j = 0; j < fields.length; j++ ) {
							if ( fields[ j ].setCustomValidity ) {
								fields[ j ].setCustomValidity( '' );
							}
						}
					}, 0 );
				} );

				panel.addEventListener( 'click', function ( event ) {
					var button = event.target.closest ? event.target.closest( '[data-bmfcf7-media]' ) : null;
					if ( button ) {
						event.preventDefault();
						openMedia( button );
					}
				} );
			}( panels[ i ] ) );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', onReady );
	} else {
		onReady();
	}
}( window, document ) );
