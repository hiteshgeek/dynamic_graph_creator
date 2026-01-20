<?php

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

    interface UniqueIdentifier extends DatabaseObject
    {

        /**
         * @desc Method that returns the username used to identify this user
         */
        public function createFullId();

        public function getFullId();

        public function setFullId();
    }

