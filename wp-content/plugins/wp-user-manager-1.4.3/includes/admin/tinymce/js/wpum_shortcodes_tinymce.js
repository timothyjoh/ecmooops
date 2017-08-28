(function() {

	// Default Values
	var yes_no = [
		{text: 'Yes', value: 'yes'},
		{text: 'No', value: 'no'},
	];
	var no_yes = [
		{text: 'No', value: 'no'},
		{text: 'Yes', value: 'yes'},
	];
	var true_false = [
		{text: 'Yes', value: 'true'},
		{text: 'No', value: 'false'},
	];
	var false_true = [
		{text: 'No', value: 'false'},
		{text: 'Yes', value: 'true'},
	];

	tinymce.PluginManager.add( 'wpum_shortcodes_mce_button', function( editor, url ) {
		editor.addButton( 'wpum_shortcodes_mce_button', {
			title: 'WP User Manager Shortcodes',
			type: 'menubutton',
			icon: 'icon wpum-shortcodes-icon',
			menu: [

				/** Forms **/
				{
					text: 'Forms',
					menu: [

						/* Login Form */
						{
							text: 'Login Form',
							onclick: function() {
								editor.windowManager.open( {
									title: 'Login Form Shortcode',
									body: [
										{
											type: 'textbox',
											name: 'id',
											label: 'Form ID (optional)',
											value: ''
										},
										{
											type: 'textbox',
											name: 'label_username',
											label: 'Username label (optional)',
											value: ''
										},
										{
											type: 'textbox',
											name: 'label_password',
											label: 'Password label (optional)',
											value: ''
										},
										{
											type: 'textbox',
											name: 'label_remember',
											label: 'Remember label (optional)',
											value: ''
										},
										{
											type: 'textbox',
											name: 'label_log_in',
											label: 'Login label (optional)',
											value: ''
										},
										{
											type: 'listbox',
											name: 'profile',
											label: 'Show Profile Info',
											'values': no_yes
										},
										{
											type: 'listbox',
											name: 'login_link',
											label: 'Show login link',
											'values': no_yes
										},
										{
											type: 'listbox',
											name: 'psw_link',
											label: 'Show password link',
											'values': yes_no
										},
										{
											type: 'listbox',
											name: 'register_link',
											label: 'Show registration link',
											'values': yes_no
										}
									],
									onsubmit: function( e ) {
										editor.insertContent( '[wpum_login_form id="' + e.data.id + '" label_username="' + e.data.label_username + '" label_password="' + e.data.label_password + '" label_remember="' + e.data.label_remember + '" label_log_in="' + e.data.label_log_in + '" profile="' + e.data.profile + '" login_link="' + e.data.login_link + '" psw_link="' + e.data.psw_link + '" register_link="' + e.data.register_link + '" ]');
									}
								});
							}
						}, // End Login Form

						/* Registration form */
						{
							text: 'Registration Form',
							onclick: function() {
								editor.windowManager.open( {
									title: 'Registration Form',
									body: [
										{
											type: 'textbox',
											name: 'form_id',
											label: 'Form ID (optional)',
											value: ''
										},
										{
											type: 'listbox',
											name: 'login_link',
											label: 'Show login link',
											'values': yes_no
										},
										{
											type: 'listbox',
											name: 'psw_link',
											label: 'Show password link',
											'values': yes_no
										},
										{
											type: 'listbox',
											name: 'register_link',
											label: 'Show registration link',
											'values': no_yes
										}
									],
									onsubmit: function( e ) {
										editor.insertContent( '[wpum_register form_id="' + e.data.form_id + '" login_link="' + e.data.login_link + '" psw_link="' + e.data.psw_link + '" register_link="' + e.data.register_link + '" ]');
									}
								});
							}
						}, // End Registration form

						/* Password Recovery Form */
						{
							text: 'Password Recovery Form',
							onclick: function() {
								editor.windowManager.open( {
									title: 'Password Recovery Form',
									body: [
										{
											type: 'textbox',
											name: 'form_id',
											label: 'Form ID (optional)',
											value: ''
										},
										{
											type: 'listbox',
											name: 'login_link',
											label: 'Show login link',
											'values': yes_no
										},
										{
											type: 'listbox',
											name: 'psw_link',
											label: 'Show password link',
											'values': no_yes
										},
										{
											type: 'listbox',
											name: 'register_link',
											label: 'Show registration link',
											'values': yes_no
										}
									],
									onsubmit: function( e ) {
										editor.insertContent( '[wpum_password_recovery form_id="' + e.data.form_id + '" login_link="' + e.data.login_link + '" psw_link="' + e.data.psw_link + '" register_link="' + e.data.register_link + '" ]');
									}
								});
							}
						}, // End Password Recovery Form

					]
				}, // End Layout Section

				/** Pages **/
				{
					text: 'Pages',
					menu: [

						/* Profile Edit */
						{
							text: 'Account Page',
							onclick: function() {
								editor.insertContent( '[wpum_account]');
							}
						}, // End Profile Edit

						/* Profile */
						{
							text: 'Profiles Page',
							onclick: function() {
								editor.insertContent( '[wpum_profile]');
							}
						}, // End Profile

					]
				},

				/** Users **/
				{
					text: 'Users',
					menu: [

						/* Latest Registered Users */
						{
							text: 'Recently Registered Users',
							onclick: function() {
								editor.windowManager.open( {
									title: 'Recently Registered Users',
									body: [
										{
											type: 'textbox',
											name: 'amount',
											label: 'Users amount:',
											value: ''
										},
										{
											type: 'listbox',
											name: 'link_to_profile',
											label: 'Link to profile?',
											'values': yes_no
										}
									],
									onsubmit: function( e ) {
										editor.insertContent( '[wpum_recently_registered amount="' + e.data.amount + '" link_to_profile ="' + e.data.link_to_profile + '" ]');
									}
								});
							}
						}, // End Latest Registered Users

						/* Profile Card */
						{
							text: 'Profile Card',
							onclick: function() {
								editor.windowManager.open( {
									title: 'Profile Card',
									body: [
										{
											type: 'textbox',
											name: 'user_id',
											label: 'User ID',
											value: ''
										},
										{
											type: 'listbox',
											name: 'link_to_profile',
											label: 'Link to profile?',
											'values': yes_no
										},
										{
											type: 'listbox',
											name: 'display_buttons',
											label: 'Display buttons?',
											'values': yes_no
										}
									],
									onsubmit: function( e ) {
										editor.insertContent( '[wpum_profile_card user_id="' + e.data.user_id + '" link_to_profile ="' + e.data.link_to_profile + '" display_buttons ="' + e.data.display_buttons + '" ]');
									}
								});
							}
						}, // End Profile Card

						/* Directory */
						{
							text: 'User Directory',
							onclick: function() {
								editor.windowManager.open( {
									title: 'User Directory',
									body: [
										{
											type: 'textbox',
											name: 'id',
											label: 'Directory ID',
											value: ''
										}
									],
									onsubmit: function( e ) {
										editor.insertContent( '[wpum_user_directory id="' + e.data.id + '"]');
									}
								});
							}
						}, // End Directory

					]
				},

				/** Restrictions **/
				{
					text: 'Restrictions',
					menu: [

						/* Logged In Content */
						{
							text: 'Restrict to logged in users only',
							onclick: function() {
								editor.insertContent( '[wpum_restrict_logged_in] content here [/wpum_restrict_logged_in]');
							}
						}, // End Logout Link

						/* User ID Restriction */
						{
							text: 'Restrict to specific users only',
							onclick: function() {
								editor.windowManager.open( {
									title: 'Restrict to specific users only',
									body: [
										{
											type: 'textbox',
											name: 'ids',
											label: 'Comma separated user id(s)',
											value: '1, 55, 80'
										}
									],
									onsubmit: function( e ) {
										editor.insertContent( '[wpum_restrict_to_users ids="' + e.data.ids + '"] content here [/wpum_restrict_to_users]');
									}
								});
							}
						}, // End User ID Restriction

						/* User role Restriction */
						{
							text: 'Restrict to specific user roles only',
							onclick: function() {
								editor.windowManager.open( {
									title: 'Restrict to specific user roles only',
									body: [
										{
											type: 'textbox',
											name: 'roles',
											label: 'Comma separated user role(s)',
											value: 'subscriber, administrator'
										}
									],
									onsubmit: function( e ) {
										editor.insertContent( '[wpum_restrict_to_user_roles roles="' + e.data.roles + '"] content here [/wpum_restrict_to_user_roles]');
									}
								});
							}
						}, // End User role Restriction

					]
				},

				/* Login Link */
				{
					text: 'Login Link',
					onclick: function() {
						editor.windowManager.open( {
							title: 'Login Link',
							body: [
								{
									type:  'textbox',
									name:  'redirect',
									label: 'Redirect after login (optional)',
									value: ''
								},
								{
									type:  'textbox',
									name:  'label',
									label: 'Link Label',
									value: 'Login'
								},
							],
							onsubmit: function( e ) {
								editor.insertContent( '[wpum_login redirect="' + e.data.redirect + '" label="' + e.data.label + '" ]');
							}
						});
					}
				}, // End Login Link
				
				/* Logout Link */
				{
					text: 'Logout Link',
					onclick: function() {
						editor.windowManager.open( {
							title: 'Logout Link',
							body: [
								{
									type: 'textbox',
									name: 'redirect',
									label: 'Redirect after logout (optional)',
									value: ''
								},
								{
									type: 'textbox',
									name: 'label',
									label: 'Link Label',
									value: 'Logout'
								},
							],
							onsubmit: function( e ) {
								editor.insertContent( '[wpum_logout redirect="' + e.data.redirect + '" label="' + e.data.label + '" ]');
							}
						});
					}
				}, // End Logout Link

			]
		});
	});
})();
