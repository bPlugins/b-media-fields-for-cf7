/**
 * B Media Fields for Contact Form 7 – [pdf_flipbook] front-end viewer.
 *
 * Renders a PDF with PDF.js, either as a page-turning book (StPageFlip) or a
 * scrolling stack of pages. Pages are rendered on demand around the current
 * page so long documents stay responsive.
 *
 * @package BMediaFieldsCF7
 */
( function ( window, document ) {
	'use strict';

	var data = window.bmfcf7Pdf || {};
	var i18n = data.i18n || {};

	function t( key, fallback ) {
		return i18n[ key ] || fallback;
	}

	function Viewer( el ) {
		this.el = el;
		this.stage = el.querySelector( '[data-role="stage"]' );
		this.status = el.querySelector( '[data-role="status"]' );
		this.pagesLabel = el.querySelector( '[data-role="pages"]' );
		this.zoom = 1;
		this.page = 1;
		this.canvases = {};
		this.rendering = {};

		try {
			this.cfg = JSON.parse( el.getAttribute( 'data-bmfcf7-pdf' ) ) || {};
		} catch ( e ) {
			this.cfg = {};
		}

		this.bindToolbar();
	}

	Viewer.prototype.setStatus = function ( message ) {
		if ( ! this.status ) {
			return;
		}
		this.status.textContent = message || '';
		this.status.hidden = ! message;
	};

	Viewer.prototype.load = function () {
		var self = this;

		if ( ! window.pdfjsLib ) {
			self.setStatus( t( 'error', 'The document could not be loaded.' ) );
			return;
		}

		window.pdfjsLib.GlobalWorkerOptions.workerSrc = data.worker;

		window.pdfjsLib.getDocument( { url: self.cfg.src } ).promise.then( function ( pdf ) {
			self.pdf = pdf;
			self.total = pdf.numPages;
			self.page = Math.min( Math.max( 1, self.cfg.startPage || 1 ), self.total );

			return pdf.getPage( 1 ).then( function ( first ) {
				var vp = first.getViewport( { scale: 1 } );
				self.ratio = vp.height / vp.width;
				self.setStatus( '' );
				self.build();
			} );
		} ).catch( function ( err ) {
			self.setStatus( t( 'error', 'The document could not be loaded.' ) );
			if ( window.console && window.console.error ) {
				window.console.error( '[bmfcf7] PDF load failed', err );
			}
		} );
	};

	/* ---------------------------------------------------------------- */
	/* Rendering                                                         */
	/* ---------------------------------------------------------------- */

	Viewer.prototype.renderPage = function ( number, width ) {
		var self = this;
		var key = number + '@' + Math.round( width );

		if ( self.canvases[ key ] ) {
			return Promise.resolve( self.canvases[ key ] );
		}
		if ( self.rendering[ key ] ) {
			return self.rendering[ key ];
		}

		self.rendering[ key ] = self.pdf.getPage( number ).then( function ( page ) {
			var base = page.getViewport( { scale: 1 } );
			var dpr = Math.min( window.devicePixelRatio || 1, 2 );
			var scale = ( width / base.width ) * dpr;
			var viewport = page.getViewport( { scale: scale } );
			var canvas = document.createElement( 'canvas' );

			canvas.width = Math.floor( viewport.width );
			canvas.height = Math.floor( viewport.height );
			canvas.style.width = '100%';
			canvas.style.height = '100%';
			canvas.className = 'bmfcf7-pdf__canvas';

			return page.render( { canvasContext: canvas.getContext( '2d' ), viewport: viewport } ).promise.then( function () {
				self.canvases[ key ] = canvas;
				delete self.rendering[ key ];
				return canvas;
			} );
		} );

		return self.rendering[ key ];
	};

	Viewer.prototype.fillPage = function ( holder, number, width ) {
		var self = this;
		return self.renderPage( number, width ).then( function ( canvas ) {
			if ( holder.firstChild !== canvas ) {
				holder.innerHTML = '';
				holder.appendChild( canvas.cloneNode( true ) );
				// cloneNode does not copy pixels; blit them across.
				var target = holder.firstChild;
				target.width = canvas.width;
				target.height = canvas.height;
				target.getContext( '2d' ).drawImage( canvas, 0, 0 );
			}
		} ).catch( function () {} );
	};

	/* ---------------------------------------------------------------- */
	/* Layout                                                            */
	/* ---------------------------------------------------------------- */

	Viewer.prototype.build = function () {
		if ( 'scroll' === this.cfg.mode ) {
			this.buildScroll();
		} else {
			this.buildFlip();
		}
		this.updateLabel();
	};

	Viewer.prototype.availableSize = function () {
		var toolbar = this.el.querySelector( '.bmfcf7-pdf__toolbar' );
		var height = this.el.clientHeight - ( toolbar ? toolbar.offsetHeight : 0 ) - 16;
		return {
			width: Math.max( 120, this.el.clientWidth - 16 ),
			height: Math.max( 120, height )
		};
	};

	Viewer.prototype.buildFlip = function () {
		var self = this;

		if ( ! window.St || ! window.St.PageFlip ) {
			self.cfg.mode = 'scroll';
			self.buildScroll();
			return;
		}

		var box = self.availableSize();
		var portrait = !! self.cfg.singlePage || box.width < 680;
		var pageH = box.height;
		var pageW = pageH / self.ratio;
		var maxW = portrait ? box.width : box.width / 2;

		if ( pageW > maxW ) {
			pageW = maxW;
			pageH = pageW * self.ratio;
		}

		self.pageW = Math.floor( pageW );
		self.pageH = Math.floor( pageH );
		self.portrait = portrait;

		self.stage.innerHTML = '';

		var book = document.createElement( 'div' );
		book.className = 'bmfcf7-pdf__book';
		self.stage.appendChild( book );

		var holders = [];
		for ( var i = 1; i <= self.total; i++ ) {
			var page = document.createElement( 'div' );
			page.className = 'bmfcf7-pdf__page';
			page.setAttribute( 'data-page', i );
			page.setAttribute( 'data-density', 'soft' );
			book.appendChild( page );
			holders.push( page );
		}
		self.holders = holders;

		if ( self.flip && self.flip.destroy ) {
			try {
				self.flip.destroy();
			} catch ( e ) {}
		}

		self.flip = new window.St.PageFlip( book, {
			width: self.pageW,
			height: self.pageH,
			size: 'fixed',
			usePortrait: portrait,
			showCover: ! portrait,
			drawShadow: self.cfg.shadow !== false,
			flippingTime: self.cfg.flipTime || 800,
			mobileScrollSupport: true,
			useMouseEvents: true,
			maxShadowOpacity: 0.5
		} );

		self.flip.loadFromHTML( book.querySelectorAll( '.bmfcf7-pdf__page' ) );

		self.flip.on( 'flip', function ( e ) {
			self.page = ( e.data || 0 ) + 1;
			self.updateLabel();
			self.renderWindow();
		} );

		self.renderWindow();

		if ( self.page > 1 ) {
			try {
				self.flip.turnToPage( self.page - 1 );
			} catch ( e ) {}
		}
	};

	Viewer.prototype.buildScroll = function () {
		var self = this;
		var width = self.availableSize().width;

		self.stage.innerHTML = '';
		self.el.classList.add( 'is-scroll' );

		var list = document.createElement( 'div' );
		list.className = 'bmfcf7-pdf__scroller';
		self.stage.appendChild( list );
		self.scroller = list;
		self.holders = [];

		for ( var i = 1; i <= self.total; i++ ) {
			var holder = document.createElement( 'div' );
			holder.className = 'bmfcf7-pdf__page';
			holder.setAttribute( 'data-page', i );
			holder.style.aspectRatio = '1 / ' + self.ratio;
			list.appendChild( holder );
			self.holders.push( holder );
		}

		self.pageW = Math.floor( width );

		if ( typeof window.IntersectionObserver === 'function' ) {
			var io = new window.IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( ! entry.isIntersecting ) {
						return;
					}
					var holder = entry.target;
					var number = parseInt( holder.getAttribute( 'data-page' ), 10 );
					self.fillPage( holder, number, self.pageW );
					if ( entry.intersectionRatio > 0.5 ) {
						self.page = number;
						self.updateLabel();
					}
				} );
			}, { root: list, threshold: [ 0.01, 0.55 ] } );

			self.holders.forEach( function ( holder ) {
				io.observe( holder );
			} );
		} else {
			self.holders.forEach( function ( holder, index ) {
				self.fillPage( holder, index + 1, self.pageW );
			} );
		}

		if ( self.page > 1 ) {
			window.setTimeout( function () {
				self.goTo( self.page );
			}, 60 );
		}
	};

	/**
	 * Renders the pages around the current one (flip mode).
	 */
	Viewer.prototype.renderWindow = function () {
		var self = this;
		var from = Math.max( 1, self.page - 2 );
		var to = Math.min( self.total, self.page + 3 );

		for ( var i = from; i <= to; i++ ) {
			self.fillPage( self.holders[ i - 1 ], i, self.pageW );
		}
	};

	/* ---------------------------------------------------------------- */
	/* Controls                                                          */
	/* ---------------------------------------------------------------- */

	Viewer.prototype.goTo = function ( number ) {
		var target = Math.min( Math.max( 1, number ), this.total || 1 );

		if ( 'scroll' === this.cfg.mode ) {
			var holder = this.holders && this.holders[ target - 1 ];
			if ( holder && this.scroller ) {
				this.scroller.scrollTo( { top: holder.offsetTop, behavior: 'smooth' } );
			}
			this.page = target;
			this.updateLabel();
			return;
		}

		if ( this.flip ) {
			try {
				this.flip.turnToPage( target - 1 );
			} catch ( e ) {}
			this.page = target;
			this.updateLabel();
			this.renderWindow();
		}
	};

	Viewer.prototype.updateLabel = function () {
		if ( this.pagesLabel && this.total ) {
			this.pagesLabel.textContent = this.page + ' / ' + this.total;
		}
	};

	Viewer.prototype.setZoom = function ( value ) {
		this.zoom = Math.min( 3, Math.max( 0.5, Math.round( value * 10 ) / 10 ) );
		this.stage.style.transform = 1 === this.zoom ? '' : 'scale(' + this.zoom + ')';
		this.stage.style.transformOrigin = 'top center';
		this.el.classList.toggle( 'is-zoomed', this.zoom !== 1 );
	};

	Viewer.prototype.bindToolbar = function () {
		var self = this;

		self.el.addEventListener( 'click', function ( event ) {
			var btn = event.target.closest ? event.target.closest( '[data-role]' ) : null;
			if ( ! btn ) {
				return;
			}

			switch ( btn.getAttribute( 'data-role' ) ) {
				case 'prev':
					self.goTo( self.page - ( self.portrait || 'scroll' === self.cfg.mode ? 1 : 2 ) );
					break;
				case 'next':
					self.goTo( self.page + ( self.portrait || 'scroll' === self.cfg.mode ? 1 : 2 ) );
					break;
				case 'zoom-in':
					self.setZoom( self.zoom + 0.2 );
					break;
				case 'zoom-out':
					self.setZoom( self.zoom - 0.2 );
					break;
				case 'fullscreen':
					if ( document.fullscreenElement ) {
						document.exitFullscreen();
					} else if ( self.el.requestFullscreen ) {
						self.el.requestFullscreen();
					}
					break;
			}
		} );

		document.addEventListener( 'fullscreenchange', function () {
			if ( ! self.pdf ) {
				return;
			}
			window.setTimeout( function () {
				self.setZoom( 1 );
				self.build();
			}, 120 );
		} );

		var width = self.el.clientWidth;
		window.addEventListener( 'resize', function () {
			if ( ! self.pdf || Math.abs( self.el.clientWidth - width ) < 40 ) {
				return;
			}
			width = self.el.clientWidth;
			window.clearTimeout( self._resizeTimer );
			self._resizeTimer = window.setTimeout( function () {
				self.build();
			}, 250 );
		} );
	};

	/* ---------------------------------------------------------------- */
	/* Boot                                                              */
	/* ---------------------------------------------------------------- */

	function initViewer( el ) {
		if ( el.getAttribute( 'data-bmfcf7-ready' ) === '1' ) {
			return;
		}
		el.setAttribute( 'data-bmfcf7-ready', '1' );

		var viewer = new Viewer( el );

		if ( viewer.cfg.eager || typeof window.IntersectionObserver !== 'function' ) {
			viewer.load();
			return;
		}

		var io = new window.IntersectionObserver( function ( entries, observer ) {
			if ( entries.some( function ( entry ) { return entry.isIntersecting; } ) ) {
				observer.disconnect();
				viewer.load();
			}
		}, { rootMargin: '300px' } );

		io.observe( el );
	}

	function initAll( root ) {
		var scope = root && root.querySelectorAll ? root : document;
		var nodes = scope.querySelectorAll( '[data-bmfcf7-pdf]' );

		for ( var i = 0; i < nodes.length; i++ ) {
			initViewer( nodes[ i ] );
		}
	}

	function boot() {
		initAll( document );

		if ( typeof window.MutationObserver === 'function' && document.body ) {
			new window.MutationObserver( function ( mutations ) {
				for ( var i = 0; i < mutations.length; i++ ) {
					for ( var j = 0; j < mutations[ i ].addedNodes.length; j++ ) {
						if ( mutations[ i ].addedNodes[ j ].nodeType === 1 ) {
							initAll( mutations[ i ].addedNodes[ j ] );
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

	window.bmfcf7PdfInit = initAll;
}( window, document ) );
