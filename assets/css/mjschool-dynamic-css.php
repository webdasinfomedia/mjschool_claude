<?php
$path = preg_replace( '/wp-content(?!.*wp-content).*/', '', __DIR__ );
require_once $path . 'wp-load.php';
function mjschool_my_plugin_custom_inline_styles() {
	$color = get_option( 'mjschool_system_color_code' );
	echo '<style>
        .mjschool-navigation li a {
            background-color: ' . esc_attr( $color ) . ' !important;
        }
        .mjschool-header .mjschool-logo {
            background-color: ' . esc_attr( $color ) . ' !important;
        }
        .mjschool-btn-sms-color {
            background-color: ' . esc_attr( $color ) . ' !important;
        }
        .mjschool-save-btn {
            background-color: ' . esc_attr( $color ) . ' !important;
            background: ' . esc_attr( $color ) . ';
        }
        a.mjschool-addon-button{
        background-color: ' . esc_attr( $color ) . ' !important;
        }
        .mjschool-main-sidebar #sidebar .mjschool-rs-side-menu-bgcolor{
            background-color: ' . esc_attr( $color ) . ';
        }
        #mjschool-main-sidebar-bgcolor {
            background-color: ' . esc_attr( $color ) . ' !important;
            background:' . esc_attr( $color ) . ' !important;
        }
        .mjschool-upload-image-btn {
            background-color: ' . esc_attr( $color ) . ' !important;
            border-color: ' . esc_attr( $color ) . ' !important;
        }
        .mjschool-general-setting-image-background {
            background: ' . esc_attr( $color ) . ' !important;
        }
        .steps li.current a .mjschool-step-icon, .steps li.current a:active .mjschool-step-icon, .steps .done::before, .steps li.done a .mjschool-step-icon, .steps li.done a:active .mjschool-step-icon {
            background: ' . esc_attr( $color ) . ' !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: ' . esc_attr( $color ) . '!important;
        }
        .mjschool-view-page-header-bg {
            background: ' . esc_attr( $color ) . ';
        }
        .mjschool-card-heading {
            background-color: ' . esc_attr( $color ) . ' !important;
        }
        .mjschool-qr-main-div {
            background: ' . esc_attr( $color ) . ';
        }
        .nav-tabs>li.active>a,
        .nav-tabs>li.active>a:focus {
            color: ' . esc_attr( $color ) . ' !important;
            border-bottom-color: ' . esc_attr( $color ) . ' !important;
        }
        .mjschool-class-border-div {
            border-left: 5px solid ' . esc_attr( $color ) . ' !important;
        }
        #sidebar li .submenu li span:hover {
            color: ' . esc_attr( $color ) . ';
        }
        .mjschool-download-btn a{
            background-color: ' . esc_attr( $color ) . ' !important;
        }
        .mjschool-save-attr-btn{
            background-color: ' . esc_attr( $color ) . ';
        }
        .mjschool-add-btn {
            background-color: ' . esc_attr( $color ) . ' !important;
            background: ' . esc_attr( $color ) . ';
        }
        .mjschool-invoice-table-grand-total{
            background-color: ' . esc_attr( $color ) . ' !important;
        }
        .btn-place a.dt-button{
            border: 1px solid ' . esc_attr( $color ) . '!important;
            background-color: ' . esc_attr( $color ) . '!important;
        }
        .btn-place button.dt-button{
            border: 1px solid ' . esc_attr( $color ) . '!important;
            background-color: ' . esc_attr( $color ) . '!important; 
        }
        .mjschool-attr-download-csv-btn{
            background: ' . esc_attr( $color ) . '!important;
        }
        .mjschool-inbox-tab span.mjschool-inbox-count-number{
            background-color: ' . esc_attr( $color ) . ' !important;
        }
        .mjschool-main-email-template .mjschool-accordion .accordion-item{
            border-left: 5px solid ' . esc_attr( $color ) . ' !important;
        }
        .mjschool-main-email-template .accordion-button.bg-gray{
            background-color: ' . esc_attr( $color ) . ';
        }
        #mjschool-message {
            border-left: 4px solid ' . esc_attr( $color ) . ' !important;
        }
        .dtsb-add, .dtsb-logic, .dtsb-right, .dtsb-left
        {
            background-color: ' . esc_attr( $color ) . ' !important;
            background: ' . esc_attr( $color ) . ';
        }
        .dtsb-searchBuilder div button.dtsb-add:hover, .dtsb-searchBuilder div button.dtsb-logic:hover, .dtsb-searchBuilder div button.dtsb-right:hover, .dtsb-searchBuilder div button.dtsb-left:hover {
            background-color: ' . esc_attr( $color ) . ' !important;
            cursor: pointer;
        }
        .mjschool-navigation li .active {
            background-color: #F9FDFF !important;
            color: #5B5D6E;
        }
        .mjschool-navigation li a:hover,
        .mjschool-navigation li .mjschool-droparrow:hover+a {
            background-color: #F9FDFF !important;
            color: #5B5D6E;
        }
        #sidebar .dropdown-menu li a {
            padding: 12px;
            text-decoration: none;
            background: #F2F5FA !important;
            font-style: normal;
            font-weight: normal;
            font-size: 15px;
            line-height: 22px;
            display: flex;
            align-items: center;
            color: #5B5D6E;
        }
        ul li.card-icon::marker
        {
            color: ' . esc_attr( $color ) . ' !important;
        }
        .btn-primary-prints{
            background-color: ' . esc_attr( $color ) . ' !important;
            border: ' . esc_attr( $color ) . ' !important;
        }
        .login-submit input[type="submit" i]{
            background-color: ' . esc_attr( $color ) . ' !important;
            border: ' . esc_attr( $color ) . ' !important;
        }
        .mjschool-print-id-card{
            background-color: ' . esc_attr( $color ) . ' !important;
        }
    </style>';
}
add_action( 'wp_head', 'mjschool_my_plugin_custom_inline_styles' );  // For frontend
add_action( 'admin_head', 'mjschool_my_plugin_custom_inline_styles' );