#!/bin/bash

#sudo pear channel-discover pear.phpunit.de
#sudo pear channel-discover components.ez.no
#sudo pear channel-discover pear.symfony-project.com
#sudo pear install --alldeps phpunit/PHPUnit-3.5.15

sudo pear config-set auto_discover 1
sudo pear install pear.phpunit.de/PHPUnit
