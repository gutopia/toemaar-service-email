<?php

return array(
    'service_manager' => array(
        'factories' => array(
            'service-email' => 'Service_email\EmailServiceFactory',
        ),        
    ),
    'service-email' => array(
        'transport' => 'smtp',  //Set Transport options: Sendmail, Smtp, File
        'smtp' => array(
            // Config options can be found here: http://framework.zend.com/manual/current/en/modules/zend.mail.smtp.options.html
            'name'              => '192.168.71.200',
            'host'              => '192.168.71.200',
//            'connection_class'  => 'plain',
//            'connection_config' => array(
//                'username' => 'user',
//                'password' => 'pass',
//            ),        
        ),
        'file' => array(
            // Config options can be found here: http://framework.zend.com/manual/current/en/modules/zend.mail.file.options.html
           'path'              => 'data/mail/',
            'callback'  => function (FileTransport $transport) {
                return 'Message_' . microtime(true) . '_' . mt_rand() . '.txt';
            },
        ),
    ),    
);
