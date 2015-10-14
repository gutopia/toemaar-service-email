<?php

return array(
    'service_manager' => array(
        'factories' => array(
            'service-email' => '\Toemaar\Email\EmailServiceFactory',
        ),        
    ),
    'service-email' => array(
        'transport' => 'smtp',  //Set Transport options: Sendmail, Smtp, File
        'smtp' => array(
            // Config options can be found here: http://framework.zend.com/manual/current/en/modules/zend.mail.smtp.options.html
            'name'              => '',
            'host'              => '',
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
