<?php

/**
 * Interface for database objects
 * Matches the framework's DatabaseObject interface
 *
 * @author Dynamic Graph Creator
 */
interface DatabaseObject
{
    /**
     * Check if an object exists by ID
     *
     * @param int $id
     * @return bool
     */
    public static function isExistent($id);

    /**
     * Get the primary key ID
     * @return int
     */
    public function getId();

    /**
     * Check if object has mandatory data
     * @return bool
     */
    public function hasMandatoryData();

    /**
     * Insert a new record
     * @return bool
     */
    public function insert();

    /**
     * Update existing record
     * @return bool
     */
    public function update();

    /**
     * Delete a record (soft delete)
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id);

    /**
     * Load record data from database
     * @return bool
     */
    public function load();

    /**
     * Parse a stdClass object into this object
     *
     * @param object $obj
     * @return bool
     */
    public function parse($obj);

    /**
     * Convert to string
     * @return string
     */
    public function __toString();
}
