<?php

/**
 * Dashboard restriction's custom fields.
 *
 * @package MadeIndonesia
 */

if (!function_exists('acf_add_local_field_group')) {
	return;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_5b7e3aec57417',
		'title'                 => 'Dashboard Restrictions',
		'fields'                => array(
			array(
				'key'               => 'field_5bbc1b6aacb9d',
				'label'             => '',
				'name'              => '',
				'type'              => 'message',
				'instructions'      => '',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'message'           => 'Restrict dashboard access to specific role(s) and/or specific IP address(es).',
				'new_lines'         => 'wpautop',
				'esc_html'          => 0,
			),
			array(
				'key'               => 'field_5bbc1819f8e71',
				'label'             => 'Enable role restriction',
				'name'              => 'enable_role_restriction',
				'type'              => 'true_false',
				'instructions'      => '',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'message'           => 'Administrator and Manager always can access the dashboard',
				'default_value'     => 0,
				'ui'                => 1,
				'ui_on_text'        => '',
				'ui_off_text'       => '',
			),
			array(
				'key'               => 'field_5bbc184b5c8e6',
				'label'             => 'Allowed roles',
				'name'              => 'allowed_roles',
				'type'              => 'repeater',
				'instructions'      => '',
				'required'          => 0,
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_5bbc1819f8e71',
							'operator' => '==',
							'value'    => '1',
						),
					),
				),
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'collapsed'         => '',
				'min'               => 0,
				'max'               => 0,
				'layout'            => 'table',
				'button_label'      => 'Add Role',
				'sub_fields'        => array(
					array(
						'key'               => 'field_5bbc188c5c8e7',
						'label'             => 'Role',
						'name'              => 'role',
						'type'              => 'select',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'choices' => array(
							'rolename' => 'Role Name',
						),
						'default_value' => array(),
						'placeholder'   => 'Select Role',
						'prepend'       => '',
						'append'        => '',
						'maxlength'     => '',
						'ajax'          => 0,
						'return_format' => 'value',
						'ui'            => 0,
						'allow_null'    => 0,
						'multiple'      => 0,
					),
				),
			),
			array(
				'key'               => 'field_5b7e3b09f9037',
				'label'             => 'Enable IP restriction',
				'name'              => 'enable_ip_restriction',
				'type'              => 'true_false',
				'instructions'      => '',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'message'           => '',
				'default_value'     => 0,
				'ui'                => 1,
				'ui_on_text'        => '',
				'ui_off_text'       => '',
			),
			array(
				'key'               => 'field_5b7e3b54f9038',
				'label'             => 'Allowed IP',
				'name'              => 'allowed_ips',
				'type'              => 'repeater',
				'instructions'      => '',
				'required'          => 0,
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_5b7e3b09f9037',
							'operator' => '==',
							'value'    => '1',
						),
					),
				),
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'collapsed'         => '',
				'min'               => 0,
				'max'               => 0,
				'layout'            => 'table',
				'button_label'      => 'Add IP',
				'sub_fields'        => array(
					array(
						'key'               => 'field_5b7e8846e8125',
						'label'             => 'Location',
						'name'              => 'location',
						'type'              => 'text',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'default_value'     => '',
						'placeholder'       => '',
						'prepend'           => '',
						'append'            => '',
						'maxlength'         => '',
					),
					array(
						'key'               => 'field_5b7e3b93f9039',
						'label'             => 'IP',
						'name'              => 'ip',
						'type'              => 'text',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'default_value'     => '',
						'placeholder'       => '',
						'prepend'           => '',
						'append'            => '',
						'maxlength'         => '',
					),
				),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'dashboard-restrictions',
				),
			),
		),
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen'        => '',
		'active'                => 1,
		'description'           => '',
	)
);
