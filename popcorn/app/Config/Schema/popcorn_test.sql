CREATE DATABASE popcorn_test;
GRANT ALL on popcorn_test.* to 'popcorn'@'localhost' identified by 'pagoda!';
GRANT ALL on popcorn_test.* to 'popcorn'@'127.0.0.1' identified by 'pagoda!';
GRANT ALL on popcorn_test.* to 'popcorn'@'%' identified by 'pagoda!';
