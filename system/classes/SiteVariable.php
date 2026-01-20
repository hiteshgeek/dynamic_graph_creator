<?php

    Class SiteVariable implements DatabaseObject
    {

        /**
         * SiteVariable Class includes all the functionalities to manage the SiteVariables 
         * 
         * @author ASHARANI
         * @since 20140820
         * @updatedBy Sohil Gupta @UpdatedOn 21/09/2015
         */
        private $vid, $value, $type, $required, $vcid, $pattern, $created_ts, $updated_ts, $values, $title;

        function getRequired()
        {
            return $this->required;
        }

        public function getType()
        {
            return $this->type;
        }

        public function getVcid()
        {
            return $this->vcid;
        }

        public function getValues()
        {
            return $this->values;
        }

        function setRequired($required)
        {
            $this->required = $required;
        }

        public function setValues($values)
        {
            $this->values = $values;
        }

        public function setType($type)
        {
            $this->type = $type;
        }

        public function setVcid($vcid)
        {
            $this->vcid = $vcid;
        }

        public function getPattern()
        {
            return $this->pattern;
        }

        public function getCreatedTs()
        {
            return $this->created_ts;
        }

        public function getUpdatedTs()
        {
            return $this->updated_ts;
        }

        public function setPattern($pattern)
        {
            $this->pattern = $pattern;
        }

        public function setCreatedTs($created_ts)
        {
            $this->created_ts = $created_ts;
        }

        public function setUpdatedTs($updated_ts)
        {
            $this->updated_ts = $updated_ts;
        }

        public function getTitle()
        {
            return $this->title;
        }

        public function setTitle($title)
        {
            $this->title = $title;
        }

        const IMG_WIDTH_LARGE = "600";
        const IMG_HEIGHT_LARGE = "600";
        const IMG_WIDTH_SMALL = "50";
        const IMG_HEIGHT_SMALL = "50";
        const IMG_WIDTH_MEDIUM = "250";
        const IMG_HEIGHT_MEDIUM = "250";

        /**
         * 
         * @param type $size
         * @return type reterive path of variable photos 
         */
        public static function photosDir($size)
        {
            if ($size === "medium")
            {
                return SiteConfig::filesDirectory() . "variables/medium/";
            }
            elseif ($size === "large")
            {
                return SiteConfig::filesDirectory() . "variables/large/";
            }
            elseif($size == "small")
            {
                return SiteConfig::filesDirectory() . "variables/small/";
            }
            else
            {
                return SiteConfig::filesDirectory() . "variables/";
            }
        }

        /**
         * Autoloads the Complete information of an particular Site Variable if an id is passed
         * 
         * @param Integer $vid - Id of the site variable
         * 
         * @returns Boolean Whether the load was successful
         */
        public function __construct($vid = null)
        {
            if ($vid)
            {
                $this->vid = $vid;
                return $this->load();
            }

            return false;
        }

        /**
         * 
         * @return boolean check mandatory fields
         */
        public function hasMandatoryData()
        {
            if (!isset($this->value))
            {
                return false;
            }
            return true;
        }

        public function getId()
        {
            return $this->vid;
        }

        public function getValue()
        {
            return $this->value;
        }

        public function setValue($value)
        {
            $this->value = $value;
        }

        /**
         * Checks if Site Variables exists.
         * @param Integer $id Id of the Site Variable object.
         * @return boolean True if the Object exists, false otherwise.
         */
        public static function isExistent($id)
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_VARIABLE . " WHERE vid = '::vid' LIMIT 1";

            $result = $db->query($sql, array("::vid" => $id));

            if (!$result || $db->resultNumRows($result) < 1)
            {
                return false;
            }

            return true;
        }

        public function insert()
        {
            
        }

        public static function hashPassword($password)
        {
            $salt = md5(BaseConfig::PASSWORD_SALT);
            return sha1($salt . $password);
        }

        public function update()
        {
            if (!$this->hasMandatoryData())
            {
                return false;
            }

            $args = array(
                "::vid" => $this->getId(),
                "::value" => $this->getValue(),
                "::vcid" => $this->getVcid(),
                "::time" => date('Y-m-d H:i:s', time()),
            );

            $sql = "UPDATE " . SystemTables::DB_TBL_VARIABLE . " SET value = '::value', updated_ts = '::time'  WHERE vid = '::vid'";

            $db = Rapidkart::getInstance()->getDB();

            return ($db->query($sql, $args)) ? true : false;
        }

        /**
         * 
         * @return boolean fetch all data corresponding to particular variable
         */
        public function load()
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_VARIABLE . " WHERE vid = '::vid' LIMIT 1";

            $result = $db->query($sql, array("::vid" => $this->vid));

            if (!$result || $db->resultNumRows($result) < 1)
            {
                return false;
            }
            $res = $db->fetchObject($result);

            foreach ($res as $key => $value)
            {
                $this->$key = $value;
            }

            return true;
        }

        /**
         * 
         * @param type $spid delete variable
         */
        public static function delete($spid)
        {
            
        }

        public function __toString()
        {
            
        }

        public function parse($obj)
        {
            
        }

    }
    