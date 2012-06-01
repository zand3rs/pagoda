<?php 
class PasswordComponent extends Component {

    /**
     * Password generator function
     *
     */
    function generate($length = 8){ 
        //$valid = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$';
        $valid = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle($valid), 0, $length); 
    }
}
?>
