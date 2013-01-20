<?php
App::import('Vendor', 'Utils/Sms');

class SmsShell extends Shell {
    public $uses = array('User');

    public function perform() {
        $this->initialize();
        $this->{array_shift($this->args)}();
    }

    public function mobile_verification() {
        $user_id = $this->args[0];
        $this->log('user_id: '.$user_id, 'sms');

        $this->User->id = $user_id;
        $user = $this->User->read();

        $this->log($user, 'sms');

        $mobile = $user['User']['mobile'];
        $pin_code = $user['User']['pin_code']; 
        $message = "Your popcorn verification pin code is: $pin_code";

        $this->log("mobile: $mobile", 'sms');
        $this->log("message: $message", 'sms');

        $enabled = Configure::read('CSG.enabled');
        if ($enabled) {
            $status = Sms::send($mobile, $message);
            $this->log('status: '.$status->code.': '.$status->body, 'sms');
        } else {
            $this->log('gateway is disabled...', 'sms');
        }
    }
}
