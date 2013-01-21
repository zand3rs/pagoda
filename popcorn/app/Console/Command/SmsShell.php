<?php
App::import('Vendor', 'Utils/Sms');

class SmsShell extends Shell {
    public $uses = array('User', 'Bookmark');

    public function perform() {
        $this->initialize();
        $this->{array_shift($this->args)}();
    }

    public function mobile_verification() {
        $user_id = $this->args[0];
        $this->log('mobile_verification: user_id: '.$user_id, 'sms');

        $this->User->id = $user_id;
        $user = $this->User->read();

        $mobile = $user['User']['mobile'];
        $pin_code = $user['User']['pin_code']; 
        $message = "Your popcorn verification pin code is: $pin_code";

        $this->log("mobile_verification: mobile: $mobile", 'sms');
        $this->log("mobile_verification: message: $message", 'sms');

        $enabled = Configure::read('CSG.enabled');
        if ($enabled) {
            $status = Sms::send($mobile, $message);
            $this->log('mobile_verification: status: '.$status->code.': '.$status->body, 'sms');
        } else {
            $this->log('mobile_verification: gateway is disabled...', 'sms');
        }
    }

    public function download_bookmark() {
        $bookmark_id = $this->args[0];
        $this->log('download_bookmark: bookmark_id: '.$bookmark_id, 'sms');

        $this->Bookmark->id = $bookmark_id;
        $bookmark = $this->Bookmark->read();

        $mobile = $bookmark['User']['mobile'];
        $bookmark_title = $bookmark['Bookmark']['title'];
        $message = "You've just downloaded: $bookmark_title";

        $this->log("mobile: $mobile", 'sms');
        $this->log("message: $message", 'sms');

        $enabled = Configure::read('CSG.enabled');
        if ($enabled) {
            $status = Sms::send($mobile, $message, array('SUB_TYPE' => 'PUSH2.50'));
            $this->log('status: '.$status->code.': '.$status->body, 'sms');
        } else {
            $this->log('gateway is disabled...', 'sms');
        }
    }
}
