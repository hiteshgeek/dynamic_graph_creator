<?php

    class Outlet implements DatabaseObject
    {

        private $outlid, $name, $chkid, $checkpoint_code, $address_line_1, $address_line_2, $outlet_location_link;
        private $contact, $alt_contact, $city, $stid, $ctid, $zip_code, $longitude, $latitude, $landmark;
        private $coverlid, $outlsid, $outltid;
        private $created_ts, $updated_ts;
        private $created_uid, $updated_uid;
        private $cst, $tin, $pan;
        private $sale_alid, $purchase_alid, $expense_alid;
        private $gstin;
        private $bank_name, $account_number;
        private $ifsc_code, $micr_code;
        private $branch, $cuid, $venid;
        private $company_id;
        private $account_name;
        private $is_prefix_affix;
        private $email;
        private $swift_code, $branch_code;
        private $discount_alid;
        private $pos_cuid;
        private $branch_sale_alid;
        private $gbutacomgsdid;
        private $gbutapid;
        private $commissionerate_name, $location_code, $bin_no, $gst_range, $export_office_location;
        private $is_alter;
        private $branch_purchase_alid;
        private $qr_code;
        private $is_header_show;
        private $self_sealing_number;
        private $disable_tcs;
        private $other_godown;
        private $customer_contact_no;
        /*
         * External
         */
        private $users;
        private $state;
        private $city_id;
        private $country;
        private $locality;
        private $commision;
        private $commision_percentage;
        private $msme_type;
        private $msme_no;
        private $orid;

        function __construct($outlid = NULL)
        {
            if ($outlid)
            {
                $this->outlid = $outlid;
                $this->load();
            }
        }
        
       

        public function getCustomerContactNo()
        {
            return $this->customer_contact_no;
        }

        public function setCustomerContactNo($customer_contact_no)
        {
            $this->customer_contact_no = $customer_contact_no;
            return $this;
        }

        public function getSelfSealingNumber()
        {
            return $this->self_sealing_number;
        }

        public function setSelfSealingNumber($self_sealing_number)
        {
            $this->self_sealing_number = $self_sealing_number;
            return $this;
        }

        function getCommision()
        {
            return $this->commision;
        }

        function getCommisionPercentage()
        {
            return $this->commision_percentage;
        }

        function setCommision($commision)
        {
            $this->commision = $commision;
        }

        function setCommisionPercentage($commision_percentage)
        {
            $this->commision_percentage = $commision_percentage;
        }

        function getGstin()
        {
            return $this->gstin;
        }

        function setGstin($gstin)
        {
            $this->gstin = $gstin;
        }

        function getQrCode()
        {
            return $this->qr_code;
        }

        function setQrCode($qr_code)
        {
            $this->qr_code = $qr_code;
        }

        function getName()
        {
            return $this->name;
        }

        public function getIsHeaderShow()
        {
            return $this->is_header_show;
        }

        public function setIsHeaderShow($is_header_show)
        {
            $this->is_header_show = $is_header_show;
            return $this;
        }

        function getChkid()
        {
            return $this->chkid;
        }

        function getCheckpointCode()
        {
            return $this->checkpoint_code;
        }

        function getAddressLine1()
        {
            return $this->address_line_1;
        }

        function getAddressLine2()
        {
            return $this->address_line_2;
        }

        function getContact()
        {
            return $this->contact;
        }

        function getAltContact()
        {
            return $this->alt_contact;
        }

        function getCity()
        {
            return $this->city;
        }

        function getStid()
        {
            return $this->stid;
        }

        function getCtid()
        {
            return $this->ctid;
        }

        function getZipCode()
        {
            return $this->zip_code;
        }

        function getLongitude()
        {
            return $this->longitude;
        }

        public function getCityId()
        {
            return $this->city_id;
        }

        public function setCityId($city_id)
        {
            $this->city_id = $city_id;
        }

        public function getUsers()
        {
            return $this->users;
        }

        public function getLocality()
        {
            return $this->locality;
        }

        public function getCountry()
        {
            return $this->country;
        }

        public function getState()
        {
            return $this->state;
        }

        function getOutletLocationLink()
        {
            return $this->outlet_location_link;
        }

        function getLatitude()
        {
            return $this->latitude;
        }

        function getLandmark()
        {
            return $this->landmark;
        }

        function getCoverlid()
        {
            return $this->coverlid;
        }

        function getOutlsid()
        {
            return $this->outlsid;
        }

        function getOutltid()
        {
            return $this->outltid;
        }

        function getCreatedTs()
        {
            return $this->created_ts;
        }

        function getUpdatedTs()
        {
            return $this->updated_ts;
        }

        function getCreatedUid()
        {
            return $this->created_uid;
        }

        function getUpdatedUid()
        {
            return $this->updated_uid;
        }

        function getCompanyId()
        {
            return $this->company_id;
        }

        function setCompanyId($company_id)
        {
            $this->company_id = $company_id;
        }

        function setName($name)
        {
            $this->name = $name;
        }

        function setChkid($chkid)
        {
            $this->chkid = $chkid;
        }

        function setCheckpointCode($checkpoint_code)
        {
            $this->checkpoint_code = $checkpoint_code;
        }

        function setAddressLine1($address_line_1)
        {
            $this->address_line_1 = $address_line_1;
        }

        function setAddressLine2($address_line_2)
        {
            $this->address_line_2 = $address_line_2;
        }

        function setContact($contact)
        {
            $this->contact = $contact;
        }

        function setAltContact($alt_contact)
        {
            $this->alt_contact = $alt_contact;
        }

        function setCity($city)
        {
            $this->city = $city;
        }

        function setStid($stid)
        {
            $this->stid = $stid;
        }

        function setCtid($ctid)
        {
            $this->ctid = $ctid;
        }

        function setZipCode($zip_code)
        {
            $this->zip_code = $zip_code;
        }

        function setLongitude($longitude)
        {
            $this->longitude = $longitude;
        }

        function setOutletLocationLink($outlet_location_link)
        {
            $this->outlet_location_link = $outlet_location_link;
        }

        function setLatitude($latitude)
        {
            $this->latitude = $latitude;
        }

        function setLandmark($landmark)
        {
            $this->landmark = $landmark;
        }

        function setCoverlid($coverlid)
        {
            $this->coverlid = $coverlid;
        }

        function setOutlsid($outlsid)
        {
            $this->outlsid = $outlsid;
        }

        function setOutltid($outltid)
        {
            $this->outltid = $outltid;
        }

        function setCreatedTs($created_ts)
        {
            $this->created_ts = $created_ts;
        }

        function setUpdatedTs($updated_ts)
        {
            $this->updated_ts = $updated_ts;
        }

        function setCreatedUid($created_uid)
        {
            $this->created_uid = $created_uid;
        }

        function setUpdatedUid($updated_uid)
        {
            $this->updated_uid = $updated_uid;
        }

        function getCst()
        {
            return $this->cst;
        }

        function getTin()
        {
            return $this->tin;
        }

        function getPan()
        {
            return $this->pan;
        }

        function setCst($cst)
        {
            $this->cst = $cst;
        }

        function setTin($tin)
        {
            $this->tin = $tin;
        }

        function setPan($pan)
        {
            $this->pan = $pan;
        }

        function getCuid()
        {
            return $this->cuid;
        }

        function getVenid()
        {
            return $this->venid;
        }

        function setCuid($cuid)
        {
            $this->cuid = $cuid;
        }

        function setVenid($venid)
        {
            $this->venid = $venid;
        }

        public function __toString()
        {
            
        }

        public function getId()
        {
            return $this->outlid;
        }

        function getSaleAlid()
        {
            return $this->sale_alid;
        }

        function getPurchaseAlid()
        {
            return $this->purchase_alid;
        }

        function getExpenseAlid()
        {
            return $this->expense_alid;
        }

        function setSaleAlid($sale_alid)
        {
            $this->sale_alid = $sale_alid;
        }

        function setPurchaseAlid($purchase_alid)
        {
            $this->purchase_alid = $purchase_alid;
        }

        function setExpenseAlid($expense_alid)
        {
            $this->expense_alid = $expense_alid;
        }

        function getBranch()
        {
            return $this->branch;
        }

        function setBranch($branch)
        {
            $this->branch = $branch;
        }

        function getAccountName()
        {
            return $this->account_name;
        }

        function setAccountName($account_name)
        {
            $this->account_name = $account_name;
        }

        function getIsPrefixAffix()
        {
            return $this->is_prefix_affix;
        }

        function setIsPrefixAffix($is_prefix_affix)
        {
            $this->is_prefix_affix = $is_prefix_affix;
        }

        function getEmail()
        {
            return $this->email;
        }

        function setEmail($email)
        {
            $this->email = $email;
        }

        function getSwiftCode()
        {
            return $this->swift_code;
        }

        function getBranchCode()
        {
            return $this->branch_code;
        }

        function setSwiftCode($swift_code)
        {
            $this->swift_code = $swift_code;
        }

        function setBranchCode($branch_code)
        {
            $this->branch_code = $branch_code;
        }

        function getDiscountAlid()
        {
            return $this->discount_alid;
        }

        function setDiscountAlid($discount_alid)
        {
            $this->discount_alid = $discount_alid;
        }

        function getPosCuid()
        {
            return $this->pos_cuid;
        }

        function setPosCuid($pos_cuid)
        {
            $this->pos_cuid = $pos_cuid;
        }

        function getOrid()
        {
            return $this->orid;
        }

        function setOrid($orid)
        {
            $this->orid = $orid;
        }

        function getOtherGodown()
        {
            return $this->other_godown;
        }

        function setOtherGodown($other_godown)
        {
            $this->other_godown = $other_godown;
        }

        public function hasMandatoryData()
        {
            if (!$this->name || !$this->checkpoint_code || !$this->address_line_1 || !$this->contact || !$this->city || !$this->stid || !$this->ctid || !$this->zip_code || !$this->outlsid)
            {
                return false;
            }

            return true;
        }

        public function load()
        {
            $db = Rapidkart::getInstance()->getDB();

            $args = array("::outlid" => $this->outlid, '::company_id' => BaseConfig::$company_id);
            $sql = " SELECT * FROM " . SystemTables::DB_TBL_OUTLET . " o WHERE o.outlid = '::outlid'  AND company_id ='::company_id'  LIMIT 1";
            $res = $db->query($sql, $args);

            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }

            $row = $db->fetchObject($res);
            foreach ($row as $key => $value)
            {
                $this->$key = $value;
            }
            return true;
        }

        public function loadExtra()
        {
            $db = Rapidkart::getInstance()->getDB();

            $args = array("::outlid" => $this->outlid, '::company_id' => BaseConfig::$company_id);
            $sql = " SELECT cnt.name as country,st.state as state,cov.coverid as city_id,covl.locality_name as locality,concat('[',group_concat('{\"',au.uid ,'\":\"',au.name,'\"}'),']') as users FROM " . SystemTables::DB_TBL_OUTLET . " o "
                    . " INNER JOIN " . SystemTables::DB_TBL_COUNTRY . " cnt ON (cnt.ctid = o.ctid) "
                    . " INNER JOIN " . SystemTables::DB_TBL_STATE . " st ON (st.stid = o.stid ) "
                    . " INNER JOIN " . SystemTables::DB_TBL_COVERAGE . " cov ON (cov.city LIKE o.city AND cov.company_id ='::company_id' ) "
                    . " LEFT JOIN " . SystemTables::DB_TBL_COVERAGE_LOCALITY . " covl ON (covl.coverlid = o.coverlid )"
                    . " LEFT JOIN " . SystemTables::DB_TBL_OUTLET_USER_MAPPING . " oum ON (oum.outlid = o.outlid AND oum.company_id='::company_id' )"
                    . " LEFT JOIN " . SystemTables::DB_TBL_USER . " au ON (au.uid = oum.uid AND au.company_id = '::company_id' )  "
                    . " WHERE o.outlid = '::outlid'  AND o.company_id ='::company_id' LIMIT 1";
            $res = $db->query($sql, $args);

            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }

            $row = $db->fetchObject($res);
            foreach ($row as $key => $value)
            {
                $this->$key = $value;
            }
            return true;
        }

        function getBranchSaleAlid()
        {
            return $this->branch_sale_alid;
        }

        function setBranchSaleAlid($branch_sale_alid)
        {
            $this->branch_sale_alid = $branch_sale_alid;
        }

        function getGbutacomgsdid()
        {
            return $this->gbutacomgsdid;
        }

        function setGbutacomgsdid($gbutacomgsdid)
        {
            $this->gbutacomgsdid = $gbutacomgsdid;
        }

        function getGbutapid()
        {
            return $this->gbutapid;
        }

        function setGbutapid($gbutapid)
        {
            $this->gbutapid = $gbutapid;
        }

        function getCommissionerateName()
        {
            return $this->commissionerate_name;
        }

        function getLocationCode()
        {
            return $this->location_code;
        }

        function getBinNo()
        {
            return $this->bin_no;
        }

        function getGstRange()
        {
            return $this->gst_range;
        }

        function getExportOfficeLocation()
        {
            return $this->export_office_location;
        }

        function setCommissionerateName($commissionerate_name)
        {
            $this->commissionerate_name = $commissionerate_name;
        }

        function setLocationCode($location_code)
        {
            $this->location_code = $location_code;
        }

        function setBinNo($bin_no)
        {
            $this->bin_no = $bin_no;
        }

        function setGstRange($gst_range)
        {
            $this->gst_range = $gst_range;
        }

        function setExportOfficeLocation($export_office_location)
        {
            $this->export_office_location = $export_office_location;
        }

        function getIsAlter()
        {
            return $this->is_alter;
        }

        function setIsAlter($is_alter)
        {
            $this->is_alter = $is_alter;
        }

        function getBranchPurchaseAlid()
        {
            return $this->branch_purchase_alid;
        }

        function setBranchPurchaseAlid($branch_purchase_alid)
        {
            $this->branch_purchase_alid = $branch_purchase_alid;
            return $this;
        }

        function getMsmeType()
        {
            return $this->msme_type;
        }

        function getMsmeNo()
        {
            return $this->msme_no;
        }

        function setMsmeType($msme_type)
        {
            $this->msme_type = $msme_type;
            return $this;
        }

        function setMsmeNo($msme_no)
        {
            $this->msme_no = $msme_no;
            return $this;
        }

        function getDisableTcs()
        {
            return $this->disable_tcs;
        }

        function setDisableTcs($disable_tcs)
        {
            $this->disable_tcs = $disable_tcs;
        }

        public function insert()
        {
            $db = Rapidkart::getInstance()->getDB();
            $args = array("::qr_code" => $this->qr_code, "::name" => $this->name, "::uid" => Session::loggedInUid(), "::code" => $this->checkpoint_code, "::address1" => $this->address_line_1, "::address2" => $this->address_line_2, "::contact" => $this->contact, "::city" => $this->city, "::stid" => $this->stid, "::ctid" => $this->ctid, "::zip_code" => $this->zip_code, "::outlsid" => $this->outlsid, '::outltid' => $this->outltid, "::outlet_location_link" => $this->outlet_location_link, "::longitude" => $this->longitude, "::latitude" => $this->latitude, '::coverlid' => $this->coverlid, "::alt_contact" => $this->alt_contact, '::chkid' => strlen(trim($this->chkid)) > 0 ? $this->chkid : 'NULL', '::cst' => $this->cst, '::tin' => $this->tin, '::pan' => $this->pan, '::sal' => $this->sale_alid > 0 ? $this->sale_alid : 'NULL', '::pal' => $this->purchase_alid > 0 ? $this->purchase_alid : 'NULL', '::exp' => $this->expense_alid > 0 ? $this->expense_alid : 'NULL', '::gstin' => $this->gstin, '::bank_name' => $this->bank_name, '::account_number' => $this->account_number, '::ifsc_code' => $this->ifsc_code, '::micr_code' => $this->micr_code, '::branch' => $this->branch, '::cuid' => $this->cuid > 0 ? $this->cuid : 'NULL', '::venid' => $this->venid > 0 ? $this->venid : 'NULL', '::compid' => BaseConfig::$company_id, '::commision' => $this->commision, '::cp' => $this->commision_percentage, '::an' => $this->account_name, '::email' => $this->email, '::boc' => $this->branch_code, '::swift' => $this->swift_code, '::discount' => $this->discount_alid > 0 ? $this->discount_alid : 'NULL', '::psc' => $this->pos_cuid, '::bsa' => $this->branch_sale_alid, '::details' => $this->gbutacomgsdid, '::tax' => $this->gbutapid, '::rane' => $this->commissionerate_name, '::lctin' => $this->location_code, '::bi' => $this->bin_no, '::range' => $this->gst_range, '::expor' => $this->export_office_location, '::alter' => $this->is_alter, '::bpua' => $this->branch_purchase_alid, '::is_header_show' => $this->is_header_show, '::self_sealing_number' => $this->self_sealing_number, '::msmetype' => $this->msme_type, '::msmeno' => $this->msme_no, '::tcs' => $this->disable_tcs, '::orid' => $this->orid, '::customer_contact_no' => $this->customer_contact_no);
            $sql = " INSERT INTO " . SystemTables::DB_TBL_OUTLET . " (qr_code,name,created_uid,checkpoint_code,address_line_1,address_line_2,contact,alt_contact,city,stid,ctid,zip_code,outlsid,outltid, outlet_location_link, longitude,latitude,coverlid , chkid , cst , tin , pan , sale_alid , purchase_alid, expense_alid, gstin,bank_name,account_number,ifsc_code,micr_code , branch , cuid , venid, company_id,commision,commision_percentage , account_name , email , branch_code , swift_code , discount_alid , pos_cuid , branch_sale_alid , gbutacomgsdid , gbutapid , `commissionerate_name`, `location_code`, `bin_no`, `gst_range`, `export_office_location`, is_alter , branch_purchase_alid, is_header_show, self_sealing_number, msme_type, msme_no , disable_tcs, orid, customer_contact_no) VALUES ('::qr_code','::name',::uid,'::code','::address1','::address2','::contact','::alt_contact','::city','::stid','::ctid','::zip_code','::outlsid','::outltid', '::outlet_location_link', '::longitude','::latitude' ,::coverlid , ::chkid , '::cst' , '::tin' , '::pan' , ::sal , ::pal , '::exp', '::gstin' , '::bank_name' , '::account_number' , '::ifsc_code' , '::micr_code'  , '::branch', ::cuid , ::venid, '::compid' , '::commision','::cp', '::an', '::email' , '::boc', '::swift', ::discount, '::psc' , '::bsa' , '::details'  , '::tax' , '::rane' , '::lctin' , '::bi' ,'::range' , '::expor', '::alter', '::bpua', '::is_header_show', '::self_sealing_number', '::msmetype', '::msmeno' , '::tcs', '::orid', '::customer_contact_no')";

            $res = $db->query($sql, $args);

            if (!$res)
            {
                return false;
            }

            $this->outlid = $db->lastInsertId();
            return true;
        }

        public function parse($obj)
        {
            
        }

        public function update()
        {
            $db = Rapidkart::getInstance()->getDB();
            $args = array("::id" => $this->outlid, "::qr_code" => $this->qr_code, "::name" => $this->name, "::uid" => Session::loggedInUid(), "::code" => $this->checkpoint_code, "::address1" => $this->address_line_1, "::address2" => $this->address_line_2, "::contact" => $this->contact, "::city" => $this->city, "::stid" => $this->stid, "::ctid" => $this->ctid, "::zip_code" => $this->zip_code, "::outlsid" => $this->outlsid, "::outltid" => $this->outltid, "::outlet_location_link" => $this->outlet_location_link, "::longitude" => $this->longitude, "::latitude" => $this->latitude, '::coverlid' => $this->coverlid, "::alt_contact" => $this->alt_contact, '::chkid' => strlen(trim($this->chkid)) > 0 ? $this->chkid : 'NULL', '::cst' => $this->cst, '::pan' => $this->pan, '::tin' => $this->tin, '::sal' => $this->sale_alid > 0 ? $this->sale_alid : 'NULL', '::pal' => $this->purchase_alid > 0 ? $this->purchase_alid : 'NULL', '::exp' => $this->expense_alid > 0 ? $this->expense_alid : 'NULL', '::gstin' => $this->gstin, '::bank_name' => $this->bank_name, '::account_number' => $this->account_number, '::ifsc_code' => $this->ifsc_code, '::micr_code' => $this->micr_code, '::branch' => $this->branch, '::cuid' => $this->cuid > 0 ? $this->cuid : 'NULL', '::venid' => $this->venid > 0 ? $this->venid : 'NULL', '::compid' => BaseConfig::$company_id, '::commision' => $this->commision, '::cp' => $this->commision_percentage, '::an' => $this->account_name, '::email' => $this->email, '::boc' => $this->branch_code, '::swift' => $this->swift_code, '::discount' => $this->discount_alid > 0 ? $this->discount_alid : 'NULL', '::psc' => $this->pos_cuid, '::bsa' => $this->branch_sale_alid, '::details' => $this->gbutacomgsdid, '::tax' => $this->gbutapid, '::rane' => $this->commissionerate_name, '::lctin' => $this->location_code, '::bi' => $this->bin_no, '::range' => $this->gst_range, '::expor' => $this->export_office_location, '::alter' => $this->is_alter, '::bpua' => $this->branch_purchase_alid, '::is_header_show' => $this->is_header_show, '::self_sealing_number' => $this->self_sealing_number, '::msmetype' => $this->msme_type, '::msmeno' => $this->msme_no, '::tcs' => $this->disable_tcs, '::orid' => $this->orid, '::customer_contact_no' => $this->customer_contact_no);
            $sql = " UPDATE " . SystemTables::DB_TBL_OUTLET . " SET qr_code='::qr_code', name='::name', updated_uid=::uid,checkpoint_code='::code', address_line_1='::address1',address_line_2='::address2',contact='::contact',alt_contact='::alt_contact',city='::city',stid='::stid',ctid='::ctid',zip_code='::zip_code',outlsid='::outlsid',outltid = '::outltid', outlet_location_link = '::outlet_location_link', longitude='::longitude',latitude='::latitude' , coverlid=::coverlid , chkid =::chkid , cst = '::cst' , pan = '::pan' , tin = '::tin' , sale_alid  = ::sal  , purchase_alid = ::pal , gstin = '::gstin' , bank_name = '::bank_name' , account_number = '::account_number' , ifsc_code = '::ifsc_code' , micr_code = '::micr_code' , branch = '::branch' , cuid =::cuid , venid=::venid, expense_alid = '::exp' , commision ='::commision' , commision_percentage ='::cp' , account_name = '::an' , email = '::email' , branch_code = '::boc' , swift_code = '::swift' , discount_alid = ::discount , pos_cuid = '::psc' , branch_sale_alid = '::bsa' , gbutacomgsdid = '::details' , gbutapid = '::tax' ,  commissionerate_name = '::rane' , location_code=  '::lctin' ,bin_no =  '::bi' , gst_range  = '::range' ,  export_office_location = '::expor' , is_alter = '::alter' , branch_purchase_alid = '::bpua', is_header_show ='::is_header_show', self_sealing_number = '::self_sealing_number', msme_type = '::msmetype', msme_no = '::msmeno' , disable_tcs = '::tcs' , orid = '::orid', customer_contact_no = '::customer_contact_no' WHERE outlid = '::id' AND company_id = '::compid'";
            $res = $db->query($sql, $args);
            if (!$res)
            {
                return false;
            }
            return true;
        }

        public function updateChkid()
        {
            if (!$this->hasMandatoryData())
            {
                return false;
            }

            $db = Rapidkart::getInstance()->getDB();
            $args = array("::id" => $this->outlid, "::chkid" => $this->chkid);
            $sql = " UPDATE " . SystemTables::DB_TBL_OUTLET . " SET chkid='::chkid' WHERE outlid = '::id'";
            $res = $db->query($sql, $args);
            if (!$res || $db->affectedRows() < 1)
            {
                return false;
            }
            return true;
        }

        public function updateStatus($status)
        {
            $db = Rapidkart::getInstance()->getDB();
            $args = array("::outlid" => $this->outlid, "::status" => $status, '::compid' => BaseConfig::$company_id);
            $sql = "UPDATE " . SystemTables::DB_TBL_OUTLET . " SET outlsid=::status WHERE outlid = ::outlid AND company_id = '::compid'";
            $res = $db->query($sql, $args);
            if (!$res)
            {
                return false;
            }
            return true;
        }

        public static function delete($id)
        {
            $db = Rapidkart::getInstance()->getDB();

            $args = array("::id" => $id, '::compid' => BaseConfig::$company_id);
            $sql = " UPDATE " . SystemTables::DB_TBL_OUTLET . " SET outlsid = '3'  WHERE outlid = '::id' AND company_id = '::compid'";
            $res = $db->query($sql, $args);

            if (!$res)
            {
                return false;
            }

            return true;
        }

        public static function isExistent($id)
        {
            $db = Rapidkart::getInstance()->getDB();

            $args = array("::id" => $id, '::compid' => BaseConfig::$company_id);
            $sql = " SELECT outlid FROM " . SystemTables::DB_TBL_OUTLET . " WHERE outlid = '::id' AND outlsid!='3' AND company_id = '::compid'";

            $res = $db->query($sql, $args);

            if (!$res || $db->resultNumRows() < 1)
            {
                return false;
            }

            return true;
        }

        function getBankName()
        {
            return $this->bank_name;
        }

        function getAccountNumber()
        {
            return $this->account_number;
        }

        function getIfscCode()
        {
            return $this->ifsc_code;
        }

        function getMicrCode()
        {
            return $this->micr_code;
        }

        function setBankName($bank_name)
        {
            $this->bank_name = $bank_name;
        }

        function setAccountNumber($account_number)
        {
            $this->account_number = $account_number;
        }

        function setIfscCode($ifsc_code)
        {
            $this->ifsc_code = $ifsc_code;
        }

        function setMicrCode($micr_code)
        {
            $this->micr_code = $micr_code;
        }
    }
    