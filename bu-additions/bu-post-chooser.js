/**
 * Script for the Admin Object Selector
 */

jQuery( function($) {

	// Store all object selector modal instances here
	var modals = {};

	/**
	 * Encapsulates the object selection modal interface.
	 */
	var ObjectSelectorModal = function(id) {
		this.id  = id;

		// Cache a reference to the modal DOM element
		// Thickbox will remove the DOM element referenced by #{{ id }} so we
		// rely on an inner container called #{{ id }}-inner.
		this.$el = $( '#' + id + '-inner' );

		// Shortcut function for querying classes within our context
		this.$ = function ( selector ) { return $( selector, this.$el ); }

		this.loadObjects();
		this.setupControls();
		this.attachHandlers();
	};

	/**
	 * Loads objects from a global defined in `object-selector.php`.
	 * @global burObjects
	 */
	ObjectSelectorModal.prototype.loadObjects = function () {
		this.objects = window.burObjects[this.id];
	}

	/**
	 * Initializes DOM elements for this instance.
	 */
	ObjectSelectorModal.prototype.setupControls = function () {

		// Use Bloodhound as typeahead data source
		var bloodhound = new Bloodhound({
				datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
				queryTokenizer: Bloodhound.tokenizers.whitespace,
				local: $.map( this.objects, function ( post ) { return { value: post.name, id: post.id }; } )
			});
		bloodhound.initialize();

	 	// Initialize typeahead input for this modal
	 	this.$typeahead = this.$( '.typeahead' );
		this.$typeahead.typeahead({
				hint: true,
				highlight: true,
				minLength: 1
			},
			{
				name: this.id,
				source: bloodhound.ttAdapter()
			});

		// Cache DOM references on this object
		this.$dropdown = this.$( '.choose-dropdown' );

		this.$currentSelection = this.$( '.selection-new' );
		this.$currentSelectionLabel = this.$( '.current-selection' );
		this.$currentSelectionInput = this.$( '.selection-input' );
		this.$removeSelection = this.$( '.selection-remove' );

		this.$confirmBtn = this.$( '.confirm-selection' );
		this.$removeBtn = this.$( '.remove-selection' );
	}

	ObjectSelectorModal.prototype.attachHandlers = function() {
		// Post selection
		this.$dropdown.on( 'change', $.proxy( this.onDropdownSelect, this ) );
		this.$typeahead.on( 'typeahead:selected typeahead:autocompleted', $.proxy( this.onTypeaheadSelect, this ) );

		// Complete modal workflow
		this.$confirmBtn.on( 'click', $.proxy( this.onConfirmSelection, this ) );
		this.$removeBtn.on( 'click', $.proxy( this.onRemoveSelection, this ) );
	}

	ObjectSelectorModal.prototype.clearSelection = function () {
		this.$currentSelectionInput.val( '' );
		this.$currentSelectionLabel.text( '' );

		this.$currentSelection.hide();
	}

	ObjectSelectorModal.prototype.selectObject = function (object) {
		this.$currentSelectionInput.val( object.id );
		this.$currentSelectionLabel.text( object.name );

		this.$currentSelection.show();
	}

	ObjectSelectorModal.prototype.setTarget = function ( $target ) {
		this.$target = $target;

		this.resetState();
	}

	ObjectSelectorModal.prototype.resetState = function () {

		// Reset selection state
		this.$dropdown.prop( 'selectedIndex', 0 );
		this.$typeahead.val('');
		this.clearSelection();

		// Show "Remove" section
		this.$removeSelection.show();

	}

	ObjectSelectorModal.prototype.resetDropdown = function () {
		this.$dropdown.prop( 'selectedIndex', 0 );
	}

	ObjectSelectorModal.prototype.onDropdownSelect = function (e) {
		var object = {};

		// Object was selected (first option is blank)
		if ( this.$dropdown.prop( 'selectedIndex' ) > 0 ) {

			// Clear typeahead
			this.$typeahead.val( '' );

			object = {
				id: this.$dropdown.val(),
				name: this.$dropdown.find( 'option:selected' ).text()
			};

			this.selectObject( object );
		}
	}

	ObjectSelectorModal.prototype.onTypeaheadSelect = function (e, selection) {
		this.selectObject({name: selection.value, id: selection.id });
		this.resetDropdown();
	}

	ObjectSelectorModal.prototype.onConfirmSelection = function (e) {
		e.preventDefault();
		this.$target.addClass( 'changed' );

		// Update target values to reflect current selection state
		$( '.selection-id', this.$target ).val( this.$currentSelectionInput.val() );
		$( '.selection-title', this.$target ).text( this.$currentSelectionLabel.text() );

		self.parent.tb_remove();
	}

	ObjectSelectorModal.prototype.onRemoveSelection = function (e) {
		e.preventDefault();
		this.$target.addClass( 'changed' );

		// Update target values to reflect empty selection
		$( '.selection-id', this.$target ).val( '' );
		$( '.selection-title', this.$target ).text( '[Select New]' );

		self.parent.tb_remove();
	}

	// Create modal instances
	$( '.object-selector-modal' ).each( function() {
		var $modal  = $( this ),
			id      = $modal.attr('id');

		modals[id] = new ObjectSelectorModal(id);
	});

	// Listen for opening of post selection modals
	$( '.select-object' ).on( 'click', function () {
		var $target = $( this ).closest( '.object-selector-group' ),
			modalId = $target.data( 'selectorId' );

		// Set the modal target
		modals[modalId].setTarget( $target );
	});

} );