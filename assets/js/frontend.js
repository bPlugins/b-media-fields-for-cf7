/**
 * Essential Fields for CF7 – front-end initialiser.
 *
 * Finds every .efcf7-player element rendered by the [video]/[audio] form-tags,
 * merges the per-tag config with the global defaults and boots Plyr.
 *
 * @package EssentialFieldsCF7
 */
( function ( window, document ) {
	'use strict';

	var data = window.efcf7Frontend || {};
	var instances = [];

	function isObject( value ) {
		return value !== null && typeof value === 'object' && ! Array.isArray( value );
	}

	function deepMerge( target, source ) {
		var result = {};
		var key;

		for ( key in target ) {
			if ( Object.prototype.hasOwnProperty.call( target, key ) ) {
				result[ key ] = target[ key ];
			}
		}

		for ( key in source ) {
			if ( ! Object.prototype.hasOwnProperty.call( source, key ) ) {
				continue;
			}
			if ( isObject( source[ key ] ) && isObject( result[ key ] ) ) {
				result[ key ] = deepMerge( result[ key ], source[ key ] );
			} else {
				result[ key ] = source[ key ];
			}
		}

		return result;
	}

	function parseConfig( el ) {
		var raw = el.getAttribute( 'data-efcf7-config' );

		if ( ! raw ) {
			return {};
		}

		try {
			var parsed = JSON.parse( raw );
			return isObject( parsed ) ? parsed : {};
		} catch ( e ) {
			return {};
		}
	}

	function mediaType( el ) {
		var wrap = el.closest ? el.closest( '.efcf7-player-wrap' ) : null;
		if ( wrap && wrap.classList.contains( 'efcf7-audio' ) ) {
			return 'audio';
		}
		return el.tagName && el.tagName.toLowerCase() === 'audio' ? 'audio' : 'video';
	}

	function buildConfig( el ) {
		var base = {
			iconUrl: data.iconUrl,
			blankVideo: data.blankVideo,
			i18n: data.i18n || {}
		};

		var allDefaults = data.defaults || {};
		var defaults = allDefaults[ mediaType( el ) ] || allDefaults.video || {};

		if ( Array.isArray( defaults.controls ) ) {
			base.controls = defaults.controls.slice();
		}
		if ( Array.isArray( defaults.settings ) ) {
			base.settings = defaults.settings.slice();
		}
		if ( defaults.storage ) {
			base.storage = defaults.storage;
		}
		if ( typeof defaults.hideControls === 'boolean' ) {
			base.hideControls = defaults.hideControls;
		}

		var config = deepMerge( base, parseConfig( el ) );

		if ( config.ensureDownloadControl ) {
			delete config.ensureDownloadControl;
			if ( Array.isArray( config.controls ) && config.controls.indexOf( 'download' ) === -1 ) {
				config.controls.push( 'download' );
			}
		}

		// Plyr's own "disabled" switch.
		if ( config.enabled === false ) {
			return null;
		}

		return config;
	}

	function applyGlobalColor() {
		var defaults = data.defaults || {};
		var root = document.documentElement;
		if ( defaults.video && defaults.video.color ) {
			root.style.setProperty( '--efcf7-color-video', defaults.video.color );
		}
		if ( defaults.audio && defaults.audio.color ) {
			root.style.setProperty( '--efcf7-color-audio', defaults.audio.color );
		}
	}

	function initPlayer( el ) {
		if ( el.getAttribute( 'data-efcf7-ready' ) === '1' ) {
			return;
		}
		el.setAttribute( 'data-efcf7-ready', '1' );

		var config = buildConfig( el );

		if ( ! config ) {
			// Plyr disabled: leave the native player.
			el.removeAttribute( 'data-efcf7-config' );
			return;
		}

		if ( typeof window.Plyr !== 'function' ) {
			return;
		}

		try {
			var player = new window.Plyr( el, config );
			instances.push( player );

			var wrap = el.closest ? el.closest( '.efcf7-player-wrap' ) : null;

			// Expose the instance for custom scripts.
			if ( wrap ) {
				wrap.efcf7Player = player;
			}

			// Let developers hook in: document.addEventListener('efcf7:ready', e => e.detail.player)
			var event;
			try {
				event = new window.CustomEvent( 'efcf7:ready', {
					bubbles: true,
					detail: { player: player, element: el, config: config }
				} );
			} catch ( e ) {
				event = document.createEvent( 'CustomEvent' );
				event.initCustomEvent( 'efcf7:ready', true, false, { player: player, element: el, config: config } );
			}
			el.dispatchEvent( event );
		} catch ( e ) {
			if ( window.console && window.console.error ) {
				window.console.error( '[efcf7] Could not initialise player', e );
			}
		}
	}

	function initAll( root ) {
		var scope = root && root.querySelectorAll ? root : document;
		var players = scope.querySelectorAll( '.efcf7-player' );

		for ( var i = 0; i < players.length; i++ ) {
			initPlayer( players[ i ] );
		}
	}

	function observe() {
		if ( typeof window.MutationObserver !== 'function' || ! document.body ) {
			return;
		}

		var observer = new window.MutationObserver( function ( mutations ) {
			for ( var i = 0; i < mutations.length; i++ ) {
				var added = mutations[ i ].addedNodes;
				for ( var j = 0; j < added.length; j++ ) {
					var node = added[ j ];
					if ( node.nodeType !== 1 ) {
						continue;
					}
					if ( node.classList && node.classList.contains( 'efcf7-player' ) ) {
						initPlayer( node );
					} else if ( node.querySelectorAll ) {
						initAll( node );
					}
				}
			}
		} );

		observer.observe( document.body, { childList: true, subtree: true } );
	}

	function boot() {
		applyGlobalColor();
		initAll( document );
		observe();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}

	// Public API.
	window.efcf7 = {
		init: initAll,
		players: instances
	};
}( window, document ) );
