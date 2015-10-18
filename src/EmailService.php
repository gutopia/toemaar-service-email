<?php
/**
 * Email package that provides a easy email templateing service.
 * The email is constructed and sent of through the configured
 * transport protocol.
 *
 * Toemaar (http://code.grasvezel.nl/)
 *
 * @link      http://code.grasvezel.nl/
 * @package   Toemaar/Service/Email
 * @copyright Copyright (c) 2005-2015 Toemaar. (http://www.toemaar.nl)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Toemaar\Email;

use Zend\Http\Request;
use Zend\Mime\Mime;
use Zend\Mime\Part;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Transport\File;
use Zend\Mail\Transport\FileOptions;
use Zend\Mail\Transport\Smtp;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 * Email package that provides a easy email templateing service.
 * The email is constructed and sent of through the configured 
 * transport protocol.
 * 
 * @author Jason Lentink <jason@toemaar.nl>
 *
 */
class EmailService implements ServiceManagerAwareInterface
{
    
    /**
     * 
     * @var string
     */
    protected $_templatesRoot = 'data/email-templates/';
    
    /**
     * 
     * @var ServiceLocatorInterface
     */
    protected $_serviceManager = null;
    
    /**
     * Path to the email template from the root of the project
     * 
     * @var Template
     */
    protected $_template = null;
    
    /**
     * Email meta data
     * @var \stdClass
     */
    protected $_metaData = null;
    
    /**
     * I18n identifier 
     * 
     * @var string
     */
    protected $_i18n = null;
    
    /**
     * The files  that need to be attached.
     * 
     * @var string[]
     */
    protected $_attachments = array();

    /**
     * @var string[]
     */
    protected $_variables = array();

    /**
     * @var string
     */
    protected $_subject = '';

    /**
     * @var string
     */
    protected $_replyTo = '';

    /**
     * @var string
     */
    protected $_fromAddress = '';

    /**
     * @var string
     */
    protected $_fromName = '';
    

    /**
     * Factory constructor
     */
    public function __construct(){
        $this->setTemplateRoot(__DIR__ . '/templates');
        $this->setTemplate('default', 'en_EN');
    }

    /**
     * Set Email subject and override the testament.
     *
     * @param string $subject
     * @return EmailService
     */
    public function setSubject($subject){
        $this->_subject = $subject;
        return $this;
    }

    /**
     * @param string $templateRoot
     *
     * @return EmailService
     * @throws Exception
     */
    public function setTemplateRoot($templateRoot) {
        if(!is_dir($templateRoot))
            throw new Exception('Template root is not found!');

        $this->_templatesRoot = $templateRoot;


        if($this->_template)
            $this->_template->setTemplateRoot($this->_templatesRoot);

        return $this;
    }
   
    /**
     * Set the template to be used.
     * 
     * @param string $template
     * @param string $i18n
     *
     * @return EmailService
     * @throws Exception*
     */
    public function setTemplate($template, $i18n){

        $this->_variables['baseurl'] = $this->constructBaseUrl();

        $this->_template = new Template();
        $this->_template
            ->setTemplateRoot($this->_templatesRoot)
            ->setI18n($i18n)
            ->setTemplate($template)
            ->setVariables($this->_variables);

        return $this;
    }

    /**
     * @param $fileLocation
     * @param null $fileName
     *
     * @return EmailService
     * @throws Exception
     */
    public function addAttachmentByLocation($fileLocation, $fileName = null){
        if(!is_file($fileLocation) || !is_readable($fileLocation))
            throw new Exception('Attachment file is not readable');
        
        if(!$fileName)    
            $fileName = basename($fileLocation);
        
        $this->_attachments[] = array('filelocation' => $fileLocation, 'filename' => $fileName);

        return $this;
    }

    /**
     * Remove all attachments references in the current email
     *
     * @return EmailService
     */
    public function removeAttachments(){
        $this->_attachments = array();
        return $this;
    }

