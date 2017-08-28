/*! WP Pretty Fields - v1.1.0
 * https://alessandrotesoro.me
 * Copyright (c) 2015; * Licensed GPLv2+ */
jQuery(document).ready(function($){

	// Run Single Image Media Editor
    mediaControl.init();
	// Run Gallery Editor
    GalleryControl.init();
    // Run Tabs Controller
    TabControl.init();

    // Load colorpicker if field exists
	if (jQuery('.wppf-colorpicker').length > 0 ) {
        jQuery('.wppf-colorpicker').wpColorPicker();
    }
    
});

var wppf_image_container,
    wppf_frame_title,
    wppf_button_label;

mediaControl = {
    // Initializes a new media manager or returns an existing frame.
    // @see wp.media.featuredImage.frame()
    frame: function() {
        if (this._frame)
            return this._frame;

        this._frame = wp.media({
            title: wppf_frame_title,
            frame: 'select',
            library: {
                type: 'image'
            },
            button: {
                text: wppf_button_label
            },
            multiple: false
        });

        this._frame.on('open', this.updateFrame).state('library').on('select', this.select);

        return this._frame;
    },

    select: function() {

        var media_attachment = mediaControl.frame().state().get('selection').first().toJSON();
        jQuery(wppf_image_container).val(media_attachment.url);

    },

    init: function() {
        // Handle media manager
        jQuery('body').on('click', '.wppf-open-media', function(e) {
            e.preventDefault();
            wppf_image_container = jQuery(this).data('inputid');
            wppf_frame_title = jQuery(this).data('frame');
            wppf_button_label = jQuery(this).data('button');
            mediaControl.frame().open();
        });
    }
};

// Gallery Field Control
var wppf_gallery_ids,
    wppf_get_gallery_list,
    wppf_get_gallery_field,
    wppf_window_title,
    wppf_window_button;

GalleryControl = {
    // Initializes a new media manager or returns an existing frame.
    // @see wp.media.featuredImage.frame()
    frame: function() {
        if (this._frame)
            return this._frame;

        this._frame = wp.media({
            title: wppf_window_title,
            library: {
                type: 'image'
            },
            button: {
                text: wppf_window_button
            },
            multiple: 'toggle',
        });

        this._frame.on('open', this.updateFrame).state('library').on('select', this.select);

        return this._frame;
    },

    select: function() {

        var selection = GalleryControl._frame.state().get("selection").toJSON();
        var wppf_new_ids = '';

        // Empty previous images 
        jQuery(wppf_get_gallery_list).empty();

        // Load all stuff up
        jQuery(selection).each(function(index) {
            jQuery(wppf_get_gallery_list).append('<li><input type="hidden" name="' + wppf_get_gallery_field + '[' + index + '][id]" value="' + this.id + '"><input type="hidden" name="' + wppf_get_gallery_field + '[' + index + '][url]" value="' + this.url + '"><img src="' + this.url + '"/></li>');
            wppf_new_ids += this.id + ',';
        });

        // Remove last comma
        wppf_new_ids = wppf_new_ids.substr(0, wppf_new_ids.length - 1);
        jQuery('#' + wppf_get_gallery_field).data('ids', wppf_new_ids);

    },

    updateFrame: function() {

        var selection = GalleryControl.frame().state().get('selection');
        selection.reset();

        // Grab all ids from gallery
        var all_ids = '';
        if (wppf_gallery_ids) {
            var all_ids = wppf_gallery_ids.split(',');
        }

        // Add to selection
        if (all_ids !== '') {
            jQuery.each(all_ids, function(index, value) {
                var attachment = wp.media.attachment(value);
                attachment.fetch();
                selection.add(attachment ? [attachment] : []);
            });
        }

    },

    init: function() {
        // Handle media manager
        jQuery('body').on('click', '.wppf-open-gallery', function(e) {
            e.preventDefault();
            // Get the gallery id and details
            wppf_get_gallery_field = jQuery(this).attr('id');
            wppf_gallery_ids = jQuery(this).data('ids');
            wppf_get_gallery_list = jQuery(this).data('list');
            wppf_window_title = jQuery(this).data('title');
            wppf_window_button = jQuery(this).data('button');
            GalleryControl.frame().open();
        });
        jQuery(".wppf-delete-gallery").click(function(e) {
            e.preventDefault();

            var wppf_delete_message = jQuery(this).data('del');
            var wppf_confirm_delete = confirm(wppf_delete_message);
            if (wppf_confirm_delete == true) {
                // Get the gallery list element and empty it
                wppf_delete_list = jQuery(this).data('list');
                jQuery(wppf_delete_list).empty();

                // Get the ids of the gallery trigger and empty it
                wppf_trigger_gallery = jQuery(this).parent("div").prev("div.action-left").find("a");
                wppf_gallery_ids = jQuery(wppf_trigger_gallery).data('ids');
                jQuery(wppf_trigger_gallery).data('ids', null);
            }

        });
    }
};

// TabController
var wppf_get_tabs_list;

TabControl = {

    init: function() {

        // Find The Metabox
        wppf_get_metabox = jQuery("body").find('.wppf_metabox');

        jQuery(wppf_get_metabox).each(function(index, value) {

            // Get the ID
            var metabox_id = '#' + value.id;

            // Get all the tabs fields
            wppf_get_tabs_list = jQuery(metabox_id).find('.wppf-tab-field');

            // Proceed if theres any tabs
            if (jQuery(wppf_get_tabs_list).length) {
                TabControl.add_tab_group(metabox_id);
                TabControl.add_single_tab(metabox_id, wppf_get_tabs_list);
                TabControl.run_tabs(metabox_id);
            }

        });

    },

    // Appends the tabs wrapper at the
    // beginning of the metabox
    add_tab_group: function(metabox_id) {

        var metabox_table = jQuery(metabox_id).find('table.wppf-options-table');
        jQuery(metabox_table).parent().addClass('wppf-table-wrap').before('<ul class="wppf-data-tabs wppf-tabs" style=""></ul>');

    },

    // Loop through the tabs and add <li> tag into the list
    add_single_tab: function(metabox_id, wppf_get_tabs_list) {

        jQuery(wppf_get_tabs_list).each(function(index, value) {

            var the_tab = value.id
            var tab_id = '#' + the_tab;
            var tab_name = jQuery(tab_id).data('name');
            var tab_classes = 'wppf-single-tab';
            var tab_container_id = tab_id.replace('wppf-tab-', '');

            var tab_template = '<li class="' + tab_classes + '"><a href="#wppf-group-' + the_tab + '">' + tab_name + '</a></li>';
            var tabs_container = jQuery(metabox_id).find('ul.wppf-data-tabs');

            jQuery(tabs_container).append(tab_template);

            // Create the fields group
            TabControl.create_group(tab_container_id, the_tab);

        });

    },

    // Wrap all the fields into a group
    create_group: function(tab_container_id, the_tab) {
        jQuery(tab_container_id).nextUntil('.wppf-type-tab').wrapAll('<tbody class="wppf-single-tab-group" id="wppf-group-' + the_tab + '"></tbody>');
        jQuery('.wppf-options-table tbody:first-child').children().unwrap();
    },

    // Run tabs
    run_tabs: function(metabox_id) {
        jQuery(metabox_id).tabs();
    }

};