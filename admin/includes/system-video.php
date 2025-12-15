<?php
/**
 * Video Tutorials and Feature Overview Page.
 *
 * Displays embedded YouTube video tutorials that guide administrators and users
 * through key features of the MjSchool plugin, such as general settings, student
 * admission, Zoom setup, attendance tracking, ID card generation, and more.
 *
 * Each video card includes:
 * - A preview thumbnail with overlay effect.
 * - A clickable title that opens the tutorial in a popup modal.
 * - Responsive layout optimized for various devices.
 *
 * @package    MjSchool
 * @subpackage MjSchool/admin/includes
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content mjschool-content-width">
		<div class="modal-content d-modal-style">
			<div class="mjschool-category-list"></div>
		</div>
	</div>
</div>
<div class="mjschool-page-inner mjschool-min-height-1631 mjschool-responsive-40px"><!--Page inner div start.-->
	<div class="mjschool-main-list"><!--Main wrapper div start.-->
		<div class="row"><!--Row div start.-->
			<div class="col-md-12"><!--Col 12 div start.-->
				<div class="mjschool-float-left-width-100px"><!--Panel white div start.-->
					<div class="row mjschool-responsinve-5px mjschool_padding_15px_10px" >
						<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-rtl-margin-bottom-15px">
							<div class="mjschool-exercise-list-card">
								<div class="row">
									<div class="col-5 col-sm-1 col-md-1 col-lg-3 col-xl-3 mjschool-main-preview-div">
										<a href="" class="mjschool-view-video-popup mjschool_position_relative"  link="<?php echo esc_url( 'https://www.youtube.com/embed/H2oDKfMVN-I?si=1kWparkE0ekoLYm3' ); ?>" title="<?php esc_attr_e( 'School Overview', 'mjschool' ); ?>">
											<img class="mjschool-system-preview" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/system-video-preview/mjschool-school-overview.jpg' ); ?>">
											<div class="mjschool-overlay-image-div">
												<img class="mjschool-overlay-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-overlay-image.png' ); ?>">
											</div>
										</a>
									</div>
									<div class="col-7 col-sm-8 col-md-8 col-lg-8 col-xl-9 mjschool_position_auto">
										<a href="" class="mjschool-view-video-popup" link="<?php echo esc_url( 'https://www.youtube.com/embed/H2oDKfMVN-I?si=1kWparkE0ekoLYm3' ); ?>" title="<?php esc_attr_e( 'School Overview', 'mjschool' ); ?>">
											<h2 class="mjschool-exercise-card-header mjschool-preview-title"><?php esc_html_e( 'School Management System Overview for WordPress', 'mjschool' ); ?></h2>
										</a>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-rtl-margin-bottom-15px">
							<div class="mjschool-exercise-list-card">
								<div class="row">
									<div class="col-5 col-sm-1 col-md-1 col-lg-3 col-xl-3 mjschool-main-preview-div">
										<a href="" class="mjschool-view-video-popup mjschool_position_relative"  link="<?php echo esc_url( 'https://www.youtube.com/embed/vCxdYKKX9es?si=DUUdlwfucUoScL-N' ); ?>" title="<?php esc_attr_e( 'General Settings', 'mjschool' ); ?>">
											<img class="mjschool-system-preview" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/system-video-preview/mjschool-general-setting.jpg' ); ?>">
											<div class="mjschool-overlay-image-div">
												<img class="mjschool-overlay-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-overlay-image.png' ); ?>">
											</div>
										</a>
									</div>
									<div class="col-7 col-sm-8 col-md-8 col-lg-8 col-xl-9 mjschool_position_auto">
										<a href="" class="mjschool-view-video-popup" link="<?php echo esc_url( 'https://www.youtube.com/embed/vCxdYKKX9es?si=DUUdlwfucUoScL-N' ); ?>" title="<?php esc_attr_e( 'General Settings', 'mjschool' ); ?>">
											<h2 class="mjschool-exercise-card-header mjschool-preview-title"><?php esc_html_e( 'How to setup General Settings in School Management System', 'mjschool' ); ?></h2>
										</a>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-rtl-margin-bottom-15px">
							<div class="mjschool-exercise-list-card">
								<div class="row">
									<div class="col-5 col-sm-1 col-md-1 col-lg-3 col-xl-3 mjschool-main-preview-div">
										<a href="" class="mjschool-view-video-popup mjschool_position_relative"  link="<?php echo esc_url( 'https://www.youtube.com/embed/Qz-hbpQkJXY?si=migIY_WmRJha3Zqh' ); ?>" title="<?php esc_attr_e( 'Student Admission Form: Step-by-Step Guide', 'mjschool' ); ?>">
											<img class="mjschool-system-preview" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/system-video-preview/mjschool-admission.jpg' ); ?>">
											<div class="mjschool-overlay-image-div">
												<img class="mjschool-overlay-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-overlay-image.png' ); ?>">
											</div>
										</a>
									</div>
									<div class="col-7 col-sm-8 col-md-8 col-lg-8 col-xl-9 mjschool_position_auto">
										<a href="" class="mjschool-view-video-popup" link="<?php echo esc_url( 'https://www.youtube.com/embed/Qz-hbpQkJXY?si=migIY_WmRJha3Zqh' ); ?>" title="<?php esc_attr_e( 'Student Admission Form: Step-by-Step Guide', 'mjschool' ); ?>">
											<h2 class="mjschool-exercise-card-header mjschool-preview-title"><?php esc_html_e( 'Student Admission Form: Step-by-Step Guide for School Management System', 'mjschool' ); ?></h2>
										</a>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-rtl-margin-bottom-15px">
							<div class="mjschool-exercise-list-card">
								<div class="row">
									<div class="col-5 col-sm-1 col-md-1 col-lg-3 col-xl-3 mjschool-main-preview-div">
										<a href="" class="mjschool-view-video-popup mjschool_position_relative" link="<?php echo esc_url( 'https://www.youtube.com/embed/wJ7D1I8zOao?si=PbzhjGNMS-cVdTFr' ); ?>" title="<?php esc_attr_e( 'Zoom Meeting Setup', 'mjschool' ); ?>">
											<img class="mjschool-system-preview" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/system-video-preview/mjschool-zoom-setup.jpg' ); ?>">
											<div class="mjschool-overlay-image-div">
												<img class="mjschool-overlay-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-overlay-image.png' ); ?>">
											</div>
										</a>
									</div>
									<div class="col-7 col-sm-8 col-md-8 col-lg-8 col-xl-9 mjschool_position_auto">
										<a href="" class="mjschool-view-video-popup" link="<?php echo esc_url( 'https://www.youtube.com/embed/wJ7D1I8zOao?si=PbzhjGNMS-cVdTFr' ); ?>" title="<?php esc_attr_e( 'Zoom Meeting Setup', 'mjschool' ); ?>">
											<h2 class="mjschool-exercise-card-header mjschool-preview-title"><?php esc_html_e( 'How to do ZOOM Setup in School Management System', 'mjschool' ); ?></h2>
										</a>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-rtl-margin-bottom-15px">
							<div class="mjschool-exercise-list-card">
								<div class="row">
									<div class="col-5 col-sm-1 col-md-1 col-lg-3 col-xl-3 mjschool-main-preview-div">
										<a href="" class="mjschool-view-video-popup mjschool_position_relative"  link="<?php echo esc_url( 'https://www.youtube.com/embed/TaO7Xh4SmXY?si=v4zQa-CmiEE0h151' ); ?>" title="<?php esc_attr_e( 'Student Attendance', 'mjschool' ); ?>">
											<img class="mjschool-system-preview" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/system-video-preview/mjschool-attendance.jpg' ); ?>">
											<div class="mjschool-overlay-image-div">
												<img class="mjschool-overlay-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-overlay-image.png' ); ?>">
											</div>
										</a>
									</div>
									<div class="col-7 col-sm-8 col-md-8 col-lg-8 col-xl-9 mjschool_position_auto">
										<a href="" class="mjschool-view-video-popup" link="<?php echo esc_url( 'https://www.youtube.com/embed/TaO7Xh4SmXY?si=v4zQa-CmiEE0h151' ); ?>" title="<?php esc_attr_e( 'Student Attendance', 'mjschool' ); ?>">
											<h2 class="mjschool-exercise-card-header mjschool-preview-title"><?php esc_html_e( 'How To Teacher Take Student\'s Attendance', 'mjschool' ); ?></h2>
										</a>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-rtl-margin-bottom-15px">
							<div class="mjschool-exercise-list-card">
								<div class="row">
									<div class="col-5 col-sm-1 col-md-1 col-lg-3 col-xl-3 mjschool-main-preview-div">
										<a href="" class="mjschool-view-video-popup mjschool_position_relative"  link="<?php echo esc_url( 'https://www.youtube.com/embed/Ed5SkDCKiu4?si=4rsfAczrulo_l8if' ); ?>" title="<?php esc_attr_e( 'Student Attendance With QR Code', 'mjschool' ); ?>">
											<img class="mjschool-system-preview" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/system-video-preview/mjschool-qr-attendance.jpg' ); ?>">
											<div class="mjschool-overlay-image-div">
												<img class="mjschool-overlay-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-overlay-image.png' ); ?>">
											</div>
										</a>
									</div>
									
									<div class="col-7 col-sm-8 col-md-8 col-lg-8 col-xl-9 mjschool_position_auto">
										<a href="" class="mjschool-view-video-popup" link="<?php echo esc_url( 'https://www.youtube.com/embed/Ed5SkDCKiu4?si=4rsfAczrulo_l8if' ); ?>" title="<?php esc_attr_e( 'Student Attendance With QR Code', 'mjschool' ); ?>">
											<h2 class="mjschool-exercise-card-header mjschool-preview-title"><?php echo esc_html_e( 'How To Teacher Take Student\'s Attendance With QR Code', 'mjschool' ); ?></h2>
										</a>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-rtl-margin-bottom-15px">
							<div class="mjschool-exercise-list-card">
								<div class="row">
									
									<div class="col-5 col-sm-1 col-md-1 col-lg-3 col-xl-3 mjschool-main-preview-div">
										<a href="" class="mjschool-view-video-popup mjschool_position_relative" link="<?php echo esc_url( 'https://www.youtube.com/embed/Nk-iedcC4Y0?si=OshxLuh2R77dCUWT' ); ?>" title="<?php esc_attr_e( 'Student ID Card', 'mjschool' ); ?>">
											<img class="mjschool-system-preview" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/system-video-preview/mjschool-idcard.jpg' ); ?>">
											<div class="mjschool-overlay-image-div">
												<img class="mjschool-overlay-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-overlay-image.png' ); ?>">
											</div>
										</a>
									</div>
									<div class="col-7 col-sm-8 col-md-8 col-lg-8 col-xl-9 mjschool_position_auto">
										<a href="" class="mjschool-view-video-popup" link="<?php echo esc_url( 'https://www.youtube.com/embed/Nk-iedcC4Y0?si=OshxLuh2R77dCUWT' ); ?>" title="<?php esc_attr_e( 'Student ID Card', 'mjschool' ); ?>">
											<h2 class="mjschool-exercise-card-header mjschool-preview-title"><?php esc_html_e( 'How to View and Print a student ID Card in School Management System', 'mjschool' ); ?></h2>
										</a>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-rtl-margin-bottom-15px">
							<div class="mjschool-exercise-list-card">
								<div class="row">
									<div class="col-5 col-sm-1 col-md-1 col-lg-3 col-xl-3 mjschool-main-preview-div">
										<a href="" class="mjschool-view-video-popup mjschool_position_relative"  link="<?php echo esc_url( 'https://www.youtube.com/embed/AqXYwh_8o04?si=w1NY42aZWl8eOvtd' ); ?>" title="<?php esc_attr_e( 'Conduct School Examination', 'mjschool' ); ?>">
											<img class="mjschool-system-preview" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/system-video-preview/mjschool-examination.jpg' ); ?>">
											<div class="mjschool-overlay-image-div">
												<img class="mjschool-overlay-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-overlay-image.png' ); ?>">
											</div>
										</a>
									</div>
									<div class="col-7 col-sm-8 col-md-8 col-lg-8 col-xl-9  mjschool_position_auto">
										<a href="" class="mjschool-view-video-popup" link="<?php echo esc_url( 'https://www.youtube.com/embed/AqXYwh_8o04?si=w1NY42aZWl8eOvtd' ); ?>" title="<?php esc_attr_e( 'Conduct School Examination', 'mjschool' ); ?>">
											<h2 class="mjschool-exercise-card-header mjschool-preview-title"><?php esc_html_e( 'How to conduct school examinations in School Management System', 'mjschool' ); ?></h2>
										</a>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-rtl-margin-bottom-15px">
							<div class="mjschool-exercise-list-card">
								<div class="row">
									<div class="col-5 col-sm-1 col-md-1 col-lg-3 col-xl-3 mjschool-main-preview-div">
										<a href="" class="mjschool-view-video-popup mjschool_position_relative"  link="<?php echo esc_url( 'https://www.youtube.com/embed/CZQzPhCPIr4?si=Hg16bHUL2gzi9xLA' ); ?>" title="Marksheet generation, Library, Hostel module">
											<img class="mjschool-system-preview" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/system-video-preview/mjschool-modules.jpg' ); ?>">
											<div class="mjschool-overlay-image-div">
												<img class="mjschool-overlay-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-overlay-image.png' ); ?>">
											</div>
										</a>
										
									</div>
									<div class="col-7 col-sm-8 col-md-8 col-lg-8 col-xl-9 mjschool_position_auto">
										<a href="" class="mjschool-view-video-popup" link="<?php echo esc_url( 'https://www.youtube.com/embed/CZQzPhCPIr4?si=Hg16bHUL2gzi9xLA' ); ?>" title="Marksheet generation, Library, Hostel module">
											<h2 class="mjschool-exercise-card-header mjschool-preview-title"><?php esc_html_e( 'Marksheet generation, Library, Hostel, Room module of School Management System', 'mjschool' ); ?></h2>
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div><!--Panel white div end-->
			</div><!--Col 12 div end-->
		</div><!--Row div  end-->
	</div><!--Main wrapper div end-->
</div><!--Page inner div end-->
