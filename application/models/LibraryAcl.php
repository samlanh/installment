<?php
class Application_Model_LibraryAcl extends Zend_Acl {
    public function __construct() {
        
        $this->add(new Zend_Acl_Resource('index'));
        
        $this->add(new Zend_Acl_Resource('book'));
        $this->add(new Zend_Acl_Resource('edit'), 'book');
        $this->add(new Zend_Acl_Resource('add'), 'book');
        
        $this->add(new Zend_Acl_Resource('authentication'));
        $this->add(new Zend_Acl_Resource('login'), 'authentication');
        $this->add(new Zend_Acl_Resource('logout'), 'authentication');
        
        $this->add(new Zend_Acl_Resource('books'));
        $this->add(new Zend_Acl_Resource('list'), 'books');
        
        $this->addRole(new Zend_Acl_Role('guest'));
        $this->addRole(new Zend_Acl_Role('user'), 'guest');
        $this->addRole(new Zend_Acl_Role('admin'), 'user');
        
        $this->allow(null, 'login');
        
        $this->deny('user', 'login');
        $this->allow('user', array('index', 'authentication', 'books'));
       
       $this->allow('admin', 'book');
        
    }
}
