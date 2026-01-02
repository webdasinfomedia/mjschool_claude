<?php
/**
 * School Management Parent Management Class.
 *
 * This file contains the Mjschool_Parent class, which handles
 * the creation, retrieval, updating, and deletion of parent records.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Manages all functionality related to parent records.
 *
 * @since 1.0.0
 */
class Mjschool_Parent
{
    /**
     * Get parent IDs linked to a student.
     *
     * @since 1.0.0
     * @param int $student_id Student ID.
     * @return array Array of parent IDs.
     */
    public function mjschool_get_student_parent_id( $student_id ) {
        $id             = absint( $student_id );
        $parent         = get_user_meta( $id, 'parent_id' );
        $parent_idarray = array();
        
        if ( ! empty( $parent ) && is_array( $parent ) && isset( $parent[0] ) && is_array( $parent[0] ) ) {
            foreach ( $parent[0] as $parent_id ) {
                $parent_idarray[] = absint( $parent_id );
            }
        }
        
        return $parent_idarray;
    }

    /**
     * Get child IDs linked to a parent account.
     *
     * @since 1.0.0
     * @param int $id Parent ID.
     * @return array Array of child IDs.
     */
    public function mjschool_get_parents_child_id( $id ) {
        $parent_id      = absint( $id );
        $parent         = get_user_meta( $parent_id, 'child' );
        $parent_idarray = array();
        
        if ( ! empty( $parent ) && is_array( $parent ) && isset( $parent[0] ) && is_array( $parent[0] ) ) {
            foreach ( $parent[0] as $child_id ) {
                $parent_idarray[] = absint( $child_id );
            }
        }
        
        return $parent_idarray;
    }
}