    /**
     * Create a email based on it's theme an params
     *
     * @param string $address
     * @param string $name
     * @throws Exception
     * @return \Zend\Mail\Message
     */
    protected function _constructEmail($address, $name){

        $content = $this->_template->render();
        
        if('' == $this->_replyTo)
            $this->_replyTo = $this->_template->getTestament()->getReplyTo();

        if('' == $this->_fromName)
            $this->_fromName = $this->_template->getTestament()->getFromName();

        if('' == $this->_fromAddress)
            $this->_fromAddress = $this->_template->getTestament()->getFromAddress();

        if('' == $this->_subject)
            $this->_subject = $this->_template->getTestament()->getFromAddress();
        

        $contentParts = array();
        $partText = new Part($content->getText());
        $partText->encoding = Mime::ENCODING_QUOTEDPRINTABLE;
        $partText->type = Mime::TYPE_TEXT;
        $contentParts[] = $partText;
        
        $partHtml = new Part($content->getHtml());
        $partHtml->encoding = Mime::ENCODING_QUOTEDPRINTABLE;
        $partHtml->type = Mime::TYPE_HTML;
        $partHtml->charset = 'UTF-8';
        $contentParts[] = $partHtml;

        $alternatives = new \Zend\Mime\Message();
        $alternatives->setParts($contentParts);
        $alternativesPart = new Part($alternatives->generateMessage());
        $alternativesPart->type = "multipart/alternative; boundary=\"".$alternatives->getMime()->boundary()."\"";

        $body = new \Zend\Mime\Message();
        $body->addPart($alternativesPart);

        foreach($this->_attachments as $attachmentSrc){
            $attachment = new Part(fopen($attachmentSrc['filelocation'], 'r'));
            $attachment->filename = $attachmentSrc['filename'];
            $attachment->encoding = Mime::ENCODING_BASE64;
            $attachment->type = Mime::DISPOSITION_ATTACHMENT;
            $attachment->disposition = true;
            $body->addPart($attachment);
        }
        
        $subject = $this->_subject;
        
        foreach($this->_variables as $name => $variable){
            $subject = str_replace('{{:'.$name.':}}', $variable, $subject);
        }
        
        $message = new Message();
        $message->setSubject($subject);
        $message->setFrom($this->_fromAddress, $this->_fromName);

        if($this->_replyTo)
            $message->setReplyTo($this->_replyTo);

        $message->setBody($body);
        $message->setTo($address, $name);
        $message->setEncoding("UTF-8");

        return $message;
    }

    /**
     * @return string
     */
    protected function constructBaseUrl(){

        /** @var Request $request */
        /*
        $request = $this->getServiceManager()->get('Request');
        $uri = $request->getUri();
        $scheme = $uri->getScheme();
        $host = $uri->getHost();
        $port = $uri->getPort();
        $url = $request->getBasePath();

        if($scheme == 'http'){
            if($port == '80') {
                $port = '';
            }else {
                $port = ':' . $port;
            }
        } else if($scheme == 'https'){
            if($port == '443') {
                $port = '';
            }else {
                $port = ':' . $port;
            }
        }

        if(!$url)
            $url = '/';
*/
        $host = $scheme = $port = $url = '';
        return sprintf('%s://%s%s%s', $scheme, $host,$port, $url);
    }

    /**
     * Sent a email to a user based on the set template.
     * 
     * @param string
     * @param string $name
     * @param array $variables
     *
     * @return EmailService
     * @throws \Exception
     */
    public function send($address, $name = null, array $variables = null){
        $config = $this->getServiceManager()->get('Config')['service-email'];

        if(null !== $variables)
            $this->setVariables($variables);


        $this->setVariable('baseurl', $this->constructBaseUrl());

        $message = $this->_constructEmail($address, $name);

        switch(strtolower($config['transport'])){
            case 'sendmail':
                $transport = new Sendmail();
                break;
            case 'smtp':                
                $transport = new Smtp(new SmtpOptions($config['smtp']));
                break;
            case 'file':
                $transport = new File(new FileOptions($config['file']));
                break;
            default:
                throw new Exception('Invalid transport type: ' . $config->transport . '.');
        }
        try {
            $transport->send($message);
        }   
        catch (\Exception $e) {
            throw new \Exception('Could not send out the requested e-mail. An error occured: ' . $e->getMessage());
        }
        $this->clean();

        return $this;
    }

    /**
     * Clean all variables set in this object
     *
     * @return EmailService
     */
    public function clean(){
        $this->_subject = '';
        $this->_replyTo = '';
        $this->_fromName = '';
        $this->_fromAddress = '';
        $this->setVariables(null);
        $this->removeAttachments();

        return $this;
    }
    
    /**
     * Get a single view variable
     *
     * @param  string       $name
     * @param  mixed|null   $default (optional) default value if the variable is not present.
     * @return mixed
     */
    public function getVariable($name, $default = null)
    {
        $name = (string) $name;
        if (array_key_exists($name, $this->_variables)) {
            return $this->_variables[$name];
        }
    
        return $default;
    }
    
    /**
     * Set view variable
     *
     * @param  string $name
     * @param  mixed $value
     * @return EmailService
     */
    public function setVariable($name, $value)
    {
        $this->_variables[(string) $name] = $value;
    
        if(null !== $this->_template)
            $this->_template->setVariables($this->_variables);

        return $this;
    }
    
    /**
     * Set view variables en masse
     *
     *
     * @param  array $variables
     * @return Template
     */
    public function setVariables($variables)
    {
        $this->_variables = $variables;
        if(null !== $this->_template)
            $this->_template->setVariables($this->_variables);
    
        return $this;
    }    
    
	/* (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceManagerAwareInterface::setServiceManager()
     */
    public function setServiceManager(\Zend\ServiceManager\ServiceManager $serviceManager)
    {
        $this->_serviceManager = $serviceManager;        
    }
    
    /**
     * Grep the service manager
     * 
     * @return ServiceLocatorInterface
     */
    public function getServiceManager(){
        return $this->_serviceManager;
    }

}