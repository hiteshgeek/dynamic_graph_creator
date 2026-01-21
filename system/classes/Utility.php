<?php

/**
 * Utility class with helper methods
 * Matches live project structure - DGC-specific functions moved to LocalUtility
 *
 * @author Dynamic Graph Creator
 */
class Utility
{
    /**
     * Send successful AJAX response
     *
     * @param string $message
     * @param mixed $data
     */
    public static function ajaxResponseTrue($message, $data = null)
    {
        $response = array(
            'success' => true,
            'message' => $message,
            'data' => $data
        );

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    /**
     * Send error AJAX response
     *
     * @param string $message
     * @param mixed $data
     */
    public static function ajaxResponseFalse($message, $data = null)
    {
        $response = array(
            'success' => false,
            'message' => $message,
            'data' => $data
        );

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    /**
     * Generate outlet options HTML for select dropdown
     * Simplified version of Rapidkart's getViewOutletList
     *
     * @param array $outlets Array of outlet objects from OutletManager::getUserCheckPoint
     * @param int $obj_bit Whether to use object methods (1) or properties (0)
     * @param mixed $filter Reference to store filter info (optional)
     * @return string HTML options string
     */
    public static function getViewOutletList($outlets, $obj_bit = 0, &$filter = null)
    {
        $html = '';

        if (empty($outlets)) {
            return $html;
        }

        foreach ($outlets as $outlet) {
            if ($obj_bit) {
                // Use getter methods
                $id = $outlet->getId();
                $name = $outlet->getName();
            } else {
                // Use properties directly
                $id = $outlet->id;
                $name = $outlet->name;
            }

            $html .= '<option value="' . htmlspecialchars($id) . '">' . htmlspecialchars($name) . '</option>';
        }

        return $html;
    }
}
