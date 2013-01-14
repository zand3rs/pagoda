<?php
App::import('Vendor', 'Utils/Sms');

class PushShell extends Shell {

    public function main() {
        $response = Sms::send('639209813808', 'test message');
        $this->out($response->code);
        $this->out($response->body);
        $this->out($response->raw);
    }

}
