<?php


class SanityTest implements DatabaseObject
{

      private $stid, $title, $description, $sql_query, $stsid, $created_ts, $updated_ts, $created_uid, $updated_uid;


      public function __construct($id = null)
      {
            if ($id !== null) {
                  $this->stid = intval($id);
                  $this->load();
            }
      }

      public static function isExistent($id) {}

      public function getId()
      {
            return $this->stid;
      }

      public function hasMandatoryData() {}

      public function insert()
      {
            $db = Rapidkart::getInstance()->getDB();
            $user = SystemConfig::getUser();

            $query = "
                  INSERT INTO 
                        " . SystemTables::DB_TBL_SANITY_TEST . " 
                  (
                        title,
                        description,
                        sql_query,
                        created_uid
                  ) VALUES 
                  (
                        '::title',
                        '::description',
                        '::sql_query',
                        '::created_uid'
                  ) 
                  ";

            $args = [
                  '::title' => $this->title,
                  '::description' => $this->description,
                  '::sql_query' => $this->sql_query,
                  '::created_uid' => $user->getId(),
            ];

            $res = $db->query($query, $args);
            if (!$res) {
                  return false;
            }
            $this->stid = $db->lastInsertId();
            return true;
      }

      public function update()
      {
            $db = Rapidkart::getInstance()->getDB();
            $user = SystemConfig::getUser();

            $query = "
                  UPDATE 
                        " . SystemTables::DB_TBL_SANITY_TEST . " 
                  SET 
                        title = '::title',
                        description = '::description',
                        sql_query = '::sql_query',
                        updated_uid = '::updated_uid' 
                  WHERE 
                        stid = '::stid' 
                  ";

            $args = [
                  '::title' => $this->title,
                  '::description' => $this->description,
                  '::sql_query' => $this->sql_query,
                  '::updated_uid' => $user->getId(),
                  '::stid' => $this->stid,
            ];

            $res = $db->query($query, $args);
            if (!$res) {
                  return false;
            }
            return true;
      }

      public static function delete($id)
      {
            $db = Rapidkart::getInstance()->getDB();
            $user = SystemConfig::getUser();

            $query = "
                  UPDATE  
                        " . SystemTables::DB_TBL_SANITY_TEST . " 
                  SET
                        stsid = 3,
                        updated_uid = '::updated_uid'
                  WHERE 
                        stid = '::stid' 
                  ";

            $args = [
                  '::stid' => intval($id),
                  '::updated_uid' => $user->getId(),
            ];

            $res = $db->query($query, $args);
            if (!$res) {
                  return false;
            }
            return true;
      }

      public function load()
      {

            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM `" . SystemTables::DB_TBL_SANITY_TEST . "` WHERE `stid` = '::stid' AND `stsid` <> 3 LIMIT 1";

            $res = $db->query($sql, ['::stid' => $this->stid]);
            if (!$res || $db->resultNumRows($res) < 1) {
                  return FALSE;
            }

            $row = $db->fetchObject($res);
            foreach ($row as $key => $value) {
                  $this->$key = $value;
            }
            return TRUE;
      }

      public function parse($obj) {}

      public function __toString() {}

      //getters and setters

      public function getTitle()
      {
            return $this->title;
      }

      public function setTitle($title)
      {
            $this->title = $title;
      }

      public function getDescription()
      {
            return $this->description;
      }

      public function setDescription($description)
      {
            $this->description = $description;
      }

      public function getSqlQuery()
      {
            return $this->sql_query;
      }

      public function setSqlQuery($sql_query)
      {
            $this->sql_query = $sql_query;
      }

      public function getStsId()
      {
            return $this->stsid;
      }

      public function setStsId($stsid)
      {
            $this->stsid = $stsid;
      }

      public function getCreatedTs()
      {
            return $this->created_ts;
      }

      public function getUpdatedTs()
      {
            return $this->updated_ts;
      }

      public function getCreatedUid()
      {
            return $this->created_uid;
      }

      public function setUpdatedUid($updated_uid)
      {
            $this->updated_uid = $updated_uid;
      }

      public function getUpdatedUid()
      {
            return $this->updated_uid;
      }

      public function setCreatedUid($created_uid)
      {
            $this->created_uid = $created_uid;
      }
}
