ALTER TABLE auser_session
    MODIFY mac_addr VARCHAR(255) DEFAULT '',
    MODIFY fcm_token VARCHAR(255) DEFAULT '',
    MODIFY fcmskid INT(11) DEFAULT 0,
    MODIFY outlet_chkid INT(11) DEFAULT 0,
    MODIFY licid INT(11) DEFAULT 0;