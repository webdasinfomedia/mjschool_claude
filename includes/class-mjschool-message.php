<?php
/**
 * School Management Message Management Class.
 *
 * This file contains the Mjschool_Message class, which handles
 * CRUD operations using custom database tables.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Mjschool_Message Class
 *
 * Handles all leave-related operations for the mjschool plugin,
 * including adding, editing, fetching, approving, rejecting, and deleting leave records.
 * It also manages email, SMS, and push notifications for these actions.
 *
 * @since 1.0.0
 */
class Mjschool_Message
{
    /**
     * Retrieves all replies for a given message thread.
     *
     * @since 1.0.0
     *
     * @param int $tid Message ID.
     *
     * @return array List of reply records.
     */
    public function mjschool_get_all_replies( $tid ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_message_replies';
        $user_id    = intval( $tid );
        $query      = $wpdb->prepare( "SELECT * FROM $table_name WHERE message_id = %d GROUP BY message_id, sender_id, message_comment ORDER BY id ASC", $user_id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $wpdb->get_results( $query );
    }

    /**
     * Retrieve all message replies for a given message ID (frontend use).
     *
     * @since 1.0.0
     * @param int $id Message ID.
     * @return array List of reply objects.
     */
    public function mjschool_get_all_replies_frontend( $id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_message_replies';
        $user_id    = intval( $id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $result = $wpdb->get_results( $wpdb->prepare( "SELECT *  FROM $table_name where message_id = %d", $user_id ) );
    }

    /**
     * Delete a single reply record from the replies table.
     *
     * @since 1.0.0
     * @param int $id Reply ID.
     * @return int|false Number of rows deleted or false on failure.
     */
    public function mjschool_delete_reply( $id ) {
        global $wpdb;
        $table_name     = $wpdb->prefix . 'mjschool_message_replies';
        $reply_id['id'] = intval( $id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $result = $wpdb->delete( $table_name, $reply_id );
    }

    /**
     * Count total unread messages and replies for the current user.
     *
     * @since 1.0.0
     * @param int $user_id User ID.
     * @return int Total unread message count.
     */
    public function mjschool_count_reply_item( $user_id ) {
        global $wpdb;
        $tbl_name                 = $wpdb->prefix . 'mjschool_message';
        $mjschool_message_replies = $wpdb->prefix . 'mjschool_message_replies';
        $user_id                  = get_current_user_id();
        $id                       = intval( $user_id );
        // Query for inbox/sent box messages.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $inbox_sent_box = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $tbl_name WHERE (receiver = %d AND sender != %d) AND post_id = %d AND status = 0", $user_id, $user_id, $id ) );
        // Query for reply messages.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $reply_msg = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $mjschool_message_replies WHERE receiver_id = %d AND message_id = %d AND (status = 0 OR status IS NULL)", $user_id, $id ) );
        // Count total messages.
        $count_total_message = count( $inbox_sent_box ) + count( $reply_msg );
        return $count_total_message;
    }
    /**
     * Retrieves all students belonging to the same class as the specified student.
     *
     * @since 1.0.0
     *
     * @param int $id Student ID.
     *
     * @return array List of WP_User classmate objects.
     */
    public function mjschool_get_teacher_class_student( $id ) {
        $student_id = absint( $id );
        $meta_val   = get_user_meta( $student_id, 'class_name', true );

        $meta_query_result = get_users(
            array(
                'meta_key'   => 'class_name',
                'meta_value' => $meta_val,
            )
        );

        return $meta_query_result;
    }

    /**
     * Sends a reply message, uploads attachments, stores the message in the database,
     * and triggers email notifications if enabled.
     *
     * @since 1.0.0
     *
     * @param array $data Message data including receiver IDs and attachments.
     *
     * @return int|false Insert result or false.
     */
    public function mjschool_send_replay_message( $data ) {
        global $wpdb;
        $table_mjschool_message_replies        = $wpdb->prefix . 'mjschool_message_replies';
        $upload_docs_array = array();
        
        // Validate and sanitize file uploads
        if ( ! empty( $_FILES['message_attachment']['name'] ) && is_array( $_FILES['message_attachment']['name'] ) ) {
            $count_array = count( $_FILES['message_attachment']['name'] );
            
            for ( $a = 0; $a < $count_array; $a++ ) {
                // Validate file exists and has no error
                if ( isset( $_FILES['message_attachment']['error'][ $a ] ) && 
                    $_FILES['message_attachment']['error'][ $a ] === UPLOAD_ERR_OK ) {
                    
                    $document_array = array(
                        'name'     => isset( $_FILES['message_attachment']['name'][ $a ] ) ? sanitize_file_name( $_FILES['message_attachment']['name'][ $a ] ) : '',
                        'type'     => isset( $_FILES['message_attachment']['type'][ $a ] ) ? sanitize_mime_type( $_FILES['message_attachment']['type'][ $a ] ) : '',
                        'tmp_name' => isset( $_FILES['message_attachment']['tmp_name'][ $a ] ) ? $_FILES['message_attachment']['tmp_name'][ $a ] : '',
                        'error'    => isset( $_FILES['message_attachment']['error'][ $a ] ) ? absint( $_FILES['message_attachment']['error'][ $a ] ) : UPLOAD_ERR_NO_FILE,
                        'size'     => isset( $_FILES['message_attachment']['size'][ $a ] ) ? absint( $_FILES['message_attachment']['size'][ $a ] ) : 0,
                    );
                    
                    $get_file_name = $document_array['name'];
                    
                    if ( ! empty( $document_array['name'] ) ) {
                        $upload_result = mjschool_load_documets_new( $document_array, $document_array, $get_file_name );
                        if ( $upload_result ) {
                            $upload_docs_array[] = $upload_result;
                        }
                    }
                }
            }
        }
        
        $upload_docs_array_filter = array_filter( $upload_docs_array );
        $attachment               = ! empty( $upload_docs_array_filter ) ? implode( ',', $upload_docs_array_filter ) : '';
        
        $result = '';
        
        if ( ! empty( $data['receiver_id'] ) && is_array( $data['receiver_id'] ) ) {
            foreach ( $data['receiver_id'] as $receiver_id ) {
                $receiver_id = absint( $receiver_id );
                
                $messagedata = array(
                    'message_id'         => isset( $data['message_id'] ) ? absint( $data['message_id'] ) : 0,
                    'sender_id'          => isset( $data['user_id'] ) ? absint( $data['user_id'] ) : 0,
                    'receiver_id'        => $receiver_id,
                    'message_comment'    => isset( $data['replay_message_body'] ) ? sanitize_textarea_field( wp_unslash( $data['replay_message_body'] ) ) : '',
                    'message_attachment' => $attachment,
                    'status'             => 0,
                    'created_date'       => current_time( 'mysql' ),
                );
                
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                $result = $wpdb->insert( $table_mjschool_message_replies, $messagedata );
                
                if ( $result ) {
                    $mjschool_name             = sanitize_text_field( get_option( 'mjschool_name' ) );
                    $SubArr['{{school_name}}'] = $mjschool_name;
                    $SubArr['{{from_mail}}']   = mjschool_get_display_name( isset( $data['user_id'] ) ? absint( $data['user_id'] ) : 0 );
                    $MailSub                   = mjschool_string_replacement( $SubArr, get_option( 'mjschool_message_received_mailsubject' ) );
                    
                    $user_info = get_userdata( $receiver_id );
                    if ( $user_info ) {
                        $to = sanitize_email( $user_info->user_email );
                        
                        $MailBody                      = get_option( 'mjschool_message_received_mailcontent' );
                        $MesArr['{{receiver_name}}']   = mjschool_get_display_name( $receiver_id );
                        $MesArr['{{message_content}}'] = isset( $data['replay_message_body'] ) ? sanitize_textarea_field( wp_unslash( $data['replay_message_body'] ) ) : '';
                        $MesArr['{{school_name}}']     = $mjschool_name;
                        $messg                         = mjschool_string_replacement( $MesArr, $MailBody );
                        
                        $headers  = '';
                        $headers .= 'From: ' . $mjschool_name . ' <noreplay@gmail.com>' . "\r\n";
                        $headers .= "MIME-Version: 1.0\r\n";
                        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
                        
                        // MAIL CONTENT WITH TEMPLATE DESIGN.
                        $email_template = mjschool_get_mail_content_with_template_design( $messg );
                        
                        if ( absint( get_option( 'mjschool_mail_notification' ) ) === 1 ) {
                            wp_mail( $to, $MailSub, $email_template, $headers );
                        }
                    }
                }
            }
        }
        
        return $result;
    }

    /**
     * Delete a message by ID.
     *
     * @since 1.0.0
     * @param string $tablenm Table name.
     * @param int $id Message ID.
     * @return int Rows affected.
     */
    public function mjschool_delete_message( $tablenm, $id ) {
        // Sanitize $_REQUEST['page'] with isset() check
        $current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
        
        mjschool_append_audit_log( esc_html__( 'Message Deleted', 'mjschool' ), null, get_current_user_id(), 'delete', $current_page );
        
        global $wpdb;
        $record_id = absint( $id );
        // Sanitize table name.
        $tablenm    = sanitize_key( $tablenm );
        $insert_table_name = $wpdb->prefix . $tablenm;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->query( $wpdb->prepare( "DELETE FROM $insert_table_name WHERE message_id = %d", $record_id ) );
        
        return $result;
    }
    /**
     * Get inbox messages for a specific user.
     *
     * @since 1.0.0
     *
     * @param int $id User ID.
     * @return array  Inbox messages.
     */
    public function mjschool_count_inbox_item( $id ) {
        global $wpdb;
        $tbl_name = $wpdb->prefix . 'mjschool_message';
        $id       = absint( $id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $inbox = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$tbl_name} WHERE receiver = %d", $id )
        );
        return $inbox;
    }

    /**
     * Count unread messages for a user from both main messages and replies.
     *
     * @since 1.0.0
     *
     * @param int $user_id User ID.
     * @return int         Total unread messages.
     */
    public function mjschool_count_unread_message( $user_id ) {
        global $wpdb;
        $tbl_name                 = $wpdb->prefix . 'mjschool_message';
        $mjschool_message_replies = $wpdb->prefix . 'mjschool_message_replies';
        $user_id                  = absint( $user_id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $inbox = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$tbl_name} WHERE receiver = %d AND sender != %d AND status = %d", $user_id, $user_id, 0 )
        );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $reply_msg = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$mjschool_message_replies} WHERE receiver_id = %d AND (status = %d OR status IS NULL)", $user_id, 0 )
        );
        $count_total_message = count( $inbox ) + count( $reply_msg );
        return $count_total_message;
    }
    
    /**
     * Count unread messages for the logged-in user by post/message ID.
     *
     * @since 1.0.0
     *
     * @param int $post_id Message post ID.
     * @return int         Number of unread messages.
     */
    public function mjschool_count_unread_message_current_user( $post_id ) {
        global $wpdb;
        $tbl_name_message      = $wpdb->prefix . 'mjschool_message';
        $wpcrm_message_replies = $wpdb->prefix . 'mjschool_message_replies';
        $post_id               = absint( $post_id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $inbox = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$tbl_name_message} WHERE post_id = %d AND status = %d", $post_id, 0 )
        );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $reply_msg = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$wpcrm_message_replies} WHERE message_id = %d AND (status = %d OR status IS NULL)", $post_id, 0 )
        );
        $count_total_message = count( $inbox ) + count( $reply_msg );
        return $count_total_message;
    }

    /**
     * Retrieve inbox messages with pagination.
     *
     * @since 1.0.0
     *
     * @param int $user_id User ID.
     * @param int $p       Offset.
     * @param int $lpm1    Limit per page.
     * @return array        Inbox message list.
     */
    public function mjschool_get_inbox_message( $user_id, $p = 0, $lpm1 = 10 ) {
        global $wpdb;
        
        $tbl_name                 = $wpdb->prefix . 'mjschool_message';
        $tbl_name_message_replies = $wpdb->prefix . 'mjschool_message_replies';
        
        // Sanitize all inputs
        $user_id = absint( $user_id );
        $p       = absint( $p );
        $lpm1    = absint( $lpm1 );
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $inbox = $wpdb->get_results(
            $wpdb->prepare( "SELECT DISTINCT b.message_id, a.* FROM {$tbl_name} a LEFT JOIN {$tbl_name_message_replies} b ON a.post_id = b.message_id WHERE (a.receiver = %d OR b.receiver_id = %d) AND (a.receiver = %d OR a.sender = %d) ORDER BY date DESC LIMIT %d, %d", $user_id, $user_id, $user_id, $user_id, $p, $lpm1 )
        );
        
        return $inbox;
    } 

    
    /**
     * Retrieve sent messages by a user.
     *
     * @since 1.0.0
     *
     * @param int $user_id User ID.
     * @param int $max     Max messages per page.
     * @param int $offset  Offset.
     * @return array        Sent messages.
     */
    public function mjschool_get_send_message( $user_id, $max = 10, $offset = 0 ) {
        $user_id = absint( $user_id );
        $max     = absint( $max );
        $offset  = absint( $offset );
        $args = array(
            'post_type'      => 'message',
            'posts_per_page' => $max,
            'offset'         => $offset,
            'post_status'    => 'publish',
            'author'         => $user_id,
        );
        $q            = new WP_Query();
        $sent_message = $q->query( $args );
        return $sent_message;
    }

    /**
     * Count total sent messages by user.
     *
     * @since 1.0.0
     *
     * @param int $id User ID.
     * @return int     Total sent messages.
     */
    public function mjschool_count_send_item( $id ) {
        global $wpdb;
        $posts = $wpdb->prefix . 'posts';
        $id    = absint( $id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $total = $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM {$posts} WHERE post_type = %s AND post_author = %d", 'message', $id )
        );
        
        return absint( $total );
    } 

    /**
     * Generate pagination HTML for frontend sentbox.
     *
     * @since 1.0.0
     *
     * @param int $totalposts Total pages.
     * @param int $p          Current page.
     * @param int $lpm1       Limit per page.
     * @param int $prev       Previous page link.
     * @param int $next       Next page link.
     * @return string         Pagination HTML.
     */
    public function mjschool_frontend_sentbox_pagination( $totalposts, $p, $lpm1, $prev, $next ) {
        $totalposts = absint( $totalposts );
        $p          = absint( $p );
        $lpm1       = absint( $lpm1 );
        $prev       = absint( $prev );
        $next       = absint( $next );
        $pagination = '';
        $form_id    = 1;
        $page_order = '';
        if ( isset( $_REQUEST['form_id'] ) ) {
            $form_id = absint( $_REQUEST['form_id'] );
        }
        if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) ) {
            $orderby    = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
            $order      = sanitize_text_field( wp_unslash( $_GET['order'] ) );
            $page_order = '&orderby=' . esc_attr( $orderby ) . '&order=' . esc_attr( $order );
        }
        if ( $totalposts > 1 ) {
            $pagination .= '<div class="btn-group">';
            if ( $p > 1 ) {
                $url         = esc_url( add_query_arg( array(
                    'dashboard' => 'mjschool_user',
                    'page'      => 'message',
                    'tab'       => 'sentbox',
                    'pg'        => $prev,
                ) ) );
                $pagination .= '<a href="' . $url . esc_attr( $page_order ) . '" class="btn btn-default"><i class="fa fa-angle-left"></i></a> ';
            } else {
                $pagination .= '<a class="btn btn-default disabled"><i class="fa fa-angle-left"></i></a> ';
            }
            if ( $p < $totalposts ) {
                $url         = esc_url( add_query_arg( array(
                    'dashboard' => 'mjschool_user',
                    'page'      => 'message',
                    'tab'       => 'sentbox',
                    'pg'        => $next,
                ) ) );
                $pagination .= ' <a href="' . $url . '" class="btn btn-default next-page"><i class="fa fa-angle-right"></i></a>';
            } else {
                $pagination .= ' <a class="btn btn-default disabled"><i class="fa fa-angle-right"></i></a>';
            }
            $pagination .= "</div>\n";
        }
        return $pagination;
    } 

    /**
     * Generate pagination HTML for inbox.
     *
     * @since 1.0.0
     *
     * @param int $totalposts Total pages.
     * @param int $p          Current page.
     * @param int $lpm1       Limit per page.
     * @param int $prev       Previous page link.
     * @param int $next       Next page link.
     * @return string         Pagination HTML.
     */
    public function mjschool_inbox_pagination( $totalposts, $p, $lpm1, $prev, $next ) {
        $totalposts = absint( $totalposts );
        $p          = absint( $p );
        $lpm1       = absint( $lpm1 );
        $prev       = absint( $prev );
        $next       = absint( $next );
        $pagination = '';
        $form_id    = 1;
        $page_order = '';
        if ( isset( $_REQUEST['form_id'] ) ) {
            $form_id = absint( $_REQUEST['form_id'] );
        }
        if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) ) {
            $orderby    = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
            $order      = sanitize_text_field( wp_unslash( $_GET['order'] ) );
            $page_order = '&orderby=' . esc_attr( $orderby ) . '&order=' . esc_attr( $order );
        }
        if ( $totalposts > 1 ) {
            $pagination .= '<div class="btn-group">';
            if ( $p > 1 ) {
                $url         = esc_url( add_query_arg( array(
                    'dashboard' => 'mjschool_user',
                    'page'      => 'message',
                    'tab'       => 'inbox',
                    'pg'        => $prev,
                ) ) );
                $pagination .= '<a href="' . $url . '" class="btn btn-default"><i class="fa fa-angle-left"></i></a> ';
            } else {
                $pagination .= '<a class="btn btn-default disabled"><i class="fa fa-angle-left"></i></a> ';
            }
            if ( $p < $totalposts ) {
                $url         = esc_url( add_query_arg( array(
                    'dashboard' => 'mjschool_user',
                    'page'      => 'message',
                    'tab'       => 'inbox',
                    'pg'        => $next,
                ) ) );
                $pagination .= ' <a href="' . $url . '" class="btn btn-default next-page"><i class="fa fa-angle-right"></i></a>';
            } else {
                $pagination .= ' <a class="btn btn-default disabled"><i class="fa fa-angle-right"></i></a>';
            }
            $pagination .= "</div>\n";
        }
        return $pagination;
    }

    /**
     * Retrieve a single message by message ID.
     *
     * @since 1.0.0
     *
     * @param int $id Message ID.
     * @return object Message record.
     */
    public function mjschool_get_message_by_id( $id ) {
        global $wpdb;
        
        $table_mjschool_message = $wpdb->prefix . 'mjschool_message';
        $id         = absint( $id );
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $retrieve_subject = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table_mjschool_message} WHERE message_id = %d", $id )
        );
        
        return $retrieve_subject;
    }

}